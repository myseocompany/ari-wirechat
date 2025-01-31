<h3>Miami 2024</h3>
@php $count = 0; @endphp
@foreach($model7 as $item)
      @php
        $count += $item->count;
      @endphp
@endforeach




<h5>Agendados: {{$countShowUp1}}  Total: {{$count}} </h5>
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





  <h3>TipoA US</h3>
@php $count = 0;
@endphp
@foreach($model30 as $item)
      @php
        $count += $item->count;
      @endphp
@endforeach
  <h5>Agendados: {{$countShowUp2}}  Total: {{$count}}</h5>
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


    <!-- EMAIL  -->
    <h3>Abrieron Correos</h3>
    @php $count = 0;
@endphp
@foreach($model3 as $item)
      @php
        $count += $item->count;
      @endphp
@endforeach

  <h5>Agendados: {{$countShowUp3}}  Total: {{$count}}</h5>
  <div>
    @if($model3->count()!=0)
    <ul class="groupbar bb_hbox">
    
      @foreach($model3 as $item)
      <li class="groupBarGroup" style="background-color: {{$item->color}}; width: <?php 
          if($model3->count()!=0){
            echo 100/$model3->count();
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
@foreach($customersByProject3 as $item)
      @php
        $count += $item->count;
      @endphp
@endforeach

    <h5>Linea: {{$count}}</h5>
  @if($customersByProject3->count()!=0)
    <ul class="groupbar bb_hbox">
      @foreach($customersByProject3 as $item)
      <li class="groupBarGroup" style="background-color: @if(is_null($item->project_color)) #cccc @else {{$item->project_color}} @endif; width: <?php 
          if($customersByProject3->count()!=0){
            echo 100/$customersByProject3->count();
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