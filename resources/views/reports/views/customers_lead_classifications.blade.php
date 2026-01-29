@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-col gap-1">
    <h1 class="mb-1">Tipificación de conversaciones</h1>
    <div class="text-xs text-slate-500">{{ $model->total() }} conversaciones</div>
  </div>
</div>

@if (session('status'))
  <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
    {{ session('status') }}
  </div>
@endif

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form action="/reports/views/customers_lead_classifications" method="GET" class="flex flex-col gap-4">
    <div class="grid gap-4 lg:grid-cols-[repeat(12,minmax(0,1fr))]">
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="from_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desde</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="from_date" name="from_date" value="{{ $fromDate?->format('Y-m-d') ?? $request->from_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="to_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hasta</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="to_date" name="to_date" value="{{ $toDate?->format('Y-m-d') ?? $request->to_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-2">
        <label for="status" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</label>
        <select id="status" name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todos</option>
          @foreach ([\App\Models\LeadConversationClassification::STATUS_CALIFICADO => 'Calificado', \App\Models\LeadConversationClassification::STATUS_NURTURING => 'Nurturing', \App\Models\LeadConversationClassification::STATUS_NO_CALIFICADO => 'No calificado'] as $value => $label)
            <option value="{{ $value }}" @if ((string) $request->status === (string) $value) selected @endif>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="score_min" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Score min</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="0" max="100" id="score_min" name="score_min" value="{{ $request->score_min }}">
      </div>
      <div class="col-span-6 flex flex-col gap-1 lg:col-span-2">
        <label for="score_max" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Score max</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="number" min="0" max="100" id="score_max" name="score_max" value="{{ $request->score_max }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="classifier_version" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Versión</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="classifier_version" name="classifier_version" value="{{ $request->classifier_version }}" placeholder="v1">
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
        <label for="status_ids" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estados CRM</label>
        <select id="status_ids" name="status_ids[]" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          @foreach ($statuses as $status)
            <option value="{{ $status->id }}" @if (collect($request->status_ids)->contains($status->id)) selected @endif>
              {{ $status->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="tag_ids" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Etiquetas cliente</label>
        <select id="tag_ids" name="tag_ids[]" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @if (collect($request->tag_ids)->contains($tag->id)) selected @endif>
              {{ $tag->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-6">
        <label for="suggested_tag_ids" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Etiqueta sugerida</label>
        <select id="suggested_tag_ids" name="suggested_tag_ids[]" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @if (collect($request->suggested_tag_ids)->contains($tag->id)) selected @endif>
              {{ $tag->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-6">
        <label for="applied_tag_ids" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Etiqueta aplicada</label>
        <select id="applied_tag_ids" name="applied_tag_ids[]" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @if (collect($request->applied_tag_ids)->contains($tag->id)) selected @endif>
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

  <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
    <span class="mr-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Procesar</span>

    @foreach ([0 => 'Todas', 5 => '5 de prueba', 10 => '10 de prueba', 50 => '50 de prueba'] as $limitValue => $label)
      <form action="{{ route('reports.customers_lead_classifications.run') }}" method="POST" class="inline-flex">
        @csrf
        <input type="hidden" name="from_date" value="{{ $request->from_date }}">
        <input type="hidden" name="to_date" value="{{ $request->to_date }}">
        <input type="hidden" name="status" value="{{ $request->status }}">
        <input type="hidden" name="score_min" value="{{ $request->score_min }}">
        <input type="hidden" name="score_max" value="{{ $request->score_max }}">
        <input type="hidden" name="classifier_version" value="{{ $request->classifier_version }}">
        <input type="hidden" name="customer_id" value="{{ $request->customer_id }}">
        <input type="hidden" name="conversation_id" value="{{ $request->conversation_id }}">
        <input type="hidden" name="user_id" value="{{ $request->user_id }}">
        @if ($request->boolean('user_unassigned'))
          <input type="hidden" name="user_unassigned" value="1">
        @endif
        @if ($request->boolean('tag_none'))
          <input type="hidden" name="tag_none" value="1">
        @endif
        @foreach ($request->input('status_ids', []) as $statusId)
          <input type="hidden" name="status_ids[]" value="{{ $statusId }}">
        @endforeach
        @foreach ($request->input('tag_ids', []) as $tagId)
          <input type="hidden" name="tag_ids[]" value="{{ $tagId }}">
        @endforeach
        @foreach ($request->input('suggested_tag_ids', []) as $tagId)
          <input type="hidden" name="suggested_tag_ids[]" value="{{ $tagId }}">
        @endforeach
        @foreach ($request->input('applied_tag_ids', []) as $tagId)
          <input type="hidden" name="applied_tag_ids[]" value="{{ $tagId }}">
        @endforeach
        <input type="hidden" name="limit" value="{{ $limitValue }}">
        <button type="submit" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-800">
          {{ $label }}
        </button>
      </form>
    @endforeach

    <span class="text-[11px] text-slate-400">
      Usa el filtro de fechas para acotar el procesamiento.
    </span>
  </div>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cliente</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Clasificación</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Señales y razones</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Ultimos mensajes cliente</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
        @foreach ($model as $item)
          @php
            $tagNames = $item->tag_names ? explode('||', $item->tag_names) : [];
            $bestPhone = $item->phone ?: ($item->phone2 ?: $item->contact_phone2);
            $whatsappPhone = $bestPhone ? preg_replace('/\D+/', '', $bestPhone) : null;
            $signals = is_array($item->signals_json) ? $item->signals_json : [];
            $reasons = is_array($item->reasons_json) ? $item->reasons_json : [];
            $highlightSignals = collect([
                'pide_cita_fabrica' => 'Pide cita fábrica',
                'pide_llamada' => 'Pide llamada',
                'tiene_productos' => 'Ya produce',
                'volumen_mayor_500' => 'Volumen > 500',
                'apertura_nuevo_punto' => 'Abre nuevo punto',
                'dolor_operarios' => 'Dolor operarios',
                'dolor_tiempo' => 'Dolor tiempo',
                'urgencia_alta' => 'Urgencia alta',
                'tiene_presupuesto' => 'Tiene presupuesto',
                'pide_cotizacion_o_ficha' => 'Pide cotización/ficha',
            ])->filter(function ($label, $key) use ($signals) {
                return (bool) data_get($signals, $key, false);
            });

            $statusLabel = match ($item->classification_status) {
                \App\Models\LeadConversationClassification::STATUS_CALIFICADO => 'Calificado',
                \App\Models\LeadConversationClassification::STATUS_NURTURING => 'Nurturing',
                \App\Models\LeadConversationClassification::STATUS_NO_CALIFICADO => 'No calificado',
                default => $item->classification_status,
            };

            $statusClasses = match ($item->classification_status) {
                \App\Models\LeadConversationClassification::STATUS_CALIFICADO => 'bg-emerald-100 text-emerald-700',
                \App\Models\LeadConversationClassification::STATUS_NURTURING => 'bg-amber-100 text-amber-700',
                \App\Models\LeadConversationClassification::STATUS_NO_CALIFICADO => 'bg-slate-200 text-slate-600',
                default => 'bg-slate-100 text-slate-600',
            };
          @endphp
          <tr class="customer-overlay-link hover:bg-slate-50" data-url="{{ route('customers.show', $item->customer_id) }}">
            <td class="px-4 py-3 font-semibold">
              <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                  <a href="{{ route('customers.show', $item->customer_id) }}" class="customer-overlay-link text-slate-900 hover:underline" data-url="{{ route('customers.show', $item->customer_id) }}">{{ $item->name }}</a>
                  <a href="{{ route('customers.show', $item->customer_id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700" data-customer-overlay-ignore>Ver</a>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm font-normal text-slate-700">
                  <span>{{ $bestPhone ?: 'Sin telefono' }}</span>
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
                <div class="flex flex-wrap items-center gap-2 text-xs font-normal text-slate-500">
                  <span>{{ $item->user_name ?? 'Sin asignar' }}</span>
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $item->status_color ?? '#94a3b8' }};">
                    {{ $item->status_name ?? 'Sin estado' }}
                  </span>
                  <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-500">Conv {{ $item->conversation_id }}</span>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-col gap-2">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                    {{ $statusLabel }}
                  </span>
                  <span class="inline-flex items-center rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                    Score {{ (int) $item->score }}
                  </span>
                  @if (! is_null($item->confidence))
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                      Conf {{ number_format((float) $item->confidence, 2) }}
                    </span>
                  @endif
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                  @if ($item->suggested_tag_name)
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-1 font-semibold text-indigo-700">
                      Sugerida: {{ $item->suggested_tag_name }}
                    </span>
                  @endif
                  @if ($item->applied_tag_name)
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 font-semibold text-emerald-700">
                      Aplicada: {{ $item->applied_tag_name }}
                    </span>
                  @endif
                  <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 font-semibold text-slate-600">
                    {{ $item->classifier_version }}
                  </span>
                  <span class="inline-flex items-center rounded-full px-2 py-1 font-semibold {{ $item->llm_used ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                    {{ $item->llm_used ? 'LLM' : 'Heurístico' }}
                  </span>
                  @if ($item->model)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 font-semibold text-slate-600">
                      {{ $item->model }}
                    </span>
                  @endif
                  @if (! is_null($item->llm_duration_ms))
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 font-semibold text-slate-600">
                      {{ $item->llm_duration_ms }} ms
                    </span>
                  @endif
                </div>
                @if ($item->llm_error)
                  <div class="text-xs text-rose-500">
                    LLM error: {{ $item->llm_error }}
                  </div>
                @endif
                <div class="flex flex-wrap gap-2">
                  @forelse ($tagNames as $tagName)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">
                      {{ $tagName }}
                    </span>
                  @empty
                    <span class="text-xs text-slate-400">Sin etiqueta cliente</span>
                  @endforelse
                </div>
                <div class="text-xs text-slate-400">
                  Ult. msg: {{ $item->last_customer_message_at ?: '—' }} · Clasif: {{ $item->classified_at ?: '—' }}
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-col gap-3">
                <div>
                  <div class="mb-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Señales clave</div>
                  @if ($highlightSignals->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                      @foreach ($highlightSignals as $label)
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">
                          {{ $label }}
                        </span>
                      @endforeach
                    </div>
                  @else
                    <div class="text-xs text-slate-400">Sin señales destacadas</div>
                  @endif
                </div>
                <div>
                  <div class="mb-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Razones</div>
                  @if (count($reasons))
                    <div class="flex flex-col gap-2 text-xs text-slate-600">
                      @foreach ($reasons as $reason)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1">{{ $reason }}</div>
                      @endforeach
                    </div>
                  @else
                    <div class="text-xs text-slate-400">Sin razones</div>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              @php
                $lastMessages = $item->last_messages_body ? explode("\n", $item->last_messages_body) : [];
              @endphp
              @if (count($lastMessages))
                <div class="flex flex-col gap-3 text-sm text-slate-600">
                  @foreach ($lastMessages as $messageLine)
                    @php
                      $parts = array_pad(explode('|||', $messageLine), 2, '');
                      [$messageBody, $messageDate] = $parts;
                    @endphp
                    <div class="space-y-1">
                      <div>{{ $messageBody ?: 'Sin mensaje' }}</div>
                      <div class="text-xs text-slate-400">{{ $messageDate ?: '—' }}</div>
                    </div>
                  @endforeach
                </div>
              @else
                <span class="text-sm text-slate-400">Sin mensajes</span>
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

@include('customers.partials.customer_overlay')

@endsection
