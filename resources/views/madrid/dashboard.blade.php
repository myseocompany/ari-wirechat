{{-- resources/views/madrid/dashboard.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
  .report-compact .table { font-size: 12px; }
  .report-compact .table-sm td, 
  .report-compact .table-sm th { padding: .35rem .5rem; vertical-align: middle; }
  .report-compact .cell-stacked small { display:block; line-height:1.1; }
  .report-compact .table thead th { position: sticky; top: 0; background: #fff; z-index: 2; }
  /* Fuerza scroll horizontal si no cabe */
  .report-compact .scroll-x { overflow-x: auto; }
  .report-compact .nowrap { white-space: nowrap; }
</style>
@endpush


@section('content')
<div class="container-fluid">

  {{-- Filtros simples --}}
  <form class="row g-2 mb-3">
    <div class="col-auto">
      <input type="date" class="form-control" name="from" value="{{ $from }}">
    </div>
    <div class="col-auto">
      <input type="date" class="form-control" name="to" value="{{ $to }}">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Filtrar</button>
    </div>
  </form>

 {{-- ==== KPIs compactos ==== --}}
@php
  $abbrev = function($n) {
    if ($n === null) return '0';
    $n = (float)$n;
    if ($n >= 1000000) return number_format($n/1000000,1).'M';
    if ($n >= 1000)    return number_format($n/1000,1).'K';
    return number_format($n);
  };
  $rango = \Carbon\Carbon::parse($from)->format('d/m/Y').' → '.\Carbon\Carbon::parse($to)->format('d/m/Y');
@endphp

<div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-2 mb-2">
  <div class="col">
    <div class="card h-100">
      <div class="card-body py-2">
        <div class="small text-muted">Leads España</div>
        <div class="h4 mb-0">{{ $abbrev($kpi_es) }}</div>
        <div class="small text-muted">+34 / ES</div>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card h-100">
      <div class="card-body py-2">
        <div class="small text-muted">Fuente pauta</div>
        <div class="h4 mb-0">{{ $abbrev($es_pauta) }}</div>
        <div class="small text-muted">source_id=76</div>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card h-100">
      <div class="card-body py-2">
        <div class="small text-muted">Entraron por WhatsApp</div>
        <div class="h4 mb-0">{{ $abbrev($es_whatsapp_src) }}</div>
        <div class="small text-muted">source_id=8</div>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card h-100">
      <div class="card-body py-2">
        <div class="small text-muted">Agendados (RSVP)</div>
        <div class="h4 mb-0">{{ $abbrev($agendados_101) }}</div>
        <div class="small text-muted">{{ $rango }}</div>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card h-100">
      <div class="card-body py-2">
        <div class="small text-muted">Emails automáticos</div>
        <div class="h4 mb-0">{{ $abbrev($auto_emails_2) }}</div>
        <div class="small text-muted">type_id=2 · {{ $rango }}</div>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card h-100">
      <div class="card-body py-2">
        <div class="small text-muted">Fabricantes</div>
        <div class="h4 mb-0">{{ $abbrev($fabricantes) }}</div>
        <div class="small text-muted">maker=1</div>
      </div>
    </div>
  </div>
</div>

{{-- KPIs operativos (colapsables) --}}
<div class="mb-3">
  <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#kpisOps" aria-expanded="false">
    Ver actividad detallada
  </button>
  <div class="collapse mt-2" id="kpisOps">
    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-2">
      <div class="col"><div class="card h-100"><div class="card-body py-2">
        <div class="small text-muted">Alcanzados (OUT)</div>
        <div class="h4 mb-0">{{ $abbrev($alcanzados) }}</div>
        <div class="small text-muted">{{ $rango }}</div>
      </div></div></div>

      <div class="col"><div class="card h-100"><div class="card-body py-2">
        <div class="small text-muted">Engaged (IN)</div>
        <div class="h4 mb-0">{{ $abbrev($engaged) }}</div>
        <div class="small text-muted">{{ $rango }}</div>
      </div></div></div>

      <div class="col"><div class="card h-100"><div class="card-body py-2">
        <div class="small text-muted">Mensajes automáticos</div>
        <div class="h4 mb-0">{{ $abbrev($auto_msgs_105) }}</div>
        <div class="small text-muted">type_id=105 · {{ $rango }}</div>
      </div></div></div>

      <div class="col"><div class="card h-100"><div class="card-body py-2">
        <div class="small text-muted">Llamadas automáticas</div>
        <div class="h4 mb-0">{{ $abbrev($auto_calls_104) }}</div>
        <div class="small text-muted">type_id=104 · {{ $rango }}</div>
      </div></div></div>

      <div class="col"><div class="card h-100"><div class="card-body py-2">
        <div class="small text-muted">Mensajes manuales</div>
        <div class="h4 mb-0">{{ $abbrev($manual_msgs_14) }}</div>
        <div class="small text-muted">type_id=14 · {{ $rango }}</div>
      </div></div></div>

      <div class="col"><div class="card h-100"><div class="card-body py-2">
        <div class="small text-muted">Llamadas manuales</div>
        <div class="h4 mb-0">{{ $abbrev($manual_calls) }}</div>
        <div class="small text-muted">1/20/21/106 · {{ $rango }}</div>
      </div></div></div>
    </div>
  </div>
</div>




{{-- LISTADO PRINCIPAL (compacto) --}}
<div class="card report-compact">
  <div class="card-header">
    <strong>Leads España (+34 / ES) — ventana: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</strong>
  </div>

  <div class="card-body p-0 scroll-x">
    <table class="table table-sm mb-0">
      <thead>
        <tr>
          <th class="nowrap">Cliente / Celular</th>

          <th class="nowrap">Auto WA (105)</th>
          <th class="nowrap">Auto Call (104)</th>
          <th class="nowrap">Auto Email (2)</th>
          <th class="nowrap">MSG Manual (14)</th>
          <th class="nowrap">Calls Manual (1,20,21,106)</th>
          <th class="nowrap">Agendados (101)</th>
          <th class="nowrap">Perf. SQL (106)</th>

        </tr>
      </thead>
      <tbody>
        @forelse($leads as $lead)
          <tr>
            {{-- Cliente + teléfono en una sola celda, apilado --}}
            <td class="cell-stacked no-wrap">
              <a href="{{ $lead->link }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
                {{ $lead->name ?? '—' }}
              </a><br>
              <small class="text-muted">{{ $lead->phone ?? '—' }}</small>
              <br>
                            {{ $lead->last_action_name ?? '—' }}
              <small class="text-muted ms-1">
                {{ $lead->last_action_at ? \Carbon\Carbon::parse($lead->last_action_at)->diffForHumans() : '' }}
              </small>
            </td>


            <td class="text-end">{{ $lead->auto_msgs_105 ?? 0 }}</td>
            <td class="text-end">{{ $lead->auto_calls_104 ?? 0 }}</td>
            <td class="text-end">{{ $lead->auto_emails_2 ?? 0 }}</td>
            <td class="text-end">{{ $lead->manual_msgs_14 ?? 0 }}</td>
            <td class="text-end">{{ $lead->manual_calls ?? 0 }}</td>
            <td class="text-end">{{ $lead->agendados_101 ?? 0 }}</td>
            <td class="text-end">{{ $lead->perfilacion_sql_106 ?? 0 }}</td>

 
          </tr>
        @empty
          <tr><td colspan="10" class="text-center p-4">Sin datos</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>


@endsection
