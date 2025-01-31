@extends('layout')

@section('content')

<h1>Estados por Usuarios</h1>

@include('reports.user_status.graph')	
@include('reports.user_status.filter')	
	<!--  
	*
	*    Tabla reportes
	*
	-->
<?php 
	$array_total = array();
	$actions_total = 0;
	

	for ($j=0; $j<count($users); $j++) { 
		for ($i=0; $i<count($customer_statuses); $i++) { 
			$actions_total += $data[$j][$i];
		}
		$array_total[] = $actions_total;
		$actions_total = 0;
	}

	$array_efecty = array();
	$actions_efecty = 0;

	for ($j=0; $j<count($users); $j++) { 
		for ($i=0; $i<count($customer_statuses); $i++) { 
			if($customer_statuses[$i]->name == "Ganado" || $customer_statuses[$i]->name == "Ganado otros"){
				$actions_efecty += $data[$j][$i];
			}
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
	<!-- 14 Estados-->
	@for($j=0; $j<count($customer_statuses); $j++)
		<tr>
			<!-- 11 Usuarios-->
			<td>{{$customer_statuses[$j]->name}}</td>
				@for($i=0; $i<count($users); $i++)
				<td>{{$data[$i][$j]}}</td>
				@endfor
		</tr>	
		
	@endfor
	<tr>
		<th>TOTAL</th>
			@foreach ($array_total as $value)
			<td>{{$value}}</td>
			@endforeach
	</tr>
	<tr>
		<th>EFECTIVIDAD</th>

			@foreach ($array_efecty as $key => $value)
				@if($value > 0)
					<td>{{ round($value / $array_total[$key],2)}}</td>
				@else
					<td>0</td>
				@endif
			
			@endforeach
	</tr>
	<tr>
		<th>OPORTUNIDADES [ACCIONES]</th>
		
		@foreach ($users as $item)
			<td>{{$item->getActions($request,	28)}}</td>
		@endforeach
			
	</tr>
	<tr>
		<th>VENTAS [ACCIONES]</th>
		
		@foreach ($users as $item)
			<td>{{$item->getActions($request,	27)}}</td>
		@endforeach
			
	</tr>

</table>


@endsection