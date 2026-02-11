@extends('layout')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h1 class="h4 mb-1">Máquina {{ $machine->serial }}</h1>
      <p class="text-muted mb-0">Detalle operativo y trazabilidad del módulo IoT.</p>
    </div>
    <div>
      <a href="{{ route('admin.machines.edit', $machine) }}" class="btn btn-outline-secondary">Editar</a>
      <a href="{{ route('admin.machines.index') }}" class="btn btn-link">Volver al listado</a>
    </div>
  </div>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  @if (session('issued_machine_token'))
    <div class="alert alert-warning">
      <strong>Token emitido (mostrar una sola vez):</strong>
      <code>{{ session('issued_machine_token') }}</code>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row">
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header">Datos base</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-5">ID</dt>
            <dd class="col-sm-7">{{ $machine->id }}</dd>
            <dt class="col-sm-5">Serial</dt>
            <dd class="col-sm-7">{{ $machine->serial }}</dd>
            <dt class="col-sm-5">Customer actual</dt>
            <dd class="col-sm-7">
              @if ($machine->currentCustomer)
                {{ $machine->currentCustomer->name }} (#{{ $machine->currentCustomer->id }})
              @else
                <span class="text-muted">Sin asignar</span>
              @endif
            </dd>
            <dt class="col-sm-5">Última conexión</dt>
            <dd class="col-sm-7">
              @if ($machine->last_seen_at)
                {{ $machine->last_seen_at }}
              @else
                <span class="text-muted">Nunca</span>
              @endif
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header">Emisión de token</div>
        <div class="card-body">
          <p class="text-muted">
            La clave se almacena hasheada y solo se muestra una vez al emitirla.
          </p>
          <form action="{{ route('admin.machines.tokens.issue', $machine) }}" method="POST">
            @csrf
            <div class="form-group form-check">
              <input type="checkbox" class="form-check-input" id="confirm_issue" name="confirm_issue" value="1">
              <label class="form-check-label" for="confirm_issue">Confirmo emisión de un nuevo token</label>
            </div>
            <button type="submit" class="btn btn-primary">Emitir token</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Historial de customer</div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Inicio</th>
            <th>Fin</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($history as $item)
            <tr>
              <td>{{ optional($item->customer)->name }} (#{{ $item->customer_id }})</td>
              <td>{{ $item->start_at }}</td>
              <td>{{ $item->end_at ?? 'Activo' }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-muted">Sin historial</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Tokens</div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Creado</th>
            <th>Último uso</th>
            <th>Estado</th>
            <th style="width: 120px;">Acción</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($tokens as $token)
            <tr>
              <td>{{ $token->id }}</td>
              <td>{{ $token->created_at }}</td>
              <td>{{ $token->last_used_at ?? 'Nunca' }}</td>
              <td>
                @if ($token->revoked_at)
                  <span class="badge badge-danger">Revocado</span>
                @else
                  <span class="badge badge-success">Activo</span>
                @endif
              </td>
              <td>
                @if (! $token->revoked_at)
                  <form action="{{ route('admin.machines.tokens.revoke', [$machine, $token]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">Revocar</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">Sin tokens registrados</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="row">
    <div class="col-md-4 mb-3">
      <div class="card h-100">
        <div class="card-header">Últimos reportes raw</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Batch</th>
                  <th>Recibido</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($reports as $report)
                  <tr>
                    <td>{{ $report->id }}</td>
                    <td>{{ $report->batch_id ?? '—' }}</td>
                    <td>{{ $report->received_at }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-center text-muted">Sin reportes</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="card h-100">
        <div class="card-header">Últimos minutos</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
              <thead>
                <tr>
                  <th>Minuto</th>
                  <th>Tacómetro</th>
                  <th>Unidades</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($minutes as $minute)
                  <tr>
                    <td>{{ $minute->minute_at }}</td>
                    <td>{{ $minute->tacometer_total }}</td>
                    <td>{{ $minute->units_in_minute }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-center text-muted">Sin minutos</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-3">
      <div class="card h-100">
        <div class="card-header">Últimas fallas</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Código</th>
                  <th>Severidad</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($faults as $fault)
                  <tr>
                    <td>{{ $fault->reported_at }}</td>
                    <td>{{ $fault->fault_code }}</td>
                    <td>{{ $fault->severity }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-center text-muted">Sin fallas</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
