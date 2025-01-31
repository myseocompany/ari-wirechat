<h3>90 dias</h3>
@php $count = 0;
@endphp
@foreach($model7 as $item)
      @php
        $count += $item->count;
      @endphp
@endforeach
<h5>Estados: {{$count}}</h5>
<div>
    @if($model7->count()!=0)
    <ul class="groupbar bb_hbox">
      @foreach($model7 as $item)
      <li class="groupBarGroup" style="background-color: {{$item->color}}; width: <?php 
          if($model7->count()!=0){
            echo 100/$model7->count();
          }
       ?>%">
        <h3>{{$item->count}}</h3>
        <div><h4><a href="#" onclick="changeStatus('{{ $item->id }}')">{{$item->name}}</a></h4></div>
      </li>          
      @endforeach
    </ul>
    @else
      Sin Estados
    @endif


          @php $count = 0;
@endphp
@foreach($customersByProject7 as $item)
      @php
        $count += $item->count;
      @endphp
@endforeach

<h5>Lineas: {{$count}}</h5>
  @if($customersByProject7->count()!=0)
    <ul class="groupbar bb_hbox">
      @foreach($customersByProject7 as $item)
      <li class="groupBarGroup" style="background-color: @if(is_null($item->project_color)) #cccc @else {{$item->project_color}} @endif; width: <?php 
          if($customersByProject7->count()!=0){
            echo 100/$customersByProject7->count();
          }
       ?>%">
        <h3>{{$item->count}}</h3>
        <div><h4><a href="#" onclick="changeStatus('{{ $item->id }}')">@if(is_null($item->project_name)) Sin tipificar @else {{$item->project_name}} @endif</a></h4></div>
      </li>          
      @endforeach
    </ul>
    @else
      Sin Proyectos
    @endif


    @php $count = 0;
@endphp
@foreach($model30 as $item)
      @php
        $count += $item->count;
      @endphp
@endforeach


  <h3>Más de 90 dias</h3>
  <h5>Estados: {{$count}}</h5>
  <div>
    @if($model30->count()!=0)
    <ul class="groupbar bb_hbox">
    
      @foreach($model30 as $item)
      <li class="groupBarGroup" style="background-color: {{$item->color}}; width: <?php 
          if($model30->count()!=0){
            echo 100/$model30->count();
          }
       ?>%">
        <h3>{{$item->count}}</h3>
        <div><h4><a href="#" onclick="changeStatus('{{$item->id}}')">{{$item->name}}</a></h4></div>
      </li>          
      @endforeach
    </ul>
    @else
      Sin Estados
    @endif


    @php $count = 0;
@endphp
@foreach($customersByProject30 as $item)
      @php
        $count += $item->count;
      @endphp
@endforeach

    <h5>Linea: {{$count}}</h5>
  @if($customersByProject30->count()!=0)
    <ul class="groupbar bb_hbox">
      @foreach($customersByProject30 as $item)
      <li class="groupBarGroup" style="background-color: @if(is_null($item->project_color)) #cccc @else {{$item->project_color}} @endif; width: <?php 
          if($customersByProject30->count()!=0){
            echo 100/$customersByProject30->count();
          }
       ?>%">
        <h3>{{$item->count}}</h3>
        <div><h4><a href="#" onclick="changeStatus('{{ $item->id }}')">@if(is_null($item->project_name)) Sin proyecto @else {{$item->project_name}} @endif</a></h4></div>
      </li>          
      @endforeach
    </ul>
    @else
      Sin Proyectos
    @endif

    </div>