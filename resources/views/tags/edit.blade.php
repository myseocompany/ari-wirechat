@extends('layout')

@section('content')
<div class="container mt-4">
  <h1 class="h4 mb-3">Editar etiqueta</h1>
  <form action="{{ route('tags.update', $tag) }}" method="POST">
    @method('PUT')
    @include('tags.form')
  </form>
</div>
@endsection
