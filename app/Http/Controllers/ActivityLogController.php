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
        $fromDateTime = (string) $request->get('from_datetime');
        $toDateTime = (string) $request->get('to_datetime');
        $parsedFromDateTime = $this->parseDateTime($fromDateTime);
        $parsedToDateTime = $this->parseDateTime($toDateTime);

        $query = ActivityLog::with('user')->orderByDesc('id');
        $this->applyFilters($query, $search, $action, $userId, $parsedFromDateTime, $parsedToDateTime);

        $actions = $this->getAvailableActions();
        $users = $this->getAvailableUsers();

        $logs = $query->paginate($perPage)->withQueryString();
        [$dashboardFromDateTime, $dashboardToDateTime] = $this->resolveDashboardRange($parsedFromDateTime, $parsedToDateTime);
        $dashboardMetrics = $this->buildDashboardMetrics(
            $search,
            $action,
            $userId,
            $dashboardFromDateTime,
            $dashboardToDateTime
        );

        return view('activity_logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'search' => $search,
            'perPage' => $perPage,
            'action' => $action,
            'userId' => $userId,
            'fromDateTime' => $fromDateTime,
            'toDateTime' => $toDateTime,
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
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDashboardRange(?Carbon $fromDateTime, ?Carbon $toDateTime): array
    {
        if ($fromDateTime && $toDateTime) {
            $from = $fromDateTime->copy();
            $to = $toDateTime->copy();
        } elseif ($fromDateTime) {
            $from = $fromDateTime->copy();
            $to = now();
        } elseif ($toDateTime) {
            $to = $toDateTime->copy();
            $from = $to->copy()->subDays(6)->startOfDay();
        } else {
            $to = now();
            $from = $to->copy()->subDays(6)->startOfDay();
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
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
        Carbon $fromDateTime,
        Carbon $toDateTime
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
                'range_label' => $fromDateTime->format('d/m/Y H:i').' - '.$toDateTime->format('d/m/Y H:i'),
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

        $dateKeys = [];
        $dateLabels = [];
        $cursor = $fromDateTime->copy()->startOfDay();
        $lastDate = $toDateTime->copy()->startOfDay();
        while ($cursor->lessThanOrEqualTo($lastDate)) {
            $dateKeys[] = $cursor->format('Y-m-d');
            $dateLabels[] = $cursor->format('d/m');
            $cursor->addDay();
        }

        $eventsByDayQuery = ActivityLog::query()
            ->selectRaw('DATE(created_at) as log_date, user_id, COUNT(*) as total_logs')
            ->whereNotNull('user_id');
        $this->applyFilters($eventsByDayQuery, $search, $action, $userId, $fromDateTime, $toDateTime);

        $eventsByDayRows = $eventsByDayQuery
            ->groupBy('log_date', 'user_id')
            ->orderBy('log_date')
            ->get();

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
            'range_label' => $fromDateTime->format('d/m/Y H:i').' - '.$toDateTime->format('d/m/Y H:i'),
        ];
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
