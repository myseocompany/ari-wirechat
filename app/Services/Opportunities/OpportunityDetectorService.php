<?php

namespace App\Services\Opportunities;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OpportunityDetectorService
{
    private const KEYWORDS = [
        'alimentec',
        'precio',
        'precios',
        'valor',
        'costo',
        'costos',
        'cotizacion',
        'cotización',
        'cotizar',
        'comprar',
        'compra',
        'maquina',
        'máquina',
        'maquinas',
        'máquinas',
        'equipo',
        'produccion',
        'producción',
        'capacidad',
        'envio',
        'envío',
        'asesor',
        'demo',
        'ficha tecnica',
        'ficha técnica',
    ];

    private const EARLY_STATUSES = [
        'nuevo',
        'buscando',
        'interesado',
        'recontacto',
        'reingreso',
        'no contesta',
        'negociación',
        'negociacion',
    ];

    private const LOW_VALUE_STATUSES = [
        'ganado',
        'posventa',
        'pidió la baja',
        'pidio la baja',
        'repetido',
    ];

    /**
     * @param array<string, mixed> $filters
     * @return array{model: LengthAwarePaginator, summary: array<string, int>, fromDate: Carbon, toDate: Carbon}
     */
    public function analyze(array $filters = [], int $perPage = 25): array
    {
        [$fromDate, $toDate] = $this->dateRange($filters);
        $limit = min(1000, max(10, (int) ($filters['limit'] ?? 500)));
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = min(100, max(10, $perPage));

        $rows = $this->baseRows($filters, $fromDate, $toDate, $limit);
        $rows = $this->attachHeavyData($rows);
        $rows = $this->scoreRows($rows);
        $rows = $this->applyComputedFilters($rows, $filters);

        $summary = [
            'total' => $rows->count(),
            'high' => $rows->where('priority', 'high')->count(),
            'medium' => $rows->where('priority', 'medium')->count(),
            'low' => $rows->where('priority', 'low')->count(),
            'unattended' => $rows->where('is_unattended', true)->count(),
        ];

        $pageRows = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pageRows,
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

        return [
            'model' => $paginator,
            'summary' => $summary,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function dateRange(array $filters): array
    {
        if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            return [
                Carbon::createFromFormat('Y-m-d', (string) $filters['from_date'])->startOfDay(),
                Carbon::createFromFormat('Y-m-d', (string) $filters['to_date'])->endOfDay(),
            ];
        }

        return [
            Carbon::today()->subDays(7)->startOfDay(),
            Carbon::today()->endOfDay(),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, object>
     */
    private function baseRows(array $filters, Carbon $fromDate, Carbon $toDate, int $limit): Collection
    {
        $messageStatsQuery = DB::table('wire_messages as wm')
            ->whereNotNull('wm.sendable_id')
            ->whereBetween('wm.created_at', [$fromDate, $toDate])
            ->when(! empty($filters['message_search']), fn ($q) => $q->where('wm.body', 'like', '%'.$filters['message_search'].'%'))
            ->groupBy('wm.sendable_id')
            ->select(
                'wm.sendable_id as customer_id',
                DB::raw('count(*) as messages_count'),
                DB::raw('count(*) as customer_messages_count'),
                DB::raw('max(wm.created_at) as last_message_at'),
                DB::raw('max(wm.created_at) as last_customer_message_at')
            );

        $actionStatsQuery = DB::table('actions')
            ->whereNotNull('creator_user_id')
            ->where('creator_user_id', '>', 0)
            ->groupBy('customer_id')
            ->select(
                'customer_id',
                DB::raw('count(*) as human_actions_count'),
                DB::raw('max(created_at) as last_action_at')
            );

        $pendingActionsQuery = DB::table('actions')
            ->whereNotNull('due_date')
            ->whereNull('delivery_date')
            ->groupBy('customer_id')
            ->select(
                'customer_id',
                DB::raw('count(*) as pending_actions_count'),
                DB::raw('min(due_date) as next_due_at')
            );

        return Customer::query()
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.status_id',
                'customers.user_id',
                'users.name as user_name',
                'customer_sources.name as source_name',
                'customer_statuses.name as status_name',
                'customer_statuses.color as status_color',
                'ms.messages_count',
                'ms.customer_messages_count',
                'ms.last_message_at',
                'ms.last_customer_message_at',
                'astats.human_actions_count',
                'astats.last_action_at',
                'pending.pending_actions_count',
                'pending.next_due_at'
            )
            ->joinSub($messageStatsQuery, 'ms', fn ($join) => $join->on('ms.customer_id', '=', 'customers.id'))
            ->leftJoinSub($actionStatsQuery, 'astats', fn ($join) => $join->on('astats.customer_id', '=', 'customers.id'))
            ->leftJoinSub($pendingActionsQuery, 'pending', fn ($join) => $join->on('pending.customer_id', '=', 'customers.id'))
            ->leftJoin('users', 'users.id', '=', 'customers.user_id')
            ->leftJoin('customer_statuses', 'customer_statuses.id', '=', 'customers.status_id')
            ->leftJoin('customer_sources', 'customer_sources.id', '=', 'customers.source_id')
            ->when(! empty($filters['status_ids']), fn ($query) => $query->whereIn('customers.status_id', (array) $filters['status_ids']))
            ->when(! empty($filters['source_id']), fn ($query) => $query->where('customers.source_id', $filters['source_id']))
            ->when(! empty($filters['user_id']), fn ($query) => $query->where('customers.user_id', $filters['user_id']))
            ->when(! empty($filters['messages_min']), fn ($query) => $query->where('ms.messages_count', '>=', (int) $filters['messages_min']))
            ->when(! empty($filters['messages_max']), fn ($query) => $query->where('ms.messages_count', '<=', (int) $filters['messages_max']))
            ->when(! empty($filters['action_note_search']), function ($query) use ($filters) {
                $query->whereExists(function ($subQuery) use ($filters) {
                    $subQuery->selectRaw('1')
                        ->from('actions as filter_actions')
                        ->whereColumn('filter_actions.customer_id', 'customers.id')
                        ->where('filter_actions.note', 'like', '%'.$filters['action_note_search'].'%');
                });
            })
            ->when(! empty($filters['tag_none']), function ($query) {
                $query->whereNotExists(function ($subQuery) {
                    $subQuery->selectRaw('1')
                        ->from('customer_tag as ct')
                        ->whereColumn('ct.customer_id', 'customers.id');
                });
            })
            ->when(! empty($filters['tag_ids']) && empty($filters['tag_none']), function ($query) use ($filters) {
                $query->whereExists(function ($subQuery) use ($filters) {
                    $subQuery->selectRaw('1')
                        ->from('customer_tag as ct')
                        ->whereColumn('ct.customer_id', 'customers.id')
                        ->whereIn('ct.tag_id', (array) $filters['tag_ids']);
                });
            })
            ->orderByDesc('ms.last_message_at')
            ->orderByDesc('ms.messages_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @param Collection<int, object> $rows
     * @return Collection<int, object>
     */
    private function attachHeavyData(Collection $rows): Collection
    {
        $customerIds = $rows->pluck('id')->all();
        if (empty($customerIds)) {
            return $rows;
        }

        $heavyData = DB::table('customers')
            ->select(
                'customers.id',
                DB::raw("(select group_concat(concat(case when nullif(trim(wm.body), '') is null then concat('[', coalesce(wm.type, 'mensaje'), ']') else wm.body end, '||@ts||', date_format(wm.created_at, '%Y-%m-%d %H:%i')) order by wm.created_at desc separator '\n') from (select wire_messages.body, wire_messages.type, wire_messages.created_at from wire_messages where wire_messages.sendable_id = customers.id order by wire_messages.created_at desc limit 5) as wm) as last_messages_body"),
                DB::raw("(select group_concat(concat(coalesce(nullif(trim(a.note), ''), '[sin nota]'), '||@type||', coalesce(at.name, 'Sin tipo'), '||@user||', a.creator_user_name, '||@ts||', date_format(a.created_at, '%Y-%m-%d %H:%i')) order by a.created_at desc separator '\n') from (select actions.note, actions.type_id, actions.created_at, users.name as creator_user_name from actions inner join users on users.id = actions.creator_user_id where actions.customer_id = customers.id and actions.creator_user_id is not null and actions.creator_user_id > 0 order by actions.created_at desc limit 3) as a left join action_types as at on at.id = a.type_id) as last_actions_body"),
                DB::raw("(select group_concat(tags.name order by tags.name separator '||') from customer_tag as ct join tags on tags.id = ct.tag_id where ct.customer_id = customers.id) as tag_names")
            )
            ->whereIn('customers.id', $customerIds)
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($heavyData) {
            $extra = $heavyData->get($row->id);
            $row->last_messages_body = $extra->last_messages_body ?? null;
            $row->last_actions_body = $extra->last_actions_body ?? null;
            $row->tag_names = $extra->tag_names ?? null;

            return $row;
        });
    }

    /**
     * @param Collection<int, object> $rows
     * @return Collection<int, object>
     */
    private function scoreRows(Collection $rows): Collection
    {
        return $rows->map(function ($row) {
            $score = 0;
            $reasons = [];

            $statusName = $this->normalize((string) ($row->status_name ?? ''));
            $isLowValueStatus = $this->containsAny($statusName, self::LOW_VALUE_STATUSES);
            $isEarlyStatus = $this->containsAny($statusName, self::EARLY_STATUSES);
            $lastCustomerMessageAt = $row->last_customer_message_at ? Carbon::parse($row->last_customer_message_at) : null;
            $lastActionAt = $row->last_action_at ? Carbon::parse($row->last_action_at) : null;
            $messagesText = $this->normalize((string) ($row->last_messages_body ?? ''));
            $matchedKeywords = $this->matchedKeywords($messagesText);
            $isUnattended = $lastCustomerMessageAt && (! $lastActionAt || $lastCustomerMessageAt->gt($lastActionAt));

            if ($isUnattended) {
                $score += 4;
                $reasons[] = 'Ultimo mensaje del cliente sin accion posterior';
            }

            if (! empty($matchedKeywords)) {
                $score += 3;
                $reasons[] = 'Intencion comercial: '.implode(', ', array_slice($matchedKeywords, 0, 4));
            }

            if ((int) ($row->human_actions_count ?? 0) === 0) {
                $score += 2;
                $reasons[] = 'Sin acciones humanas registradas';
            }

            if ($isEarlyStatus) {
                $score += 2;
                $reasons[] = 'Estado temprano: '.($row->status_name ?? 'Sin estado');
            }

            if ((int) ($row->messages_count ?? 0) >= 5) {
                $score += 1;
                $reasons[] = 'Conversacion activa';
            }

            if (empty($row->user_id)) {
                $score += 1;
                $reasons[] = 'Sin asesor asignado';
            }

            if ($isLowValueStatus) {
                $score = max(0, $score - 4);
                $reasons[] = 'Estado de baja prioridad: '.($row->status_name ?? 'Sin estado');
            }

            $row->opportunity_score = $score;
            $row->priority = $score >= 8 ? 'high' : ($score >= 5 ? 'medium' : 'low');
            $row->priority_label = $score >= 8 ? 'Alta' : ($score >= 5 ? 'Media' : 'Baja');
            $row->is_unattended = (bool) $isUnattended;
            $row->matched_keywords = $matchedKeywords;
            $row->opportunity_reasons = $reasons;

            return $row;
        })->sort(function ($left, $right) {
            return [
                (int) $right->opportunity_score,
                (string) $right->last_customer_message_at,
                (int) $right->messages_count,
            ] <=> [
                (int) $left->opportunity_score,
                (string) $left->last_customer_message_at,
                (int) $left->messages_count,
            ];
        })->values();
    }

    /**
     * @param Collection<int, object> $rows
     * @param array<string, mixed> $filters
     * @return Collection<int, object>
     */
    private function applyComputedFilters(Collection $rows, array $filters): Collection
    {
        if (! empty($filters['priority'])) {
            $rows = $rows->where('priority', $filters['priority']);
        }

        if (! empty($filters['unattended'])) {
            $rows = $rows->where('is_unattended', true);
        }

        return $rows->values();
    }

    private function normalize(string $value): string
    {
        return mb_strtolower($value);
    }

    /**
     * @param array<int, string> $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $this->normalize($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function matchedKeywords(string $text): array
    {
        $matches = [];
        foreach (self::KEYWORDS as $keyword) {
            if (str_contains($text, $this->normalize($keyword))) {
                $matches[] = $keyword;
            }
        }

        return array_values(array_unique($matches));
    }
}
