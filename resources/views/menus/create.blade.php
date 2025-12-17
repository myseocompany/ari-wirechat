@extends('layout')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Nuevo menú</h1>
        <a href="{{ route('menus.index') }}" class="btn btn-link">Volver al listado</a>
    </div>

    <form method="POST" action="{{ route('menus.store') }}">
        @csrf
        @include('menus.partials.form', ['submitLabel' => 'Crear menú'])
    </form>
</div>
@endsection
