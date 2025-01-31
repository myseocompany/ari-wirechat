@extends('layout')

@section('content')
<h1>Reporte de usuarios</h1>

<!--Inicio del formulario-->
<div>
  	<form action="/reports/users/" method="GET" id="filter_form">
  	<input type="hidden" name="search" id="search" @if(isset($request->search))value="{{$request->search}}"@endif>
  		 <select name="filter" class="custom-select" id="filter" onchange="update()">
        <option value="">Seleccione tiempo</option>
        <option value="0" @if ($request->filter == "0") selected="selected" @endif>hoy</option>
        <option value="-1" @if ($request->filter == "-1") selected="selected" @endif>ayer</option>
        <option value="thisweek" @if ($request->filter == "thisweek") selected="selected" @endif>esta semana</option>
        
        <option value="lastweek" @if ($request->filter == "lastweek") selected="selected" @endif>semana pasada</option>
        <option value="lastmonth" @if ($request->filter == "lastmonth") selected="selected" @endif>mes pasado</option>
      	<option value="currentmonth" @if ($request->filter == "currentmonth") selected="selected" @endif>este mes</option>
      	<option value="-7" @if ($request->filter == "-7") selected="selected" @endif>ultimos 7 dias</option>
        <option value="-30" @if ($request->filter == "-30") selected="selected" @endif>ultimos 30 dias</option>
        
      </select>
      <input class="input-date" type="date" id="from_date" name="from_date" onchange="cleanFilter()" value="{{$request->from_date}}">
      <input class="input-date" type="date" id="to_date" name="to_date" onchange="cleanFilter()" value="{{$request->to_date}}">

     
  
      <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
  	</form>
  </div>

<!-- Fin del formulario -->




<table class="table table-striped table-hover table-responsive" id="taskTable">
<thead class="thead-default">
	<tr>
		<th></th>
		@foreach($users as $item)
		<th>{{$item->name}}</th>
		@endforeach
	</tr>
</thead>
<tbody>
	@foreach ($statuses as $status)
	<tr>
		<td>
			{{$status->name}}
		</td>
		@foreach($users as $user)
		<td>
			{{$user->getTotalStatus($status->id, $request)}}
		</td>
		@endforeach
		
		
	</tr>	
	@endforeach

	
	<tr>
		<td>
			Total
		</td>
		@foreach($users as $user)
		<td>
			<strong>{{$user->getTotalStatus(null, $request)}}</strong>
		</td>
		@endforeach
		
		
	</tr>	


	<tr>
		<td>
			--
		</td>
		@foreach($users as $user)
		<td>
			--
		</td>
		@endforeach
		
		
	</tr>	

	@foreach ($actions as $action)
	<tr>
		<td>
			{{$action->name}}
		</td>
		@foreach($users as $user)
		<td>
			{{$user->getTotalActions($action->id, $request)}}
		</td>
		@endforeach
		
		
	</tr>	
	@endforeach
	<tr>
		<td>
			Total
		</td>
		@foreach($users as $user)
		<td>
			<strong>{{$user->getTotalActions(null, $request)}}</strong>
		</td>
		@endforeach
		
		
	</tr>


</tbody>
</table>



@endsection