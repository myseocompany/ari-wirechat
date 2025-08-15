<?php 

// app/Http/Controllers/MadridDashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MadridLead;
use App\Models\MadridUnified;

class MadridDashboardController extends Controller
{
    

public function index(Request $request)
{
    // Filtros opcionales
    $from = $request->input('from'); // yyyy-mm-dd
    $to   = $request->input('to');   // yyyy-mm-dd

    // ========== BASE: clientes ES (tel +34/34, país ES/España/Spain, o tag #España) ==========
    // La armamos como subconsulta para reusarla en todos los KPIs
    $esBase = MadridUnified::select('customer_id');

    // ========= LISTA principal (primeros 100 de la base ES) =========
        $leads = DB::table('customers as c')
            ->joinSub($esBase, 'es', fn($j) => $j->on('es.customer_id', '=', 'c.id'))
            ->leftJoin('actions as a', 'a.customer_id', '=', 'c.id')
            ->selectRaw("
                c.id,
                COALESCE(c.business,c.name)  as name,
                c.country,
                COALESCE(c.phone_wp,c.phone,c.phone2) as phone,
                c.email,
                MAX(CASE WHEN a.type_id=101 THEN a.created_at END) as last_rsvp_at,
                MAX(CASE WHEN a.type_id=102 THEN a.created_at END) as last_attended_at,
                MAX(CASE WHEN a.type_id=103 THEN a.created_at END) as last_noshow_at,
                MAX(a.created_at)                                     as last_action_at
            ")
            ->when($from && $to, fn($q) => $q->whereBetween('a.created_at', [$from, $to]))
            ->groupBy('c.id','c.business','c.name','c.country','c.phone_wp','c.phone','c.phone2','c.email')
            ->orderByDesc('last_action_at')
            ->limit(100)
            ->get()
            ->map(function($row){
                $row->link = "https://arichat.co/customers/{$row->id}/show";
                return $row;
            });

    // ========= KPIs existentes (mantén si los usas) =========
    // Alcanzados (salientes) sobre base ES
    $alcanzados = DB::table('actions as a')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at', [$from,$to]))
        ->whereExists(function($w){
            $w->select(DB::raw(1))
              ->from('action_types as at')
              ->whereColumn('at.id','a.type_id')
              ->where('at.outbound',1);
        })
        ->count();

    // Engaged (inbound/abren) sobre base ES
    $engaged = DB::table('actions as a')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at', [$from,$to]))
        ->whereIn('a.type_id',[4,23,8,10,11,6,7])
        ->count();

    // Calificados (score=1) sobre base ES
    $calificados = DB::table('customers as c')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.id','=','c.id'))
        ->where('c.scoring','=',1)
        ->count();

    // RSVPs / Asistieron / No-show (por acciones 101/102/103) sobre base ES
    $rsvps = DB::table('actions as a')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at', [$from,$to]))
        ->where('a.type_id',101)->count();

    $asistieron = DB::table('actions as a')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at', [$from,$to]))
        ->where('a.type_id',102)->count();

    $noshow = DB::table('actions as a')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at', [$from,$to]))
        ->where('a.type_id',103)->count();

    $tasa_show = $rsvps > 0 ? round(($asistieron / $rsvps) * 100, 1) : 0.0;

    // ========= NUEVOS KPIs solicitados (todos sobre base ES) =========

    // 1) Total clientes ES (para tarjeta "Leads España")
    $kpi_es = DB::query()->fromSub($esBase, 'es')->count();

    // 2) De esos, fuente pauta (source_id = 76)
    $es_pauta = DB::table('customers as c')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.id','=','c.id'))
        ->where('c.source_id', 76)
        ->count();

    // 3) De esos, los que entraron por WhatsApp (source_id = 8)
    $es_whatsapp_src = DB::table('customers as c')
        ->joinSub($esBase, 'es', fn($j)=>$j->on('es.id','=','c.id'))
        ->where('c.source_id', 8)
        ->count();

    // 4) Mensajes automáticos hechos (type_id = 105)  [totales]
    $auto_msgs_105 = DB::table('actions as a')
        ->joinSub($esBase,'es',fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at',[$from,$to]))
        ->where('a.type_id',105)->count();

    // 5) Llamadas automáticas (type_id = 104)
    $auto_calls_104 = DB::table('actions as a')
        ->joinSub($esBase,'es',fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at',[$from,$to]))
        ->where('a.type_id',104)->count();

    // 6) Emails automáticos (type_id = 2)
    $auto_emails_2 = DB::table('actions as a')
        ->joinSub($esBase,'es',fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at',[$from,$to]))
        ->where('a.type_id',2)->count();

    // 7) Mensajes manuales (type_id = 14)
    $manual_msgs_14 = DB::table('actions as a')
        ->joinSub($esBase,'es',fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at',[$from,$to]))
        ->where('a.type_id',14)->count();

    // 8) Llamadas manuales (type_id IN 1,20,21,106)
    $manual_calls = DB::table('actions as a')
        ->joinSub($esBase,'es',fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at',[$from,$to]))
        ->whereIn('a.type_id',[1,20,21,106])->count();

    // 9) Agendados (type_id = 101)
    $agendados_101 = DB::table('actions as a')
        ->joinSub($esBase,'es',fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at',[$from,$to]))
        ->where('a.type_id',101)->count();

    // 10) Llamadas de perfilación SQL (type_id = 106)  [ya incluidas en manual_calls, pero lo separamos]
    $perfilacion_sql_106 = DB::table('actions as a')
        ->joinSub($esBase,'es',fn($j)=>$j->on('es.id','=','a.customer_id'))
        ->when($from && $to, fn($q)=>$q->whereBetween('a.created_at',[$from,$to]))
        ->where('a.type_id',106)->count();

    // 11) Fabricantes (maker = 1) dentro de la base ES
    $fabricantes = DB::table('customers as c')
        ->joinSub($esBase,'es',fn($j)=>$j->on('es.id','=','c.id'))
        ->where('c.maker',1)
        ->count();

    // ========= Render =========
    return view('madrid.dashboard', compact(
        // lista
        'leads','from','to',
        // KPIs existentes
        'alcanzados','engaged','calificados','rsvps','asistieron','noshow','tasa_show',
        // KPIs ES/pauta y nuevos
        'kpi_es','es_pauta','es_whatsapp_src',
        'auto_msgs_105','auto_calls_104','auto_emails_2',
        'manual_msgs_14','manual_calls','agendados_101','perfilacion_sql_106',
        'fabricantes'
    ));
}

}
