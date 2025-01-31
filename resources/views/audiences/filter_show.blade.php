
    <form action="/audiences/{{$audience->id}}/show" method="GET" id="filter_form">
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

      
        <div class="form-group">
          <div class="row">
            <div class="col-4">
              <input type="text" name="kpi" id="kpi" value="{{$request->kpi}}" placeholder="kpi" class="form-control">
            </div>
            <div class="col-4">
              <input type="text" name="search" id="search" value="{{$request->search}}" placeholder="Busca o escribe" class="form-control">
            </div>
            
            <span>
              <?php $cu = $request->created_updated; ?>
              <label class="radio"> Fecha de creación 
                <input type="radio" name="created_updated" value="created" 
                @if((isset($cu)&& ($cu == 'created'))||(!isset($cu)))  checked @endif onchange="submit();"> </label> 
              <label class="radio" > o actualizacion 
                <input type="radio" name="created_updated" value="updated" @if(isset($request->created_updated)&& ($request->created_updated == "updated")) checked @endif onchange="submit();"> </label>
            </span>
          </div>
        </div>
      
      
      <div class="row">
        <div class="col-md-1">
          <label>Perfil</label>
        </div>
        <div class="col-md-2">
          <div class="rating">
            <label @if($request->scoring_profile=="a") class="selected" @endif>
              <input type="radio" @if($request->scoring_profile=="a") checked @endif name="scoring_profile" value="a" title="4 stars"> 4
            </label>
            <label @if($request->scoring_profile=="b") class="selected" @endif>
              <input type="radio" @if($request->scoring_profile=="b") checked @endif name="scoring_profile" value="b" title="3 stars"> 3
            </label>
            <label @if($request->scoring_profile=="c") class="selected" @endif>
              <input type="radio" @if($request->scoring_profile=="c") checked @endif name="scoring_profile" value="c" title="2 stars"> 2
            </label>
            <label @if($request->scoring_profile=="d") class="selected" @endif>
              <input type="radio" @if($request->scoring_profile=="d") checked @endif name="scoring_profile" value="d" title="1 star"> 1
            </label>
          </div>
        </div>
        <div class="col-md-8">
          <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
        </div>
      </div>
      
    </form>


<style type="text/css">.rating {
    unicode-bidi: bidi-override;
    direction: rtl;
    width: 9em;
}

.rating input {
    position: absolute;
    left: -999999px;
}

.rating label {
    display: inline-block;
    font-size: 0;
}

.rating > label:before {
    position: relative;
    font: 24px/1 FontAwesome;
    display: block;
    content: "\f005";
    color: #ccc;
    background: -webkit-linear-gradient(-45deg, #d9d9d9 0%, #b3b3b3 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.rating > label:hover:before,
.rating > label:hover ~ label:before,
.rating > label.selected:before,
.rating > label.selected ~ label:before {
    color: #f8ce0b;
    background: -webkit-linear-gradient(-45deg, #f8ce0b 0%, #f8ce0b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>
<script type="text/javascript">
  $('.rating input').change(function () {
  var $radio = $(this);
  $('.rating .selected').removeClass('selected');
  $radio.closest('label').addClass('selected');
});



  function searchSynchronize(){
    var search = $("#search").val(); 
    $("#search_2").val(search);
  }

  function search2Synchronize(){
    var search = $("#search_2").val(); 
    $("#search").val(search);
  }
</script>