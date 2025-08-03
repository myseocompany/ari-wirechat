@if($customer->actions->count() > 0)
  @foreach($customer->actions as $action)
    @php
      $color = $action->type->color ?? '#0d6efd';
      $icon = $action->type->icon ?? null;
    @endphp

    <div class="mb-2 p-3 rounded shadow-sm" style="border-left: 5px solid {{ $color }}; background-color: #f8f9fa;">
      <div class="d-flex justify-content-between">
        <div>
          <div class="fw-bold mb-1">
            @if($icon)
              <i class="fa {{ $icon }}"></i>
            @endif
            {{ $action->note }}
          </div>
        </div>
        <div class="text-end small text-muted">
          {{ $action->created_at ? $action->created_at->format('d M Y H:i') : 'Fecha no disponible' }}<br>
          {{ $action->creator->name ?? 'Automático' }}

          @if(Auth::check() && (Auth::user()->role_id == 1 || Auth::user()->role_id == 14))
            <br>
            <a href="/actions/{{ $action->id }}/destroy" class="text-danger" style="font-size: 0.9rem;" title="Eliminar">
              <i class="fa fa-trash-o"></i>
            </a>
          @endif
        </div>
      </div>

      @if(in_array($action->type_id, [2, 4]) && method_exists($action, 'getEmailSubject'))
        <div class="small text-muted">
          <strong>{{ $action->getEmailSubject() }}</strong>
        </div>
      @endif
    </div>
  @endforeach
@endif
