@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="space-y-5">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="mb-1 text-2xl font-semibold text-slate-900">Agente de oportunidades</h1>
      <p class="text-sm text-slate-500">Clasificacion comercial por mensajes, actividad e IA procesada en segundo plano.</p>
    </div>
    <a href="{{ route('reports.opportunities.export', request()->query()) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
      Exportar CSV
    </a>
  </div>

  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div class="rounded-lg border border-slate-200 bg-white p-4">
      <p class="text-xs text-slate-500">Leads en rango</p>
      <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['total_leads_range'] }}</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white p-4">
      <p class="text-xs text-slate-500">Prioridad alta</p>
      <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['high'] }}</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white p-4">
      <p class="text-xs text-slate-500">Producen</p>
      <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['makers'] }}</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white p-4">
      <p class="text-xs text-slate-500">IA pendiente</p>
      <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['llm_pending'] }}</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-white p-4">
      <p class="text-xs text-slate-500">IA procesada</p>
      <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['llm_analyzed'] }}</p>
    </div>
  </div>

  <form action="{{ route('reports.opportunities') }}" method="GET" class="rounded-lg border border-slate-200 bg-white" data-opportunities-loading-form>
    <details>
      <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-medium text-slate-700">
        <span>Filtros</span>
        <span class="text-xs font-normal text-slate-400">{{ $summary['candidate_total'] }} candidatos · limite {{ $summary['limit'] }}</span>
      </summary>
      <div class="grid gap-3 border-t border-slate-100 bg-slate-50 p-3 md:grid-cols-12">
        <div class="flex flex-col gap-1 md:col-span-3">
          <label for="message_search" class="text-xs font-medium text-slate-500">Buscar mensaje</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="text" id="message_search" name="message_search" value="{{ $request->message_search }}">
        </div>
        <div class="flex flex-col gap-1 md:col-span-3">
          <label for="notes_tags" class="text-xs font-medium text-slate-500">Tags en notas</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="text" id="notes_tags" name="notes_tags" value="{{ $request->notes_tags }}" placeholder="#alimentec2026">
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <label for="priority" class="text-xs font-medium text-slate-500">Prioridad</label>
          <select id="priority" name="priority" class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700">
            <option value="">Todas</option>
            <option value="high" @selected($request->priority === 'high')>Alta</option>
            <option value="medium" @selected($request->priority === 'medium')>Media</option>
            <option value="low" @selected($request->priority === 'low')>Baja</option>
          </select>
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <label for="maker" class="text-xs font-medium text-slate-500">Produccion</label>
          <select id="maker" name="maker" class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700">
            <option value="">Todas</option>
            <option value="makes" @selected($request->maker === 'makes')>Hace empanadas</option>
            <option value="project" @selected($request->maker === 'project')>Proyecto</option>
            <option value="other" @selected($request->maker === 'other')>Otros</option>
            <option value="unknown" @selected($request->maker === 'unknown')>Sin clasificar</option>
          </select>
        </div>
        <div class="flex items-end md:col-span-2">
          <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">Analizar</button>
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <label for="from_date" class="text-xs font-medium text-slate-500">Desde</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="date" id="from_date" name="from_date" value="{{ $fromDate?->format('Y-m-d') ?? $request->from_date }}">
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <label for="to_date" class="text-xs font-medium text-slate-500">Hasta</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="date" id="to_date" name="to_date" value="{{ $toDate?->format('Y-m-d') ?? $request->to_date }}">
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <label for="messages_min" class="text-xs font-medium text-slate-500">Min mensajes</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="number" min="0" id="messages_min" name="messages_min" value="{{ $request->messages_min }}">
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <label for="production_min" class="text-xs font-medium text-slate-500">Min emp/dia</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="number" min="0" id="production_min" name="production_min" value="{{ $request->production_min }}">
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <label for="limit" class="text-xs font-medium text-slate-500">Analizar</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="number" min="10" max="3000" id="limit" name="limit" value="{{ $request->limit ?? 200 }}">
        </div>
        <div class="flex flex-col gap-1 md:col-span-2">
          <label for="llm_limit" class="text-xs font-medium text-slate-500">Max IA</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="number" min="1" max="200" id="llm_limit" name="llm_limit" value="{{ $request->llm_limit ?? 50 }}">
        </div>
        <div class="flex flex-col gap-1 md:col-span-3">
          <label for="action_note_search" class="text-xs font-medium text-slate-500">Buscar accion</label>
          <input class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700" type="text" id="action_note_search" name="action_note_search" value="{{ $request->action_note_search }}">
        </div>
        <div class="flex flex-col gap-1 md:col-span-3">
          <label for="status_ids" class="text-xs font-medium text-slate-500">Estados</label>
          <select id="status_ids" name="status_ids[]" multiple class="min-h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700">
            @foreach ($statuses as $status)
              <option value="{{ $status->id }}" @if (collect($request->status_ids)->contains($status->id)) selected @endif>{{ $status->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex flex-col gap-1 md:col-span-3">
          <label for="user_id" class="text-xs font-medium text-slate-500">Asesor</label>
          <select id="user_id" name="user_id" class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700">
            <option value="">Todos</option>
            @foreach ($users as $user)
              <option value="{{ $user->id }}" @selected($request->user_id == $user->id)>{{ $user->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex flex-col gap-1 md:col-span-3">
          <label for="source_id" class="text-xs font-medium text-slate-500">Origen</label>
          <select id="source_id" name="source_id" class="h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700">
            <option value="">Todos</option>
            @foreach ($sources as $source)
              <option value="{{ $source->id }}" @selected($request->source_id == $source->id)>{{ $source->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex flex-col gap-1 md:col-span-4">
          <label for="tag_ids" class="text-xs font-medium text-slate-500">Etiquetas</label>
          <select id="tag_ids" name="tag_ids[]" multiple class="min-h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-700">
            @foreach ($tags as $tag)
              <option value="{{ $tag->id }}" @if (collect($request->tag_ids)->contains($tag->id)) selected @endif>{{ $tag->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex flex-wrap items-center gap-4 md:col-span-8 md:pt-6">
          <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="unattended" value="1" class="rounded border-slate-300 text-slate-900" @if ($request->boolean('unattended')) checked @endif>
            Sin atender
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="tag_none" value="1" class="rounded border-slate-300 text-slate-900" @if ($request->boolean('tag_none')) checked @endif>
            Sin etiqueta
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="llm" value="1" class="rounded border-slate-300 text-slate-900" @if ($request->boolean('llm')) checked @endif>
            Mostrar IA procesada
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="llm_only" value="1" class="rounded border-slate-300 text-slate-900" @if ($request->boolean('llm_only')) checked @endif>
            Solo con IA
          </label>
        </div>
      </div>
    </details>
  </form>

  <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Prospecto</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Prioridad</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Produccion</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Evidencia</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Siguiente accion</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
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
                ? 'bg-rose-50 text-rose-700'
                : ($item->priority === 'medium' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600');
              $firstMessage = $messageLines[0] ?? null;
              [$messageBodyPart, $messageDatePart] = array_pad(explode('||@ts||', (string) $firstMessage, 2), 2, '');
              $firstAction = $actionLines[0] ?? null;
              [$actionNotePart, $actionMetaPart] = array_pad(explode('||@type||', (string) $firstAction, 2), 2, '');
              [$actionTypePart, $actionMetaTail] = array_pad(explode('||@user||', $actionMetaPart, 2), 2, '');
              [$actionUserPart, $actionDatePart] = array_pad(explode('||@ts||', $actionMetaTail, 2), 2, '');
            @endphp
            <tr class="align-top hover:bg-slate-50">
              <td class="px-4 py-3">
                <a href="{{ route('customers.show', $item->id) }}" class="text-sm font-semibold text-slate-900 hover:underline">{{ $item->name }}</a>
                <p class="mt-1 text-xs text-slate-400">+{{ preg_replace('/\D+/', '', (string) $item->phone) }} · {{ $item->user_name ?? 'Sin asignar' }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium text-white" style="background-color: {{ $item->status_color ?? '#94a3b8' }};">
                    {{ $item->status_name ?? 'Sin estado' }}
                  </span>
                  @foreach ($tagNames as $tagName)
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-600">{{ $tagName }}</span>
                  @endforeach
                </div>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $priorityClass }}">{{ $item->priority_label }}</span>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $item->opportunity_score }} pts</p>
                <p class="text-xs text-slate-400">{{ $item->messages_count }} mensajes</p>
                @if ($item->is_unattended)
                  <p class="mt-1 text-xs font-medium text-blue-600">Sin atender</p>
                @endif
              </td>
              <td class="px-4 py-3 text-sm text-slate-700">
                <p class="font-medium text-slate-900">{{ $item->production_label }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $item->estimated_daily_empanadas ? number_format($item->estimated_daily_empanadas).' emp/dia' : 'Sin volumen' }}</p>
                <p class="mt-1 text-xs text-slate-500">Intencion: {{ $item->intent_label ?? 'No claro' }}</p>
                @if ($item->llm_used)
                  <span class="mt-2 inline-flex rounded-full bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-700">IA {{ $item->llm_confidence !== null ? number_format($item->llm_confidence * 100, 0).'%' : '' }}</span>
                @elseif ($item->llm_error)
                  <p class="mt-2 text-xs text-slate-400">IA: {{ $item->llm_error }}</p>
                @endif
              </td>
              <td class="max-w-sm px-4 py-3 text-xs leading-5 text-slate-600">
                @if ($item->production_evidence)
                  <p class="font-medium text-slate-700">{{ $item->production_evidence }}</p>
                @endif
                @if ($item->llm_evidence && $item->llm_evidence !== $item->production_evidence)
                  <p class="mt-1 text-teal-700">{{ $item->llm_evidence }}</p>
                @endif
                @foreach (array_slice($item->opportunity_reasons, 0, 2) as $reason)
                  <p class="mt-1">{{ $reason }}</p>
                @endforeach
                @if ($messageBodyPart !== '')
                  <p class="mt-2 text-slate-400">{{ $messageDatePart }} · {{ $messageBodyPart }}</p>
                @endif
              </td>
              <td class="max-w-sm px-4 py-3 text-xs leading-5 text-slate-600">
                <p class="text-sm font-semibold text-slate-900">{{ $item->next_best_action_label ?? 'Esperar señal' }}</p>
                <p class="mt-1 text-slate-500">{{ $item->recommended_channel_label ?? 'CRM' }} · {{ $item->recommended_sla ?? 'esperar' }}</p>
                @if ($item->action_reason)
                  <p class="mt-2">{{ $item->action_reason }}</p>
                @endif
                @if ($item->suggested_message)
                  <p class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">{{ $item->suggested_message }}</p>
                @endif
                @if ($actionNotePart !== '')
                  <p class="mt-2 text-slate-400">{{ $actionNotePart }} · {{ $actionTypePart }} {{ $actionUserPart }} {{ $actionDatePart }}</p>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">Sin resultados</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="border-t border-slate-200 px-4 py-3" data-opportunities-loading-links>
      {{ $model->onEachSide(1)->links() }}
    </div>
  </div>
</div>

<div id="opportunities-loading" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-white/70 backdrop-blur-sm">
  <div class="flex min-w-[240px] flex-col items-center gap-3 rounded-lg border border-slate-200 bg-white px-6 py-5 text-center shadow-lg">
    <div class="h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-slate-900"></div>
    <div>
      <p class="text-sm font-semibold text-slate-900">Cargando analisis</p>
      <p class="mt-1 text-xs text-slate-500">Esto puede tardar unos segundos.</p>
    </div>
  </div>
</div>

@endsection

@push('scripts')
  <script>
    (function () {
      var loading = document.getElementById('opportunities-loading');
      if (!loading) {
        return;
      }

      function showLoading() {
        loading.classList.remove('hidden');
        loading.classList.add('flex');
      }

      function hideLoading() {
        loading.classList.add('hidden');
        loading.classList.remove('flex');
      }

      document.querySelectorAll('[data-opportunities-loading-form]').forEach(function (form) {
        form.addEventListener('submit', function () {
          showLoading();
        });
      });

      document.querySelectorAll('[data-opportunities-loading-links] a[href]').forEach(function (link) {
        link.addEventListener('click', function () {
          showLoading();
        });
      });

      window.addEventListener('pageshow', hideLoading);
    })();
  </script>
@endpush
