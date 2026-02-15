@php use Illuminate\Support\Str; @endphp
<h2 class="mt-4">Línea de tiempo del cliente</h2>

@php
  $timeline = collect();

  // Agregamos acciones
  foreach ($customer->actions as $action) {
      $timeline->push([
          'type' => 'action',
          'id' => $action->id,
          'date' => $action->created_at,
          'note' => $action->note,
          'creator' => $action->creator->name ?? 'Automático',
          'icon' => $action->type->icon ?? 'fa-sticky-note',
          'color' => $action->type->color ?? '#0d6efd',
          'url' => $action->url,
          'type_name' => $action->getTypeName(), // nombre del tipo
          'is_pending' => $action->isPending(),  // estado pendiente
          'due_date' => $action->due_date,       // fecha de vencimiento
          'creation_seconds' => $action->creation_seconds,
          
      ]);
  }

  // Estado actual del customer (registro actual)
  $timeline->push([
      'type' => 'estado',
      'date' => $customer->updated_at,
      'status' => $customer->status->name ?? $customer->status_id,
      'assigned_to' => $customer->user->name ?? 'Sin asignar',
      'editor' => $customer->updated_user->name ?? 'Desconocido',
      'color' => $customer->status->color ?? 'gray',
      'is_current' => true,
  ]);

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
              'color' => '#003366', // Azul oscuro
          ]);
      }

      $timeline->push([
          'type' => 'estado',
          'date' => $history->updated_at,
          'status' => $history->status->name ?? $history->status_id,
          'assigned_to' => $asignadoA,
          'editor' => $editor,
          'color' => $history->status->color ?? 'gray',
          'is_current' => false,
      ]);

      $prevUserId = $currentUserId;
  }

  // Ordenamos por fecha descendente
  $timeline = $timeline->sortByDesc('date');
@endphp

<div class="timeline">
  @foreach($timeline as $item)
    <div class="mb-3 p-3 rounded shadow-sm" style="border-left: 5px solid {{ $item['color'] }}; background: #f8f9fa;">
      @if($item['type'] === 'action')
        <div class="d-flex justify-content-between">
          <div>
            {{-- ⏰ Mostrar si está pendiente y tiene fecha --}}
            @if($item['is_pending'] && $item['due_date'])
              <span class="text-danger fw-bold small">
                ⏰ Programado para: {{ \Carbon\Carbon::parse($item['due_date'])->format('d M Y H:i') }}
              </span><br>
            @endif

            {{-- Título cortito, no todo en bold --}}
            <div class="fw-semibold">
              @if($item['icon'])
                <i class="fa {{ $item['icon'] }}"></i>
              @endif
              
            </div>

            {{-- Texto de la nota, sin bold y con saltos de línea --}}
            <div class="mt-1" style="white-space: normal;">
              {!! nl2br(e($item['note'])) !!}
            </div><br>

            {{-- 🎧 Reproductor de audio --}}
            @if(!empty($item['url']) && Str::contains(Str::lower($item['note']), 'llamada'))
              @php
                $audioUrl = trim((string) $item['url']);
                if (Str::startsWith($audioUrl, '//')) {
                  $audioUrl = 'https:'.$audioUrl;
                }
                if (! Str::startsWith($audioUrl, ['http://', 'https://'])) {
                  $audioUrl = 'https://'.$audioUrl;
                }
                $lower = Str::lower($audioUrl);
                $isTwilioRecording = Str::contains($lower, 'api.twilio.com')
                  && Str::contains($lower, '/recordings/');
                if ($isTwilioRecording && !empty($item['id'])) {
                  $audioUrl = route('actions.audio', $item['id']);
                }
                $mime = Str::contains($lower, '.mp3') ? 'audio/mpeg' :
                        (Str::contains($lower, '.wav') ? 'audio/wav' :
                        (Str::contains($lower, ['.ogg', '.oga']) ? 'audio/ogg' :
                        (Str::contains($lower, ['.m4a', '.m4b']) ? 'audio/mp4' :
                        (Str::contains($lower, '.webm') ? 'audio/webm' : 'audio/mpeg'))));
              @endphp
              <audio controls class="mt-2" style="width:100%;">
                <source src="{{ $audioUrl }}" type="{{ $mime }}">
                Tu navegador no soporta el audio.
              </audio><br>
            @endif


            {{-- Tipo de acción --}}
            <span class="text-muted small">
              {{ $item['type_name'] ?? 'Tipo no definido' }}
            </span>

            @if(!empty($item['creation_seconds']))
              @php
                $durationSeconds = (int) $item['creation_seconds'];
                $durationLabel = $durationSeconds >= 3600
                  ? gmdate('H:i:s', $durationSeconds)
                  : gmdate('i:s', $durationSeconds);
              @endphp
              <div class="text-muted small">
                ⏱ Duración: {{ $durationLabel }}
              </div>
            @endif

          </div>

          {{-- Lado derecho: fecha y creador --}}
          <div class="text-end small text-muted">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}<br>
            {{ $item['creator'] }}
          </div>
        </div>


      @elseif($item['type'] === 'asignacion')
        <div class="d-flex justify-content-between">
          <div>
            <strong class="text-primary">🔁 Asignación registrada</strong><br>
            <small class="text-muted">
              Propietario actual: <strong>{{ $item['assigned_to'] }}</strong><br>
              Registrado por <strong>{{ $item['editor'] }}</strong>
            </small>
          </div>
          <div class="text-end small text-muted">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}
          </div>
        </div>

      @elseif($item['type'] === 'estado')
        <div class="d-flex justify-content-between">
          <div>
            <strong>📝 Cambio de estado</strong>
            @if(!empty($item['is_current']))
              <span class="badge bg-success ms-2">Actual</span>
            @endif
            <br>
            <small class="text-muted">
              Nuevo estado: <strong>{{ $item['status'] }}</strong><br>
              Asignado a <strong>{{ $item['assigned_to'] }}</strong><br>
              Modificado por <strong>{{ $item['editor'] }}</strong>
            </small>
          </div>
          <div class="text-end small text-muted">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}
          </div>
        </div>
      @endif
    </div>
  @endforeach
</div>
