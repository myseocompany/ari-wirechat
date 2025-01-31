@extends('layout')

@section('content')

<h1>Projects by state</h1>

<div>
    <form action="" method="">

      <div class="row">
        <div class="col">
        	<input class="input-date" type="date" id="from_date" name="from_date" onchange="cleanFilter()" value="{{$request->from_date}}">
        </div>
        <div class="col">
          
          <input class="input-date" type="date" id="to_date" name="to_date" onchange="cleanFilter()" value="{{$request->to_date}}">
       	</div>
       

        <div class="col">
			
			<select  name="type_id" class="slectpicker custom-select" id="status_id" onchange="submit();">
	        <option value="">Select user </option>


	        @foreach($users as $item)
	        <option value="{{$item->id}}"  @if ($item->id == $request->item_id)  selected="selected" @endif >{{$item->name}}</option>
	        @endforeach
	       
	      </select>
      </div>
      <div class="col">
	     <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
       </div>
      </div>




    </form>
  </div>
	
	<!--  
	*
	*    Tabla reportes
	*
	-->
	
<br>
	<div class="report">
		<table  class="table table-bordered table-sm table-hover">
			<tr>
				
				<th class="title">Projects</th>
				
				<th class="input_bg text-center">Task at start</th>
			@foreach($task_statuses as $item)
				<th class="text-center">{{$item->name}}</th>
			@endforeach
				
				<th class="text-center title">Acumulated</th>
			</tr>
			
			@foreach($projects as $project)
			<tr>  
				<td class="subtitle"><a href="/tasks/?from_date={{$request->from_date}}&to_date={{$request->to_date}}&project_id={{$project->id}}">{{ $project->name }}</a></td>
				<td class="text-center"><?php $count = $project->countTaskInventoryBydDate($request->from_date); ?>@if($count>0){{ $count  }} @endif</td>
			@foreach($task_statuses as $status)
				<td class="text-center"><?php $count= $project->countTaskByStatusAndDates($status->id, $request->from_date, $request->to_date); ?>@if($count>0){{$count}}@endif</td>
			@endforeach
				
				<td class="text-center"><?php $count = $project->countTaskInventoryBydDate($request->to_date); ?>@if($count>0){{$count}}@endif</td>
			</tr>
			
			@endforeach()
		</table>	

</div>


@endsection