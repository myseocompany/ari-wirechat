@extends('layout')

@section('content')

<h1>User Points by Days</h1>

@include('reports.days_by_user.graph')	
@include('reports.days_by_user.filter')	
	<!--  
	*
	*    Tabla reportes
	*
	-->

<table class="table table-striped table-hover table-responsive">
	<tr>
		<th>Team </th>
		@for($i=0; $i<$days; $i++)
		<th>
			<div>{{$days_array[$i][0]}}</div> 
			<div>{{$days_array[$i][1]}}</div>
		</th>
		@endfor
	</tr><?php $j = 0; ?>
	@foreach($users as $item)
	<tr>
		<td>{{$item->name}}</td>
		@for($i=0; $i<$days; $i++)
		<td>{{$data[$j][$i]}}</td>
		@endfor
		<?php $j++; ?>
	</tr>	
	@endforeach
	
</table>


@endsection