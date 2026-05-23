<?php

namespace App\Mcp\Tools;

use App\Models\Action;
use App\Models\ActionType;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CrmAnalyticsSummaryTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Genera un resumen operativo del CRM con conteos clave por rango de fechas.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d'],
            'top_limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ], [
            'from_date.date_format' => 'La fecha inicial debe tener el formato YYYY-MM-DD.',
            'to_date.date_format' => 'La fecha final debe tener el formato YYYY-MM-DD.',
            'top_limit.integer' => 'El límite de tipos debe ser numérico.',
            'top_limit.min' => 'El límite mínimo de tipos es 1.',
            'top_limit.max' => 'El límite máximo de tipos es 10.',
        ]);

        $fromDate = isset($validated['from_date'])
            ? Carbon::createFromFormat('Y-m-d', (string) $validated['from_date'])->startOfDay()
            : now()->subDays(30)->startOfDay();
        $toDate = isset($validated['to_date'])
            ? Carbon::createFromFormat('Y-m-d', (string) $validated['to_date'])->endOfDay()
            : now()->endOfDay();
        $topLimit = (int) ($validated['top_limit'] ?? 5);

        $customersCount = Customer::query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        $actionsQuery = Action::query()->whereBetween('created_at', [$fromDate, $toDate]);

        $actionsCount = (clone $actionsQuery)->count();
        $pendingActionsCount = (clone $actionsQuery)
            ->whereNotNull('due_date')
            ->whereNull('delivery_date')
            ->count();

        $topActionTypeRows = (clone $actionsQuery)
            ->selectRaw('type_id, COUNT(*) as total')
            ->whereNotNull('type_id')
            ->groupBy('type_id')
            ->orderByDesc('total')
            ->limit($topLimit)
            ->get();

        $actionTypeNames = ActionType::query()
            ->whereIn('id', $topActionTypeRows->pluck('type_id')->all())
            ->pluck('name', 'id');

        $topActionTypes = $topActionTypeRows
            ->map(function ($row) use ($actionTypeNames): array {
                $typeId = (int) $row->type_id;

                return [
                    'type_id' => $typeId,
                    'type_name' => (string) ($actionTypeNames[$typeId] ?? 'Sin tipo'),
                    'total' => (int) $row->total,
                ];
            })
            ->values()
            ->all();

        return Response::json([
            'period' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'summary' => [
                'new_customers' => $customersCount,
                'actions' => $actionsCount,
                'pending_actions' => $pendingActionsCount,
                'top_action_types' => $topActionTypes,
            ],
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'from_date' => $schema->string()->description('Fecha inicial en formato YYYY-MM-DD.'),
            'to_date' => $schema->string()->description('Fecha final en formato YYYY-MM-DD.'),
            'top_limit' => $schema->integer()->description('Cantidad de tipos de acción a retornar (1-10).'),
        ];
    }
}
