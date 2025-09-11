<?php

// app/Http/Controllers/Madrid2025Controller.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventMadridParticipation as Part;
use App\Models\EventMadridKpis as Kpis;

use Illuminate\Support\Facades\DB;

class Madrid2025Controller extends Controller
{
    public function dashboard(Request $r) {
        $q = Part::query();

        // Filtros
        if ($r->filled('country'))  $q->where('country', $r->country);
        if ($r->filled('owner_id')) $q->where('owner_id', $r->owner_id);
        if ($r->filled('from') && $r->filled('to')) {
            $q->whereBetween('last_action_at', [$r->from, $r->to]);
        }

        // KPI (una fila)
        $kpis = Kpis::query()->first();

        // Listado (paginado)
        $list = $q->orderByDesc('last_action_at')->paginate(25);

        // Mix por canal (para mini-gráficos)
        $mix = [
            'wa_out'       => (int) $list->sum('wa_out'),
            'wa_in'        => (int) $list->sum('wa_in'),
            'emails_out'   => (int) $list->sum('emails_out'),
            'email_open'   => (int) $list->sum('email_open'),
            'calls_out'    => (int) $list->sum('calls_out'),
            'ai_sessions'  => (int) $list->sum('ai_interactions'),
        ];

        return view('madrid2025.dashboard', compact('kpis','list','mix'));
    }

    public function export(Request $r) {
        $rows = Part::query()->get();
        $csv = implode(",", array_keys($rows->first()->getAttributes()))."\n";
        foreach ($rows as $row) $csv .= implode(",", array_map(fn($v)=>str_replace([",","\n"],[";"," "], (string)$v), $row->getAttributes()))."\n";
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="madrid2025_participation.csv"'
        ]);
    }


public function schedule(Request $r) {
    $from = '2025-09-16 00:00:00';
    $to   = '2025-09-17 23:59:59';

    $q = DB::table('actions')
        ->join('customers', 'actions.customer_id', '=', 'customers.id')
        ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
        ->select(
            'actions.id as action_id',
            'actions.due_date',
            'actions.delivery_date',
            'actions.note',
            'customers.id as customer_id',
            'customers.name',
            'customers.phone',
            'customers.maker',
            DB::raw('COUNT(orders.id) > 0 as has_orders')
        )
        ->where('actions.type_id', 101)
        ->where('customers.notes', 'like', '%#EspañaAgenda2025%')
        ->whereBetween('actions.due_date', [$from, $to])
        ->groupBy('customers.id', 'actions.id', 'actions.due_date', 'actions.delivery_date', 'actions.note', 'customers.name', 'customers.phone', 'customers.maker');

    // Filtros opcionales
    if ($r->filled('maker')) {
        if ($r->maker === 'null') {
            $q->whereNull('customers.maker');
        } else {
            $q->where('customers.maker', (int) $r->maker);
        }
    }

    if ($r->filled('orders')) {
        if ($r->orders === 'yes') {
            $q->having('has_orders', '=', 1);
        } elseif ($r->orders === 'no') {
            $q->having('has_orders', '=', 0);
        }
    }

    $acciones = $q->orderBy('actions.due_date')->get();

    // Clientes sin acción type_id=101 en este rango
$customersSinAgenda = DB::table('customers')
    ->where('notes', 'like', '%#EspañaAgenda2025%')
    ->whereNotIn('id', function ($q) use ($from, $to) {
        $q->select('customer_id')
          ->from('actions')
          ->where('type_id', 101)
          ->whereBetween('due_date', [$from, $to]);
    })
    ->select(
        'id as customer_id',
        'name',
        'phone',
        'maker',
        DB::raw('(SELECT COUNT(*) FROM orders WHERE orders.customer_id = customers.id) > 0 as has_orders')
    )
    ->get();

    $totalSinAgenda = $customersSinAgenda->count();

    return view('madrid.schedule', [
        'acciones' => $acciones,
        'from' => $from,
        'to' => $to,
        'maker_filter' => $r->maker,
        'orders_filter' => $r->orders,
        'customersSinAgenda' => $customersSinAgenda,
        'totalSinAgenda' => $totalSinAgenda
    ]);
}

}
