@extends('layout')

@section('content')
<h1>{{$model->name}}</h1>
{{-- Formulario de edicion --}}
<form method="POST" action="/roles/{{$model->id}}/update">
	{{csrf_field() }}
	<div class="form-group">
	    <label for="name">Nombre de Rol:</label>
	    <input type="text" class="form-control" id="name" name="name" placeholder="nombre del Rol" value="{{$model->name}}" required="">
  	</div>
  <button type="submit" class="btn btn-primary">Modificar</button>
</form>
@endsection