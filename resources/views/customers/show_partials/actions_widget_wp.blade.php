@php use Illuminate\Support\Str; @endphp

<h2 class="text-base font-semibold text-slate-900">Línea de tiempo del cliente</h2>

@php
  $timeline = collect();
  $chatMessages = $chatMessages ?? collect();
  $messageSourceLabelsByConversation = $messageSourceLabelsByConversation ?? collect();

  $actions = $actions ?? $customer->actions ?? collect();

  // Agregamos acciones
  foreach ($actions as $action) {
      $timeline->push([
          'type' => 'action',
          'id' => $action->id,
          'date' => $action->created_at,
          'note' => $action->note,
          'creator' => $action->creator->name ?? 'Automático',
          'icon' => $action->type->icon ?? 'fa-sticky-note',
          'color' => $action->type->color ?? '#0d6efd',
          'url' => $action->url,
          'type_name' => $action->getTypeName() ?? 'Acción',
          'is_pending' => $action->isPending(),
          'due_date' => $action->due_date,
          'type_id' => $action->type_id,
          'creation_seconds' => $action->creation_seconds,
          'status_id' => $customer->status_id,
          'customer_name' => $customer->name,
          'transcription_status' => $action->transcription->status ?? null,
          'transcription_text' => $action->transcription->transcript_text ?? null,
          'transcription_error' => $action->transcription->error_message ?? null,
          'transcription_step' => $action->transcription->progress_step ?? null,
          'transcription_message' => $action->transcription->progress_message ?? null,
          'transcription_percent' => $action->transcription->progress_percent ?? null,
      ]);
  }

  // Detectamos cambios de asignación o estado en historial
  $prevUserId = null;
  foreach ($customer->histories->sortBy('updated_at') as $history) {
      $currentUserId = $history->user_id;
      $asignadoA = $history->user->name ?? 'Sin asignar';
      $editor = $history->updated_user->name ?? 'Desconocido';

      if ($prevUserId !== null && $currentUserId != $prevUserId) {
          $timeline->push([
              'type' => 'asignacion',
              'date' => $history->updated_at,
              'assigned_to' => $asignadoA,
              'editor' => $editor,
              'color' => '#003366',
          ]);
      }

      $timeline->push([
          'type' => 'estado',
          'date' => $history->updated_at,
          'status' => $history->status->name ?? $history->status_id,
          'assigned_to' => $asignadoA,
          'editor' => $editor,
          'color' => $history->status->color ?? 'gray',
      ]);

      $prevUserId = $currentUserId;
  }

  foreach ($chatMessages as $message) {
      $conversationName = optional($message->conversation->group)->name ?: 'Chat '.$message->conversation_id;
      $isCustomerMessage = $message->sendable_type === $customer->getMorphClass()
          && (int) $message->sendable_id === (int) $customer->id;
      $sourceLabel = $messageSourceLabelsByConversation[$message->conversation_id]
          ?? (Str::startsWith($conversationName, 'Chat ') ? 'WhatsApp' : $conversationName);

      $timeline->push([
          'type' => 'chat',
          'date' => $message->created_at,
          'note' => $message->body ?: 'Mensaje sin texto',
          'conversation' => $conversationName,
          'direction' => $isCustomerMessage ? 'Entrante' : 'Saliente',
          'color' => $isCustomerMessage ? '#10b981' : '#334155',
          'url' => url('/chats/'.$message->conversation_id),
          'source_label' => $sourceLabel,
      ]);
  }

  $timeline = $timeline->sortByDesc('date');
@endphp

<div class="space-y-3">
  @foreach($timeline as $item)
    <div class="rounded-lg border border-slate-200 border-l-4 bg-white p-4 shadow-sm" style="border-left-color: {{ $item['color'] }};">
      @if($item['type'] === 'action')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0 flex-1">
            {{-- ⏰ Pendiente programado --}}
            @if($item['is_pending'] && $item['due_date'])
              <span class="text-xs font-semibold text-red-600">
                ⏰ Programado para: {{ \Carbon\Carbon::parse($item['due_date'])->format('d M Y H:i') }}
              </span><br>
            @endif

            {{-- Nota de acción --}}
            <strong class="text-sm text-slate-900">
              @if($item['icon'])
                <i class="fa {{ $item['icon'] }}"></i>
              @endif
              {{ $item['note'] }}
            </strong><br>

            {{-- 🎧 Reproductor si es llamada --}}
            @if(!empty($item['url']) && (int) $item['type_id'] === 21)
              @php
                $audioUrl = trim((string) $item['url']);
                if (Str::startsWith($audioUrl, '//')) {
                  $audioUrl = 'https:'.$audioUrl;
                }
                if (! Str::startsWith($audioUrl, ['http://', 'https://'])) {
                  $audioUrl = 'https://'.$audioUrl;
                }
                $lowerUrl = Str::lower($audioUrl);
                $isTwilioRecording = Str::contains($lowerUrl, 'api.twilio.com')
                  && Str::contains($lowerUrl, '/recordings/');
                if ($isTwilioRecording) {
                  $audioUrl = route('actions.audio', $item['id']);
                }
                $mime = Str::contains($lowerUrl, '.mp3') ? 'audio/mpeg' :
                  (Str::contains($lowerUrl, '.wav') ? 'audio/wav' :
                  (Str::contains($lowerUrl, ['.ogg', '.oga']) ? 'audio/ogg' :
                  (Str::contains($lowerUrl, ['.m4a', '.m4b']) ? 'audio/mp4' :
                  (Str::contains($lowerUrl, '.webm') ? 'audio/webm' : 'audio/mpeg'))));
              @endphp
              <audio controls class="mt-2 w-full max-w-full">
                <source src="{{ $audioUrl }}" type="{{ $mime }}">
                Tu navegador no soporta el audio.
              </audio><br>
            @endif

            @if(!empty($item['url']) && (int) $item['type_id'] === 21)
              <div class="mt-2 space-y-2">
                @if(($item['transcription_status'] ?? null) === 'done' && ! empty($item['transcription_text']))
                  <div class="whitespace-pre-line break-words rounded-md border border-slate-200 bg-slate-50 p-2 text-sm text-slate-700">
                    {{ $item['transcription_text'] }}
                  </div>
                @elseif(in_array($item['transcription_status'] ?? '', ['pending', 'processing'], true))
                  <div class="text-xs text-slate-500">Transcribiendo...</div>
                @elseif(($item['transcription_status'] ?? null) === 'error')
                  <div class="text-xs text-red-600">
                    Error al transcribir: {{ $item['transcription_error'] ?? 'Error desconocido' }}
                  </div>
                @endif

                @if(Auth::check() && Auth::user()->role_id == 1)
                  <form method="POST" action="{{ route('actions.transcribe', $item['id']) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md border border-blue-600 px-2 py-1 text-[11px] font-semibold text-blue-600 transition hover:bg-blue-50">
                      Transcribir
                    </button>
                  </form>
                @endif

                @if(($item['transcription_status'] ?? null) !== null)
                  <details class="text-xs text-slate-500">
                    <summary class="cursor-pointer select-none">Ver proceso</summary>
                    <div class="mt-2 space-y-1">
                      <div>Estado: {{ $item['transcription_status'] }}</div>
                      @if(! empty($item['transcription_message']))
                        <div>Paso: {{ $item['transcription_message'] }}</div>
                      @endif
                      @if(($item['transcription_percent'] ?? null) !== null)
                        <div>Progreso: {{ $item['transcription_percent'] }}%</div>
                      @endif
                    </div>
                  </details>
                @endif
              </div>
            @endif

            {{-- Tipo de acción --}}
            <span class="text-xs text-slate-500">
              {{ $item['type_name'] }}
            </span>

            @if(!empty($item['creation_seconds']))
              @php
                $durationSeconds = (int) $item['creation_seconds'];
                $durationLabel = $durationSeconds >= 3600
                  ? gmdate('H:i:s', $durationSeconds)
                  : gmdate('i:s', $durationSeconds);
              @endphp
              <div class="text-xs text-slate-500">
                ⏱ Duración: {{ $durationLabel }}
              </div>
            @endif
          </div>
          <div class="text-right text-xs text-slate-500">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}<br>
            {{ $item['creator'] }}
            @if($item['is_pending'])
              <div class="mt-2">
                <button
                  type="button"
                  data-toggle="modal"
                  data-id="{{ $item['id'] }}"
                  data-note="{{ $item['note'] }}"
                  data-type-id="{{ $item['type_id'] }}"
                  data-status-id="{{ $item['status_id'] }}"
                  data-customer-name="{{ $item['customer_name'] }}"
                  class="inline-flex items-center rounded-md border border-blue-600 px-2 py-1 text-[11px] font-semibold text-blue-600 transition hover:bg-blue-50"
                >
                  Completar
                </button>
              </div>
            @endif
            @if(Auth::check() && Auth::user()->role_id == 1 && isset($item['id']))
              <br>
              <a href="/actions/{{ $item['id'] }}/destroy" class="text-red-600 hover:text-red-700" title="Eliminar acción">
                <i class="fa fa-trash-o"></i>
              </a>
            @endif
          </div>
        </div>

      @elseif($item['type'] === 'asignacion')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <strong class="text-blue-600">🔁 Asignación registrada</strong><br>
            <small class="text-slate-500">
              Propietario actual: <strong>{{ $item['assigned_to'] }}</strong><br>
              Registrado por <strong>{{ $item['editor'] }}</strong>
            </small>
          </div>
          <div class="text-right text-xs text-slate-500">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}
          </div>
        </div>

      @elseif($item['type'] === 'estado')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <strong>📝 Cambio de estado</strong><br>
            <small class="text-slate-500">
              Nuevo estado: <strong>{{ $item['status'] }}</strong><br>
              Asignado a <strong>{{ $item['assigned_to'] }}</strong><br>
              Modificado por <strong>{{ $item['editor'] }}</strong>
            </small>
          </div>
          <div class="text-right text-xs text-slate-500">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}
          </div>
        </div>
      @elseif($item['type'] === 'chat')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <div class="text-base font-semibold text-slate-900">
              {{ \Illuminate\Support\Str::limit($item['note'], 160) }}
            </div>
            <small class="text-slate-500">
              {{ $item['direction'] }}
            </small>
            <div class="text-xs text-slate-400">{{ $item['source_label'] ?? 'WhatsApp' }}</div>
          </div>
          <div class="text-right text-xs text-slate-500">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}
          </div>
        </div>
      @endif
    </div>
  @endforeach
</div>

@include('actions.modal_pending', [
  'action_options' => $action_options,
  'statuses_options' => $statuses_options
])

@push('scripts')
  <script>
    function senForm(){ document.getElementById('complete_action_form').submit(); }
    function closeModal(){ document.getElementById('pendingActionModal').classList.add('hidden'); }
    function openModal(){ document.getElementById('pendingActionModal').classList.remove('hidden'); }

    $(function(){
      $('[data-toggle="modal"]').on('click', function(){
        var b = $(this), m = $('#pendingActionModal');
        m.find('#action_id').val(b.data('id'));
        m.find('#pending_note').text(b.data('note'));
        m.find('#type_id').val(b.data('type-id'));
        m.find('#status_id').val(b.data('status-id'));
        m.find('#customer_name').text(b.data('customer-name'));
        openModal();
      });
    });
  </script>
@endpush
