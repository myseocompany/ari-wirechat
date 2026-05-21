<?php

namespace App\Services\Opportunities;

use App\Enums\CustomerMaker;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OpportunityDetectorService
{
    private const MAKER_FILTERS = [
        'project' => CustomerMaker::Project->value,
        'makes' => CustomerMaker::MakesEmpanadas->value,
        'other' => CustomerMaker::Other->value,
    ];

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
     * @param  array<string, mixed>  $filters
     * @return array{model: LengthAwarePaginator, summary: array<string, int>, fromDate: Carbon, toDate: Carbon}
     */
    public function analyze(array $filters = [], int $perPage = 25): array
    {
        [$fromDate, $toDate] = $this->dateRange($filters);
        $limit = min(3000, max(10, (int) ($filters['limit'] ?? 500)));
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = min(100, max(10, $perPage));

        $baseQuery = $this->baseQuery($filters, $fromDate, $toDate);
        $candidateTotal = (clone $baseQuery)->count();
        $rows = $this->baseRows($baseQuery, $limit);
        $rows = $this->attachHeavyData($rows);
        $rows = $this->scoreRows($rows);
        $rows = $this->analyzeAmbiguousRowsWithLlm($rows, $filters);
        $rows = $this->applyComputedFilters($rows, $filters);

        $summary = [
            'total' => $rows->count(),
            'analyzed' => $rows->count(),
            'candidate_total' => $candidateTotal,
            'limit' => $limit,
            'high' => $rows->where('priority', 'high')->count(),
            'medium' => $rows->where('priority', 'medium')->count(),
            'low' => $rows->where('priority', 'low')->count(),
            'unattended' => $rows->where('is_unattended', true)->count(),
            'makers' => $rows->where('production_status', 'makes')->count(),
            'projects' => $rows->where('production_status', 'project')->count(),
            'production_known' => $rows->filter(fn ($row) => (int) ($row->estimated_daily_empanadas ?? 0) > 0)->count(),
            'llm_analyzed' => $rows->where('llm_used', true)->count(),
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
     * @param  array<string, mixed>  $filters
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
     * @param  array<string, mixed>  $filters
     */
    private function baseQuery(array $filters, Carbon $fromDate, Carbon $toDate): \Illuminate\Database\Eloquent\Builder
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
                'customers.maker',
                'customers.count_empanadas',
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
            ->when(! empty($filters['maker']) && $filters['maker'] === 'unknown', fn ($query) => $query->whereNull('customers.maker'))
            ->when(! empty($filters['maker']) && isset(self::MAKER_FILTERS[$filters['maker']]), fn ($query) => $query->where('customers.maker', self::MAKER_FILTERS[$filters['maker']]))
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
            });
    }

    /**
     * @return Collection<int, object>
     */
    private function baseRows(\Illuminate\Database\Eloquent\Builder $baseQuery, int $limit): Collection
    {
        return $baseQuery
            ->orderByDesc('ms.last_message_at')
            ->orderByDesc('ms.messages_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Collection<int, object>  $rows
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
                DB::raw("(select group_concat(concat(case when nullif(trim(wm.body), '') is null then concat('[', coalesce(wm.type, 'mensaje'), ']') else wm.body end, '||@ts||', date_format(wm.created_at, '%Y-%m-%d %H:%i')) order by wm.created_at desc separator '\n') from (select wire_messages.body, wire_messages.type, wire_messages.created_at from wire_messages where wire_messages.sendable_id = customers.id order by wire_messages.created_at desc limit 30) as wm) as analysis_messages_body"),
                DB::raw("(select group_concat(concat(coalesce(nullif(trim(a.note), ''), '[sin nota]'), '||@type||', coalesce(at.name, 'Sin tipo'), '||@user||', a.creator_user_name, '||@ts||', date_format(a.created_at, '%Y-%m-%d %H:%i')) order by a.created_at desc separator '\n') from (select actions.note, actions.type_id, actions.created_at, users.name as creator_user_name from actions inner join users on users.id = actions.creator_user_id where actions.customer_id = customers.id and actions.creator_user_id is not null and actions.creator_user_id > 0 order by actions.created_at desc limit 3) as a left join action_types as at on at.id = a.type_id) as last_actions_body"),
                DB::raw("(select group_concat(tags.name order by tags.name separator '||') from customer_tag as ct join tags on tags.id = ct.tag_id where ct.customer_id = customers.id) as tag_names")
            )
            ->whereIn('customers.id', $customerIds)
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($heavyData) {
            $extra = $heavyData->get($row->id);
            $row->last_messages_body = $extra->last_messages_body ?? null;
            $row->analysis_messages_body = $extra->analysis_messages_body ?? null;
            $row->last_actions_body = $extra->last_actions_body ?? null;
            $row->tag_names = $extra->tag_names ?? null;

            return $row;
        });
    }

    /**
     * @param  Collection<int, object>  $rows
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
            $messagesText = $this->normalize((string) ($row->analysis_messages_body ?? $row->last_messages_body ?? ''));
            $matchedKeywords = $this->matchedKeywords($messagesText);
            $production = $this->detectProduction($row, $messagesText);
            $isUnattended = $lastCustomerMessageAt && (! $lastActionAt || $lastCustomerMessageAt->gt($lastActionAt));

            if ($production['status'] === 'makes') {
                $score += 3;
                $reasons[] = 'Produce empanadas: '.$production['label'];
            } elseif ($production['status'] === 'project') {
                $reasons[] = 'Proyecto: no produce actualmente';
            } elseif ($production['status'] === 'other') {
                $reasons[] = 'Tipo de cliente: '.$production['label'];
            }

            if ($production['daily_amount'] > 0) {
                $score += min(3, max(1, intdiv($production['daily_amount'], 300)));
                $reasons[] = 'Produccion estimada: '.number_format($production['daily_amount']).' emp/dia';
            }

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
            $row->production_status = $production['status'];
            $row->production_label = $production['label'];
            $row->estimated_daily_empanadas = $production['daily_amount'];
            $row->production_evidence = $production['evidence'];
            $row->production_rank = $production['rank'];
            $row->intent = $this->detectIntent($matchedKeywords);
            $row->intent_label = $this->intentLabel($row->intent);
            $nextAction = $this->recommendNextAction($row, $isLowValueStatus);
            $row->next_best_action = $nextAction['action'];
            $row->next_best_action_label = $nextAction['label'];
            $row->recommended_channel = $nextAction['channel'];
            $row->recommended_channel_label = $nextAction['channel_label'];
            $row->recommended_sla = $nextAction['sla'];
            $row->action_reason = $nextAction['reason'];
            $row->suggested_message = $nextAction['message'];
            $row->stop_condition = $nextAction['stop_condition'];
            $row->llm_used = false;
            $row->llm_error = null;
            $row->llm_duration_ms = null;
            $row->llm_model = null;
            $row->llm_confidence = null;
            $row->llm_evidence = null;
            $row->matched_keywords = $matchedKeywords;
            $row->opportunity_reasons = $reasons;

            return $row;
        })->pipe(fn ($rows) => $this->sortRows($rows));
    }

    /**
     * @param  Collection<int, object>  $rows
     * @param  array<string, mixed>  $filters
     * @return Collection<int, object>
     */
    private function analyzeAmbiguousRowsWithLlm(Collection $rows, array $filters): Collection
    {
        if (empty($filters['llm'])) {
            return $rows;
        }

        $limit = min(200, max(1, (int) ($filters['llm_limit'] ?? 50)));
        $analyzer = app(OpportunityLlmAnalyzer::class);

        $rowsToAnalyze = $rows
            ->filter(fn ($row) => $this->shouldAnalyzeWithLlm($row))
            ->take($limit);

        foreach ($rowsToAnalyze as $row) {
            $analysis = $analyzer->analyze($row);
            $this->applyLlmAnalysis($row, $analysis);
        }

        return $this->sortRows($rows);
    }

    private function shouldAnalyzeWithLlm(object $row): bool
    {
        if (! in_array($row->priority, ['high', 'medium'], true)) {
            return false;
        }

        return $row->production_status === 'unknown'
            || (int) ($row->estimated_daily_empanadas ?? 0) === 0
            || $row->intent === 'unknown';
    }

    /**
     * @param array{
     *     llm_used: bool,
     *     llm_error: string|null,
     *     llm_duration_ms: int|null,
     *     model: string|null,
     *     produce_empanadas: string,
     *     estimated_daily_empanadas: int|null,
     *     intent: string,
     *     confidence: float|null,
     *     evidence: string|null,
     *     next_best_action: string,
     *     recommended_channel: string,
     *     recommended_sla: string,
     *     action_reason: string|null,
     *     suggested_message: string|null,
     *     stop_condition: string|null
     * } $analysis
     */
    private function applyLlmAnalysis(object $row, array $analysis): void
    {
        $row->llm_used = $analysis['llm_used'];
        $row->llm_error = $analysis['llm_error'];
        $row->llm_duration_ms = $analysis['llm_duration_ms'];
        $row->llm_model = $analysis['model'];
        $row->llm_confidence = $analysis['confidence'];
        $row->llm_evidence = $analysis['evidence'];

        if (! $analysis['llm_used']) {
            return;
        }

        if ($row->production_status === 'unknown' && $analysis['produce_empanadas'] === 'yes') {
            $row->production_status = 'makes';
            $row->production_label = 'Hace empanadas (IA)';
            $row->production_rank = 3;
            $row->opportunity_score += 3;
            $row->opportunity_reasons[] = 'IA: produce empanadas';
        }

        if ($row->production_status === 'unknown' && $analysis['produce_empanadas'] === 'no') {
            $row->production_status = 'project';
            $row->production_label = 'Proyecto (IA)';
            $row->production_rank = 1;
            $row->opportunity_reasons[] = 'IA: parece proyecto';
        }

        if ($row->production_status === 'unknown' && $analysis['produce_empanadas'] === 'other') {
            $row->production_status = 'other';
            $row->production_label = 'Otro producto (IA)';
            $row->production_rank = 2;
            $row->opportunity_reasons[] = 'IA: oportunidad de otro tipo';
        }

        if ((int) ($row->estimated_daily_empanadas ?? 0) === 0 && $analysis['estimated_daily_empanadas']) {
            $row->estimated_daily_empanadas = $analysis['estimated_daily_empanadas'];
            $row->opportunity_score += min(3, max(1, intdiv($analysis['estimated_daily_empanadas'], 300)));
            $row->opportunity_reasons[] = 'IA: produccion estimada '.number_format($analysis['estimated_daily_empanadas']).' emp/dia';
        }

        if ($row->intent === 'unknown' && $analysis['intent'] !== 'unknown') {
            $row->intent = $analysis['intent'];
            $row->intent_label = $this->intentLabel($analysis['intent']);
            $row->opportunity_score += in_array($analysis['intent'], ['buy', 'quote'], true) ? 3 : 1;
            $row->opportunity_reasons[] = 'IA: intencion '.$row->intent_label;
        }

        if ($analysis['evidence'] && ! $row->production_evidence) {
            $row->production_evidence = $analysis['evidence'];
        }

        $this->applyLlmAction($row, $analysis);

        $row->priority = $row->opportunity_score >= 8 ? 'high' : ($row->opportunity_score >= 5 ? 'medium' : 'low');
        $row->priority_label = $row->opportunity_score >= 8 ? 'Alta' : ($row->opportunity_score >= 5 ? 'Media' : 'Baja');
    }

    /**
     * @param array{
     *     next_best_action: string,
     *     recommended_channel: string,
     *     recommended_sla: string,
     *     action_reason: string|null,
     *     suggested_message: string|null,
     *     stop_condition: string|null
     * } $analysis
     */
    private function applyLlmAction(object $row, array $analysis): void
    {
        if ($analysis['next_best_action'] !== 'wait_for_signal') {
            $row->next_best_action = $analysis['next_best_action'];
            $row->next_best_action_label = $this->actionLabel($analysis['next_best_action']);
        }

        if ($analysis['recommended_channel'] !== 'crm') {
            $row->recommended_channel = $analysis['recommended_channel'];
            $row->recommended_channel_label = $this->channelLabel($analysis['recommended_channel']);
        }

        if ($analysis['recommended_sla'] !== 'esperar') {
            $row->recommended_sla = $analysis['recommended_sla'];
        }

        if ($analysis['action_reason']) {
            $row->action_reason = $analysis['action_reason'];
        }

        if ($analysis['suggested_message']) {
            $row->suggested_message = $analysis['suggested_message'];
        }

        if ($analysis['stop_condition']) {
            $row->stop_condition = $analysis['stop_condition'];
        }
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return Collection<int, object>
     */
    private function sortRows(Collection $rows): Collection
    {
        return $rows->sort(function ($left, $right) {
            return [
                (int) $right->production_rank,
                (int) $right->estimated_daily_empanadas,
                (int) $right->opportunity_score,
                (string) $right->last_customer_message_at,
                (int) $right->messages_count,
            ] <=> [
                (int) $left->production_rank,
                (int) $left->estimated_daily_empanadas,
                (int) $left->opportunity_score,
                (string) $left->last_customer_message_at,
                (int) $left->messages_count,
            ];
        })->values();
    }

    /**
     * @param  Collection<int, object>  $rows
     * @param  array<string, mixed>  $filters
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

        if (isset($filters['production_min']) && $filters['production_min'] !== '') {
            $rows = $rows->filter(fn ($row) => (int) ($row->estimated_daily_empanadas ?? 0) >= (int) $filters['production_min']);
        }

        return $rows->values();
    }

    /**
     * @return array{status: string, label: string, daily_amount: int, evidence: ?string, rank: int}
     */
    private function detectProduction(object $row, string $messagesText): array
    {
        $maker = CustomerMaker::fromDatabase($row->maker ?? null);
        $fieldEvidence = trim((string) ($row->count_empanadas ?? ''));
        $fieldAmount = $this->extractEmpanadasAmount($fieldEvidence);
        $messageAmount = $this->extractEmpanadasAmount($messagesText, true);
        $dailyAmount = $fieldAmount ?: $messageAmount;
        $messageStatus = $this->inferProductionStatusFromText($messagesText);

        if ($maker === CustomerMaker::MakesEmpanadas || $dailyAmount > 0) {
            return [
                'status' => 'makes',
                'label' => CustomerMaker::MakesEmpanadas->label(),
                'daily_amount' => $dailyAmount,
                'evidence' => $fieldEvidence !== '' ? $fieldEvidence : $this->productionEvidence($messagesText),
                'rank' => 3,
            ];
        }

        if ($maker === CustomerMaker::Project || $this->textMeansProject($fieldEvidence) || $messageStatus === 'project') {
            return [
                'status' => 'project',
                'label' => CustomerMaker::Project->label(),
                'daily_amount' => 0,
                'evidence' => $fieldEvidence !== '' ? $fieldEvidence : $this->productionEvidence($messagesText),
                'rank' => 1,
            ];
        }

        if ($maker === CustomerMaker::Other) {
            return [
                'status' => 'other',
                'label' => CustomerMaker::Other->label(),
                'daily_amount' => 0,
                'evidence' => $fieldEvidence !== '' ? $fieldEvidence : null,
                'rank' => 2,
            ];
        }

        if ($messageStatus === 'makes') {
            return [
                'status' => 'makes',
                'label' => 'Hace empanadas (inferido)',
                'daily_amount' => 0,
                'evidence' => $this->productionEvidence($messagesText),
                'rank' => 3,
            ];
        }

        return [
            'status' => 'unknown',
            'label' => 'Sin clasificar',
            'daily_amount' => 0,
            'evidence' => $fieldEvidence !== '' ? $fieldEvidence : null,
            'rank' => 0,
        ];
    }

    private function extractEmpanadasAmount(string $value, bool $requireProductionContext = false): int
    {
        $normalized = $this->normalize($value);
        if ($normalized === '' || $this->textMeansProject($normalized)) {
            return 0;
        }

        $searchText = $requireProductionContext
            ? $this->productionContextText($normalized)
            : $normalized;

        if ($searchText === '' || ! preg_match_all('/\d{1,3}(?:[.,]\d{3})*|\d+/', $searchText, $matches)) {
            return 0;
        }

        $numbers = array_map(
            fn ($number) => (int) preg_replace('/\D+/', '', $number),
            $matches[0]
        );

        $numbers = array_values(array_filter($numbers, fn ($number) => $number > 0 && $number < 100000));

        return max($numbers ?: [0]);
    }

    private function productionContextText(string $value): string
    {
        $parts = preg_split('/[\n.;!?]+/', $value) ?: [];
        $relevantParts = array_filter($parts, function (string $part): bool {
            return preg_match('/(empanada|produ|diaria|diario|dia|día|hora|semana|mensual|hacemos|fabricamos|vendemos)/u', $part) === 1;
        });

        return implode(' ', $relevantParts);
    }

    private function textMeansProject(string $value): bool
    {
        $normalized = $this->normalize($value);

        return str_contains($normalized, 'no produzco')
            || str_contains($normalized, 'proyecto')
            || str_contains($normalized, 'quiero empezar')
            || str_contains($normalized, 'voy a iniciar')
            || str_contains($normalized, 'emprendimiento');
    }

    private function inferProductionStatusFromText(string $value): string
    {
        if ($this->textMeansProject($value)) {
            return 'project';
        }

        if (preg_match('/\b(produzco|producimos|hacemos|fabricamos|vendemos)\b/u', $this->normalize($value)) === 1) {
            return 'makes';
        }

        return 'unknown';
    }

    /**
     * @return array{action: string, label: string, channel: string, channel_label: string, sla: string, reason: string, message: ?string, stop_condition: string}
     */
    private function recommendNextAction(object $row, bool $isLowValueStatus): array
    {
        if ($isLowValueStatus) {
            return $this->nextAction(
                'wait_for_signal',
                'crm',
                'esperar',
                'El estado actual indica baja prioridad comercial.',
                null,
                'Retomar solo si el cliente envía una nueva señal clara.'
            );
        }

        if (empty($row->user_id)) {
            return $this->nextAction(
                'assign_owner',
                'crm',
                'hoy',
                'El prospecto no tiene asesor asignado.',
                null,
                'Continuar cuando haya un responsable comercial asignado.'
            );
        }

        if ($row->is_unattended && in_array($row->intent, ['buy', 'quote'], true)) {
            return $this->nextAction(
                $row->intent === 'quote' ? 'send_quote' : 'reply_whatsapp',
                'whatsapp',
                'hoy',
                'El cliente escribió después de la última acción humana y tiene intención comercial.',
                $this->buildSuggestedMessage($row),
                'Cerrar cuando responda, se agende llamada o quede definida la cotización.'
            );
        }

        if ($row->production_status === 'makes' && (int) ($row->estimated_daily_empanadas ?? 0) >= 500) {
            return $this->nextAction(
                'create_call_task',
                'phone',
                '24h',
                'Produce empanadas y tiene volumen suficiente para priorizar llamada consultiva.',
                null,
                'Cerrar cuando quede agendada llamada, demo o envío de cotización.'
            );
        }

        if ($row->intent === 'quote') {
            return $this->nextAction(
                'send_quote',
                'whatsapp',
                '24h',
                'El cliente pide precio, cotización, ficha o condiciones.',
                $this->buildSuggestedMessage($row),
                'Cerrar cuando se envíe propuesta o se identifique la objeción principal.'
            );
        }

        if ($row->intent === 'buy') {
            return $this->nextAction(
                'book_demo',
                'whatsapp',
                '24h',
                'El cliente muestra intención de compra.',
                $this->buildSuggestedMessage($row),
                'Cerrar cuando acepte horario, llamada o siguiente paso comercial.'
            );
        }

        if ($row->production_status === 'project') {
            return $this->nextAction(
                'qualify_project',
                'whatsapp',
                '48h',
                'Parece estar en etapa de proyecto y necesita calificación antes de cotizar.',
                $this->buildSuggestedMessage($row),
                'Cerrar cuando confirme producción esperada, fecha de inicio y presupuesto aproximado.'
            );
        }

        if ($row->is_unattended) {
            return $this->nextAction(
                'reply_whatsapp',
                'whatsapp',
                '24h',
                'Hay mensaje reciente del cliente sin acción posterior.',
                $this->buildSuggestedMessage($row),
                'Cerrar cuando responda o quede creada una acción humana.'
            );
        }

        return $this->nextAction(
            'wait_for_signal',
            'crm',
            'esperar',
            'No hay una señal suficientemente fuerte para intervenir ahora.',
            null,
            'Retomar si llega nuevo mensaje, cambia el estado o aparece intención comercial.'
        );
    }

    /**
     * @return array{action: string, label: string, channel: string, channel_label: string, sla: string, reason: string, message: ?string, stop_condition: string}
     */
    private function nextAction(string $action, string $channel, string $sla, string $reason, ?string $message, string $stopCondition): array
    {
        return [
            'action' => $action,
            'label' => $this->actionLabel($action),
            'channel' => $channel,
            'channel_label' => $this->channelLabel($channel),
            'sla' => $sla,
            'reason' => $reason,
            'message' => $message,
            'stop_condition' => $stopCondition,
        ];
    }

    private function buildSuggestedMessage(object $row): ?string
    {
        $name = trim((string) ($row->name ?? ''));
        $firstName = $name !== '' ? strtok($name, ' ') : 'Hola';
        $greeting = $firstName === 'Hola' ? 'Hola.' : 'Hola '.$firstName.'.';

        if ($row->intent === 'quote') {
            return $greeting.' Vi que estás revisando información de la máquina. Para cotizar bien, ¿me confirmas cuántas empanadas hacen al día y en qué ciudad estás?';
        }

        if ($row->intent === 'buy') {
            return $greeting.' Para avanzar sin hacerte perder tiempo, ¿prefieres que revisemos por llamada la máquina que mejor encaja con tu producción o te envío primero las opciones?';
        }

        if ($row->production_status === 'project') {
            return $greeting.' Para orientarte mejor, ¿ya tienes fecha estimada para iniciar producción y cuántas empanadas esperas hacer al día?';
        }

        return $greeting.' Retomo tu mensaje para ayudarte mejor. ¿Tu prioridad es conocer precios, capacidad de producción o agendar una llamada?';
    }

    private function productionEvidence(string $value): ?string
    {
        $plain = trim(preg_replace('/\s+/', ' ', str_replace('||@ts||', ' ', $value)) ?? '');
        if ($plain === '') {
            return null;
        }

        return mb_strimwidth($plain, 0, 160, '...');
    }

    private function normalize(string $value): string
    {
        return mb_strtolower($value);
    }

    /**
     * @param  array<int, string>  $needles
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

    /**
     * @param  array<int, string>  $matchedKeywords
     */
    private function detectIntent(array $matchedKeywords): string
    {
        $keywords = array_map(fn ($keyword) => $this->normalize($keyword), $matchedKeywords);

        if (array_intersect($keywords, ['comprar', 'compra'])) {
            return 'buy';
        }

        if (array_intersect($keywords, ['precio', 'precios', 'valor', 'costo', 'costos', 'cotizacion', 'cotización', 'cotizar', 'ficha tecnica', 'ficha técnica'])) {
            return 'quote';
        }

        if (in_array('alimentec', $keywords, true)) {
            return 'event';
        }

        if (array_intersect($keywords, ['maquina', 'máquina', 'maquinas', 'máquinas', 'equipo', 'produccion', 'producción', 'capacidad', 'demo'])) {
            return 'info';
        }

        return 'unknown';
    }

    private function intentLabel(string $intent): string
    {
        return match ($intent) {
            'buy' => 'Comprar',
            'quote' => 'Cotizar',
            'info' => 'Información',
            'event' => 'Evento',
            'support' => 'Soporte',
            default => 'No claro',
        };
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'reply_whatsapp' => 'Responder WhatsApp',
            'create_call_task' => 'Crear llamada',
            'send_quote' => 'Enviar cotización',
            'book_demo' => 'Agendar demo',
            'qualify_project' => 'Calificar proyecto',
            'assign_owner' => 'Asignar asesor',
            'disqualify' => 'Descartar',
            default => 'Esperar señal',
        };
    }

    private function channelLabel(string $channel): string
    {
        return match ($channel) {
            'whatsapp' => 'WhatsApp',
            'phone' => 'Llamada',
            'email' => 'Email',
            'none' => 'Ninguno',
            default => 'CRM',
        };
    }
}
