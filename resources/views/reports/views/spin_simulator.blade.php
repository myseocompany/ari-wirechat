@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-col gap-1">
    <h1 class="mb-1">Simulador SPIN — Sesiones de entrenamiento</h1>
    <div class="text-xs text-slate-500">{{ $model->total() }} sesiones registradas</div>
  </div>
</div>

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form action="/reports/views/spin-simulator" method="GET" class="flex flex-wrap items-end gap-4">
    <div class="flex flex-col gap-1">
      <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desde</label>
      <input class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" name="from_date" value="{{ $fromDate?->format('Y-m-d') }}">
    </div>
    <div class="flex flex-col gap-1">
      <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hasta</label>
      <input class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" name="to_date" value="{{ $toDate?->format('Y-m-d') }}">
    </div>
    <div class="flex flex-col gap-1">
      <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Escenario</label>
      <select name="escenario" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
        <option value="">Todos</option>
        <option value="productor_pereira" @selected($request->escenario === 'productor_pereira')>Productor (Pereira)</option>
        <option value="restaurantero_medellin" @selected($request->escenario === 'restaurantero_medellin')>Restaurantero (Medellín)</option>
        <option value="inversionista_bogota" @selected($request->escenario === 'inversionista_bogota')>Inversionista (Bogotá)</option>
        <option value="emprendedor_nuevo" @selected($request->escenario === 'emprendedor_nuevo')>Emprendedor nuevo</option>
      </select>
    </div>
    <div class="flex flex-col gap-1">
      <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Puntaje mín.</label>
      <input class="w-24 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="1" max="10" name="puntaje_min" value="{{ $request->puntaje_min }}">
    </div>
    <button type="submit" class="inline-flex items-center rounded-xl bg-[color:var(--ds-coral)] px-4 py-2 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(255,92,92,0.35)]">
      Filtrar
    </button>
  </form>
</div>

@php
  $spinFields = [
    'hizo_apertura_correcta'  => 'Apertura',
    'preguntas_situacion'     => 'Situación',
    'identifico_problema'     => 'Problema',
    'hizo_implicacion'        => 'Implicación',
    'cliente_dijo_beneficio'  => 'Beneficio',
    'cerro_con_paso_concreto' => 'Cierre',
  ];
@endphp

<div class="space-y-4">
  @forelse ($model as $item)
    @php
      $payloadArray = is_array($item->payload) ? $item->payload : (json_decode((string) $item->payload, true) ?: []);
      $normalized   = isset($payloadArray[0]) ? $payloadArray[0] : $payloadArray;
      if (isset($normalized['body']) && is_array($normalized['body'])) { $normalized = $normalized['body']; }
      $call       = $normalized['call'] ?? $normalized;
      $from       = $call['from_number'] ?? '—';
      $durationS  = isset($call['duration_ms']) ? round($call['duration_ms'] / 1000) : null;
      $transcript = $call['transcript'] ?? null;
      $score      = (int) $item->puntaje_spin;
      $colorCard  = $score >= 7 ? 'border-emerald-200' : ($score >= 5 ? 'border-amber-200' : ($score > 0 ? 'border-red-200' : 'border-slate-200'));
      $colorBadge = $score >= 7 ? 'bg-emerald-100 text-emerald-700' : ($score >= 5 ? 'bg-amber-100 text-amber-700' : ($score > 0 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500'));
      $createdAt  = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
    @endphp

    <div class="rounded-2xl border {{ $colorCard }} bg-white p-5 shadow-sm">

      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
          @if ($score > 0)
            <span class="inline-flex items-center rounded-full px-3 py-1 text-lg font-bold {{ $colorBadge }}">{{ $score }}/10</span>
          @else
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-400">Sin puntaje</span>
          @endif
          @if ($item->escenario_detectado)
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ $item->escenario_detectado }}</span>
          @endif
          <span class="text-xs text-slate-400">{{ $createdAt?->format('Y-m-d H:i') }}</span>
          <span class="text-xs text-slate-400">{{ $from }}</span>
          @if ($durationS)
            <span class="text-xs text-slate-400">{{ $durationS }}s</span>
          @endif
        </div>
        <span class="font-mono text-xs text-slate-300">{{ $item->call_id }}</span>
      </div>

      <div class="mt-4 flex flex-wrap gap-3">
        @foreach ($spinFields as $field => $label)
          @php $val = $item->$field; @endphp
          @if (is_null($val))
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-3 py-1 text-xs text-slate-300">○ {{ $label }}</span>
          @elseif ((int) $val === 1)
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">✓ {{ $label }}</span>
          @else
            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-500">✗ {{ $label }}</span>
          @endif
        @endforeach
      </div>

      @if ($item->resumen_llamada || $item->principal_error || $item->recomendacion)
        <div class="mt-4 grid gap-3 text-sm lg:grid-cols-3">
          @if ($item->resumen_llamada)
            <div class="rounded-xl bg-slate-50 p-3">
              <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Resumen</div>
              <div class="text-slate-700">{{ $item->resumen_llamada }}</div>
            </div>
          @endif
          @if ($item->principal_error)
            <div class="rounded-xl bg-red-50 p-3">
              <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-red-400">Error principal</div>
              <div class="text-red-700">{{ $item->principal_error }}</div>
            </div>
          @endif
          @if ($item->recomendacion)
            <div class="rounded-xl bg-amber-50 p-3">
              <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-amber-500">Recomendación</div>
              <div class="text-amber-800">{{ $item->recomendacion }}</div>
            </div>
          @endif
        </div>
      @endif

      @if ($transcript)
        <details class="mt-3">
          <summary class="cursor-pointer text-xs text-slate-400 hover:text-slate-600">Ver transcripción</summary>
          <pre class="mt-2 max-h-72 overflow-y-auto whitespace-pre-wrap rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-700">{{ $transcript }}</pre>
        </details>
      @endif

    </div>
  @empty
    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
      No hay sesiones de entrenamiento en este período.
    </div>
  @endforelse
</div>

<div class="mt-4">
  {{ $model->onEachSide(1)->links() }}
</div>

@endsection
