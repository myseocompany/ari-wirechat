@extends('layout')

@section('content')

<h1>Points by Week</h1>

@include('reports.weeks_by_team.graph') 
@include('reports.weeks_by_team.filter')  
  <!--  
  *
  *    Tabla reportes
  *
  -->

<table class="table table-striped table-hover table-responsive">
  <tr>
    <th>Week</th>
    <th>Points</th>
  </tr> 
  @for($i=0; $i<$weeks; $i++)
  <tr>
    <td>
      {{$weeks_array[$i][0]}} - {{$weeks_array[$i][1]}}
    </td>
    <td>{{$data[$i]}}</td>
    
  </tr> 
  @endfor
  
</table>


@endsection