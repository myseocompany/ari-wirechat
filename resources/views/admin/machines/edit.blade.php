@extends('layout')

@section('content')
<div class="container mt-4">
  <div class="mb-3">
    <h1 class="h4 mb-1">Editar máquina</h1>
    <p class="text-muted mb-0">Actualiza serial y asignación actual de customer.</p>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.machines.update', $machine) }}" method="POST">
        @include('admin.machines._form', ['method' => 'PUT', 'submitLabel' => 'Guardar cambios'])
      </form>
    </div>
  </div>
</div>
@endsection
