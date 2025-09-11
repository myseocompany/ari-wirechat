@extends('layout')

@section('content')
<div class="container">
  <h4 class="mb-3">Agenda programada (#EspañaAgenda2025)</h4>

<form class="row g-2 mb-3" method="GET">
  <input type="hidden" name="from" value="{{ \Carbon\Carbon::parse($from)->format('Y-m-d') }}">
  <input type="hidden" name="to" value="{{ \Carbon\Carbon::parse($to)->format('Y-m-d') }}">

  <div class="col-auto">
    <select name="maker" class="form-select">
      <option value="">— Hace o no hace empanadas —</option>
      <option value="1" {{ request('maker') === '1' ? 'selected' : '' }}>Hace empanadas</option>
      <option value="0" {{ request('maker') === '0' ? 'selected' : '' }}>No hace</option>
      <option value="null" {{ request('maker') === 'null' ? 'selected' : '' }}>No sabemos</option>
    </select>
  </div>

  <div class="col-auto">
    <select name="orders" class="form-select">
      <option value="">— Tiene cotización —</option>
      <option value="yes" {{ request('orders') === 'yes' ? 'selected' : '' }}>Con órdenes</option>
      <option value="no" {{ request('orders') === 'no' ? 'selected' : '' }}>Sin órdenes</option>
    </select>
  </div>

  <div class="col-auto">
    <button class="btn btn-primary">Filtrar</button>
  </div>
</form>

@php
  $totalAgendados = $acciones->count();
  $totalYes = $acciones->where('maker', 1)->count();
  $totalNo = $acciones->where('maker', 0)->count();
  $totalUnk = $acciones->whereNull('maker')->count();
  $totalOrders = $acciones->where('has_orders', true)->count();
@endphp

<div class="alert alert-info" role="alert">
  <strong>Totales:</strong>
  {{ $totalAgendados }} agendado(s),
  <span class="text-success">{{ $totalYes }} empanadero(s)</span>,
  <span class="text-danger">{{ $totalNo }} proyecto(s)</span>,
  <span class="text-muted">{{ $totalUnk }} ¿?</span>,
  <span class="text-primary">{{ $totalOrders }} con órdenes</span>.
</div>

  <div class="table-responsive">
    <table class="table table-sm table-hover">
     <thead>
  <tr>
    <th>Cliente</th>
    <th>Teléfono</th>
    <th>Fecha/Hora</th>
    <th>Hace empanadas</th>
    <th>Órdenes</th>
    <th>Estado</th>
    <th>Acción</th>
  </tr>
</thead>
@php
  use Carbon\Carbon;

  // Agrupamos por H:00
$agrupadas = $acciones->groupBy(function($a) {
  return \Carbon\Carbon::parse($a->due_date)->format('Y-m-d H:00');
});

  $fueraHorario = $acciones->filter(fn($a) => 
    Carbon::parse($a->due_date)->hour < 9 || Carbon::parse($a->due_date)->hour > 17
  );
@endphp

@php
$fechas = [
  \Carbon\Carbon::parse($from),
  \Carbon\Carbon::parse($to),
];
@endphp

@foreach ($fechas as $fecha)
  @for ($h = 9; $h <= 17; $h++)
    @php
      $slot = $fecha->format('Y-m-d') . ' ' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
      $accionesSlot = $agrupadas[$slot] ?? collect();
      $idSlot = 'slot_' . str_replace([' ',':'], '_', $slot);

      $countYes  = $accionesSlot->where('maker', 1)->count();
      $countNo   = $accionesSlot->where('maker', 0)->count();
      $countUnk  = $accionesSlot->whereNull('maker')->count();
      $countCot  = $accionesSlot->where('has_orders', true)->count();
    @endphp

    {{-- Fila resumen alineada con cabeceras --}}
    <tr class="table-light fw-bold">
      <td>{{ $fecha->format('d/m') }} — {{ $h }}:00 h</td>
      <td>{{ $accionesSlot->count() }} agendado(s)</td>
      <td>—</td>
      <td>
        <span class="text-success">{{ $countYes }} empanadero</span>, 
        <span class="text-danger">{{ $countNo }} proyecto</span>, 
        <span class="text-muted">{{ $countUnk }} ¿?</span>
      </td>
      <td>
        <span class="text-primary">{{ $countCot }} con órdenes</span>
      </td>
      <td>—</td>
      <td>
        <button class="btn btn-sm btn-outline-secondary toggle-slot" data-target="{{ $idSlot }}">Ver</button>
      </td>
    </tr>

    {{-- Detalle del slot --}}
    <tbody id="{{ $idSlot }}" class="d-none">
      @foreach($accionesSlot as $a)
        <tr>
          <td>
            <a href="{{ url('/customers/' . $a->customer_id . '/show') }}" target="_blank">
              {{ $a->name ?? 'ID: ' . $a->customer_id }}
            </a>
          </td>
          <td>{{ $a->phone ?? '—' }}</td>
          <td>{{ \Carbon\Carbon::parse($a->due_date)->format('d/m H:i') }}</td>
          <td>
            @if($a->maker === 1)
              <span class="badge bg-success">Empanadero</span>
            @elseif($a->maker === 0)
              <span class="badge bg-danger">Proyecto</span>
            @else
              <span class="badge bg-secondary">¿?</span>
            @endif
          </td>
          <td>
            @if($a->has_orders)
              <a href="{{ url('/customers/' . $a->customer_id . '/show') }}" target="_blank" class="badge bg-success">Sí</a>
            @else
              <span class="badge bg-secondary">No</span>
            @endif
          </td>
          <td>
            @if($a->delivery_date)
              <span class="badge bg-success">Completada</span>
            @else
              <span class="badge bg-warning text-dark">Pendiente</span>
            @endif
          </td>
          <td>
            <a href="{{ url('/crm/actions/' . $a->action_id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Ver</a>
          </td>
        </tr>
      @endforeach
    </tbody>
  @endfor
@endforeach



@if ($customersSinAgenda->isNotEmpty())
  <tr>
    <td colspan="7" class="table-danger fw-bold">Sin agenda (sin acción type_id=101)</td>
  </tr>

  @foreach ($customersSinAgenda as $c)
    <tr>
      <td>
        <a href="{{ url('/customers/' . $c->customer_id . '/show') }}" target="_blank">
          {{ $c->name ?? 'ID: ' . $c->customer_id }}
        </a>
      </td>
      <td>{{ $c->phone ?? '—' }}</td>
      <td>—</td>
      <td>
        @if($c->maker === 1)
          <span class="badge bg-success">Empanadero</span>
        @elseif($c->maker === 0)
          <span class="badge bg-danger">Proyecto</span>
        @else
          <span class="badge bg-secondary">¿?</span>
        @endif
      </td>
      <td>
        @if($c->has_orders)
          <span class="badge bg-success">Sí</span>
        @else
          <span class="badge bg-secondary">No</span>
        @endif
      </td>
      <td>—</td>
      <td>
        {{-- sin acción, así que no hay botón de acción --}}
        <span class="text-muted">Sin acción</span>
      </td>
    </tr>
  @endforeach
@endif


    </table>
  </div>
</div>
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-slot').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.target);
        target.classList.toggle('d-none');
      });
    });
  });
</script>
@endpush


@endsection
