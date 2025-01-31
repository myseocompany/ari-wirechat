@extends('layout')
@section('content')
<h1>Crear Campaña</h1>

<form action="/campaigns" method="POST">
	{{ csrf_field() }}
	<div class="form-group">
    	<label for="name">Nombre:</label>
    	<input type="text" class="form-control" id="name" name="name" placeholder="Name" required="required">
  	</div>
  	<button type="submit" class="btn btn-primary">Crear</button>
</form>

@endsection