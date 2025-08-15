<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MadridDashboardController extends Controller
{
    // app/Http/Controllers/MadridDashboardController.php

public function index(Request $request)
{
    // 1) rango "hoy" por defecto
$from = $request->input('from') ?? '2025-08-01';
$to   = $request->input('to') ?? now()->toDateString();


    if (!$from || !$to) {
        $from = now()->startOfDay()->toDateTimeString(); // hoy 00:00
        $to   = now()->toDateTimeString();               // ahora
    }

    // base ES desde la vista
    $esBase = DB::table('v_madrid2025_unified')->select('customer_id')->where('is_es', 1);

    // ===== LISTA (top 100) con métricas por cliente =====
    $lastSub = DB::table('actions')
        ->selectRaw('customer_id, MAX(created_at) as last_action_at')
        ->groupBy('customer_id');

    $leads = DB::table('customers as c')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.customer_id','=','c.id'))
        ->leftJoin('actions as a', function($j) use ($from,$to){
            $j->on('a.customer_id','=','c.id')
              ->whereBetween('a.created_at', [$from,$to]); // 👈 mismas fechas
        })
        ->leftJoinSub($lastSub, 'la', fn($j)=>$j->on('la.customer_id','=','c.id'))
        ->leftJoin('actions as a_last', function($j){
            $j->on('a_last.customer_id','=','c.id')
              ->on('a_last.created_at','=','la.last_action_at');
        })
        ->leftJoin('action_types as at_last', 'at_last.id','=','a_last.type_id')
        ->selectRaw("
            c.id,
            COALESCE(c.business,c.name)  AS name,
            c.country,
            COALESCE(c.phone_wp,c.phone,c.phone2) AS phone,
            c.email,

            -- últimos hitos (por si los necesitas en la tabla)
            MAX(CASE WHEN a.type_id=101 THEN a.created_at END) AS last_rsvp_at,
            MAX(CASE WHEN a.type_id=102 THEN a.created_at END) AS last_attended_at,
            MAX(CASE WHEN a.type_id=103 THEN a.created_at END) AS last_noshow_at,

            -- contadores 'como en los indicadores'
            SUM(a.type_id=105) AS auto_msgs_105,
            SUM(a.type_id=104) AS auto_calls_104,
            SUM(a.type_id=2)   AS auto_emails_2,
            SUM(a.type_id=14)  AS manual_msgs_14,
            SUM(a.type_id IN (1,20,21,106)) AS manual_calls,
            SUM(a.type_id=101) AS agendados_101,
            SUM(a.type_id=106) AS perfilacion_sql_106,

            -- último evento (global, no filtrado) para mostrar etiqueta
            la.last_action_at,
            at_last.name       AS last_action_name
        ")
        ->groupBy('c.id','c.business','c.name','c.country','c.phone_wp','c.phone','c.phone2','c.email','la.last_action_at','at_last.name')
        ->orderByDesc('la.last_action_at')
        ->limit(10)
        ->get()
        ->map(function($row){
            $row->link = "https://arichat.co/customers/{$row->id}/show";
            return $row;
        });

    // ===== KPIs (una sola vez, mismo rango) =====
    $kpi_es          = $this->kpiCustomerCount();
    $es_pauta        = $this->kpiCustomerCount(sourceId:76);
    $es_whatsapp_src = $this->kpiCustomerCount(sourceId:8);

    $alcanzados  = $this->kpiActionCount(outbound:1, from:$from, to:$to);
    $engaged     = $this->kpiActionCount(typeIds:[4,23,8,10,11,6,7], from:$from, to:$to);
    $calificados = DB::table('customers as c')
                        ->joinSub($esBase,'es',fn($j)=>$j->on('es.customer_id','=','c.id'))
                        ->where('c.scoring',1)->count();

    $rsvps      = $this->kpiActionCount(typeIds:[101], from:$from, to:$to);
    $asistieron = $this->kpiActionCount(typeIds:[102], from:$from, to:$to);
    $noshow     = $this->kpiActionCount(typeIds:[103], from:$from, to:$to);
    $tasa_show  = $rsvps>0 ? round(($asistieron/$rsvps)*100,1) : 0.0;

    $auto_msgs_105   = $this->kpiActionCount(typeIds:[105], from:$from, to:$to);
    $auto_calls_104  = $this->kpiActionCount(typeIds:[104], from:$from, to:$to);
    $auto_emails_2   = $this->kpiActionCount(typeIds:[2],   from:$from, to:$to);
    $manual_msgs_14  = $this->kpiActionCount(typeIds:[14],  from:$from, to:$to);
    $manual_calls    = $this->kpiActionCount(typeIds:[1,20,21,106], from:$from, to:$to);
    $agendados_101   = $this->kpiActionCount(typeIds:[101], from:$from, to:$to);
    $perfilacion_sql_106 = $this->kpiActionCount(typeIds:[106], from:$from, to:$to);

    $fabricantes = $this->kpiCustomerCount(maker:1);

    return view('madrid.dashboard', compact(
        'from','to','leads',
        'kpi_es','es_pauta','es_whatsapp_src',
        'alcanzados','engaged','calificados','rsvps','asistieron','noshow','tasa_show',
        'auto_msgs_105','auto_calls_104','auto_emails_2',
        'manual_msgs_14','manual_calls','agendados_101','perfilacion_sql_106',
        'fabricantes'
    ));
}


    /**
     * Cuenta acciones en base ES (v_madrid2025_unified->is_es=1).
     * Permite:
     *  - typeIds: array/int de type_id (si null, se puede filtrar por outbound=1)
     *  - outbound: 1 para “acciones de salida” según action_types
     *  - from/to: rango en a.created_at
     *  - onlyPauta: si true limita a clientes con is_pauta=1
     */
    private function kpiActionCount(
        array|int|null $typeIds = null,
        ?string $from = null,
        ?string $to = null,
        bool $onlyPauta = false,
        int $outbound = 0
    ): int {
        $v = DB::table('v_madrid2025_unified as v')
              ->select('v.customer_id')
              ->where('v.is_es', 1)
              ->when($onlyPauta, fn($q)=>$q->where('v.is_pauta', 1));

        $q = DB::table('actions as a')
            ->joinSub($v, 'es', fn($j)=>$j->on('es.customer_id', '=', 'a.customer_id'))
            ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at', [$from, $to]));

        if ($typeIds) {
            $typeIds = is_array($typeIds) ? $typeIds : [$typeIds];
            $q->whereIn('a.type_id', $typeIds);
        }

        if ($outbound === 1) {
            $q->whereExists(function($w){
                $w->select(DB::raw(1))
                  ->from('action_types as at')
                  ->whereColumn('at.id', 'a.type_id')
                  ->where('at.outbound', 1);
            });
        }

        return (int) $q->count();
    }

    /**
     * Cuenta clientes en base ES (v_madrid2025_unified->is_es=1).
     * Filtros opcionales: sourceId, maker (1/0), onlyPauta.
     */
    private function kpiCustomerCount(
        ?int $sourceId = null,
        ?int $maker = null,
        bool $onlyPauta = false
    ): int {
        $v = DB::table('v_madrid2025_unified')->where('is_es', 1);
        if ($onlyPauta) $v->where('is_pauta', 1);

        return (int) DB::table('customers as c')
            ->joinSub($v->select('customer_id'), 'es', fn($j)=>$j->on('es.customer_id','=','c.id'))
            ->when($sourceId, fn($q)=>$q->where('c.source_id', $sourceId))
            ->when(isset($maker), fn($q)=>$q->where('c.maker', $maker))
            ->count();
    }
}
