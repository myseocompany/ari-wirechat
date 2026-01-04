@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-col gap-1">
    <h1 class="mb-1">Mensajes por cliente</h1>
    <div class="text-xs text-slate-500">{{ $model->total() }} clientes</div>
  </div>
</div>

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form action="/reports/views/customers_messages_count" method="GET" class="flex flex-col gap-4">
    <div class="grid gap-4 lg:grid-cols-[repeat(12,minmax(0,1fr))]">
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="from_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desde</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="from_date" name="from_date" value="{{ $fromDate?->format('Y-m-d') ?? $request->from_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="to_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hasta</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="to_date" name="to_date" value="{{ $toDate?->format('Y-m-d') ?? $request->to_date }}">
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="messages_min" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Min mensajes</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="0" id="messages_min" name="messages_min" value="{{ $request->messages_min }}">
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="messages_max" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Max mensajes</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="0" id="messages_max" name="messages_max" value="{{ $request->messages_max }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="message_search" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Buscar mensaje</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="message_search" name="message_search" value="{{ $request->message_search }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="status_ids" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estados</label>
        <select id="status_ids" name="status_ids[]" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          @foreach ($statuses as $status)
            <option value="{{ $status->id }}" @if (collect($request->status_ids)->contains($status->id)) selected @endif>
              {{ $status->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="tag_ids" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Etiquetas</label>
        <select id="tag_ids" name="tag_ids[]" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @if (collect($request->tag_ids)->contains($tag->id)) selected @endif>
              {{ $tag->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3">
      <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
        <input type="checkbox" name="tag_none" value="1" class="rounded border-slate-300 text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" @if ($request->boolean('tag_none')) checked @endif>
        Sin etiqueta
      </label>
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
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Telefono</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asesor</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Etiquetas</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Mensajes</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Ultimo mensaje</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Ultimos 5 mensajes</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
        @foreach ($model as $item)
          @php
            $tagNames = $item->tag_names ? explode('||', $item->tag_names) : [];
          @endphp
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 font-semibold">
              <a href="{{ route('customers.show', $item->id) }}" class="customer-overlay-link text-slate-900 hover:underline" data-url="{{ route('customers.show', $item->id) }}">{{ $item->name }}</a>
            </td>
            <td class="px-4 py-3">{{ $item->phone }}</td>
            <td class="px-4 py-3 text-sm text-slate-600">{{ $item->user_name ?? 'Sin asignar' }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $item->status_color ?? '#94a3b8' }};">
                {{ $item->status_name ?? 'Sin estado' }}
              </span>
            </td>
            <td class="px-4 py-3">
              @if (count($tagNames))
                <div class="flex flex-wrap gap-2">
                  @foreach ($tagNames as $tagName)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">
                      {{ $tagName }}
                    </span>
                  @endforeach
                </div>
              @else
                <span class="text-xs text-slate-400">Sin etiqueta</span>
              @endif
            </td>
            <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->messages_count }}</td>
            <td class="px-4 py-3">{{ $item->last_message_at }}</td>
            <td class="px-4 py-3 text-sm text-slate-600 whitespace-pre-line">
              {{ $item->last_messages_body ?? 'Sin mensajes' }}
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

@include('customers.partials.customer_overlay')

@endsection
