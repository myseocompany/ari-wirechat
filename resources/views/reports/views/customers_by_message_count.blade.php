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
        <label for="user_id" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asesor</label>
        <select id="user_id" name="user_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todos</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}" @if ((string) $user->id === (string) $request->user_id) selected @endif>
              {{ $user->name }}
            </option>
          @endforeach
        </select>
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
      <div class="flex flex-wrap items-center gap-4">
        <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
          <input type="checkbox" name="user_unassigned" value="1" class="rounded border-slate-300 text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" @if ($request->boolean('user_unassigned')) checked @endif>
          Sin asesor
        </label>
        <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
          <input type="checkbox" name="tag_none" value="1" class="rounded border-slate-300 text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" @if ($request->boolean('tag_none')) checked @endif>
          Sin etiqueta
        </label>
      </div>
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
            $bestPhone = $item->getBestPhoneCandidate();
            $whatsappPhone = $bestPhone ? preg_replace('/\\D+/', '', $bestPhone) : null;
          @endphp
          <tr class="customer-overlay-link hover:bg-slate-50" data-url="{{ route('customers.show', $item->id) }}">
            <td class="px-4 py-3 font-semibold">
              <div class="flex items-center gap-2">
                <a href="{{ route('customers.show', $item->id) }}" class="customer-overlay-link text-slate-900 hover:underline" data-url="{{ route('customers.show', $item->id) }}">{{ $item->name }}</a>
                <a href="{{ route('customers.show', $item->id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700" data-customer-overlay-ignore>Ver</a>
              </div>
            </td>
            <td class="px-4 py-3">
              @if ($bestPhone)
                <div class="flex flex-wrap items-center gap-2">
                  <span>{{ $item->getInternationalPhone($bestPhone) }}</span>
                  @if ($whatsappPhone)
                    <a
                      href="https://wa.me/{{ $whatsappPhone }}"
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                      data-customer-overlay-ignore
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                        <path d="M12 2a9.9 9.9 0 0 0-8.53 15.03L2 22l5.1-1.33A9.93 9.93 0 0 0 12 22a10 10 0 0 0 0-20Zm0 18a7.93 7.93 0 0 1-4.02-1.1l-.29-.18-2.97.77.79-2.89-.19-.3A7.9 7.9 0 0 1 4 12a8 8 0 1 1 8 8Zm4.38-5.36c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1-.37-1.9-1.18-.7-.62-1.18-1.39-1.32-1.63-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.48-.4-.41-.54-.42-.14-.01-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2 0 1.18.86 2.32.98 2.48.12.16 1.7 2.6 4.12 3.64.58.25 1.04.4 1.39.51.58.18 1.1.16 1.52.1.46-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/>
                      </svg>
                      WhatsApp
                    </a>
                  @endif
                </div>
              @else
                <span>Sin telefono</span>
              @endif
            </td>
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
