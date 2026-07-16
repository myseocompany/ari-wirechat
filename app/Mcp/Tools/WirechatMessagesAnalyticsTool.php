<?php

namespace App\Mcp\Tools;

use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class WirechatMessagesAnalyticsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Analiza mensajes de Wirechat por rango de fechas, direccion, fuente, cliente y conversacion.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'range' => ['nullable', 'string', 'in:today,yesterday,weekly,monthly,last30,last60,last90,all,custom'],
            'from_date' => ['nullable', 'required_if:range,custom', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'required_if:range,custom', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'direction' => ['nullable', 'string', 'in:incoming,outgoing,all'],
            'customer_search' => ['nullable', 'string', 'max:191'],
            'message_search' => ['nullable', 'string', 'max:200'],
            'message_source_id' => ['nullable', 'integer', 'min:1'],
            'conversation_id' => ['nullable', 'integer', 'min:1'],
            'top_limit' => ['nullable', 'integer', 'min:1', 'max:25'],
            'include_recent_messages' => ['nullable', 'boolean'],
        ], [
            'range.in' => 'El rango seleccionado no es valido.',
            'from_date.date_format' => 'La fecha inicial debe tener el formato YYYY-MM-DD.',
            'from_date.required_if' => 'La fecha inicial es obligatoria para el rango personalizado.',
            'to_date.date_format' => 'La fecha final debe tener el formato YYYY-MM-DD.',
            'to_date.required_if' => 'La fecha final es obligatoria para el rango personalizado.',
            'to_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            'direction.in' => 'La direccion debe ser incoming, outgoing o all.',
            'top_limit.max' => 'El limite maximo es 25.',
        ]);

        [$fromDate, $toDate, $selectedRange] = $this->resolveDateRange($validated);
        $direction = (string) ($validated['direction'] ?? 'incoming');
        $topLimit = (int) ($validated['top_limit'] ?? 10);
        $includeRecentMessages = (bool) ($validated['include_recent_messages'] ?? false);

        $baseQuery = $this->baseQuery($validated, $fromDate, $toDate, $direction);

        $totalMessages = (clone $baseQuery)->count();
        $totalCustomers = (clone $baseQuery)
            ->whereNotNull('customers.id')
            ->distinct('customers.id')
            ->count('customers.id');
        $totalConversations = (clone $baseQuery)
            ->whereNotNull('wire_messages.conversation_id')
            ->distinct('wire_messages.conversation_id')
            ->count('wire_messages.conversation_id');
        $totalMessageSources = (clone $baseQuery)
            ->whereNotNull('message_sources.id')
            ->distinct('message_sources.id')
            ->count('message_sources.id');

        $payload = [
            'period' => [
                'range' => $selectedRange,
                'from' => $fromDate?->toDateString(),
                'to' => $toDate?->toDateString(),
            ],
            'filters' => [
                'direction' => $direction,
                'customer_search' => $validated['customer_search'] ?? null,
                'message_search' => $validated['message_search'] ?? null,
                'message_source_id' => $validated['message_source_id'] ?? null,
                'conversation_id' => $validated['conversation_id'] ?? null,
            ],
            'summary' => [
                'messages' => $totalMessages,
                'customers' => $totalCustomers,
                'conversations' => $totalConversations,
                'message_sources' => $totalMessageSources,
            ],
            'series' => [
                'daily_messages' => $this->dailyMessages($baseQuery),
                'hourly_messages' => $this->hourlyMessages($baseQuery),
            ],
            'breakdowns' => [
                'by_direction' => $this->messagesByDirection($baseQuery),
                'by_message_type' => $this->messagesByType($baseQuery, $topLimit),
                'by_message_source' => $this->messagesBySource($baseQuery, $topLimit),
                'top_conversations' => $this->topConversations($baseQuery, $topLimit),
                'top_customers' => $this->topCustomers($baseQuery, $topLimit),
            ],
        ];

        if ($includeRecentMessages) {
            $payload['recent_messages'] = $this->recentMessages($baseQuery, $topLimit);
        }

        return Response::json($payload);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'range' => $schema->string()->description('Rango predefinido: today, yesterday, weekly, monthly, last30, last60, last90, all o custom. Por defecto last30.'),
            'from_date' => $schema->string()->description('Fecha inicial YYYY-MM-DD. Solo se usa con range custom o cuando no se envia range.'),
            'to_date' => $schema->string()->description('Fecha final YYYY-MM-DD. Solo se usa con range custom o cuando no se envia range.'),
            'direction' => $schema->string()->description('Direccion de mensajes: incoming para clientes, outgoing para usuarios/fuentes o all. Por defecto incoming.'),
            'customer_search' => $schema->string()->description('Filtro opcional por nombre, telefono o negocio del cliente.'),
            'message_search' => $schema->string()->description('Filtro opcional por texto dentro del cuerpo del mensaje.'),
            'message_source_id' => $schema->integer()->description('ID opcional de la fuente/canal de mensajes.'),
            'conversation_id' => $schema->integer()->description('ID opcional de conversacion Wirechat.'),
            'top_limit' => $schema->integer()->description('Cantidad maxima de filas en rankings, de 1 a 25. Por defecto 10.'),
            'include_recent_messages' => $schema->boolean()->description('Si es true incluye una muestra de mensajes recientes con extracto del cuerpo. Por defecto false.'),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{0: Carbon|null, 1: Carbon|null, 2: string}
     */
    private function resolveDateRange(array $validated): array
    {
        $range = (string) ($validated['range'] ?? 'last30');

        if ($range === 'custom' || (isset($validated['from_date']) && isset($validated['to_date']))) {
            return [
                Carbon::createFromFormat('Y-m-d', (string) $validated['from_date'])->startOfDay(),
                Carbon::createFromFormat('Y-m-d', (string) $validated['to_date'])->endOfDay(),
                'custom',
            ];
        }

        return match ($range) {
            'today' => [now()->startOfDay(), now()->endOfDay(), $range],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), $range],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek(), $range],
            'monthly' => [now()->startOfMonth(), now()->endOfMonth(), $range],
            'last60' => [now()->subDays(59)->startOfDay(), now()->endOfDay(), $range],
            'last90' => [now()->subDays(89)->startOfDay(), now()->endOfDay(), $range],
            'all' => [null, null, $range],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay(), 'last30'],
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function baseQuery(array $validated, ?Carbon $fromDate, ?Carbon $toDate, string $direction): Builder
    {
        $customerMorph = (new Customer)->getMorphClass();
        $userMorph = (new User)->getMorphClass();

        $query = DB::table('wire_messages')
            ->leftJoin('message_source_conversations as msc', 'msc.conversation_id', '=', 'wire_messages.conversation_id')
            ->leftJoin('message_sources', 'message_sources.id', '=', 'msc.message_source_id')
            ->leftJoin('customers', function ($join) use ($customerMorph): void {
                $join
                    ->on('customers.id', '=', 'msc.customer_id')
                    ->orOn(function ($join) use ($customerMorph): void {
                        $join
                            ->on('customers.id', '=', 'wire_messages.sendable_id')
                            ->where('wire_messages.sendable_type', '=', $customerMorph);
                    });
            })
            ->leftJoin('users', function ($join) use ($userMorph): void {
                $join
                    ->on('users.id', '=', 'wire_messages.sendable_id')
                    ->where('wire_messages.sendable_type', '=', $userMorph);
            })
            ->whereNull('wire_messages.deleted_at');

        if ($fromDate !== null && $toDate !== null) {
            $query->whereBetween('wire_messages.created_at', [$fromDate, $toDate]);
        }

        if ($direction === 'incoming') {
            $query->where('wire_messages.sendable_type', $customerMorph);
        } elseif ($direction === 'outgoing') {
            $query->where('wire_messages.sendable_type', '!=', $customerMorph);
        }

        if (isset($validated['customer_search'])) {
            $searchTerm = '%'.$validated['customer_search'].'%';
            $query->where(function (Builder $query) use ($searchTerm): void {
                $query
                    ->where('customers.name', 'like', $searchTerm)
                    ->orWhere('customers.phone', 'like', $searchTerm)
                    ->orWhere('customers.phone2', 'like', $searchTerm)
                    ->orWhere('customers.contact_phone2', 'like', $searchTerm)
                    ->orWhere('customers.business', 'like', $searchTerm);
            });
        }

        if (isset($validated['message_search'])) {
            $query->where('wire_messages.body', 'like', '%'.$validated['message_search'].'%');
        }

        if (isset($validated['message_source_id'])) {
            $query->where('message_sources.id', (int) $validated['message_source_id']);
        }

        if (isset($validated['conversation_id'])) {
            $query->where('wire_messages.conversation_id', (int) $validated['conversation_id']);
        }

        return $query;
    }

    /**
     * @return array<int, array{date: string, total: int}>
     */
    private function dailyMessages(Builder $baseQuery): array
    {
        return (clone $baseQuery)
            ->selectRaw('date(wire_messages.created_at) as message_date')
            ->selectRaw('count(*) as total')
            ->groupBy('message_date')
            ->orderBy('message_date')
            ->get()
            ->map(fn ($row): array => [
                'date' => (string) $row->message_date,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array{hour: int, total: int}>
     */
    private function hourlyMessages(Builder $baseQuery): array
    {
        return (clone $baseQuery)
            ->selectRaw('hour(wire_messages.created_at) as message_hour')
            ->selectRaw('count(*) as total')
            ->groupBy('message_hour')
            ->orderBy('message_hour')
            ->get()
            ->map(fn ($row): array => [
                'hour' => (int) $row->message_hour,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array{direction: string, total: int}>
     */
    private function messagesByDirection(Builder $baseQuery): array
    {
        $customerMorph = (new Customer)->getMorphClass();

        return (clone $baseQuery)
            ->selectRaw("case when wire_messages.sendable_type = ? then 'incoming' else 'outgoing' end as direction", [$customerMorph])
            ->selectRaw('count(*) as total')
            ->groupBy('direction')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => [
                'direction' => (string) $row->direction,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array{type: string, total: int}>
     */
    private function messagesByType(Builder $baseQuery, int $limit): array
    {
        return (clone $baseQuery)
            ->selectRaw("coalesce(nullif(wire_messages.type, ''), 'sin_tipo') as message_type")
            ->selectRaw('count(*) as total')
            ->groupBy('message_type')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'type' => (string) $row->message_type,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array{message_source_id: int|null, message_source_name: string, message_source_type: string|null, total: int}>
     */
    private function messagesBySource(Builder $baseQuery, int $limit): array
    {
        return (clone $baseQuery)
            ->selectRaw('message_sources.id as message_source_id')
            ->selectRaw("coalesce(message_sources.name, 'Sin fuente') as message_source_name")
            ->selectRaw('message_sources.type as message_source_type')
            ->selectRaw('count(*) as total')
            ->groupBy('message_sources.id', 'message_sources.name', 'message_sources.type')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'message_source_id' => $row->message_source_id !== null ? (int) $row->message_source_id : null,
                'message_source_name' => (string) $row->message_source_name,
                'message_source_type' => $row->message_source_type !== null ? (string) $row->message_source_type : null,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array{conversation_id: int|null, customer_id: int|null, customer_name: string, user_name: string|null, message_source_name: string, total: int, last_message_at: string|null}>
     */
    private function topConversations(Builder $baseQuery, int $limit): array
    {
        return (clone $baseQuery)
            ->selectRaw('wire_messages.conversation_id')
            ->selectRaw('customers.id as customer_id')
            ->selectRaw("coalesce(customers.name, 'Sin cliente') as customer_name")
            ->selectRaw('users.name as user_name')
            ->selectRaw("coalesce(message_sources.name, 'Sin fuente') as message_source_name")
            ->selectRaw('count(*) as total')
            ->selectRaw('max(wire_messages.created_at) as last_message_at')
            ->groupBy('wire_messages.conversation_id', 'customers.id', 'customers.name', 'users.name', 'message_sources.name')
            ->orderByDesc('total')
            ->orderByDesc('last_message_at')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'conversation_id' => $row->conversation_id !== null ? (int) $row->conversation_id : null,
                'customer_id' => $row->customer_id !== null ? (int) $row->customer_id : null,
                'customer_name' => (string) $row->customer_name,
                'user_name' => $row->user_name !== null ? (string) $row->user_name : null,
                'message_source_name' => (string) $row->message_source_name,
                'total' => (int) $row->total,
                'last_message_at' => $row->last_message_at !== null ? (string) $row->last_message_at : null,
            ])
            ->all();
    }

    /**
     * @return array<int, array{customer_id: int|null, customer_name: string, phone: string|null, business: string|null, total: int, last_message_at: string|null}>
     */
    private function topCustomers(Builder $baseQuery, int $limit): array
    {
        return (clone $baseQuery)
            ->selectRaw('customers.id as customer_id')
            ->selectRaw("coalesce(customers.name, 'Sin cliente') as customer_name")
            ->selectRaw('customers.phone')
            ->selectRaw('customers.business')
            ->selectRaw('count(*) as total')
            ->selectRaw('max(wire_messages.created_at) as last_message_at')
            ->whereNotNull('customers.id')
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.business')
            ->orderByDesc('total')
            ->orderByDesc('last_message_at')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'customer_id' => $row->customer_id !== null ? (int) $row->customer_id : null,
                'customer_name' => (string) $row->customer_name,
                'phone' => $row->phone !== null ? (string) $row->phone : null,
                'business' => $row->business !== null ? (string) $row->business : null,
                'total' => (int) $row->total,
                'last_message_at' => $row->last_message_at !== null ? (string) $row->last_message_at : null,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, conversation_id: int|null, direction: string, type: string, body_excerpt: string|null, customer_id: int|null, customer_name: string|null, user_name: string|null, message_source_name: string|null, created_at: string}>
     */
    private function recentMessages(Builder $baseQuery, int $limit): array
    {
        $customerMorph = (new Customer)->getMorphClass();

        return (clone $baseQuery)
            ->select([
                'wire_messages.id',
                'wire_messages.conversation_id',
                'wire_messages.sendable_type',
                'wire_messages.body',
                'wire_messages.type',
                'wire_messages.created_at',
                'customers.id as customer_id',
                'customers.name as customer_name',
                'users.name as user_name',
                'message_sources.name as message_source_name',
            ])
            ->orderByDesc('wire_messages.created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'conversation_id' => $row->conversation_id !== null ? (int) $row->conversation_id : null,
                'direction' => $row->sendable_type === $customerMorph ? 'incoming' : 'outgoing',
                'type' => (string) $row->type,
                'body_excerpt' => $row->body !== null ? mb_substr((string) $row->body, 0, 180) : null,
                'customer_id' => $row->customer_id !== null ? (int) $row->customer_id : null,
                'customer_name' => $row->customer_name !== null ? (string) $row->customer_name : null,
                'user_name' => $row->user_name !== null ? (string) $row->user_name : null,
                'message_source_name' => $row->message_source_name !== null ? (string) $row->message_source_name : null,
                'created_at' => (string) $row->created_at,
            ])
            ->all();
    }
}
