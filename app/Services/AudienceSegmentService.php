<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PDO;

class AudienceSegmentService
{
    public const FIELD_CONFIG = [
        'created_at' => [
            'type' => 'date',
            'operators' => ['on', 'before', 'after', 'between'],
        ],
        'status_id' => [
            'type' => 'select',
            'operators' => ['eq', 'in'],
        ],
        'user_id' => [
            'type' => 'select',
            'operators' => ['eq', 'in'],
        ],
        'source_id' => [
            'type' => 'select',
            'operators' => ['eq', 'in'],
        ],
        'tag_id' => [
            'type' => 'select',
            'operators' => ['eq', 'in'],
        ],
        'name' => [
            'type' => 'text',
            'operators' => ['contains', 'eq'],
        ],
        'business' => [
            'type' => 'text',
            'operators' => ['contains', 'eq'],
        ],
        'city' => [
            'type' => 'text',
            'operators' => ['contains', 'eq'],
        ],
        'country' => [
            'type' => 'text',
            'operators' => ['contains', 'eq'],
        ],
        'scoring_profile' => [
            'type' => 'select',
            'operators' => ['eq', 'in'],
        ],
        'has_whatsapp' => [
            'type' => 'boolean',
            'operators' => ['eq'],
        ],
    ];

    public static function getFieldConfig(): array
    {
        return self::FIELD_CONFIG;
    }

    public function preview(array $segment, int $sampleLimit = 20): array
    {
        $query = $this->buildCustomerQuery($segment);

        $count = (clone $query)
            ->distinct('customers.id')
            ->count('customers.id');

        $sample = (clone $query)
            ->select([
                'customers.id',
                'customers.name',
                'customers.business',
                'customers.phone',
                'customers.email',
                'customers.city',
                'customers.country',
                'customers.created_at',
            ])
            ->orderByDesc('customers.created_at')
            ->limit($sampleLimit)
            ->get()
            ->map(function (Customer $customer): array {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'business' => $customer->business,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'city' => $customer->city,
                    'country' => $customer->country,
                    'created_at' => optional($customer->created_at)->toDateTimeString(),
                ];
            })
            ->values()
            ->all();

        $sqlQuery = (clone $query)
            ->select('customers.id')
            ->distinct()
            ->orderBy('customers.id')
            ->limit(5000);

        return [
            'count' => $count,
            'sql' => $this->toRawSql($sqlQuery),
            'sample' => $sample,
        ];
    }

    public function collectCustomerIds(array $segment, ?int $limit = null): Collection
    {
        $query = $this->buildCustomerQuery($segment)
            ->select('customers.id')
            ->distinct()
            ->orderBy('customers.id');

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        return $query->pluck('customers.id')->map(function ($id): int {
            return (int) $id;
        });
    }

    public function buildCustomerQuery(array $segment): Builder
    {
        $normalized = $this->normalizeSegment($segment);
        $conditions = $normalized['conditions'];

        $query = Customer::query()->select('customers.*');

        if ($conditions === []) {
            return $query;
        }

        $query->where(function (Builder $rootQuery) use ($conditions): void {
            foreach ($conditions as $index => $condition) {
                $boolean = strtoupper((string) Arr::get($condition, 'boolean', 'AND'));
                $method = $index === 0 ? 'where' : ($boolean === 'OR' ? 'orWhere' : 'where');

                $rootQuery->{$method}(function (Builder $conditionQuery) use ($condition): void {
                    $this->applyCondition($conditionQuery, $condition);
                });
            }
        });

        return $query;
    }

    private function normalizeSegment(array $segment): array
    {
        $conditions = collect(Arr::get($segment, 'conditions', []))
            ->filter(function ($condition): bool {
                return is_array($condition) && isset($condition['field']) && isset($condition['operator']);
            })
            ->map(function (array $condition): array {
                return [
                    'boolean' => strtoupper((string) Arr::get($condition, 'boolean', 'AND')),
                    'field' => (string) Arr::get($condition, 'field'),
                    'operator' => (string) Arr::get($condition, 'operator'),
                    'value' => Arr::get($condition, 'value'),
                ];
            })
            ->values()
            ->all();

        return [
            'conditions' => $conditions,
        ];
    }

    private function applyCondition(Builder $query, array $condition): void
    {
        $field = (string) Arr::get($condition, 'field');
        $operator = (string) Arr::get($condition, 'operator');
        $value = Arr::get($condition, 'value');

        if (! array_key_exists($field, self::FIELD_CONFIG)) {
            throw new InvalidArgumentException("El campo [$field] no está soportado.");
        }

        if (! in_array($operator, self::FIELD_CONFIG[$field]['operators'], true)) {
            throw new InvalidArgumentException("El operador [$operator] no está permitido para [$field].");
        }

        if ($field === 'created_at') {
            $this->applyCreatedAtCondition($query, $operator, $value);

            return;
        }

        if (in_array($field, ['status_id', 'user_id', 'source_id'], true)) {
            $this->applyIdCondition($query, "customers.$field", $operator, $value);

            return;
        }

        if ($field === 'tag_id') {
            $this->applyTagCondition($query, $operator, $value);

            return;
        }

        if (in_array($field, ['name', 'business', 'city', 'country'], true)) {
            $this->applyTextCondition($query, "customers.$field", $operator, $value);

            return;
        }

        if ($field === 'scoring_profile') {
            $this->applyScoringCondition($query, $operator, $value);

            return;
        }

        if ($field === 'has_whatsapp') {
            $this->applyHasWhatsappCondition($query, $value);
        }
    }

    private function applyCreatedAtCondition(Builder $query, string $operator, mixed $value): void
    {
        if ($operator === 'between') {
            $values = $this->normalizeArray($value);
            if (count($values) !== 2) {
                throw new InvalidArgumentException('El operador "between" requiere dos fechas.');
            }

            $from = Carbon::parse((string) $values[0])->startOfDay();
            $to = Carbon::parse((string) $values[1])->endOfDay();
            $query->whereBetween('customers.created_at', [$from, $to]);

            return;
        }

        $dateValue = Carbon::parse((string) $value)->toDateString();

        if ($operator === 'on') {
            $query->whereDate('customers.created_at', '=', $dateValue);

            return;
        }

        if ($operator === 'before') {
            $query->whereDate('customers.created_at', '<=', $dateValue);

            return;
        }

        if ($operator === 'after') {
            $query->whereDate('customers.created_at', '>=', $dateValue);
        }
    }

    private function applyIdCondition(Builder $query, string $column, string $operator, mixed $value): void
    {
        $ids = $this->normalizeIntegerArray($value);

        if ($operator === 'eq') {
            $singleId = $ids[0] ?? null;
            if ($singleId === null) {
                throw new InvalidArgumentException("El campo [$column] requiere un valor numérico.");
            }
            $query->where($column, $singleId);

            return;
        }

        if ($ids === []) {
            throw new InvalidArgumentException("El campo [$column] requiere uno o más valores.");
        }

        $query->whereIn($column, $ids);
    }

    private function applyTagCondition(Builder $query, string $operator, mixed $value): void
    {
        $tagIds = $this->normalizeIntegerArray($value);

        if ($tagIds === []) {
            throw new InvalidArgumentException('Debes seleccionar al menos una etiqueta.');
        }

        if ($operator === 'eq') {
            $tagId = $tagIds[0];
            $query->whereHas('tags', function (Builder $tagQuery) use ($tagId): void {
                $tagQuery->where('tags.id', $tagId);
            });

            return;
        }

        $query->whereHas('tags', function (Builder $tagQuery) use ($tagIds): void {
            $tagQuery->whereIn('tags.id', $tagIds);
        });
    }

    private function applyTextCondition(Builder $query, string $column, string $operator, mixed $value): void
    {
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("El campo [$column] requiere un texto.");
        }

        $value = trim($value);

        if ($operator === 'eq') {
            $query->where($column, $value);

            return;
        }

        if ($operator === 'contains') {
            $query->where($column, 'like', '%'.$this->escapeLike($value).'%');
        }
    }

    private function applyScoringCondition(Builder $query, string $operator, mixed $value): void
    {
        $allowed = ['a', 'b', 'c', 'd'];
        $profiles = collect($this->normalizeArray($value))
            ->map(function ($item): string {
                return strtolower(trim((string) $item));
            })
            ->filter(function (string $item): bool {
                return $item !== '';
            })
            ->unique()
            ->values()
            ->all();

        foreach ($profiles as $profile) {
            if (! in_array($profile, $allowed, true)) {
                throw new InvalidArgumentException('Perfil de scoring no válido.');
            }
        }

        if ($operator === 'eq') {
            $query->where('customers.scoring_profile', $profiles[0] ?? null);

            return;
        }

        $query->whereIn('customers.scoring_profile', $profiles);
    }

    private function applyHasWhatsappCondition(Builder $query, mixed $value): void
    {
        $hasWhatsApp = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($hasWhatsApp === null) {
            throw new InvalidArgumentException('El valor de "has_whatsapp" debe ser verdadero o falso.');
        }

        if ($hasWhatsApp) {
            $query->where(function (Builder $innerQuery): void {
                $this->applyAnyPhoneCondition($innerQuery);
            });

            return;
        }

        $query->where(function (Builder $innerQuery): void {
            $this->applyNoPhoneCondition($innerQuery);
        });
    }

    private function applyAnyPhoneCondition(Builder $query): void
    {
        $query->where(function (Builder $phoneQuery): void {
            $phoneQuery->whereNotNull('customers.phone')
                ->whereRaw("TRIM(customers.phone) != ''");
        })->orWhere(function (Builder $phone2Query): void {
            $phone2Query->whereNotNull('customers.phone2')
                ->whereRaw("TRIM(customers.phone2) != ''");
        })->orWhere(function (Builder $contactPhoneQuery): void {
            $contactPhoneQuery->whereNotNull('customers.contact_phone2')
                ->whereRaw("TRIM(customers.contact_phone2) != ''");
        });
    }

    private function applyNoPhoneCondition(Builder $query): void
    {
        $query->where(function (Builder $phoneQuery): void {
            $phoneQuery->whereNull('customers.phone')
                ->orWhereRaw("TRIM(customers.phone) = ''");
        })->where(function (Builder $phone2Query): void {
            $phone2Query->whereNull('customers.phone2')
                ->orWhereRaw("TRIM(customers.phone2) = ''");
        })->where(function (Builder $contactPhoneQuery): void {
            $contactPhoneQuery->whereNull('customers.contact_phone2')
                ->orWhereRaw("TRIM(customers.contact_phone2) = ''");
        });
    }

    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && Str::contains($value, ',')) {
            return array_map('trim', explode(',', $value));
        }

        if ($value === null || $value === '') {
            return [];
        }

        return [$value];
    }

    private function normalizeIntegerArray(mixed $value): array
    {
        return collect($this->normalizeArray($value))
            ->filter(function ($item): bool {
                return $item !== null && $item !== '';
            })
            ->map(function ($item): int {
                return (int) $item;
            })
            ->filter(function (int $item): bool {
                return $item > 0;
            })
            ->unique()
            ->values()
            ->all();
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    public function toRawSql(Builder $query): string
    {
        $baseQuery = $query->getQuery();

        if (method_exists($baseQuery, 'toRawSql')) {
            return $baseQuery->toRawSql();
        }

        $sql = $baseQuery->toSql();
        $bindings = $baseQuery->getBindings();
        $pdo = DB::connection()->getPdo();

        return Str::replaceArray('?', array_map(function ($binding) use ($pdo): string {
            return $this->quoteBinding($binding, $pdo);
        }, $bindings), $sql);
    }

    private function quoteBinding(mixed $binding, PDO $pdo): string
    {
        if ($binding === null) {
            return 'null';
        }

        if (is_bool($binding)) {
            return $binding ? '1' : '0';
        }

        if (is_int($binding) || is_float($binding)) {
            return (string) $binding;
        }

        if ($binding instanceof Carbon) {
            return $pdo->quote($binding->toDateTimeString());
        }

        return $pdo->quote((string) $binding);
    }
}
