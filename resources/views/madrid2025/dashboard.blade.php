@extends('layouts.app')

@section('content')
<div class="container">

  {{-- Filtros --}}
  <form class="row g-2 mb-3" method="get">
    <div class="col-auto">
      <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="Desde">
    </div>
    <div class="col-auto">
      <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="Hasta">
    </div>
    <div class="col-auto">
      <input type="text" name="country" class="form-control" value="{{ request('country') }}" placeholder="País">
    </div>
    <div class="col-auto">
      <input type="number" name="owner_id" class="form-control" value="{{ request('owner_id') }}" placeholder="Owner ID">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Filtrar</button>
      <a href="{{ route('madrid2025.export') }}" class="btn btn-outline-secondary">Exportar CSV</a>
    </div>
  </form>

  {{-- KPIs LEAD vs LAG --}}
  <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
    {{-- LEADs --}}
    <div class="col"><div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">Alcanzados (LEAD)</h6>
        <h2 class="mb-0">{{ number_format($kpis->reached ?? 0) }}</h2>
        <small class="text-muted">Mensajes WA/Email/LLamada o interacción IA</small>
      </div></div></div>

    <div class="col"><div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">Engaged (LEAD)</h6>
        <h2 class="mb-0">{{ number_format($kpis->engaged ?? 0) }}</h2>
        <small class="text-muted">Respondieron / Abrieron email</small>
      </div></div></div>

    <div class="col"><div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">Calificados (LEAD)</h6>
        <h2 class="mb-0">{{ number_format($kpis->qualified ?? 0) }}</h2>
        <small class="text-muted">BANT/Score</small>
      </div></div></div>

    <div class="col"><div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">RSVP con fecha/hora (LEAD)</h6>
        <h2 class="mb-0">{{ number_format($kpis->rsvps ?? 0) }}</h2>
        <small class="text-muted">Action type 101</small>
      </div></div></div>

    {{-- LAGs --}}
    <div class="col"><div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">Asistieron (LAG)</h6>
        <h2 class="mb-0">{{ number_format($kpis->attended ?? 0) }}</h2>
        <small class="text-muted">Check-in (102)</small>
      </div></div></div>

    <div class="col"><div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">No show (LAG)</h6>
        <h2 class="mb-0">{{ number_format($kpis->no_show ?? 0) }}</h2>
        <small class="text-muted">Marcados 103</small>
      </div></div></div>

    <div class="col"><div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">Total etiquetados</h6>
        <h2 class="mb-0">{{ number_format($kpis->total_tagged ?? 0) }}</h2>
        <small class="text-muted">#Madrid2025</small>
      </div></div></div>

    <div class="col"><div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted">Tasa show</h6>
        @php
          $rsvps = max(1, (int)($kpis->rsvps ?? 0));
          $att = (int)($kpis->attended ?? 0);
          $show_rate = round(100 * $att / $rsvps, 1);
        @endphp
        <h2 class="mb-0">{{ $show_rate }}%</h2>
        <small class="text-muted">Asistieron / RSVPs</small>
      </div></div></div>
  </div>

  {{-- Mix de canales (mini resumen) --}}
  <div class="card mb-3">
    <div class="card-body">
      <h6 class="text-muted mb-2">Actividad por canal (ventana actual)</h6>
      <div class="d-flex flex-wrap gap-3">
        <span>WA OUT: <strong>{{ $mix['wa_out'] }}</strong></span>
        <span>WA IN: <strong>{{ $mix['wa_in'] }}</strong></span>
        <span>Emails OUT: <strong>{{ $mix['emails_out'] }}</strong></span>
        <span>Email Opens: <strong>{{ $mix['email_open'] }}</strong></span>
        <span>Llamadas: <strong>{{ $mix['calls_out'] }}</strong></span>
        <span>IA Sessions: <strong>{{ $mix['ai_sessions'] }}</strong></span>
      </div>
    </div>
  </div>

  {{-- Lista operativa --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Cliente</th><th>País</th><th>Owner</th>
            <th>WA</th><th>Email</th><th>Calls</th><th>IA</th>
            <th>RSVP</th><th>Asistió</th><th>No show</th>
            <th>Última acción</th>
          </tr>
        </thead>
        <tbody>
        @forelse($list as $row)
          <tr>
            <td>{{ $row->account_name }}</td>
            <td>{{ $row->country }}</td>
            <td>{{ $row->owner_id }}</td>
            <td>{{ $row->wa_out + $row->wa_in }}</td>
            <td>{{ $row->emails_out + $row->email_open + $row->emails_in }}</td>
            <td>{{ $row->calls_out }}</td>
            <td>{{ $row->ai_interactions }}</td>
            <td>@if($row->rsvp_confirmed) ✅ @endif</td>
            <td>@if($row->attended) ✅ @endif</td>
            <td>@if($row->no_show) ✅ @endif</td>
            <td>{{ \Illuminate\Support\Carbon::parse($row->last_action_at)->diffForHumans() }}</td>
          </tr>
        @empty
          <tr><td colspan="11" class="text-center text-muted">Sin datos</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $list->withQueryString()->links() }}
    </div>
  </div>

</div>
@endsection
