@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-col gap-1">
    <h1 class="mb-1">Llamadas Twilio</h1>
    <div class="text-xs text-slate-500">{{ $model->total() }} llamadas</div>
  </div>
</div>

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form action="{{ route('reports.twilio_calls') }}" method="GET" class="flex flex-col gap-4">
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
        <label for="call_sid" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Call SID</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="call_sid" name="call_sid" value="{{ $request->call_sid }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="customer_search" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cliente</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="customer_search" name="customer_search" value="{{ $request->customer_search }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="phone" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Teléfono</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="phone" name="phone" value="{{ $request->phone }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="user_id" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asesor</label>
        <select id="user_id" name="user_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todos</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}" @if ((string) $request->user_id === (string) $user->id) selected @endif>{{ $user->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="twilio_status" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado Twilio</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="twilio_status" name="twilio_status" value="{{ $request->twilio_status }}" placeholder="completed, no-answer, failed...">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="has_recording" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Con grabación</label>
        <select id="has_recording" name="has_recording" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todos</option>
          <option value="yes" @if ($request->has_recording === 'yes') selected @endif>Sí</option>
          <option value="no" @if ($request->has_recording === 'no') selected @endif>No</option>
        </select>
      </div>
    </div>
    <div class="flex items-center justify-end">
      <button type="submit" class="inline-flex items-center rounded-xl bg-[color:var(--ds-coral)] px-4 py-2 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(255,92,92,0.35)]">Filtrar</button>
    </div>
  </form>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Fecha</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cliente</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asesor</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Call SID</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Duración</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Audio</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Nota</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
        @forelse ($model as $item)
          @php
            $note = (string) ($item->note ?? '');
            preg_match('/twilio_call_sid:([A-Za-z0-9]+)/i', $note, $sidMatches);
            $callSid = $sidMatches[1] ?? null;
            preg_match('/Estado Twilio:\s*([^\n\r]+)/i', $note, $statusMatches);
            $twilioStatus = $statusMatches[1] ?? '—';
            $durationSeconds = is_numeric($item->creation_seconds ?? null) ? (int) $item->creation_seconds : null;
            $durationFormatted = $durationSeconds === null
              ? '—'
              : ($durationSeconds >= 3600 ? gmdate('H:i:s', $durationSeconds) : gmdate('i:s', $durationSeconds));
            $phone = $item->customer_phone ?: ($item->customer_phone2 ?: $item->customer_contact_phone2);
          @endphp
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">{{ optional($item->created_at)->format('Y-m-d H:i:s') }}</td>
            <td class="px-4 py-3">
              <div class="flex flex-col gap-1">
                @if ($item->customer_id)
                  <a href="{{ route('customers.show', $item->customer_id) }}" class="font-semibold text-slate-900 hover:underline">
                    {{ $item->customer_name ?: 'Sin nombre' }}
                  </a>
                @else
                  <span class="font-semibold text-slate-900">{{ $item->customer_name ?: 'Sin cliente' }}</span>
                @endif
                <span class="text-xs text-slate-500">{{ $phone ?: 'Sin teléfono' }}</span>
              </div>
            </td>
            <td class="px-4 py-3">{{ $item->user_name ?: 'Automático' }}</td>
            <td class="px-4 py-3">
              @if ($callSid)
                <code>{{ $callSid }}</code>
              @else
                <span class="text-slate-400">—</span>
              @endif
            </td>
            <td class="px-4 py-3">{{ $twilioStatus }}</td>
            <td class="px-4 py-3">{{ $durationFormatted }}</td>
            <td class="px-4 py-3">
              @if (! empty($item->url))
                <a href="{{ route('actions.audio', $item->id) }}" target="_blank" rel="noopener noreferrer" class="text-[color:var(--ds-coral)] hover:underline">
                  Escuchar
                </a>
              @else
                <span class="text-slate-400">Sin audio</span>
              @endif
            </td>
            <td class="px-4 py-3">
              <details>
                <summary class="cursor-pointer text-xs text-slate-500">Ver nota</summary>
                <pre class="mt-2 whitespace-pre-wrap rounded-lg bg-slate-50 p-2 text-xs text-slate-600">{{ $note !== '' ? $note : 'Sin nota' }}</pre>
              </details>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-400">No se encontraron llamadas Twilio con esos filtros.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">
  {{ $model->onEachSide(1)->links() }}
</div>
@endsection
