@extends('layout')

@section('content')
<h1>Points by Week</h1>
<table class="table table-striped table-hover table-responsive" id="taskTable">
<thead class="thead-default">
	<tr>
		<th style="text-align: right;">Year</th>
		<th style="text-align: right;">Week</th>
		<th style="text-align: right;">Points</th>
	</tr>
</thead>
<tbody>
	@foreach ($model as $item)

	<tr>
	
		<td style="text-align: right;">
				
				{{$item->year}}
			
		</td>
		<td style="text-align: right;"><a href="/tasks?week={{$item->week}}&year={{$item->year}}">{{$item->week}}</a></td>
		<td style="text-align: right;">{{$item->sum_points}}</td>

	</tr>	
@endforeach
</tbody>
</table>


<h1>Points by Users Week</h1>
<table class="table table-striped table-hover table-responsive" id="taskTable">
<thead class="thead-default">
	<tr>
		<th>User</th>
		<th>Year</th>
		<th>Week</th>
		<th>Points</th>
	</tr>
</thead>
<tbody>
	@foreach ($users as $item)

	<tr>
		<td style="text-align: right;">{{$item->name}}</td>
		
		<td style="text-align: right;">{{$item->year}}</td>
		<td style="text-align: right;">{{$item->week}}</td>
		<td style="text-align: right;">{{$item->sum_points}}</td>

	</tr>	
@endforeach
</tbody>
</table>

@endsection