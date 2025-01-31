
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
   