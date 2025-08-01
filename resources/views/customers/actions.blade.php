@if($customer->actions->count() > 0)
  

  @foreach($customer->actions as $action)
    <div class="card mb-3 shadow-sm rounded">
      <div class="card-body">
        {{-- Título de la acción --}}
        <h5 class="card-title mb-2">
          {{ $action->note }}
          @if($action->type_id == 29)
            <span class="badge bg-danger">Venta perdida</span>
          @endif
        </h5>

        {{-- Tipo de acción e icono --}}
        <p class="mb-1 text-muted">
          @if($action->type)
            @if($action->type->id == 27)
              <i class="fa fa-money"></i>
            @elseif($action->type->id == 28)
              <i class="fa fa-bell"></i>
            @elseif($action->type->id == 29)
              <i class="fa fa-exclamation-triangle"></i>
            @endif
            {{ $action->type->name }}
          @endif
          • Creado por {{ $action->creator->name ?? 'Automático' }}
        </p>

        {{-- Datos del cliente --}}
        <p class="mb-1">
          <a href="#" class="fw-bold text-primary">
            {{ $customer->name }}
          </a>
          @if($customer->phone)
            <span class="badge bg-success">
              <i class="fa fa-phone"></i> {{ $customer->phone }}
            </span>
          @endif
          @if($customer->email)
            <span class="ms-2">
              <i class="fa fa-envelope"></i> {{ $customer->email }}
            </span>
          @endif
        </p>

        {{-- Fecha con enlace a detalle --}}
        <p class="mb-0">
          <a href="/actions/{{ $action->id }}/show" class="text-decoration-none text-muted">
            @if($action->created_at)
              {{ $action->created_at->format('d M Y H:i') }}
            @else
              <em class="text-muted">Fecha no disponible</em>
            @endif

          </a>
        </p>

        {{-- Admin: opción de eliminar --}}
        @if(Auth::check() && Auth::user()->role_id == 1)
          <div class="mt-2">
            <a href="/actions/{{ $action->id }}/destroy" class="btn btn-sm btn-outline-danger">
              <i class="fa fa-trash-o"></i> Eliminar
            </a>
          </div>
        @endif
      </div>
    </div>
  @endforeach
@endif
