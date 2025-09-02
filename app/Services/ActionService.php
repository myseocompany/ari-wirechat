<?php 

namespace App\Services;

use Illuminate\Http\Request;
use App\Customer;
use App\CustomerStatus;
use App\Models\Action;
use Illuminate\Support\Facades\Log;

use Carbon;
use DB;


class ActionService{

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
    $isPending  = $request->input('pending') === 'true';
    $dateColumn = $isPending ? 'due_date' : 'created_at';

    $hasDates = $request->filled('from_date') && $request->filled('to_date');
    $from = $hasDates ? Carbon\Carbon::parse($request->from_date)->startOfDay() : null;
    $to   = $hasDates ? Carbon\Carbon::parse($request->to_date)->endOfDay()   : null;

    return Action::where(function ($query) use ($request, $isPending, $dateColumn, $hasDates, $from, $to) {

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
                if ($hasDates) $query->whereBetween($dateColumn, [$from, $to]);

            } elseif ($rangeType === 'today') {
                // Hoy: del inicio al fin de hoy, y además respeta el rango si viene
                $query->whereDate($dateColumn, $now->toDateString());
                if ($hasDates) $query->whereBetween($dateColumn, [$from, $to]);

            } elseif ($rangeType === 'upcoming' && $isPending) {
                // Próximas: después de hoy, y además respeta el rango si viene
                $query->where($dateColumn, '>', $now->endOfDay());
                if ($hasDates) $query->whereBetween($dateColumn, [$from, $to]);

            } elseif ($rangeType === 'all') {
                // Todas: solo aplica el rango si viene
                if ($hasDates) $query->whereBetween($dateColumn, [$from, $to]);
            }

        } else if ($hasDates) {
            // Sin range_type: usa únicamente el rango explícito
            $query->whereBetween($dateColumn, [$from, $to]);
        }

        if ($request->filled('user_id'))   $query->where('creator_user_id', $request->user_id);
        if ($request->filled('type_id'))   $query->where('type_id', $request->type_id);
        if ($request->filled('action_search')) {
            $query->where('note', 'like', '%'.$request->action_search.'%');
        }
    });
}


/** ACTUALIZA: respeta fechas y reusa la query base */
public function countAllMatching(Request $request)
{
    return $this->buildBaseQuery($request)->count();
}


    public function getAll(Request $request) {
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
    $filteredRequest = new Request();

    $filters = ['user_id', 'type_id', 'action_search', 'from_date', 'to_date'];
    foreach ($filters as $filter) {
        if ($originalRequest->has($filter)) {
            $filteredRequest->merge([
                $filter => $originalRequest->input($filter)
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
    if (!$originalRequest->filled('from_date') || !$originalRequest->filled('to_date')) {
        $filteredRequest->merge([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
        ]);
    }

    if ($forcePendingOnly) {
        $filteredRequest->merge([
            'pending' => 'true'
        ]);
    }

    $filteredRequest->merge([
        'range_type' => $dateRangeType
    ]);

    return $filteredRequest;
}




}
