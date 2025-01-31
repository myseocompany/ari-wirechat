<div>
 
    <form action="/{{$model->action}}/"  actione="/customers/phase/1" method="GET" id="mini_filter_form">

      <div id="quicksearch" class="form-group">
  
      <div class="row">
        <div class="col-md-9">
        <input placeholder="Escriba aquí" type="text" name="search" id="search" class="form-control" onkeyup="searchSynchronize();" @if(isset($request->search))value="{{$request->search}}" @endif>
            
      </div>
          
      <div class="col-md-2">
        <input type="submit" class="btn btn-primary btn-sm" value="Filtrar">
      </div> 
      </div>
    </div> 
    </form>    
  


<div class="accordion" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingFilter">
      <h2 class="mb-0">
        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseFilter" aria-expanded="true" aria-controls="collapseFilter">
          Filtro
        </button>
      </h2>
    </div>

    <div id="collapseFilter" class="collapse" aria-labelledby="headingFilter" data-parent="#accordionExample">
      <!--<div class="card-body row">-->
      <div>
        <form action="/{{$model->action}}/" actione="/customers/phase/1" method="GET" id="filter_form">

      <div class="col-md-12">
          <select name="filter" class="form-control col-12" id="filter" onchange="update()">
            <option value="">Seleccione tiempo</option>
            <option value="0" @if ($request->filter == "0") selected="selected" @endif>hoy</option>
            <option value="-1" @if ($request->filter == "-1") selected="selected" @endif>ayer</option>
            <option value="thisweek" @if ($request->filter == "thisweek") selected="selected" @endif>esta semana</option>
            
            <option value="lastweek" @if ($request->filter == "lastweek") selected="selected" @endif>semana pasada</option>
            <option value="lastmonth" @if ($request->filter == "lastmonth") selected="selected" @endif>mes pasado</option>
            <option value="currentmonth" @if ($request->filter == "currentmonth") selected="selected" @endif>este mes</option>
            <option value="lastyear" @if ($request->filter == "lastyear") selected="selected" @endif>año pasado</option>
            <option value="currentyear" @if ($request->filter == "currentyear") selected="selected" @endif>este año</option>
            <option value="-7" @if ($request->filter == "-7") selected="selected" @endif>ultimos 7 dias</option>
            <option value="-30" @if ($request->filter == "-30") selected="selected" @endif>ultimos 30 dias</option>
          </select>
        </div>
        <div class="col-md-12">
          <div class="row">
            <div class="col-6">
              <input class="form-control" type="date" id="from_date" name="from_date" onchange="cleanFilter()" value="{{$request->from_date}}">
            </div>
            <div class="col-6">
              <input class="form-control" type="date" id="to_date" name="to_date" onchange="cleanFilter()" value="{{$request->to_date}}">
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="row">
          <div class="col-4">
            <input type="radio" name="maker" id="created" value="empty" onchange="submit();" @if(isset($request->maker) &&($request->maker=="empty"))  checked="checked"   @endif>
            <label for="maker"><small>Sin clasificar</small></label>
              </div>
            <div class="col-4">
              <input type="radio" name="maker" id="created" value="0" onchange="submit();" @if(isset($request->maker) &&($request->maker=="0"))  checked="checked"   @endif>
              <label for="maker"><small>Proyecto</small></label>
            </div>
            <!--
            <div class="col-4">
              <label for="maker"><small>Desmechadora</small></label>
              <input type="radio" name="maker" id="updated" value="2" onchange="submit();" @if(isset($request->maker)&&($request->maker=="2")) checked="checked" @endif>
            </div>
-->
            <div class="col-4">
              <input type="radio" name="maker" id="updated" value="1" onchange="submit();" @if(isset($request->maker)&&($request->maker=="1")) checked="checked" @endif>
              <label for="maker"><small>Hace empanadas</small></label>
            </div>

          </div>
        </div>

        <div class="col-md-12">
          <div class="row">
            <div class="col-6">
              <label for="created">Creado</label>
              <input type="radio" name="created_updated" id="created" value="created" onchange="submit();" @if(!isset($request->created_updated) || (isset($request->created_updated)&&($request->created_updated=="created")) ) checked="checked"   @endif>
            </div>
            <div class="col-6">
              <label for="created">Actualizado</label>
              <input type="radio" name="created_updated" id="updated" value="updated" onchange="submit();" @if(isset($request->created_updated)&&($request->created_updated=="updated")) checked="checked" @endif>
            </div>
          </div>
        </div>


  <div class="col-md-12">
    <div class="row">
      <div class="col-6">
        <div class="row">
          <div class="col-4">
            Perfil
          </div>
          <div class="col-8">
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
        </div>
      </div>
    
    

<div class="col-6">
      <select name="scoring_interest" class="form-control" id="scoring_interest" onchange="submit();">
        <option value="">Interes...</option>
        @foreach($scoring_interest as $item)
          <option value="{{$item->scoring_interest}}" @if ($request->scoring_interest == $item->scoring_interest) selected="selected" @endif>
             {{ $item->scoring_interest }}
            
          </option>
        @endforeach
      </select>
    </div>
    </div>
  </div>
  

        
<style type="text/css">
.rating {
    unicode-bidi: bidi-override;
    direction: rtl;
    width: 8em;
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
        {{-- Combo de Interest --}}
<div class="col-md-12">
  <div class="row">
    
    <div class="col-6">
      <select name="country" class="form-control" id="country" onchange="submit();">
        <option value="">País...</option>
       @if(isset($country_options))
       @foreach($country_options as $item)
          <option value="{{$item->country}}" @if ($request->country == $item->country && $item->country != "") selected="selected" @endif>
             {{ $item->country }}
            
          </option>
        @endforeach
       @endif
      </select>
    </div>
    
    <div class="col-6">
        <select name="status_id" id="status_id" class="form-control" onchange="submit();">
          <option value="">Estado...</option>
          @foreach($customer_options->all() as $item)
            <option value="{{$item->id}}" @if ($request->status_id == $item->id) selected="selected" @endif>
               {{ $item->name }}
              
            </option>
          @endforeach
        </select>
      </div>

    {{-- Combo de Perfil --}}
    <!--
  <select name="scoring_profile" class="slectpicker custom-select" id="scoring_profile" onchange="submit();">
      <option value="">Perfil...</option>
      <option value="">0</option>
      @foreach($scoring_profile as $item)
        <option value="{{$item->scoring_profile}}" @if ($request->scoring_profile == $item->scoring_profile) selected="selected" @endif>
          @if ($item->scoring_profile=="a")
              {{ 4 }}
          @endif
          @if ($item->scoring_profile=="b")
              {{ 3 }}
          @endif
          @if ($item->scoring_profile=="c")
              {{ 2 }}
          @endif
          @if ($item->scoring_profile=="d")
              {{ 1 }}
          @endif
        </option>
      @endforeach
    </select>
  -->
  </div>
</div>

  <div class="col-md-12">
    <div class="row">
      <div class="col-6">
        <select name="product_id" class="form-control" id="product_id" onchange="submit();">
          <option value="">Producto...</option>
          @foreach($products as $item)
            <option value="{{$item->id}}" @if ($request->product_id == $item->id) selected="selected" @endif>
               {{ $item->name }}
              
            </option>
          @endforeach
        </select>
      </div>
     <div class="col-6">
        <select name="user_id" class="form-control" id="user_id" onchange="submit();">
          <option value="">Usuario...</option>
          <option value="null">Sin asignar</option>
          @foreach($users as $user)
            <option value="{{$user->id}}" @if ($request->user_id == $user->id) selected="selected" @endif>
               <?php echo substr($user->name, 0, 10); ?>
              
            </option>
          @endforeach
        </select>
      </div>
    </div>
  </div>
    <div class="col-md-12">
      <div class="row">
        <div class="col-6">
          <select name="source_id" class="form-control" id="source_id" onchange="submit();">
            <option value="">Fuente...</option>
            @foreach($sources as $item)
              <option value="{{$item->id}}" @if ($request->source_id == $item->id) selected="selected" @endif>
                 <?php echo substr($item->name, 0, 15); ?>
                
              </option>
            @endforeach
          </select>
        </div>
        <!-- 
        <div class="col-6">
          <select name="audience_id" class="form-control" id="audience_id" onchange="submit();">
            <option value="">Audiencia...</option>
            @foreach($audiences as $item)
              <option value="{{$item->id}}" @if ($request->audience_id == $item->id) selected="selected" @endif>
                 <?php echo substr($item->name, 0, 50); ?>@if(isset($item->customeres))
                 - {{$item->customeres->count()}} @endif
                
              </option>
            @endforeach
          </select>
        </div>
-->
    @if(isset($inquiry_products))
      <div class="col-6">
          <select name="inquiry_product_id" class="form-control" id="inquiry_product_id" onchange="submit();">
            <option value="">Producto buscado...</option>
            @foreach($inquiry_products as $item)
              <option value="{{$item->id}}" @if ($request->inquiry_product_id == $item->id) selected="selected" @endif>
                 <?php echo substr($item->name, 0, 50); ?>
                
              </option>
            @endforeach
          </select>
        </div>
        @endif
      </div>
  </div>
<div class="col-md-12">
        <!--  fin del filtro -->
        <div class="row">
          <div class="col-md-9">
              <input placeholder="Escriba aquí" type="text" name="search" id="search_2" class="form-control" onkeyup="search2Synchronize();"  @if(isset($request->search))value="{{$request->search}}"@endif>
          </div>
          <div class="col-md-3">
              <input type="submit" class="btn btn-primary btn-sm "  value="Filtrar" >    
          </div>
        </div>
</div>
      
      </form>
    </div>
  </div>
  <div class="card">
    
    
      <div class="card-body row">
        <div><a style="color: #4178be;" href="/customers/create">Crear
              <i class="fa fa-plus" aria-hidden="true"></i>
        </a> | <a href="/leads/excel{{ requestToStr($request) }}">Exportar</a> 
              | <a href="/import/">Importar</a>
              | 
              <a href="#" data-toggle="modal" data-target="#miModal">Cambiar Estado</a>
        
            </div>
      </div>
    
  </div>
  
</div>


<!-- Botón que activa el modal -->

<!-- Modal -->
<form action="/leads/change_status{{ requestToStr($request) }}" type="GET" id="changeStatusForm">
<div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Cambiar usuarios de estado</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      @foreach($request->query() as $key => $value)
    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
  @endforeach
        
        <select name="modal_status_id"  id="modal_status_id" class="form-control">
         
          <option value="">Estado...</option>
          @foreach($customer_options->all() as $item)
            <option value="{{$item->id}}" @if ($request->status_id == $item->id) selected="selected" @endif>
               {{ $item->name }}
              
            </option>
          @endforeach
        </select>
    
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="saveChanges">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
</form>

      

<script>
document.getElementById('saveChanges').addEventListener('click', function() {
  var statusSelect = document.getElementById('modal_status_id');
  if (statusSelect.value === '') {
    alert('Por favor, seleccione un estado.');
    return false;
  }else{
    document.getElementById('changeStatusForm').submit();  
  }
  
});
</script>

       
    
   
    
</div>

