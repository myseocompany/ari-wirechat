@if($customer->actions->count() > 0)
  

  @foreach($customer->actions as $action)
<div class="card mb-2 border-start border-{{ $action->type_id == 29 ? 'danger' : 'primary' }} border-3 shadow-sm">
  <div class="card-body">
    <div class="d-flex justify-content-between">
      <h6 class="mb-1 fw-bold">{{ $action->note }}</h6>
      <small class="text-muted">
        {{ $action->created_at ? $action->created_at->format('d M Y H:i') : 'Fecha no disponible' }}
      </small>
    </div>

    <p class="mb-0 text-muted">
      @if($action->type)
        <i class="fa {{ $action->type->id == 27 ? 'fa-money' : ($action->type->id == 28 ? 'fa-bell' : 'fa-exclamation-triangle') }}"></i>
        {{ $action->type->name }}
      @endif
      • <span class="fst-italic">por {{ $action->creator->name ?? 'Automático' }}</span>
    </p>

    @if(Auth::check() && Auth::user()->role_id == 1)
      <div class="text-end mt-2">
        <a href="/actions/{{ $action->id }}/destroy" class="btn btn-sm btn-outline-danger">
          <i class="fa fa-trash-o"></i> Eliminar
        </a>
      </div>
    @endif
  </div>
</div>
  @endforeach
@endif
