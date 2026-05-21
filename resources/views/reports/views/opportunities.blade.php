@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-col gap-1">
    <h1 class="mb-1">Agente de oportunidades</h1>
    <div class="text-xs text-slate-500">
      {{ $summary['analyzed'] }} analizados de {{ $summary['candidate_total'] }} candidatos encontrados · {{ $summary['makers'] }} producen · {{ $summary['production_known'] }} con volumen
    </div>
  </div>
  <a href="{{ url('/reports/views/customers_messages_count') }}?{{ http_build_query(request()->except('priority', 'unattended')) }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
    Ver mensajes
  </a>
</div>

<div class="mb-4 grid gap-3 md:grid-cols-4">
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Alta</div>
    <div class="mt-2 text-3xl font-bold text-rose-600">{{ $summary['high'] }}</div>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Producen</div>
    <div class="mt-2 text-3xl font-bold text-amber-600">{{ $summary['makers'] }}</div>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Analizados</div>
    <div class="mt-2 text-3xl font-bold text-slate-500">{{ $summary['analyzed'] }}</div>
    <div class="mt-1 text-xs text-slate-400">de {{ $summary['candidate_total'] }} candidatos</div>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Con volumen</div>
    <div class="mt-2 text-3xl font-bold text-blue-600">{{ $summary['production_known'] }}</div>
  </div>
</div>

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form action="{{ route('reports.opportunities') }}" method="GET" class="flex flex-col gap-4">
    <div class="grid gap-4 lg:grid-cols-[repeat(12,minmax(0,1fr))]">
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="from_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desde</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="from_date" name="from_date" value="{{ $fromDate?->format('Y-m-d') ?? $request->from_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="to_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hasta</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="to_date" name="to_date" value="{{ $toDate?->format('Y-m-d') ?? $request->to_date }}">
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="messages_min" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Min mensajes</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="0" id="messages_min" name="messages_min" value="{{ $request->messages_min }}">
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="priority" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Prioridad</label>
        <select id="priority" name="priority" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todas</option>
          <option value="high" @selected($request->priority === 'high')>Alta</option>
          <option value="medium" @selected($request->priority === 'medium')>Media</option>
          <option value="low" @selected($request->priority === 'low')>Baja</option>
        </select>
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="maker" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Produccion</label>
        <select id="maker" name="maker" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todas</option>
          <option value="makes" @selected($request->maker === 'makes')>Hace empanadas</option>
          <option value="project" @selected($request->maker === 'project')>Proyecto</option>
          <option value="other" @selected($request->maker === 'other')>Otros</option>
          <option value="unknown" @selected($request->maker === 'unknown')>Sin clasificar</option>
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="message_search" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Buscar mensaje</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="message_search" name="message_search" value="{{ $request->message_search }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="action_note_search" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Buscar accion</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="action_note_search" name="action_note_search" value="{{ $request->action_note_search }}">
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
        <label for="user_id" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asesor</label>
        <select id="user_id" name="user_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todos</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected($request->user_id == $user->id)>{{ $user->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="source_id" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Origen</label>
        <select id="source_id" name="source_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todos</option>
          @foreach ($sources as $source)
            <option value="{{ $source->id }}" @selected($request->source_id == $source->id)>{{ $source->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="tag_ids" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Etiquetas</label>
        <select id="tag_ids" name="tag_ids[]" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @if (collect($request->tag_ids)->contains($tag->id)) selected @endif>{{ $tag->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="production_min" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Min emp/dia</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="0" id="production_min" name="production_min" value="{{ $request->production_min }}">
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="limit" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Analizar</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="10" max="3000" id="limit" name="limit" value="{{ $request->limit ?? 500 }}">
      </div>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-4">
        <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
          <input type="checkbox" name="unattended" value="1" class="rounded border-slate-300 text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" @if ($request->boolean('unattended')) checked @endif>
          Sin atender
        </label>
        <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
          <input type="checkbox" name="tag_none" value="1" class="rounded border-slate-300 text-[color:var(--ds-coral)] focus:ring-[color:var(--ds-coral)]" @if ($request->boolean('tag_none')) checked @endif>
          Sin etiqueta
        </label>
      </div>
      <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">Analizar</button>
    </div>
  </form>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full table-fixed divide-y divide-slate-200">
      <colgroup>
        <col class="w-[14%]">
        <col class="w-[24%]">
        <col class="w-[20%]">
        <col class="w-[20%]">
        <col class="w-[22%]">
      </colgroup>
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Prioridad</th>
          <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Prospecto</th>
          <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Produccion</th>
          <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Motivos</th>
          <th class="px-4 py-4 text-left text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Contexto</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
        @forelse ($model as $item)
          @php
            $tagNames = $item->tag_names ? explode('||', $item->tag_names) : [];
            $messageLines = $item->last_messages_body
              ? array_values(array_filter(explode("\n", $item->last_messages_body), fn ($line) => trim($line) !== ''))
              : [];
            $actionLines = $item->last_actions_body
              ? array_values(array_filter(explode("\n", $item->last_actions_body), fn ($line) => trim(str_replace(['||@type||', '||@user||', '||@ts||'], '', $line)) !== ''))
              : [];
            $priorityClass = $item->priority === 'high'
              ? 'bg-rose-100 text-rose-700'
              : ($item->priority === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600');
          @endphp
          <tr class="align-top hover:bg-slate-50">
            <td class="px-4 py-5 align-top">
              <div class="space-y-2">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] {{ $priorityClass }}">
                  {{ $item->priority_label }}
                </span>
                <div class="text-3xl font-bold text-slate-900">{{ $item->opportunity_score }}</div>
                <div class="text-xs text-slate-500">{{ $item->messages_count }} mensajes</div>
                @if ($item->is_unattended)
                  <div class="text-xs font-semibold text-blue-600">Sin atender</div>
                @endif
              </div>
            </td>
            <td class="px-4 py-5 align-top">
              <div class="space-y-2">
                <a href="{{ route('customers.show', $item->id) }}" class="text-lg font-semibold leading-tight text-slate-900 hover:underline">
                  {{ $item->name }}
                </a>
                <div class="text-[15px] font-medium text-emerald-600">+{{ preg_replace('/\D+/', '', (string) $item->phone) }}</div>
                <div class="text-sm text-slate-500">{{ $item->user_name ?? 'Sin asignar' }}</div>
                <div class="flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $item->status_color ?? '#94a3b8' }};">
                    {{ $item->status_name ?? 'Sin estado' }}
                  </span>
                  @foreach ($tagNames as $tagName)
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $tagName }}</span>
                  @endforeach
                </div>
                <div class="text-xs text-slate-400">{{ $item->source_name ?? 'Sin origen' }}</div>
              </div>
            </td>
            <td class="px-4 py-5 align-top">
              <div class="space-y-2">
                <div class="text-sm font-semibold text-slate-900">{{ $item->production_label }}</div>
                <div class="text-2xl font-bold text-slate-900">{{ $item->estimated_daily_empanadas ? number_format($item->estimated_daily_empanadas) : '-' }}</div>
                <div class="text-xs uppercase tracking-[0.16em] text-slate-400">emp/dia estimadas</div>
                @if ($item->production_evidence)
                  <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] leading-5 text-slate-600">{{ $item->production_evidence }}</div>
                @endif
              </div>
            </td>
            <td class="px-4 py-5 align-top">
              <div class="space-y-2">
                @foreach ($item->opportunity_reasons as $reason)
                  <div class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-[15px] leading-6 text-slate-700">{{ $reason }}</div>
                @endforeach
              </div>
            </td>
            <td class="px-4 py-5 align-top">
              <div class="grid gap-3 lg:grid-cols-2">
                <div class="space-y-2">
                  <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Ultimos mensajes</div>
                  @forelse($messageLines as $line)
                    @php
                      [$msgBodyPart, $msgDatePart] = array_pad(explode('||@ts||', $line, 2), 2, '');
                    @endphp
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2">
                      <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ $msgDatePart }}</div>
                      <div class="mt-1 break-words text-[14px] leading-5 text-slate-700">{{ $msgBodyPart }}</div>
                    </div>
                  @empty
                    <span class="text-xs text-slate-400">Sin mensajes del cliente</span>
                  @endforelse
                </div>
                <div class="space-y-2">
                  <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Ultimas acciones</div>
                  @forelse($actionLines as $line)
                    @php
                      [$actionNotePart, $actionMetaPart] = array_pad(explode('||@type||', $line, 2), 2, '');
                      [$actionTypePart, $actionMetaTail] = array_pad(explode('||@user||', $actionMetaPart, 2), 2, '');
                      [$actionUserPart, $actionDatePart] = array_pad(explode('||@ts||', $actionMetaTail, 2), 2, '');
                    @endphp
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                      <div class="break-words text-[14px] font-semibold leading-5 text-slate-800">{{ $actionNotePart }}</div>
                      <div class="mt-1 text-[11px] uppercase tracking-[0.12em] text-slate-500">{{ $actionTypePart }} · {{ $actionUserPart }} · {{ $actionDatePart }}</div>
                    </div>
                  @empty
                    <span class="text-xs text-slate-400">Sin acciones</span>
                  @endforelse
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No se encontraron oportunidades con estos filtros.</td>
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
