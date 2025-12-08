@extends('layout')

@section('content')
<div class="card mt-3">
  <div class="card-header d-flex align-items-center justify-content-between">
    <div>
      <h5 class="mb-0">{{ $model->name ?? 'Cliente' }}</h5>
      <small class="text-muted">ID: {{ $model->id }}</small>
    </div>
    @if($model->status)
      <span class="badge" style="background-color: {{ $model->status->color ?? '#6c757d' }}">
        {{ $model->status->name }}
      </span>
    @endif
  </div>
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">Nombre</dt>
      <dd class="col-sm-9">{{ $model->name ?? 'Sin nombre' }}</dd>

      <dt class="col-sm-3">Email</dt>
      <dd class="col-sm-9">{{ $model->email ?? '—' }}</dd>

      <dt class="col-sm-3">Estado</dt>
      <dd class="col-sm-9">{{ $model->status->name ?? 'Sin estado' }}</dd>

      <dt class="col-sm-3">Asesor</dt>
      <dd class="col-sm-9">{{ $model->user->name ?? 'Sin asignar' }}</dd>
    </dl>
  </div>
  <div class="card-footer text-muted">
    Solo lectura: no puedes editar clientes que no están asignados a ti.
  </div>
</div>
@endsection
