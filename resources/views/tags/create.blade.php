@extends('layout')

@section('content')
<div class="container mt-4">
  <h1 class="h4 mb-3">Crear etiqueta</h1>
  <form action="{{ route('tags.store') }}" method="POST">
    @include('tags.form', ['tag' => $tag ?? null])
  </form>
</div>
@endsection
