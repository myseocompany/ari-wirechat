<?php 

// app/Http/Controllers/MadridDashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MadridLead;

class MadridDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filtros opcionales
        $from = $request->input('from'); // yyyy-mm-dd
        $to   = $request->input('to');   // yyyy-mm-dd
        $ownerId = $request->input('owner_id'); // si lo usas
        $pais = 'España';

        // Base query (vista ya trae España + #Tour_Madrid_Pauta)
        $q = MadridLead::query();

        if ($from && $to) {
            // Filtrar por ventana de acciones/RSVP (si quieres acotar periodo)
            $q->where(function ($qq) use ($from, $to) {
                $qq->whereBetween('last_rsvp_at', [$from, $to])
                   ->orWhereBetween('last_attended_at', [$from, $to])
                   ->orWhereBetween('last_noshow_at', [$from, $to])
                   ->orWhereBetween('last_action_at', [$from, $to]);
            });
        }

        // Tabla principal (primeros 100 por defecto)
        $leads = $q->orderByDesc('last_action_at')->limit(100)->get();

        // Métricas de tarjetas (LEADs)
        $alcanzados = DB::table('actions as a')
            ->join('v_madrid2025_es as v', 'v.customer_id', '=', 'a.customer_id')
            ->when($from && $to, fn($qq) => $qq->whereBetween('a.created_at', [$from, $to]))
            ->whereExists(function($w){
                $w->select(DB::raw(1))
                  ->from('action_types as at')
                  ->whereColumn('at.id','a.type_id')
                  ->where('at.outbound',1); // WhatsApp/Email de salida/Llamada salida
            })
            ->count();

        $engaged = DB::table('actions as a')
            ->join('v_madrid2025_es as v', 'v.customer_id', '=', 'a.customer_id')
            ->when($from && $to, fn($qq) => $qq->whereBetween('a.created_at', [$from, $to]))
            ->whereIn('a.type_id',[4,23,8,10,11,6,7]) // abrió email, email in, WA in, visitó MQE, etc.
            ->count();

        $calificados = DB::table('customers as c')
            ->join('v_madrid2025_es as v', 'v.customer_id', '=', 'c.id')
            ->where('c.scoring','=',1) // o tu lógica BANT/score
            ->count();

        $rsvps = DB::table('actions as a')
            ->join('v_madrid2025_es as v', 'v.customer_id', '=', 'a.customer_id')
            ->when($from && $to, fn($qq) => $qq->whereBetween('a.created_at', [$from, $to]))
            ->where('a.type_id',101)->count();

        $asistieron = DB::table('actions as a')
            ->join('v_madrid2025_es as v', 'v.customer_id', '=', 'a.customer_id')
            ->when($from && $to, fn($qq) => $qq->whereBetween('a.created_at', [$from, $to]))
            ->where('a.type_id',102)->count();

        $noshow = DB::table('actions as a')
            ->join('v_madrid2025_es as v', 'v.customer_id', '=', 'a.customer_id')
            ->when($from && $to, fn($qq) => $qq->whereBetween('a.created_at', [$from, $to]))
            ->where('a.type_id',103)->count();

        $tasa_show = $rsvps > 0 ? round(($asistieron / $rsvps) * 100, 1) : 0.0;


        $kpi_es = DB::table('v_es_customers')->count();

        // Leads por pauta #Tour_Madrid_Pauta (distintos)
        $kpi_pauta = DB::table('v_pauta_customers')->count();

        // ==== LISTAS (top 100) ====
        // España
        $list_es = DB::table('customers as c')
            ->join('v_es_customers as es', 'es.customer_id','=','c.id')
            ->leftJoin('v_event_status as st','st.customer_id','=','c.id')
            ->selectRaw('c.id, COALESCE(c.business,c.name) as name, c.country, COALESCE(c.phone_wp,c.phone,c.phone2) as phone, c.email, st.last_rsvp_at, st.last_attended_at, st.last_noshow_at')
            ->when($from && $to, function($q) use ($from,$to){
                $q->where(function($qq) use ($from,$to){
                    $qq->whereBetween('st.last_rsvp_at', [$from,$to])
                       ->orWhereBetween('st.last_attended_at', [$from,$to])
                       ->orWhereBetween('st.last_noshow_at', [$from,$to])
                       ->orWhereBetween('c.updated_at', [$from,$to]);
                });
            })
            ->orderByDesc('st.last_rsvp_at')
            ->limit(100)->get();

        // Pauta
        $list_pauta = DB::table('customers as c')
            ->join('v_pauta_customers as pa', 'pa.customer_id','=','c.id')
            ->leftJoin('v_event_status as st','st.customer_id','=','c.id')
            ->selectRaw('c.id, COALESCE(c.business,c.name) as name, c.country, COALESCE(c.phone_wp,c.phone,c.phone2) as phone, c.email, st.last_rsvp_at, st.last_attended_at, st.last_noshow_at')
            ->when($from && $to, function($q) use ($from,$to){
                $q->where(function($qq) use ($from,$to){
                    $qq->whereBetween('st.last_rsvp_at', [$from,$to])
                       ->orWhereBetween('st.last_attended_at', [$from,$to])
                       ->orWhereBetween('st.last_noshow_at', [$from,$to])
                       ->orWhereBetween('c.updated_at', [$from,$to]);
                });
            })
            ->orderByDesc('st.last_rsvp_at')
            ->limit(100)->get();

        return view('madrid.dashboard', compact(
            'leads','alcanzados','engaged','calificados','rsvps','asistieron','noshow','tasa_show', 'kpi_es','kpi_pauta','list_es','list_pauta','from','to'
        ));
    }
}
