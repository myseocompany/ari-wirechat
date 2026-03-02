<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ActivityLogController extends Controller
{
    private function ensureAdmin(): void
    {
        $user = Auth::user();

        if (! $user || (int) $user->role_id !== 1) {
            abort(403);
        }
    }

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        $perPage = (int) $request->get('per_page', 25);
        $perPage = $perPage > 0 ? min($perPage, 100) : 25;
        $search = trim((string) $request->get('q'));
        $action = trim((string) $request->get('action'));
        $userId = $request->get('user_id');
        [$parsedFromDateTime, $parsedToDateTime, $selectedRange, $fromDate, $toDate] = $this->resolveDateRange($request);

        $query = ActivityLog::with('user')->orderByDesc('id');
        $this->applyFilters($query, $search, $action, $userId, $parsedFromDateTime, $parsedToDateTime);

        $actions = $this->getAvailableActions();
        $users = $this->getAvailableUsers();

        $logs = $query->paginate($perPage)->withQueryString();
        $dashboardMetrics = $this->buildDashboardMetrics(
            $search,
            $action,
            $userId,
            $parsedFromDateTime,
            $parsedToDateTime,
            $selectedRange
        );

        return view('activity_logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'search' => $search,
            'perPage' => $perPage,
            'action' => $action,
            'userId' => $userId,
            'selectedRange' => $selectedRange,
            'filterOptions' => $this->filterOptions(),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'users' => $users,
            'activityCards' => $dashboardMetrics['activity_cards'],
            'eventsByDayChart' => $dashboardMetrics['events_by_day_chart'],
            'topUsersChart' => $dashboardMetrics['top_users_chart'],
            'dashboardRangeLabel' => $dashboardMetrics['range_label'],
        ]);
    }

    public function show(int $id): View|RedirectResponse
    {
        $this->ensureAdmin();

        $log = ActivityLog::with('user')->find($id);

        if (! $log) {
            return redirect()->route('activity_logs.index')->with('error', 'Registro no encontrado');
        }

        $payloadPretty = json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('activity_logs.show', [
            'log' => $log,
            'payloadPretty' => $payloadPretty,
        ]);
    }

    private function getAvailableActions(): Collection
    {
        return ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');
    }

    private function getAvailableUsers(): Collection
    {
        return User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    private function parseDateTime(?string $dateTime): ?Carbon
    {
        if (! $dateTime) {
            return null;
        }

        try {
            return Carbon::parse($dateTime);
        } catch (Throwable) {
            return null;
        }
    }

    private function applyFilters(
        Builder $query,
        string $search,
        string $action,
        mixed $userId,
        ?Carbon $fromDateTime,
        ?Carbon $toDateTime
    ): void {
        if ($search !== '') {
            $query->where(function (Builder $innerQuery) use ($search): void {
                $innerQuery->where('action', 'like', '%'.$search.'%')
                    ->orWhere('subject_type', 'like', '%'.$search.'%')
                    ->orWhere('subject_id', 'like', '%'.$search.'%')
                    ->orWhere('meta', 'like', '%'.$search.'%');
            });
        }

        if ($action !== '') {
            $query->where('action', $action);
        }

        if ($userId !== null && $userId !== '') {
            $query->where('user_id', (int) $userId);
        }

        if ($fromDateTime && $toDateTime) {
            $query->whereBetween('created_at', [$fromDateTime, $toDateTime]);

            return;
        }

        if ($fromDateTime) {
            $query->where('created_at', '>=', $fromDateTime);
        }

        if ($toDateTime) {
            $query->where('created_at', '<=', $toDateTime);
        }
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string, 3: ?string, 4: ?string}
     */
    private function resolveDateRange(Request $request): array
    {
        $range = trim((string) $request->get('range', 'today'));
        $fromDateValue = trim((string) $request->input('from_date'));
        $toDateValue = trim((string) $request->input('to_date'));

        if ($fromDateValue !== '' && $toDateValue !== '') {
            try {
                $start = Carbon::parse($fromDateValue)->startOfDay();
                $end = Carbon::parse($toDateValue)->endOfDay();

                return [
                    $start,
                    $end,
                    'custom',
                    $start->toDateString(),
                    $end->toDateString(),
                ];
            } catch (Throwable) {
                $fromDateValue = '';
                $toDateValue = '';
            }
        }

        if ($fromDateValue === '' && $toDateValue === '') {
            $legacyFromDateTime = trim((string) $request->input('from_datetime'));
            $legacyToDateTime = trim((string) $request->input('to_datetime'));
            $legacyFrom = $this->parseDateTime($legacyFromDateTime);
            $legacyTo = $this->parseDateTime($legacyToDateTime);

            if ($legacyFrom || $legacyTo) {
                if ($legacyFrom && $legacyTo) {
                    if ($legacyFrom->greaterThan($legacyTo)) {
                        [$legacyFrom, $legacyTo] = [$legacyTo, $legacyFrom];
                    }
                } elseif ($legacyFrom) {
                    $legacyTo = $legacyFrom->copy()->endOfDay();
                } else {
                    $legacyFrom = $legacyTo?->copy()->startOfDay();
                }

                return [
                    $legacyFrom,
                    $legacyTo,
                    'custom',
                    $legacyFrom?->toDateString(),
                    $legacyTo?->toDateString(),
                ];
            }
        }

        $now = now();
        $start = null;
        $end = null;

        switch ($range) {
            case 'today':
                $start = $now->copy()->subDay()->setTime(17, 0, 0);
                $end = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                break;
            case 'weekly':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'monthly':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'last30':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'last60':
                $start = $now->copy()->subDays(59)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'last90':
                $start = $now->copy()->subDays(89)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'all':
                $start = null;
                $end = null;
                break;
            default:
                $range = 'today';
                $start = $now->copy()->subDay()->setTime(17, 0, 0);
                $end = $now->copy()->endOfDay();
                break;
        }

        return [
            $start,
            $end,
            $range,
            $start?->toDateString(),
            $end?->toDateString(),
        ];
    }

    private function filterOptions(): array
    {
        return [
            'today' => 'Hoy',
            'yesterday' => 'Ayer',
            'weekly' => 'Semana',
            'monthly' => 'Mes',
            'last30' => 'Últimos 30',
            'last60' => 'Últimos 60',
            'last90' => 'Últimos 90',
            'all' => 'Todo',
        ];
    }

    /**
     * @return array{
     *     activity_cards: array<int, array<string, mixed>>,
     *     events_by_day_chart: array<string, mixed>,
     *     top_users_chart: array<string, mixed>,
     *     range_label: string
     * }
     */
    private function buildDashboardMetrics(
        string $search,
        string $action,
        mixed $userId,
        ?Carbon $fromDateTime,
        ?Carbon $toDateTime,
        string $selectedRange
    ): array {
        $summaryQuery = ActivityLog::query()
            ->selectRaw('user_id, COUNT(*) as total_logs, MAX(created_at) as last_activity_at')
            ->whereNotNull('user_id');
        $this->applyFilters($summaryQuery, $search, $action, $userId, $fromDateTime, $toDateTime);

        $userSummaries = $summaryQuery
            ->groupBy('user_id')
            ->orderByDesc('total_logs')
            ->get();

        $allUserIds = $userSummaries
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        if (count($allUserIds) === 0) {
            return [
                'activity_cards' => [],
                'events_by_day_chart' => [
                    'labels' => [],
                    'datasets' => [],
                ],
                'top_users_chart' => [
                    'labels' => [],
                    'actions' => [],
                    'active_minutes' => [],
                    'active_time_labels' => [],
                ],
                'range_label' => $this->buildRangeLabel($fromDateTime, $toDateTime, $selectedRange),
            ];
        }

        $userNames = User::query()
            ->whereIn('id', $allUserIds)
            ->pluck('name', 'id');

        $activeBucketsQuery = ActivityLog::query()
            ->selectRaw('user_id, COUNT(DISTINCT FLOOR(UNIX_TIMESTAMP(created_at) / 300)) as active_buckets')
            ->whereNotNull('user_id');
        $this->applyFilters($activeBucketsQuery, $search, $action, $userId, $fromDateTime, $toDateTime);

        $activeBucketsByUser = $activeBucketsQuery
            ->groupBy('user_id')
            ->pluck('active_buckets', 'user_id');

        $cardUserIds = $userSummaries
            ->take(12)
            ->pluck('user_id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        $latestActionsQuery = ActivityLog::query()
            ->select(['user_id', 'action', 'created_at'])
            ->whereNotNull('user_id')
            ->whereIn('user_id', $cardUserIds)
            ->orderByDesc('created_at');
        $this->applyFilters($latestActionsQuery, $search, $action, $userId, $fromDateTime, $toDateTime);

        $latestActionByUser = $latestActionsQuery
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $now = now();
        $activityCards = [];
        foreach ($userSummaries->take(12) as $summary) {
            $currentUserId = (int) $summary->user_id;
            $lastActivityAt = Carbon::parse($summary->last_activity_at);
            $activeMinutes = ((int) ($activeBucketsByUser->get($currentUserId) ?? 0)) * 5;
            $latestAction = $latestActionByUser->get($currentUserId);
            $isOnline = $lastActivityAt->greaterThanOrEqualTo($now->copy()->subMinutes(15));

            $activityCards[] = [
                'user_id' => $currentUserId,
                'name' => (string) ($userNames->get($currentUserId) ?? ('Usuario #'.$currentUserId)),
                'total_logs' => (int) $summary->total_logs,
                'last_action' => (string) ($latestAction->action ?? 'Sin acción reciente'),
                'last_activity_at_human' => $lastActivityAt->diffForHumans(),
                'active_time_label' => $this->formatActiveMinutes($activeMinutes),
                'status' => $isOnline ? 'online' : 'range',
                'status_label' => $isOnline ? 'Activo ahora' : 'Activo en rango',
            ];
        }

        $eventsByDayQuery = ActivityLog::query()
            ->selectRaw('DATE(created_at) as log_date, user_id, COUNT(*) as total_logs')
            ->whereNotNull('user_id');
        $this->applyFilters($eventsByDayQuery, $search, $action, $userId, $fromDateTime, $toDateTime);

        $eventsByDayRows = $eventsByDayQuery
            ->groupBy('log_date', 'user_id')
            ->orderBy('log_date')
            ->get();

        $dateKeys = [];
        $dateLabels = [];
        if ($fromDateTime && $toDateTime) {
            $cursor = $fromDateTime->copy()->startOfDay();
            $lastDate = $toDateTime->copy()->startOfDay();
            while ($cursor->lessThanOrEqualTo($lastDate)) {
                $dateKeys[] = $cursor->format('Y-m-d');
                $dateLabels[] = $cursor->format('d/m');
                $cursor->addDay();
            }
        } else {
            $dateKeys = $eventsByDayRows
                ->pluck('log_date')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->map(fn ($date): string => (string) $date)
                ->all();
            $dateLabels = array_map(function (string $date): string {
                try {
                    return Carbon::parse($date)->format('d/m');
                } catch (Throwable) {
                    return $date;
                }
            }, $dateKeys);
        }

        $eventsByUserAndDay = [];
        foreach ($eventsByDayRows as $row) {
            $eventsByUserAndDay[(int) $row->user_id][(string) $row->log_date] = (int) $row->total_logs;
        }

        $lineUserIds = $userSummaries
            ->take(6)
            ->pluck('user_id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        $lineDatasets = [];
        foreach ($lineUserIds as $lineUserId) {
            $lineDatasets[] = [
                'label' => (string) ($userNames->get($lineUserId) ?? ('Usuario #'.$lineUserId)),
                'data' => array_map(
                    fn (string $dateKey): int => (int) ($eventsByUserAndDay[$lineUserId][$dateKey] ?? 0),
                    $dateKeys
                ),
            ];
        }

        $otherUserIds = array_values(array_diff($allUserIds, $lineUserIds));
        if (count($otherUserIds) > 0) {
            $lineDatasets[] = [
                'label' => 'Otros usuarios',
                'data' => array_map(
                    function (string $dateKey) use ($otherUserIds, $eventsByUserAndDay): int {
                        $total = 0;
                        foreach ($otherUserIds as $otherUserId) {
                            $total += (int) ($eventsByUserAndDay[$otherUserId][$dateKey] ?? 0);
                        }

                        return $total;
                    },
                    $dateKeys
                ),
            ];
        }

        $topUsersSummary = $userSummaries->take(10);
        $topUsersLabels = [];
        $topUsersActions = [];
        $topUsersActiveMinutes = [];
        $topUsersActiveTimeLabels = [];
        foreach ($topUsersSummary as $summary) {
            $currentUserId = (int) $summary->user_id;
            $currentActiveMinutes = ((int) ($activeBucketsByUser->get($currentUserId) ?? 0)) * 5;

            $topUsersLabels[] = (string) ($userNames->get($currentUserId) ?? ('Usuario #'.$currentUserId));
            $topUsersActions[] = (int) $summary->total_logs;
            $topUsersActiveMinutes[] = $currentActiveMinutes;
            $topUsersActiveTimeLabels[] = $this->formatActiveMinutes($currentActiveMinutes);
        }

        return [
            'activity_cards' => $activityCards,
            'events_by_day_chart' => [
                'labels' => $dateLabels,
                'datasets' => $lineDatasets,
            ],
            'top_users_chart' => [
                'labels' => $topUsersLabels,
                'actions' => $topUsersActions,
                'active_minutes' => $topUsersActiveMinutes,
                'active_time_labels' => $topUsersActiveTimeLabels,
            ],
            'range_label' => $this->buildRangeLabel($fromDateTime, $toDateTime, $selectedRange),
        ];
    }

    private function buildRangeLabel(?Carbon $fromDateTime, ?Carbon $toDateTime, string $selectedRange): string
    {
        if ($fromDateTime && $toDateTime) {
            return $fromDateTime->format('d/m/Y').' - '.$toDateTime->format('d/m/Y');
        }

        if ($selectedRange === 'all') {
            return 'Todo el histórico';
        }

        return 'Rango sin fecha';
    }

    private function formatActiveMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        if ($hours === 0) {
            return $minutes.'m';
        }

        if ($remainingMinutes === 0) {
            return $hours.'h';
        }

        return $hours.'h '.$remainingMinutes.'m';
    }
}
