@extends('layout')
<?php  function clearWP($str){
  $str = trim($str);
  $str = str_replace("+", "", $str );
  return $str;
} ?>



@section('content')

<h1>Logística</h1>

<!--Filtro 2-->
@php
$pid=substr($request->path(), -1);

@endphp
<form action="/customers/{{$pid}}" method="GET" id="filter_form">
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

     {{-- Combo de estados --}}
<select name="status_id" class="slectpicker custom-select" id="status_id" onchange="submit();">
        <option value="">Estado...</option>
        @foreach($customer_options as $item)
          <option value="{{$item->id}}" @if ($request->status_id == $item->id) selected="selected" @endif>
             {{ $item->name }}
            
          </option>
        @endforeach
      </select>

      <!--  
*
*    Combo de usuarios
*
-->
      <select name="user_id" class="custom-select" id="user_id" onchange="submit();">
        <option value="">Usuario...</option>
        <option value="null">Sin asignar</option>
        @foreach($users as $user)
          <option value="{{$user->id}}" @if ($request->user_id == $user->id) selected="selected" @endif>
             <?php echo substr($user->name, 0, 10); ?>
            
          </option>
        @endforeach
      </select>

      <!--  
*
*    Combo de fuentes
*
-->

      <select name="scoring" class="custom-select" id="scoring" onchange="submit();">
        <option value="">Calificación...</option>
        @for($i=1; $i<=3; $i++ )
          <option value="{{$i}}" @if (($request->scoring == $i)&&($i!=0)) selected="selected" @endif>
             {{$i}}
            
          </option>
        @endfor
      </select>
      <select name="actions_number" class="custom-select" id="actions_number" onchange="submit();">
        <option value="">Acciones...</option>
        @for($i=1; $i<=3; $i++ )
          <option value="{{$i}}" @if (($request->actions_number == $i)&&($i!=0)) selected="selected" @endif>
             {{$i}}
            
          </option>
        @endfor
        
      </select>

      <select name="source_id" class="custom-select" id="source_id" onchange="submit();">
        <option value="">Fuente...</option>
        @foreach($sources as $item)
          <option value="{{$item->id}}" @if ($request->source_id == $item->id) selected="selected" @endif>
             <?php echo substr($item->name, 0, 15); ?>
            
          </option>
        @endforeach
      </select>

      @if(isset($projects))
      <select name="project_id" class="custom-select" id="project_id" onchange="submit();">
        <option value="">Proyecto...</option>
        @foreach($projects as $item)
          <option value="{{$item->id}}" @if ($request->project_id == $item->id) selected="selected" @endif>
             <?php echo substr($item->name, 0, 15); ?>
            
          </option>
        @endforeach
      </select>
      @endif
  
      
      <input type="text" name="search" id="search" value="{{$request->search}}" placeholder="Busca o escribe">
      <span>
        <?php $cu = $request->created_updated; ?>
        <label class="radio"> Fecha de creación 
          <input type="radio" name="created_updated" value="created" 
          @if((isset($cu)&& ($cu == 'created'))||(!isset($cu)))  checked @endif onchange="submit();"> </label> 
        <label class="radio" > o actualizacion 
          <input type="radio" name="created_updated" value="updated" @if(isset($request->created_updated)&& ($request->created_updated == "updated")) checked @endif onchange="submit();"> </label></span>
      <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
    </form>
   
<!--Fin filtro 2-->
@php 
  $previous_status_id = -100000;
  $cont = 0;
  
 // dd($status_array);
@endphp
  
<br><br><br>
	<div class="columns">

@foreach($model as $item)
  @if($item->status_id != $previous_status_id)
      <!-------------- STATUS ---------------> 
       <!-- TITULO -->
       <div class="status_header" style="background:{{$item->color}} "><h3 class="status_title">{{ str_replace(['PV:', 'L:'], '', $item->status_name) }}</h3></div>       
        <!-- /TITULO -->
      <div class="column" id="{{$item->status_id}}" name="{{$item->status_id}}">
       
        <div class="element lead_element width-10">
          <br>
        </div>
@endif
@if(isset($item->id))
          <!-------------- CUSTOMER {{$cont}} --------------->  
          <div class="element lead_element" draggable="true" id="{{$item->id}}" name="{{$item->id}}">
            <div class="">
              <a href="/customers/{{$item->id}}/show">{{ucwords(strtolower(substr($item->name,0,15)))}}</a>
            </div>
           
  @if (isset($item->product) && ($item->product != null))
            <div class="text_elemen lead_product">{{$item->product->name}}</div>@endif
            <div class="text_elemen lead_product">{{$item->updated_at}}</div>
          </div>
          <!-------------- /CUSTOMER ---------------> 
          
  @endif     
  @if(++$cont < count($model) && ( $model[$cont]->status_id != $item->status_id))
        </div>
        
        <!-------------- /STATUS  {{$cont}} | {{ count($model)}} --------------->
  @else
        @if($cont == count($model) )
      </div>
      <!-------------- //STATUS  {{$cont}} | {{ count($model)}} --------------->
      @endif
      
@endif
      @php $previous_status_id = $item->status_id; @endphp
 
		@endforeach


   </div>


		<script>

const elements = document.querySelectorAll('.element');

cid = 0;

elements.forEach(element => {
  element.addEventListener('dragstart', dragStart);
  element.addEventListener('dragend', dragEnd);
});

function dragStart() {
  this.classList.add('active');
  console.log(this.id);
  cid = this.id;
}

function dragEnd() {
  this.classList.remove('active');
}

const columns = document.querySelectorAll('.column');

columns.forEach(column => {
  column.addEventListener('dragover', dragOver);
  column.addEventListener('dragenter', dragEnter);
  column.addEventListener('dragleave', dragLeave);
  column.addEventListener('drop', drop);
});

function dragOver(e) {
  e.preventDefault();
}

function dragEnter(e) {
  e.preventDefault();
  this.classList.add('over');
}

function dragLeave() {
  this.classList.remove('over');
}

function drop(e) {
  const activeItem = document.querySelector('.active');
  this.appendChild(activeItem);
  this.classList.remove('over');
  console.log(e);
 
  console.log('Element soltado en la column:', this.id);
  console.log('Element id soltado en la column:', cid);

  var status_id = this.id;
  var customer_id = cid;
      $.ajax({


    type: "GET",
    url : '/customers/'+customer_id+'/action/updateAjax',
    data: {
    status_id: status_id,
    customer_id: customer_id
    },
    success : function(data){

    }
    },"html");

}
</script>
<style>

.footer
{
  display:none;
}
</style>

@endsection