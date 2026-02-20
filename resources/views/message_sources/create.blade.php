@extends('layout')

@section('content')
<h1 class="mb-3">Nueva línea de WhatsApp (WAToolBox)</h1>

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@include('message_sources._form', [
  'action' => route('message-sources.store'),
  'submitLabel' => 'Guardar línea',
])
@endsection
