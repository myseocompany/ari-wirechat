@extends('layout')
@section('content')

<h2>Campañas</h2>
<div><a href="/campaigns/create">Crear <i class="fa fa-plus-square"></i></a></div>
<div class="table-responsive-sm">
	<table class="table">
		<tr>
			<th>Id</th>
			<th>Nombre</th>
			<th>Editar</th>
		</tr>
		@foreach($model as $item)
		<tr>
			<td>{{$item->id}}</td>
			<td>{{$item->name}}</td>
			<td><a href="/campaigns/{{$item->id}}/edit" class="btn btn-success">Editar</a></td>
		</tr>
		@endforeach

	</table>
</div>
@endsection
