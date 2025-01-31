@extends('layout')

@section('content')

<h1>Acciones por usuarios</h1>

@include('reports.user_actions.graph')	
@include('reports.user_actions.filter')	
	

<?php 
	$array_total = array();
	$actions_total = 0;

	for ($j=0; $j<count($users); $j++) { 
		for ($i=0; $i<count($action_types); $i++) { 
			$actions_total += $data[$j][$i];
		}
		$array_total[] = $actions_total;
		$actions_total = 0;
	}

	$array_efecty = array();
	$actions_efecty = 0;

	for ($j=0; $j<count($users); $j++) { 
		for ($i=0; $i<count($action_types); $i++) { 
			//if($action_types[$i]->name == "Ganado" || $action_types[$i]->name == "Ganado otros"){
				$actions_efecty += $data[$j][$i];
			//}
		}
		$array_efecty[] = $actions_efecty;
		$actions_efecty = 0;
	}
?>

<table class="table table-striped table-hover table-responsive">
	<tr>
		<th>Estados</th>
		@for($i=0; $i<count($users); $i++)
				
		<th>
			{{$users[$i]->name}}
		</th>
		@endfor
	</tr>
	@for($j=0; $j<count($action_types); $j++)
		
		<tr>
		<td>{{$action_types[$j]->name}}</td>
		@for($i=0; $i<count($users); $i++)
	
		<td>{{$data[$i][$j]}}</td>
		@endfor
	</tr>	
	@endfor
	<tr>
		<th>TOTAL</th>
			@foreach ($array_total as $value)
			<th>{{$value}}</th>
			@endforeach
	</tr>
</table>


@endsection