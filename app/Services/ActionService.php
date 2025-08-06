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

public function filterModel(Request $request, $useDueDate = false)
{
    Log::info('filterModel called', $request->all());

    // Determinar si el filtro es por pendientes o por fecha de creación
    $isPending = $request->input('pending') === 'true';
    $dateColumn = $isPending ? 'due_date' : 'created_at';

    $model = Action::where(function ($query) use ($request, $isPending, $dateColumn) {

        if ($isPending) {
            // Solo pendientes
            $query->whereNull('delivery_date')
                  ->whereNotNull('due_date');
        }

        if ($request->filled('range_type')) {
            $now = Carbon\Carbon::now();

            if ($request->range_type == 'overdue' && $isPending) {
                $query->where('due_date', '<', $now->startOfDay());

            } elseif ($request->range_type == 'today') {
                $query->where(function ($q) use ($now, $isPending) {
                    if ($isPending) {
                        // Pendientes programadas para hoy
                        $q->whereDate('due_date', $now->toDateString());
                    } else {
                        // Acciones creadas hoy
                        $q->whereDate('created_at', $now->toDateString());
                    }
                });

            } elseif ($request->range_type == 'upcoming' && $isPending) {
                $query->where('due_date', '>', $now->endOfDay());
            }

        } elseif ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = Carbon\Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon\Carbon::parse($request->to_date)->endOfDay();
            $query->whereBetween($dateColumn, [$fromDate, $toDate]);
        }

        if ($request->filled('user_id')) {
            $query->where('creator_user_id', $request->user_id);
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('action_search')) {
            $searchTerm = '%' . $request->action_search . '%';
            $query->where('note', 'like', $searchTerm);
        }
    })
    ->orderBy($dateColumn, 'asc')
    ->paginate(15);

    $model->getActualRows = $model->currentPage() * $model->perPage();

    return $model;
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
            

    public function createFilteredRequest($originalRequest, $dateRangeType) {
        $filteredRequest = clone $originalRequest;
        $now = Carbon\Carbon::now();

        switch ($dateRangeType) {
            case 'overdue':
                $fromDate = Carbon\Carbon::createFromTimestamp(0); // desde el inicio de los tiempos
                $toDate = $now->copy()->startOfDay()->subSecond(); // hasta ayer
                break;
            case 'today':
                $fromDate = $now->copy()->startOfDay();
                $toDate = $now->copy()->endOfDay();
                break;
            case 'upcoming':
                $fromDate = $now->copy()->addDay()->startOfDay(); // mañana
                $toDate = $now->copy()->addWeeks(1)->endOfDay(); // hasta 1 semana
                break;
            default:
                throw new \Exception('Invalid date range type');
        }

        $filteredRequest->merge([
            'pending' => true,
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'range_type' => $dateRangeType // 👈 Nuevo
        ]);

        return $filteredRequest;
    }

}
