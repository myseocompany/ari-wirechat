@extends('layout')

@section('content')
<h1>Crear Rol</h1>
{{-- Formulario Rol --}}
<form action="/roles" method="POST">
	{{csrf_field() }}
	<div class="form-group">
		<label for="role">Nombre Rol:</label>
		<input type="text" name="name" id="name" class="form-control" placeholder="Nombre del Rol*" value="">
	</div>
	<button type="submit" class="btn btn-primary">Crear</button>
</form>


@endsection