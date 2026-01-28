@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-col gap-1">
    <h1 class="mb-1">Clientes con estado 8 y conversaciones</h1>
    <div class="text-xs text-slate-500">{{ $model->total() }} clientes</div>
  </div>
</div>

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form action="/reports/views/customers_status8_conversations" method="GET" class="flex flex-col gap-4">
    <div class="grid gap-4 lg:grid-cols-[repeat(12,minmax(0,1fr))]">
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="from_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desde</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="from_date" name="from_date" value="{{ $fromDate?->format('Y-m-d') ?? $request->from_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="to_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hasta</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="to_date" name="to_date" value="{{ $toDate?->format('Y-m-d') ?? $request->to_date }}">
      </div>
    </div>
    <div class="flex flex-wrap items-center justify-end gap-3">
      <button type="submit" class="inline-flex items-center rounded-xl bg-[color:var(--ds-coral)] px-4 py-2 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(255,92,92,0.35)]">Filtrar</button>
    </div>
  </form>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cliente</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asesor</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Conversaciones</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Último mensaje</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Última conversación</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
        @foreach ($model as $item)
          @php
            $bestPhone = $item->phone ?: ($item->phone2 ?: $item->contact_phone2);
            $whatsappPhone = $bestPhone ? preg_replace('/\D+/', '', $bestPhone) : null;
            $lastMessageAt = $item->last_message_at ? \Illuminate\Support\Carbon::parse($item->last_message_at) : null;
          @endphp
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 font-semibold text-slate-900">
              <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                  <a href="{{ route('customers.show', $item->id) }}" class="hover:underline">{{ $item->name ?: 'Sin nombre' }}</a>
                  <a href="{{ route('customers.show', $item->id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Ver</a>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-normal text-slate-600">
                  <span>{{ $bestPhone ?: 'Sin telefono' }}</span>
                  @if ($whatsappPhone)
                    <a
                      href="https://wa.me/{{ $whatsappPhone }}"
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100"
                    >
                      WhatsApp
                    </a>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-4 py-3">{{ $item->user_name ?: 'Sin asesor' }}</td>
            <td class="px-4 py-3">
              @if ($item->status_name)
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold text-white" style="background-color: {{ $item->status_color ?: '#64748b' }}">
                  {{ $item->status_name }}
                </span>
              @else
                <span class="text-xs text-slate-500">Sin estado</span>
              @endif
            </td>
            <td class="px-4 py-3">
              {{ (int) $item->conversation_count }}
            </td>
            <td class="px-4 py-3">
              @if ($lastMessageAt)
                <div class="flex flex-col gap-1">
                  <span>{{ $lastMessageAt->format('Y-m-d H:i') }}</span>
                  <span class="text-xs text-slate-500">{{ $lastMessageAt->diffForHumans() }}</span>
                </div>
              @else
                <span class="text-xs text-slate-500">Sin fecha</span>
              @endif
            </td>
            <td class="px-4 py-3">
              @if ($item->last_conversation_id)
                <a href="{{ url('/chats/'.$item->last_conversation_id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                  Ver chat #{{ $item->last_conversation_id }}
                </a>
              @else
                <span class="text-xs text-slate-500">Sin conversación</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">
  {{ $model->links() }}
</div>
@endsection
