@extends('layout')

@section('content')
<table class="table table-striped table-hover table-responsive" id="taskTable">
<thead class="thead-default">
</thead>
<tbody>
</tbody>
</table>


<h1>Seguimientos</h1>
<table class="table table-striped table-hover table-responsive" id="taskTable">
<thead class="thead-default">
	<tr>
		<th>Año</th>
		
		<th>Semana</th>
		<th>Total</th>
		<th>Atención<br>Semana 1</th>
		<th>Atención<br>Semana 2</th>
		<th>Atención<br>Semana 3</th>
		<th>Atención<br>Semana 4</th>
		<th>Atenciones<br>Totales</th>
		
	</tr>
</thead>
<tbody>
	<?php $cont=1; ?>
	@foreach ($model as $item)
	

	<tr>
		<td>{{$item->year}}</td>
		
		<td>{{$item->week}}</td>
		<td><a href="/customers/?week={{$item->week}}&year={{$item->year}}">{{$item->total}}</a></td>
		<td>
			@if($cont>=1)
				<span style="background-color: lime; display:inline-block; width: {{ceil(100*$item->lead07/$item->total)}}px">
				<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=7&with=1">{{$item->lead07}}</a></span><span style="background-color: pink; display: inline-block; width: {{ceil(100*($item->total-$item->lead07)/$item->total)}}px"> 
				<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=7&with=0">{{$item->total-$item->lead07}}</a></span>
			@endif
		</td>
		<td>
			@if($cont>=2)
			<span style="background-color: lime; display:inline-block; width: {{ceil(100*$item->lead14/$item->total)}}px">
			<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=14&with=1">{{$item->lead14}}</a></span><span style="background-color: pink; display: inline-block; width: {{ceil(100*($item->total-$item->lead14)/$item->total)}}px"> 
			<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=14&with=0">{{$item->total-$item->lead14}}</a></span>
			@endif
		</td>
		<td>
			@if($cont>=3)
			<span style="background-color: lime; display:inline-block; width: {{ceil(100*$item->lead21/$item->total)}}px">
			<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=21&with=1">{{$item->lead21}}</a></span><span style="background-color: pink; display: inline-block; width: {{ceil(100*($item->total-$item->lead21)/$item->total)}}px"> 
			<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=21&with=0">{{$item->total-$item->lead21}}</a></span>
			@endif
		</td>
		<td>
			@if($cont>=4)
			<span style="background-color: lime; display:inline-block; width: {{ceil(100*$item->lead28/$item->total)}}px">
			<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=28&with=1">{{$item->lead28}}</a></span><span style="background-color: pink; display: inline-block; width: {{ceil(100*($item->total-$item->lead28)/$item->total)}}px"> 
			<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=28&with=0">{{$item->total-$item->lead28}}</a></span>
			@endif
			<?php $cont++; ?>
		</td>
		<td>
			
			<span style="background-color: lime; display:inline-block; width: {{ceil(100*$item->has_actions/$item->total)}}px">
			<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=total&with=1">{{$item->has_actions}}</a></span><span style="background-color: pink; display: inline-block; width: {{ceil(100*($item->total-$item->has_actions)/$item->total)}}px"> 
			<a href="/customers/?week={{$item->week}}&year={{$item->year}}&lead=total&with=0">{{$item->total-$item->has_actions}}</a></span>
			
			
		</td>
	</tr>	
@endforeach
</tbody>
</table>

@endsection
