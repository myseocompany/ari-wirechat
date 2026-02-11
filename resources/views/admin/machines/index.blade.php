@extends('layout')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 mb-1">Administración de máquinas</h1>
      <p class="text-muted mb-0">Gestiona seriales, customer actual, tokens y trazabilidad operativa.</p>
    </div>
    <div class="d-flex align-items-center">
      <a href="{{ route('admin.leads-distribution.index') }}" class="btn btn-link">Leads distribution</a>
      <a href="{{ route('admin.machines.create') }}" class="btn btn-primary">Nueva máquina</a>
    </div>
  </div>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Serial</th>
          <th>Customer actual</th>
          <th>Tokens</th>
          <th>Minutos</th>
          <th>Fallas</th>
          <th>Última conexión</th>
          <th style="width: 150px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($machines as $machine)
          <tr>
            <td>{{ $machine->id }}</td>
            <td><strong>{{ $machine->serial }}</strong></td>
            <td>
              @if ($machine->currentCustomer)
                {{ $machine->currentCustomer->name }} (#{{ $machine->currentCustomer->id }})
              @else
                <span class="text-muted">Sin asignar</span>
              @endif
            </td>
            <td>{{ $machine->tokens_count }}</td>
            <td>{{ $machine->production_minutes_count }}</td>
            <td>{{ $machine->fault_events_count }}</td>
            <td>
              @if ($machine->last_seen_at)
                {{ $machine->last_seen_at }}
              @else
                <span class="text-muted">Nunca</span>
              @endif
            </td>
            <td>
              <a href="{{ route('admin.machines.show', $machine) }}" class="btn btn-sm btn-outline-primary">Ver</a>
              <a href="{{ route('admin.machines.edit', $machine) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted">No hay máquinas registradas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $machines->links() }}
  </div>
</div>
@endsection
