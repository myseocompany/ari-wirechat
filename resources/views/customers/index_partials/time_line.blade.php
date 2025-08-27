@php use Illuminate\Support\Str; @endphp
<h2 class="mt-4">Línea de tiempo del cliente</h2>

@php
  $timeline = collect();

  // Agregamos acciones
  foreach ($customer->actions as $action) {
      $timeline->push([
          'type' => 'action',
          'date' => $action->created_at,
          'note' => $action->note,
          'creator' => $action->creator->name ?? 'Automático',
          'icon' => $action->type->icon ?? 'fa-sticky-note',
          'color' => $action->type->color ?? '#0d6efd',
          'url' => $action->url,
          'type_name' => $action->getTypeName(), // nombre del tipo
          'is_pending' => $action->isPending(),  // estado pendiente
          'due_date' => $action->due_date,       // fecha de vencimiento
          
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
              {{ $item['type_name'] ?? 'Acción' }}
            </div>

            {{-- Texto de la nota, sin bold y con saltos de línea --}}
            <div class="mt-1" style="white-space: pre-wrap;">
              {!! nl2br(e($item['note'])) !!}
            </div><br>

            {{-- 🎧 Reproductor de audio --}}
            @if(!empty($item['url']) && Str::contains(Str::lower($item['note']), 'llamada'))
              @php
                $lower = Str::lower($item['url']);
                $mime = Str::endsWith($lower, '.mp3') ? 'audio/mpeg' :
                        (Str::endsWith($lower, '.wav') ? 'audio/wav' :
                        (Str::endsWith($lower, ['.ogg','.oga']) ? 'audio/ogg' : 'audio/mpeg'));
              @endphp
              <audio controls class="mt-2" style="width:100%;">
                <source src="{{ $item['url'] }}" type="{{ $mime }}">
                Tu navegador no soporta el audio.
              </audio><br>
            @endif


            {{-- Tipo de acción --}}
            <span class="text-muted small">
              {{ $item['type_name'] ?? 'Tipo no definido' }}
            </span>

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
            <strong class="text-primary">🔁 Reasignación</strong><br>
            <small class="text-muted">
              Cliente reasignado a <strong>{{ $item['assigned_to'] }}</strong><br>
              Modificado por <strong>{{ $item['editor'] }}</strong>
            </small>
          </div>
          <div class="text-end small text-muted">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}
          </div>
        </div>

      @elseif($item['type'] === 'estado')
        <div class="d-flex justify-content-between">
          <div>
            <strong>📝 Cambio de estado</strong><br>
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
