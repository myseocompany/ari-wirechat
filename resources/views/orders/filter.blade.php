<form action="/orders/" method="GET" id="filter_form" class="form-inline">
  <input type="hidden" name="search" id="search" @if(isset($request->search))value="{{$request->search}}"@endif>
       
      <input type="hidden" name="search" id="search" @if(isset($request->search))value="{{$request->search}}"@endif>
      <select name="filter" class="custom-select" id="filter" onchange="update()">
        <option value="">Seleccione tiempo</option>
        <option value="1" @if ($request->filter == "1") selected="selected" @endif>mañana</option>
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


     
  <select name="status_id" class="slectpicker custom-select" id="status_id" onchange="submit();">
      <option value="">Estado...</option>
     @if(isset($statuses)&&($statuses!=""))
      @foreach($statuses as $item)
          <option value="{{$item->id}}" @if ($request->status_id == $item->id) selected="selected" @endif>
              {{ $item->name }}
            
          </option>
        @endforeach
     @endif
  </select>

  <span>
    Fecha de entrega <input type="checkbox" value="updated" name="created_updated" id="created_updated" onchange="submit();" @if ( (isset($request->created_updated) &&  ($request->created_updated=="updated")) ) checked="checked" @endif >
  </span>

    @if(isset($types))
      <select name="type_id" class="custom-select" id="type_id" onchange="submit();">
        <option value="">Tipos...</option>
        @foreach($types as $item)
          <option value="{{$item->id}}" @if ($request->type_id == $item->id) selected="selected" @endif>
            {{$item->name}}
            
          </option>
        @endforeach
      </select>
      @endif      

      <!--
      @if(isset($categories))
      <select name="category_id" class="custom-select" id="category_id" onchange="submit();">
        <option value="">Proyecto...</option>
        @foreach($categories as $item)
          <option value="{{$item->id}}" @if ($request->category_id == $item->id) selected="selected" @endif>
            {{$item->name}}
            
          </option>
        @endforeach
      </select>
      @endif
    -->
      <input type="text" id="query" name="query">
  
      <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
    </form>
  