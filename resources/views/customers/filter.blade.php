<div>
    <form action="/{{$model->action}}/" method="GET" id="filter_form">
      
      <div class="row">
        <div class="col-md-2">
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
        </div>
        <div class="col-md-2">
          <input class="input-date" type="date" id="from_date" name="from_date" onchange="cleanFilter()" value="{{$request->from_date}}">
          <input class="input-date" type="date" id="to_date" name="to_date" onchange="cleanFilter()" value="{{$request->to_date}}">
        </div>
        <div class="col-md-2">
          

   {{-- Combo de estados --}}
<select name="status_id"  id="status_id" onchange="submit();" class="slectpicker custom-select">
      <option value="">Estado...</option>
      @foreach($customer_options as $item)
        <option value="{{$item->id}}" @if ($request->status_id == $item->id) selected="selected" @endif>
           {{ $item->name }}
          
        </option>
      @endforeach
    </select>

    {{-- Combo de Interest --}}
<select name="scoring_interest" class="slectpicker custom-select" id="scoring_interest" onchange="submit();">
    <option value="">Interes...</option>
    @foreach($scoring_interest as $item)
      <option value="{{$item->scoring_interest}}" @if ($request->scoring_interest == $item->scoring_interest) selected="selected" @endif>
         {{ $item->scoring_interest }}
        
      </option>
    @endforeach
  </select>

  {{-- Combo de Perfil --}}


<select name="scoring_profile" class="slectpicker custom-select" id="scoring_profile" onchange="submit();">
    <option value="">Perfil...</option>
    @foreach($scoring_profile as $item)
      <option value="{{$item->scoring_profile}}" @if ($request->scoring_profile == $item->scoring_profile) selected="selected" @endif>
         {{ $item->scoring_profile }}
        
      </option>
    @endforeach
  </select>

  <select name="product_id" class="slectpicker custom-select" id="product_id" onchange="submit();">
    <option value="">Producto...</option>
    @foreach($products as $item)
      <option value="{{$item->id}}" @if ($request->product_id == $item->id) selected="selected" @endif>
         {{ $item->name }}
        
      </option>
    @endforeach
  </select>
  
    </div>
    <div class="col-md-2">
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
    <select name="source_id" class="custom-select" id="source_id" onchange="submit();">
      <option value="">Fuente...</option>
      @foreach($sources as $item)
        <option value="{{$item->id}}" @if ($request->source_id == $item->id) selected="selected" @endif>
           <?php echo substr($item->name, 0, 15); ?>
          
        </option>
      @endforeach
    </select>

    <!-- audiencia  -->

    <div>
          <select name="audience_id" class="form-control" w id="audience_id" onchange="submit();">
            <option value="">Audiencia...</option>
            @foreach($audiences as $item)
              <option value="{{$item->id}}" @if ($request->audience_id == $item->id) selected="selected" @endif>
                 <?php echo substr($item->name, 0, 30); ?>@if(isset($item->customeres))
                 - {{$item->customeres->count()}} @endif
                
              </option>
            @endforeach
          </select>
        </div>


    <!-- Creacuin o actualización -->
    
        </div>
    <div class="col-md-3">
      <div>
      <label for="created">Creado</label>
    <input type="radio" name="created_updated" id="created" value="created" onchange="submit();" @if(!isset($request->created_updated) || (isset($request->created_updated)&&($request->created_updated=="created")) ) checked="checked"   @endif>

    <label for="created">Actualizado</label>
    <input type="radio" name="created_updated" id="updated" value="updated" onchange="submit();" @if(isset($request->created_updated)&&($request->created_updated=="updated")) checked="checked" @endif>
    </div>
    <div>
      <input type="text" name="search" id="search" @if(isset($request->search))value="{{$request->search}}"@endif>
          
    </div>
        
    </div>
    <div class="col-md-1">
      <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
    </div> 
      </div>
       
    
   
    </form>
</div>