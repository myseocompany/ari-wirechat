@extends('layout')
@section('content')

<h2>Audiencias</h2>
<div><a href="/audiences/create">Crear <i class="fa fa-plus-square"></i></a></div>
<div class="table-responsive-sm">
	<table class="table">
		<tr>
			<th>Id</th>
			<th>Nombre</th>
		</tr>
		@foreach($model as $item)
		<tr>
			<td>{{$item->id}}</td>
			<td><a href="/audiences/{{$item->id}}/customers">{{$item->name}}</a></td>
			<td><a href="/audiences/{{$item->id}}/survey">Satisfacción</a></td>
			<td><a href="/audiences/{{$item->id}}/show">Ver</a></td>

			<td>
				<div class="form-group">
				    <select class="form-control" onChange=nav(this.value)>
				    	<option value="">Seleccione una opción</option>
			    	@foreach($campaigns as $c)
			      		<option value="https://mqe.quirky.com.co/audiences/{{$item->id}}/campaign/{{$c->id}}/show">{{$c->name}}</option>
			      	@endforeach
				    </select>
				</div>
			</td>

		</tr>
		@endforeach

	</table>
</div>
<script>
	function nav(value) {
		if (value != "") { location.href = value; }
	}
</script>
@endsection
