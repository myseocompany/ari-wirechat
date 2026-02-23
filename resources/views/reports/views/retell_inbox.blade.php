@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-col gap-1">
    <h1 class="mb-1">Retell Inbox</h1>
    <div class="text-xs text-slate-500">{{ $model->total() }} registros</div>
  </div>
</div>

@if (session('retell_association_success'))
  <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
    {{ session('retell_association_success') }}
  </div>
@endif

@if ($errors->retellAssociation->any())
  <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    {{ $errors->retellAssociation->first() }}
  </div>
@endif

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form action="/reports/views/retell_inbox" method="GET" class="flex flex-col gap-4">
    <div class="grid gap-4 lg:grid-cols-[repeat(12,minmax(0,1fr))]">
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="from_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desde</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="from_date" name="from_date" value="{{ $fromDate?->format('Y-m-d') ?? $request->from_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="to_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hasta</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="to_date" name="to_date" value="{{ $toDate?->format('Y-m-d') ?? $request->to_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="call_id" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Call ID</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="call_id" name="call_id" value="{{ $request->call_id }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="status" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status Retell</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="status" name="status" value="{{ $request->status }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="event" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Evento</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="event" name="event" value="{{ $request->event }}">
      </div>

      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="payload_search" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Buscar en payload</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="payload_search" name="payload_search" value="{{ $request->payload_search }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="process_state" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado de proceso</label>
        <select id="process_state" name="process_state" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="all" @if (($request->process_state ?? 'all') === 'all') selected @endif>Todos</option>
          <option value="pending" @if ($request->process_state === 'pending') selected @endif>Pendiente</option>
          <option value="processed" @if ($request->process_state === 'processed') selected @endif>Procesado</option>
          <option value="error" @if ($request->process_state === 'error') selected @endif>Error</option>
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="call_successful" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Llamada efectiva</label>
        <select id="call_successful" name="call_successful" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="all" @if (($request->call_successful ?? 'all') === 'all') selected @endif>Todos</option>
          <option value="yes" @if ($request->call_successful === 'yes') selected @endif>Sí</option>
          <option value="no" @if ($request->call_successful === 'no') selected @endif>No</option>
          <option value="unknown" @if ($request->call_successful === 'unknown') selected @endif>Sin dato</option>
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="in_voicemail" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Buzón</label>
        <select id="in_voicemail" name="in_voicemail" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="all" @if (($request->in_voicemail ?? 'all') === 'all') selected @endif>Todos</option>
          <option value="yes" @if ($request->in_voicemail === 'yes') selected @endif>Sí</option>
          <option value="no" @if ($request->in_voicemail === 'no') selected @endif>No</option>
          <option value="unknown" @if ($request->in_voicemail === 'unknown') selected @endif>Sin dato</option>
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="busca_automatizar" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Busca automatizar</label>
        <select id="busca_automatizar" name="busca_automatizar" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="all" @if (($request->busca_automatizar ?? 'all') === 'all') selected @endif>Todos</option>
          <option value="yes" @if ($request->busca_automatizar === 'yes') selected @endif>Sí</option>
          <option value="no" @if ($request->busca_automatizar === 'no') selected @endif>No</option>
          <option value="unknown" @if ($request->busca_automatizar === 'unknown') selected @endif>Sin dato</option>
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="masses_used" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Masa</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="masses_used" name="masses_used" value="{{ $request->masses_used }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="live_attendance_status" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asistencia en vivo</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="live_attendance_status" name="live_attendance_status" value="{{ $request->live_attendance_status }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-1">
        <label for="daily_volume_min" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Vol. min</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="0" id="daily_volume_min" name="daily_volume_min" value="{{ $request->daily_volume_min }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-1">
        <label for="daily_volume_max" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Vol. max</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="0" id="daily_volume_max" name="daily_volume_max" value="{{ $request->daily_volume_max }}">
      </div>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-4">
        @if($hasRetellCallId)
          <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
            <input type="checkbox" name="has_action" value="1" class="rounded border-slate-300 text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" @if ($request->boolean('has_action')) checked @endif>
            Con acción asociada
          </label>
        @endif
      </div>
      <button type="submit" class="inline-flex items-center rounded-xl bg-[color:var(--ds-coral)] px-4 py-2 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(255,92,92,0.35)]">
        Filtrar
      </button>
    </div>
  </form>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Fecha</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Call ID</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Proceso</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Datos técnicos</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Variables de negocio</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cliente / Acción</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
        @foreach ($model as $item)
          @php
            $payloadArray = is_array($item->payload)
              ? $item->payload
              : (json_decode((string) $item->payload, true) ?: []);
            $normalizedPayload = isset($payloadArray[0]) && is_array($payloadArray[0]) ? $payloadArray[0] : $payloadArray;
            if (isset($normalizedPayload['body']) && is_array($normalizedPayload['body'])) {
                $normalizedPayload = $normalizedPayload['body'];
            }
            $call = $normalizedPayload['call'] ?? $normalizedPayload;
            $from = $call['from_number'] ?? null;
            $to = $call['to_number'] ?? null;
            $durationSeconds = isset($call['duration_ms']) ? (int) round(((int) $call['duration_ms']) / 1000) : null;
            $customerId = $item->customer_id_ref ?? $item->customer_id ?? null;
            $createdAt = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
            $updatedAt = $item->updated_at ? \Carbon\Carbon::parse($item->updated_at) : null;
            $processedAt = $item->processed_at ? \Carbon\Carbon::parse($item->processed_at) : null;
            $callSuccessfulLabel = is_null($item->call_successful) ? 'Sin dato' : ((int) $item->call_successful === 1 ? 'Sí' : 'No');
            $voicemailLabel = is_null($item->in_voicemail) ? 'Sin dato' : ((int) $item->in_voicemail === 1 ? 'Sí' : 'No');
            $automatizarLabel = is_null($item->busca_automatizar) ? 'Sin dato' : ((int) $item->busca_automatizar === 1 ? 'Sí' : 'No');
            $manualCustomerInput = old('call_id') === $item->call_id ? old('customer_id') : $customerId;
          @endphp
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 align-top">
              <div class="font-semibold text-slate-800">{{ $createdAt?->format('Y-m-d H:i:s') }}</div>
              <div class="text-xs text-slate-500">Upd: {{ $updatedAt?->format('Y-m-d H:i:s') }}</div>
            </td>
            <td class="px-4 py-3 align-top">
              <div class="font-mono text-xs text-slate-700">{{ $item->call_id }}</div>
              <div class="mt-1 inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $item->status ?: 'sin status' }}</div>
            </td>
            <td class="px-4 py-3 align-top">
              @if ($item->error)
                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Error</span>
                <div class="mt-1 text-xs text-red-600">{{ $item->error }}</div>
              @elseif ($item->processed_at)
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Procesado</span>
                <div class="mt-1 text-xs text-slate-500">{{ $processedAt?->format('Y-m-d H:i:s') }}</div>
              @else
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Pendiente</span>
              @endif
            </td>
            <td class="px-4 py-3 align-top text-xs text-slate-600">
              <div>Evento: {{ $item->event ?: '—' }}</div>
              <div>From: {{ $from ?: '—' }}</div>
              <div>To: {{ $to ?: '—' }}</div>
              <div>Duración: {{ $durationSeconds !== null ? $durationSeconds.'s' : '—' }}</div>
              <div>Sentimiento: {{ $item->user_sentiment ?: '—' }}</div>
            </td>
            <td class="px-4 py-3 align-top text-xs text-slate-600">
              <div>Efectiva: {{ $callSuccessfulLabel }}</div>
              <div>Buzón: {{ $voicemailLabel }}</div>
              <div>Automatizar: {{ $automatizarLabel }}</div>
              <div>Masa: {{ $item->masses_used ?: '—' }}</div>
              <div>Volumen/día: {{ $item->daily_volume_empanadas !== null ? $item->daily_volume_empanadas : '—' }}</div>
              <div>Asistencia en vivo: {{ $item->live_attendance_status ?: '—' }}</div>
            </td>
            <td class="px-4 py-3 align-top">
              @if ($customerId)
                <a href="{{ url('/customers/'.$customerId.'/show') }}" class="font-semibold text-blue-600 hover:text-blue-700">
                  {{ $item->customer_name ?: ('Cliente #'.$customerId) }}
                </a>
                @if (!empty($item->customer_phone))
                  <div class="text-xs text-slate-500">{{ $item->customer_phone }}</div>
                @endif
              @else
                <span class="text-xs text-slate-400">Sin cliente asociado</span>
              @endif

              @if (!empty($item->action_id))
                <div class="mt-1">
                  <a href="{{ url('/actions/'.$item->action_id.'/show') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-800">
                    Acción #{{ $item->action_id }}
                  </a>
                </div>
              @endif

              @if($hasRetellCallId)
                <form action="{{ route('reports.retell_inbox.associate_customer') }}" method="POST" class="mt-2 flex flex-wrap items-center gap-2">
                  @csrf
                  <input type="hidden" name="call_id" value="{{ $item->call_id }}">
                  <input
                    type="number"
                    name="customer_id"
                    min="1"
                    placeholder="customer_id"
                    value="{{ $manualCustomerInput }}"
                    class="w-28 rounded-lg border border-slate-200 px-2 py-1 text-xs text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none"
                  >
                  <button type="submit" class="inline-flex items-center rounded-lg bg-slate-800 px-3 py-1 text-xs font-semibold text-white">
                    {{ $customerId ? 'Actualizar' : 'Asociar' }}
                  </button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">
  {{ $model->onEachSide(1)->links() }}
</div>

@endsection
