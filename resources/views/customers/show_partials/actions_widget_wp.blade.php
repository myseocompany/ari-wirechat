@php use Illuminate\Support\Str; @endphp

<h2 class="text-base font-semibold text-slate-900">Línea de tiempo del cliente</h2>

@php
  $timeline = collect();
  $chatMessages = $chatMessages ?? collect();

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
          'type_name' => $action->getTypeName() ?? 'Acción',
          'is_pending' => $action->isPending(),
          'due_date' => $action->due_date,
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
      $senderName = $message->sendable?->name ?? 'Sistema';
      $isCustomerMessage = $message->sendable_id === $customer->id;

      $timeline->push([
          'type' => 'chat',
          'date' => $message->created_at,
          'note' => $message->body ?: 'Mensaje sin texto',
          'creator' => $senderName,
          'conversation' => $conversationName,
          'direction' => $isCustomerMessage ? 'Entrante' : 'Saliente',
          'color' => $isCustomerMessage ? '#10b981' : '#334155',
          'url' => url('/chats/'.$message->conversation_id),
      ]);
  }

  $timeline = $timeline->sortByDesc('date');
@endphp

<div class="space-y-3">
  @foreach($timeline as $item)
    <div class="rounded-lg border border-slate-200 border-l-4 bg-white p-4 shadow-sm" style="border-left-color: {{ $item['color'] }};">
      @if($item['type'] === 'action')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
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
            @if(!empty($item['url']) && Str::contains(Str::lower($item['note']), 'llamada'))
              <audio controls class="mt-2">
                <source src="{{ $item['url'] }}" type="audio/ogg">
                Tu navegador no soporta el audio.
              </audio><br>
            @endif

            {{-- Tipo de acción --}}
            <span class="text-xs text-slate-500">
              {{ $item['type_name'] }}
            </span>
          </div>
          <div class="text-right text-xs text-slate-500">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}<br>
            {{ $item['creator'] }}
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
            <strong class="text-blue-600">🔁 Reasignación</strong><br>
            <small class="text-slate-500">
              Cliente reasignado a <strong>{{ $item['assigned_to'] }}</strong><br>
              Modificado por <strong>{{ $item['editor'] }}</strong>
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
            <strong class="text-slate-900">💬 SellerChat</strong><br>
            <small class="text-slate-500">
              {{ $item['direction'] }} · {{ $item['conversation'] }} · {{ $item['creator'] }}
            </small>
            <div class="mt-2 text-sm text-slate-700">
              {{ \Illuminate\Support\Str::limit($item['note'], 160) }}
            </div>
          </div>
          <div class="text-right text-xs text-slate-500">
            {{ \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') }}
          </div>
        </div>
      @endif
    </div>
  @endforeach
</div>
