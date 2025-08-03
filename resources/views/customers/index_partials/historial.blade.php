<h2 class="mt-4">Historial de asignación</h2>

@php
  $histories = $customer->histories->sortBy('updated_at'); // orden cronológico ascendente
  $lastUserId = null;
  $historiesToShow = collect();

  foreach ($histories as $history) {
      if ($lastUserId !== $history->user_id) {
          $historiesToShow->push($history);
          $lastUserId = $history->user_id;
      }
  }

  // Agregamos el estado actual como el más reciente
  $historiesToShow = $historiesToShow->sortByDesc('updated_at');
@endphp

<ul class="list-group">
  @foreach($historiesToShow as $history)
    @php
      $status = $history->status->name ?? $history->status_id;
      $statusColor = $history->status->color ?? 'gray';
      $assignedTo = $history->user->name ?? 'Sin asignar';
      $editor = $history->updated_user->name ?? 'Desconocido';
    @endphp

    <li class="list-group-item border-start border-4" style="border-left-color: {{ $statusColor }}">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <a href="/customers/history/{{$history->id}}/show" class="fw-bold text-decoration-none">
            {{ \Carbon\Carbon::parse($history->updated_at)->format('d M Y H:i') }}
          </a><br>
          <span class="text-muted small">
            Asignado a <strong>{{ $assignedTo }}</strong><br>
            Modificado por <strong>{{ $editor }}</strong><br>
            Estado: {{ $status }}
          </span>
        </div>
        <span class="badge" style="background-color: {{ $statusColor }}">
          {{ \Carbon\Carbon::parse($history->updated_at)->diffForHumans() }}
        </span>
      </div>
    </li>
  @endforeach

  {{-- Estado actual --}}
  @php
    $currentUser = $customer->user->name ?? 'Sin asignar';
    $currentStatus = $customer->status->name ?? '';
    $statusColor = $customer->status->color ?? 'gray';
    $editor = $customer->updated_user->name ?? 'Desconocido';
  @endphp

  <li class="list-group-item list-group-item-light border-start border-4" style="border-left-color: {{ $statusColor }}">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <strong>Ahora</strong><br>
        <span class="text-muted small">
          Asignado a <strong>{{ $currentUser }}</strong><br>
          Modificado por <strong>{{ $editor }}</strong><br>
          Estado: {{ $currentStatus }}
        </span>
      </div>
      <span class="badge" style="background-color: {{ $statusColor }}">
        {{ \Carbon\Carbon::parse($customer->updated_at)->diffForHumans() }}
      </span>
    </div>
  </li>
</ul>
