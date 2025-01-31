@extends('layout')

@section('content')

<h1>Clientes en el tiempo</h1>

@include('reports.customers_time.graph')	
@include('reports.customers_time.filter')	
	<!--  
	*
	*    Tabla reportes
	*
	-->

<table class="table table-striped table-hover table-responsive">
	<tr>
		<th>Estados</th>
		@foreach ($time_array as $time)
				
		<th>
			<div>{{$time[0]}}</div><div>{{$time[1]}}</div>
		</th>
		@endforeach
	</tr>
	@for($j=0; $j<count($customer_statuses); $j++)
		
		<tr>
		<td>{{$customer_statuses[$j]->name}}</td>
		@for($i=0; $i<count($data); $i++)
	
		<td>{{$data[$i][$j]}}</td>
		@endfor
	</tr>	
	@endfor
	
</table>


@endsection