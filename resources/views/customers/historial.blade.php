<h2 class="mt-4">Historial de asignación</h2>
<div class="table-responsive">
  <ul class="list-group">
    @php $histories = $customer->histories->sortByDesc('updated_at'); @endphp

    @foreach($histories as $history)
      @php
        $status = $history->status->name ?? $history->status_id;
        $statusColor = $history->status->color ?? 'gray';
        $userName = $history->user->name ?? 'Sin asignar';
        $editor = $history->updated_user->name ?? 'Desconocido';
      @endphp
      <li class="list-group-item">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <a href="/customers/history/{{$history->id}}/show" class="fw-bold text-decoration-none">
              {{ \Carbon\Carbon::parse($history->updated_at)->format('d M Y H:i') }}
            </a>
            <br>
            <small class="text-muted">
              Modificado por <strong>{{ $editor }}</strong><br>
              Estado <strong>{{ $status }}</strong>, asignado a <strong>{{ $userName }}</strong>
            </small>
          </div>
          <span class="badge" style="background-color: {{ $statusColor }}">
            {{ \Carbon\Carbon::parse($history->updated_at)->diffForHumans() }}
          </span>
        </div>
      </li>
    @endforeach

    {{-- Estado actual (ahora) --}}
    @php
      $status = $customer->status->name ?? '';
      $statusColor = $customer->status->color ?? 'gray';
      $userName = $customer->user->name ?? 'Sin asignar';
      $editor = $customer->updated_user->name ?? 'Desconocido';
    @endphp
    <li class="list-group-item list-group-item-light">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="fw-bold">Ahora</span><br>
          <small class="text-muted">
            Última modificación el {{ \Carbon\Carbon::parse($customer->updated_at)->format('d M Y H:i') }}<br>
            Modificado por <strong>{{ $editor }}</strong><br>
            Estado <strong>{{ $status }}</strong>, asignado a <strong>{{ $userName }}</strong>
          </small>
        </div>
        <span class="badge" style="background-color: {{ $statusColor }}">
          {{ \Carbon\Carbon::parse($customer->updated_at)->diffForHumans() }}
        </span>
      </div>
    </li>
  </ul>
</div>
