@extends('layout')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Editar menú</h1>
            <small class="text-muted">Actualiza la información del ítem seleccionado.</small>
        </div>
        <a href="{{ route('menus.index') }}" class="btn btn-link">Volver al listado</a>
    </div>

    <form method="POST" action="{{ route('menus.update', $menu) }}">
        @csrf
        @method('PUT')
        @include('menus.partials.form', ['submitLabel' => 'Actualizar menú'])
    </form>
</div>
@endsection
