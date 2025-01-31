@extends('layout')

@section('content')
<h1>{{$model->name}}</h1>
<form action="/customers/{{$model->id}}/update" method="POST">
{{ csrf_field() }}
   {{-- Asignado a --}}
  <div class="form-group">
    <label for="users">Asignado A:</label>
    <select name="user_id" id="user_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($users as $item)
       @if($item->status_id == 1)
        <option value="{{$item->id}}" @if($item->id==$model->user_id)selected="selected" @endif>{{$item->name}}</option>
        @endif
      @endforeach
    </select>
  </div>



<fieldset class="scheduler-border">
  <legend class="scheduler-border">Datos Personales:</legend>
  <div class="row">
  <div class="col-sm-3">
      <div class="form-group">
        <label for="name">Nombre:</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Nombre..." value="{{$model->name}}">
      </div>
  </div>

  <div class="col-sm-3">
    <div class="form-group">
      <label for="phone2">Celular:</label>
      <input type="text" class="form-control" id="phone" name="phone" value="{{$model->phone}}" placeholder="Celular...">
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label for="phone">Teléfono:</label>
      <input type="text" class="form-control" id="phone2" name="phone2" value="{{$model->phone2}}" placeholder="Telefono...">
    </div>
  </div>
  
  <div class="col-sm-3">
    <div class="form-group">
      <label for="email">Correo Electrónico:</label>
      <input type="text" class="form-control" id="email" name="email" value="{{$model->email}}" placeholder="Correo Electronico...">
    </div>
</div>
  
  
  <div class="col-sm-3">
    <div class="form-group">
      <label for="country">País:</label>
      @include('customers.countries')


      <input type="hidden" class="form-control" id="selected_country" value="{{$model->country}}" name="selected_country">
    </div>
</div>
  <div class="col-sm-3">
    <div class="form-group">
      <label for="department">Departamento:</label>
      <input type="text" class="form-control" value="{{$model->department}}" id="department" name="department" placeholder="Ciudad...">
    </div>
</div>
  <div class="col-sm-3">
    <div class="form-group">
      <label for="city">Ciudad:</label>
      <input type="text" class="form-control" value="{{$model->city}}" id="city" name="city" placeholder="Ciudad...">
    </div>
</div>
<div class="col-sm-3">
    <div class="form-group">
      <label for="address">Dirección:</label>
      <input type="text" class="form-control" id="address" value="{{$model->address}}" name="address" placeholder="Dirección...">
    </div>
</div>
  <div class="col-sm-3">
    <div class="form-group">
      <label for="business">Empresa:</label>
      <input type="text" class="form-control" id="business" value="{{$model->business}}" name="business" placeholder="Empresa...">
    </div>
</div>
  <div class="col-sm-3">
    <div class="form-group">
      <label for="position">Cargo:</label>
      <input type="text" class="form-control" id="position" value="{{$model->position}}" name="position" placeholder="Cargo..">
    </div>
</div>
<div class="col-sm-3">
    <div class="form-group">
      <label for="document">Documento:</label>
      <input type="text" class="form-control" id="document" name="document" value="{{$model->document}}" placeholder="Doc Ej: C.c/DNI...">
    </div>
  </div>
  <div class="col-sm-3">
          <div class="form-group">
            <label for="document">Cuantas empanadas hace:</label>
            <input type="text" class="form-control" id="count_empanadas" name="count_empanadas" value="{{$model->count_empanadas}}" placeholder="Cantidad de empanadas...">
          </div>
        </div>
          </div>  
       
  <div class="row">
    <div class="col-sm-3">
    <div class="form-group">
    <label for="customers_statuses">Estado:</label>
    <select name="status_id" id="status_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($customer_statuses as $item)
       <option value="{{$item->id}}" @if($item->id==$model->status_id)selected="selected" @endif>{{$item->name}}</option>
      @endforeach
    </select>
  </div>
  </div>
    <div class="col-sm-3">
    <div class="form-group">
    <label for="font_customers">Fuente:</label>
    <select name="source_id" id="source_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($customer_sources as $item)
        <option value="{{$item->id}}" @if($item->id==$model->source_id)selected="selected" @endif>{{$item->name}}</option>
      @endforeach
    </select>
  </div>
  </div>
  
<div class="col-sm-3">
    <div class="form-group">
    <label for="font_customers">Producto por:</label>
    <select name="product_id" id="product_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($products as $item)
        <option value="{{$item->id}}" @if($item->id==$model->product_id)selected="selected" @endif>{{$item->name}}</option>
      @endforeach
    </select>
  </div>
  </div>

  <div class="col-sm-3">
    <div class="form-group">
    <label for="">Tamaño de empanadas:</label>
      <select name="empanadas_size" class="form-control">
        <option >Selecciona</option>
        <option value="5 cm" @if($model->empanadas_size=='5 cm') value="{{$model->empanadas_size}}" selected="selected" @endif>5 cm</option>
        <option value="7 cm" @if($model->empanadas_size=='7 cm') value="{{$model->empanadas_size}}" selected="selected" @endif>7 cm</option>
        <option value="8 cm" @if($model->empanadas_size=='8 cm') value="{{$model->empanadas_size}}" selected="selected" @endif>8 cm</option>
        <option value="10 cm" @if($model->empanadas_size=='10 cm') value="{{$model->empanadas_size}}" selected="selected" @endif>10 cm</option>
        <option value="otro" @if($model->empanadas_size=='otro') value="{{$model->empanadas_size}}" selected="selected" @endif>otro</option>
      </select> 
  </div>
</div>



  <div class="col-md-12">
      Perfil
    <div class="rating">
      @foreach($scoring_profile as $item)
        <label @if($model->scoring_profile == $item->scoring_profile) class="selected" @endif>
          <input type="radio" name="scoring_profile" value="{{$item->scoring_profile}}" title=" {{$item->getScoringToNumber()}} stars" 
          @if($model->scoring_profile == $item->scoring_profile) checked="" @endif> {{$item->getScoringToNumber()}}
        </label>
      @endforeach
    </div>
  </div>

<script type="text/javascript">   
console.log({{$model->getScoringToNumber()}});       
  starTotal = 4;
  starPercentage = ({{$model->getScoringToNumber()}} / starTotal) * 100;
  starPercentageRounded = (Math.round(starPercentage / 10) * 10)+'%';
  console.log(starPercentageRounded);
  $('.stars-inner').width(starPercentageRounded); 
</script>


<div class="col-sm-3">
    <div class="form-group">
      <label for="document">Interes:</label>
      <input type="number" class="form-control" id="scoring_interest" name="scoring_interest" placeholder="Interes" value="{{$model->scoring_interest}}">
    </div>
  </div>

  <div class="col-sm-3">
    <div class="form-group">
      <label for="">Número de sedes:</label>
      <select name="number_venues" class="form-control">
        <option >Selecciona</option>
        <option value="1 a 5" @if($model-> number_venues=='1 a 5') value="{{$model-> number_venues}}" selected="selected" @endif>1 a 5</option>
        <option value="6 a 10" @if($model-> number_venues=='6 a 10') value="{{$model-> number_venues}}" selected="selected" @endif>6 a 10</option>
        <option value="Más de 10" @if($model-> number_venues=='Más de 10') value="{{$model-> number_venues}}" selected="selected" @endif>Más de 10</option>
     number_venues
      </select> 
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label for="document">URL RD Station:</label>
      <input type="text" class="form-control" id="rd_public_url" name="rd_public_url" value="{{$model->rd_public_url}}">
    </div>
  </div>

  <div class="col-sm-3">
    <div class="form-group">
      <label for="document">Fabricante:</label>
 
      <div class="col-12">
        <label for="maker">Proyecto</label>
        <input type="radio" name="maker" id="created" value="0"" @if($model->maker == 0) checked  @endif>
        
      </div>
      <div class="col-12">
        <label for="maker">Hace empanadas</label>
        <input type="radio" name="maker" id="updated" value="1"" @if($model->maker == 1) checked  @endif>
      </div>


      <div class="col-12">
        <label for="maker">Otros</label>
        <input type="radio" name="maker" id="updated" value="2"" @if($model->maker == 2) checked  @endif>
      </div>
    



    </div>
  </div>
  </div>
  
  
  
</fieldset>
{{-- Datos de contacto --}}
  <fieldset class="scheduler-border">
  <legend class="scheduler-border">Contactos adicionales:</legend>
  <div class="row">
        <div class="col-sm-3">
        <div class="form-group">
          <label for="contact_name">Nombre:</label>
          <input type="text" class="form-control" id="contact_name" name="contact_name" value="{{$model->contact_name}}">
        </div>
        </div>
        <div class="col-sm-3">
           <div class="form-group">
            <label for="contact_phone2">Celular:</label>
            <input type="text" class="form-control" id="contact_phone2" name="contact_phone2" value="{{$model->contact_phone2}}" >
          </div>
        </div>
        
        <div class="col-sm-3">
          <div class="form-group">
            <label for="contact_email">Correo Electrónico:</label>
            <input type="text" class="form-control" id="contact_email" name="contact_email" value="{{$model->contact_email}}">
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="contact_position">Cargo:</label>
            <input type="text" class="form-control" id="contact_position" name="contact_position" value="{{$model->contact_position}}">
          </div>    
        </div>
        
  </div>
  </fieldset>

  {{-- Fin datos de contacto --}}
<fieldset class="scheduler-border">
  <legend class="scheduler-border">Datos Adicionales:</legend>
  {{-- Estado --}}

  <div class="form-group">
    <label for="bought_products">Producto Adquirido:</label>
    <input type="text" class="form-control" name="bought_products" value="{{$model->bought_products}}" id="bought_products" placeholder="Producto">
  </div>

  <div class="form-group">
    <label for="bought_products">Valor Cotizado:</label>
    <input type="text" class="form-control" name="total_sold" value="{{$model->total_sold}}" id="total_sold" placeholder="Valor cotizado">
  </div>
  <div class="form-group">
    <label for="first_installment_date">Fecha de Compra:</label>
    <input type="date" class="form-control" name="date_bought" value="{{$model->purchase_date}}" id="date_bought">
  </div>
  
  {{-- fuente --}}

    <div class="">
    <label for="notes">Visitas Técnicas:</label>
    <textarea name="technical_visit" id="technical_visit" placeholder="" cols="30" rows="10" class="form-control">{{$model->technical_visit}}</textarea>
  </div>
  <div class="">
    <label for="notes">Notas:</label>
    <textarea name="notes" id="notes" placeholder="" cols="30" rows="10" class="form-control">{{$model->notes}}</textarea>
  </div>
</fieldset>
  <button type="submit" class="btn btn-primary">Enviar</button>
</form>

<style type="text/css">



  .rating {
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
.rating > label.selected ~ label:before, .star-selected {
    color: #f8ce0b;
    background: -webkit-linear-gradient(-45deg, #f8ce0b 0%, #f8ce0b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
</style>

<script type="text/javascript">


  $('.rating input').change(function () {
    var $radio = $(this);
    $('.rating .selected').removeClass('selected'); //deja seleccionar las estrellas varias veces
    $radio.closest('label').addClass('selected'); //dejar seleccionada la estrella
  });


  $(document).ready(function(){
    var country = $("#selected_country").val();
    if(country){
      $("#country option[value="+ country +"]").attr("selected",true);
    }
  });
</script>
@endsection
