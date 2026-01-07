<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = ActivityLog::with('user')->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', '%'.$search.'%')
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

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $actions = $this->getAvailableActions();

        $logs = $query->paginate($perPage)->withQueryString();

        return view('activity_logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'search' => $search,
            'perPage' => $perPage,
            'action' => $action,
            'userId' => $userId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
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
}
