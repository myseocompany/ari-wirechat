<?php

// app/Http/Controllers/Madrid2025Controller.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventMadridParticipation as Part;
use App\Models\EventMadridKpis as Kpis;

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
}
