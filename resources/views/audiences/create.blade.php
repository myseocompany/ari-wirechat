@extends('layout')
@section('content')
<h1>Crear Audiencia</h1>

<form action="/audiences" method="POST">
	{{ csrf_field() }}
	<div class="row">
		<div class="col-auto">
			<input class="form-control" type="text" name="name">	
		</div>
		<div class="col-auto">
			<input class="btn btn-primary" type="submit" name="" value="Guardar">	
		</div>		
	</div>	
</form>

@endsection