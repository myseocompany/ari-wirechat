@extends('layout')

@section('content')
<h1>Encuesta</h1>

<div class="table table-striped">
	<table>
		<thead>
			<tr>
				<th>Nombre</th>
				<th>correo</th>
				<th>nombre de la empresa</th>
				<th>cargo en la empresa</th>
				<th>número de empanadas</th>
				<th>ir a encuesta</th>

			</tr>
		</thead>
		<tbody>
			@foreach($customers as $c)
			<tr>
				<td>{{$c->name}}</td>
				<td>{{$c->email}}</td>
				<td>{{$c->business}}</td>
				<td>{{$c->position}}</td>
				<td>{{$c->count_empanadas}}</td> 
				<td><a class="btn btn-primary" href="/metadata/1/pollAll/{{$c->id}}">Ir	</a></td> 
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
@endsection



