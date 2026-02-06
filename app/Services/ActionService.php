<?php

namespace App\Services;

use App\Models\Action;
use Carbon;
use Illuminate\Http\Request;

class ActionService
{
    // App/Services/ActionService.php

    public function filterModel(Request $request, $useDueDate = false)
    {
        $query = $this->buildBaseQuery($request);
        $dateColumn = $request->input('pending') === 'true' ? 'due_date' : 'created_at';

        // Orden y paginación
        return $query->orderBy($dateColumn, 'asc')->paginate(15);
    }

    /** NUEVO: una sola fuente de verdad para armar el query */
    private function buildBaseQuery(Request $request)
    {
        $isPending = $request->input('pending') === 'true';
        $dateColumn = $isPending ? 'due_date' : 'created_at';

        $hasDates = $request->filled('from_date') && $request->filled('to_date');
        $from = $hasDates
            ? $this->normalizeDateInput($request->input('from_date'), $request->input('from_time'), false)
            : null;
        $to = $hasDates
            ? $this->normalizeDateInput($request->input('to_date'), $request->input('to_time'), true)
            : null;
        $hasRange = $hasDates && $from && $to;

        return Action::query()
            ->select('actions.*')
            ->selectSub(function ($sub) {
                $sub->from('actions as next_actions')
                    ->select('next_actions.note')
                    ->whereColumn('next_actions.customer_id', 'actions.customer_id')
                    ->whereColumn('next_actions.created_at', '>', 'actions.created_at')
                    ->orderByDesc('next_actions.created_at')
                    ->limit(1);
            }, 'next_action_note')
            ->selectSub(function ($sub) {
                $sub->from('actions as next_actions')
                    ->select('next_actions.created_at')
                    ->whereColumn('next_actions.customer_id', 'actions.customer_id')
                    ->whereColumn('next_actions.created_at', '>', 'actions.created_at')
                    ->orderByDesc('next_actions.created_at')
                    ->limit(1);
            }, 'next_action_created_at')
            ->where(function ($query) use ($request, $isPending, $dateColumn, $hasRange, $from, $to) {

                if ($isPending) {
                    $query->whereNull('delivery_date')
                        ->whereNotNull('due_date');
                }

                $rangeType = $request->input('range_type'); // puede venir null

                if ($rangeType) {
                    $now = Carbon\Carbon::now();

                    if ($rangeType === 'overdue' && $isPending) {
                        // Vencidas: antes de hoy, y además respeta el rango si viene
                        $query->where($dateColumn, '<', $now->startOfDay());
                        if ($hasRange) {
                            $query->whereBetween($dateColumn, [$from, $to]);
                        }

                    } elseif ($rangeType === 'today') {
                        // Hoy: del inicio al fin de hoy, y además respeta el rango si viene
                        $query->whereDate($dateColumn, $now->toDateString());
                        if ($hasRange) {
                            $query->whereBetween($dateColumn, [$from, $to]);
                        }

                    } elseif ($rangeType === 'upcoming' && $isPending) {
                        // Próximas: después de hoy, y además respeta el rango si viene
                        $query->where($dateColumn, '>', $now->endOfDay());
                        if ($hasRange) {
                            $query->whereBetween($dateColumn, [$from, $to]);
                        }

                    } elseif ($rangeType === 'all') {
                        // Todas: solo aplica el rango si viene
                        if ($hasRange) {
                            $query->whereBetween($dateColumn, [$from, $to]);
                        }
                    }

                } elseif ($hasRange) {
                    // Sin range_type: usa únicamente el rango explícito
                    $query->whereBetween($dateColumn, [$from, $to]);
                }

                // Filtro de usuario: si es pending usamos el owner del cliente, sino el creador
                if ($request->filled('user_id')) {
                    if ($isPending) {
                        $query->whereHas('customer', function ($q) use ($request) {
                            $q->where('user_id', $request->user_id);
                        });
                    } else {
                        $query->where('creator_user_id', $request->user_id);
                    }
                }
                if ($request->filled('type_id')) {
                    $query->where('type_id', $request->type_id);
                }
                if ($request->filled('status_id')) {
                    $query->whereHas('customer', function ($q) use ($request) {
                        $q->where('status_id', $request->status_id);
                    });
                }
                if ($request->filled('action_search')) {
                    $query->where('note', 'like', '%'.$request->action_search.'%');
                }
                if ($request->boolean('has_audio')) {
                    $query->whereNotNull('url')
                        ->where('url', '!=', '');
                }
            });
    }

    /**
     * Normaliza la fecha del filtro combinándola con la hora (si existe).
     */
    private function normalizeDateInput(?string $dateValue, ?string $timeValue, bool $isEndOfRange): ?Carbon\Carbon
    {
        if (! $dateValue) {
            return null;
        }

        $dateValue = trim($dateValue);
        $dateIncludesTime = preg_match('/\d{2}:\d{2}/', $dateValue) === 1;
        $dateIncludesSeconds = preg_match('/\d{2}:\d{2}:\d{2}/', $dateValue) === 1;

        $date = Carbon\Carbon::parse($dateValue);

        if ($timeValue !== null && $timeValue !== '') {
            [$hour, $minute] = array_pad(explode(':', $timeValue), 2, '0');
            $hour = (int) $hour;
            $minute = (int) $minute;
            $second = $isEndOfRange ? 59 : 0;
            $date->setTime($hour, $minute, $second);

        } elseif (! $dateIncludesTime) {
            $isEndOfRange ? $date->endOfDay() : $date->startOfDay();

        } elseif ($isEndOfRange && ! $dateIncludesSeconds) {
            $date->endOfMinute();
        }

        return $date;
    }

    /** ACTUALIZA: respeta fechas y reusa la query base */
    public function countAllMatching(Request $request)
    {
        return $this->buildBaseQuery($request)->count();
    }

    public function getAll(Request $request)
    {
        // Iniciar el query base para acciones pendientes
        $query = Action::whereNotNull('due_date')->whereNull('delivery_date');

        if ($request->filled('user_id')) {
            $query = $query->where('creator_user_id', $request->user_id);
        }

        if ($request->filled('type_id')) {
            $query = $query->where('type_id', $request->type_id);
        }

        // Ordenar los resultados
        $query = $query->orderBy('updated_at', 'desc')->orderBy('type_id', 'asc');

        // Paginar los resultados
        $model = $query->get();

        return $model;
    }

    public function createFilteredRequest($originalRequest, $dateRangeType, $forcePendingOnly = true)
    {
        $filteredRequest = new Request;

        $filters = ['user_id', 'type_id', 'status_id', 'action_search', 'from_date', 'to_date', 'from_time', 'to_time', 'has_audio'];
        foreach ($filters as $filter) {
            if ($originalRequest->has($filter)) {
                $filteredRequest->merge([
                    $filter => $originalRequest->input($filter),
                ]);
            }
        }

        $now = Carbon\Carbon::now();

        switch ($dateRangeType) {
            case 'overdue':
                $fromDate = Carbon\Carbon::createFromTimestamp(0);
                $toDate = $now->copy()->startOfDay()->subSecond();
                break;
            case 'today':
                $fromDate = $now->copy()->startOfDay();
                $toDate = $now->copy()->endOfDay();
                break;
            case 'upcoming':
                $fromDate = $now->copy()->addDay()->startOfDay();
                $toDate = $now->copy()->addWeeks(1)->endOfDay();
                break;
            default:
                throw new \Exception('Invalid date range type');
        }

        // Si from_date y to_date vienen del request original, respétalos, si no, aplicar el preset
        if (! $originalRequest->filled('from_date') || ! $originalRequest->filled('to_date')) {
            $filteredRequest->merge([
                'from_date' => $fromDate->toDateString(),
                'to_date' => $toDate->toDateString(),
                'from_time' => '00:00',
                'to_time' => '23:59',
            ]);
        }

        if ($forcePendingOnly) {
            $filteredRequest->merge([
                'pending' => 'true',
            ]);
        }

        $filteredRequest->merge([
            'range_type' => $dateRangeType,
        ]);

        return $filteredRequest;
    }
}
