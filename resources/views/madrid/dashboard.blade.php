{{-- resources/views/madrid/dashboard.blade.php --}}
@extends('layouts.app')

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

  {{-- Tarjetas KPI --}}
  <div class="row mb-4">
    <div class="col-md-2">
      <div class="card"><div class="card-body">
        <h6>Alcanzados (LEAD)</h6>
        <h2 class="mb-0">{{ number_format($alcanzados) }}</h2>
        <small>WA/Email/Llamada salida</small>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card"><div class="card-body">
        <h6>Engaged (LEAD)</h6>
        <h2 class="mb-0">{{ number_format($engaged) }}</h2>
        <small>Respondieron / abrieron</small>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card"><div class="card-body">
        <h6>Calificados (LEAD)</h6>
        <h2 class="mb-0">{{ number_format($calificados) }}</h2>
        <small>BANT/Score</small>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card"><div class="card-body">
        <h6>RSVP con fecha/hora</h6>
        <h2 class="mb-0">{{ number_format($rsvps) }}</h2>
        <small>Action type 101</small>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card"><div class="card-body">
        <h6>Asistieron (LAG)</h6>
        <h2 class="mb-0">{{ number_format($asistieron) }}</h2>
        <small>Check-in (102)</small>
      </div></div>
    </div>
    <div class="col-md-2">
      <div class="card"><div class="card-body">
        <h6>Tasa show</h6>
        <h2 class="mb-0">{{ $tasa_show }}%</h2>
        <small>Asistieron / RSVPs</small>
      </div></div>
    </div>

    <div class="col-md-2">
  <div class="card"><div class="card-body">
    <h6>Leads España</h6>
    <h2 class="mb-0">{{ number_format($kpi_es ?? 0) }}</h2>
    <small>Tel +34 / País España</small>
  </div></div>
</div>
<div class="col-md-2">
  <div class="card"><div class="card-body">
    <h6>Leads por pauta</h6>
    <h2 class="mb-0">{{ number_format($kpi_pauta ?? 0) }}</h2>
    <small>#Tour_Madrid_Pauta</small>
  </div></div>
</div>
  </div>


  {{-- LISTA: España (+34 / Spain) --}}
<div class="card mb-4">
  <div class="card-header"><strong>Leads España (+34 / España)</strong></div>
  <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead><tr>
        <th>Cliente</th><th>País</th><th>Teléfono</th><th>Email</th>
        <th>RSVP</th><th>Asistió</th><th>No show</th>
      </tr></thead>
      <tbody>
      @forelse($list_es as $r)
        <tr>
          <td>{{ $r->name }}</td>
          <td>{{ $r->country }}</td>
          <td>{{ $r->phone }}</td>
          <td>{{ $r->email }}</td>
          <td>{{ $r->last_rsvp_at ? \Carbon\Carbon::parse($r->last_rsvp_at)->format('d/m H:i') : '—' }}</td>
          <td>{{ $r->last_attended_at ? \Carbon\Carbon::parse($r->last_attended_at)->format('d/m H:i') : '—' }}</td>
          <td>{{ $r->last_noshow_at ? \Carbon\Carbon::parse($r->last_noshow_at)->format('d/m H:i') : '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center p-3">Sin datos</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- LISTA: Pauta #Tour_Madrid_Pauta --}}
<div class="card mb-4">
  <div class="card-header"><strong>Leads por pauta (#Tour_Madrid_Pauta)</strong></div>
  <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead><tr>
        <th>Cliente</th><th>País</th><th>Teléfono</th><th>Email</th>
        <th>RSVP</th><th>Asistió</th><th>No show</th>
      </tr></thead>
      <tbody>
      @forelse($list_pauta as $r)
        <tr>
          <td>{{ $r->name }}</td>
          <td>{{ $r->country }}</td>
          <td>{{ $r->phone }}</td>
          <td>{{ $r->email }}</td>
          <td>{{ $r->last_rsvp_at ? \Carbon\Carbon::parse($r->last_rsvp_at)->format('d/m H:i') : '—' }}</td>
          <td>{{ $r->last_attended_at ? \Carbon\Carbon::parse($r->last_attended_at)->format('d/m H:i') : '—' }}</td>
          <td>{{ $r->last_noshow_at ? \Carbon\Carbon::parse($r->last_noshow_at)->format('d/m H:i') : '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center p-3">Sin datos</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

  {{-- LISTADO PRINCIPAL: España + #Tour_Madrid_Pauta (de la vista) --}}
  <div class="card">
    <div class="card-header">
      <strong>Leads España (+34) con #Tour_Madrid_Pauta</strong>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Empresa</th>
              <th>Teléfono</th>
              <th>Email</th>
              <th>RSVP</th>
              <th>Asistió</th>
              <th>No show</th>
              <th>Última acción</th>
            </tr>
          </thead>
          <tbody>
            @forelse($leads as $lead)
              <tr>
                <td>{{ $lead->name }}</td>
                <td>{{ $lead->business }}</td>
                <td>{{ $lead->phone_main }}</td>
                <td>{{ $lead->email }}</td>
                <td>{{ $lead->last_rsvp_at ? \Carbon\Carbon::parse($lead->last_rsvp_at)->format('d/m H:i') : '—' }}</td>
                <td>{{ $lead->last_attended_at ? \Carbon\Carbon::parse($lead->last_attended_at)->format('d/m H:i') : '—' }}</td>
                <td>{{ $lead->last_noshow_at ? \Carbon\Carbon::parse($lead->last_noshow_at)->format('d/m H:i') : '—' }}</td>
                <td>
                  {{ $lead->last_action_name ?? '—' }}
                  <small class="text-muted">
                    {{ $lead->last_action_at ? \Carbon\Carbon::parse($lead->last_action_at)->diffForHumans() : '' }}
                  </small>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center p-4">Sin datos</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection
