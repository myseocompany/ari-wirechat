@extends('layout')

@section('content')
<div class="container mt-4">
  <div class="mb-3">
    <h1 class="h4 mb-1">Nueva máquina</h1>
    <p class="text-muted mb-0">Crea una máquina y define su customer actual inicial.</p>
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
      <form action="{{ route('admin.machines.store') }}" method="POST">
        @include('admin.machines._form', ['submitLabel' => 'Crear máquina'])
      </form>
    </div>
  </div>
</div>
@endsection
