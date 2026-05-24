<?php

namespace App\Mcp\Tools;

use App\Models\Customer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CustomerN8nCsvExportTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Exporta clientes en CSV para n8n con customer_id, phone y name normalizados.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'only_with_phone' => ['nullable', 'boolean'],
        ], [
            'limit.integer' => 'El limite debe ser numerico.',
            'limit.min' => 'El limite minimo es 1.',
            'limit.max' => 'El limite maximo es 50000.',
            'only_with_phone.boolean' => 'El filtro de telefono debe ser verdadero o falso.',
        ]);

        $limit = (int) ($validated['limit'] ?? 5000);
        $onlyWithPhone = (bool) ($validated['only_with_phone'] ?? true);

        $query = Customer::query()
            ->select(['id', 'phone', 'phone2', 'contact_phone2', 'name'])
            ->orderBy('id')
            ->limit($limit);

        if ($onlyWithPhone) {
            $query->where(function (Builder $query): void {
                $query
                    ->whereNotNull('phone')
                    ->whereRaw("TRIM(phone) != ''")
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->whereNotNull('phone2')
                            ->whereRaw("TRIM(phone2) != ''");
                    })
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->whereNotNull('contact_phone2')
                            ->whereRaw("TRIM(contact_phone2) != ''");
                    });
            });
        }

        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return Response::json([
                'message' => 'No fue posible crear el CSV.',
            ]);
        }

        fputcsv($handle, ['customer_id', 'phone', 'name']);

        $query->get()->each(function (Customer $customer) use ($handle): void {
            fputcsv($handle, [
                $customer->id,
                self::getPhone3($customer->phone, $customer->phone2, $customer->contact_phone2),
                self::formatName($customer->name),
            ]);
        });

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return Response::text((string) $csv);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()->description('Cantidad maxima de clientes a exportar (1-50000).'),
            'only_with_phone' => $schema->boolean()->description('Excluir clientes sin telefono exportable. Por defecto es true.'),
        ];
    }

    private static function getPhone3(?string $phone, ?string $phone2, ?string $contactPhone2): string
    {
        foreach ([$phone, $phone2, $contactPhone2] as $candidate) {
            $digits = preg_replace('/\D+/', '', (string) $candidate);

            if (is_string($digits) && strlen($digits) >= 6) {
                return $digits;
            }
        }

        return '';
    }

    private static function formatName(?string $name): string
    {
        $normalizedName = Str::of((string) $name)
            ->squish()
            ->lower()
            ->title();

        return (string) $normalizedName;
    }
}
