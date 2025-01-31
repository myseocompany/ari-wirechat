@extends('layout')

@section('content')
<h1>Crear Cliente</h1>
{{-- FORMULARIO CLIENTES --}}
<form method="POST" action="/customers">
{{ csrf_field() }}
<fieldset class="scheduler-border">
  <legend class="scheduler-border">Datos Personales:</legend>
  <div class="row">
        <div class="col-sm-3">
        <div class="form-group">
          <label for="name">Nombre:</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Nombre..." value="">
        </div>
        </div>
        <div class="col-sm-3">
           <div class="form-group">
            <label for="phone">Celular:</label>
            <input type="number" class="form-control" id="phone" name="phone" placeholder="Celular...">
          </div>
        </div>
        
        <div class="col-sm-3">
          <div class="form-group">
            <label for="phone2">Teléfono:</label>
            <input type="number" class="form-control" id="phone2" name="phone2" placeholder="Telefono...">
          </div>
        </div>
         
        <div class="col-sm-3">
          <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="text" class="form-control" id="email" name="email" placeholder="Correo Electronico...">
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="country">País:</label>
            @include('customers.countries')
            <!--<input type="text" class="form-control" id="country" name="country" placeholder="País...">-->
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="department">Departamento:</label>
            <input type="text" class="form-control" id="department" name="department" placeholder="Departamento...">
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="city">Ciudad:</label>
            <input type="text" class="form-control" id="city" name="city" placeholder="Ciudad...">
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="address">Dirección:</label>
            <input type="text" class="form-control" id="address" name="address" placeholder="Dirección...">
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="business">Empresa:</label>
            <input type="text" class="form-control" id="business" name="business" placeholder="Empresa...">
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="position">Cargo:</label>
            <input type="text" class="form-control" id="position" name="position" placeholder="Cargo..">
          </div>    
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="document">Documento:</label>
            <input type="text" class="form-control" id="document" name="document" placeholder="Doc Ej: C.c/DNI...">
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="document">Cuantas empanadas hace:</label>
            <input type="text" class="form-control" id="count_empanadas" name="count_empanadas" placeholder="Cantidad de empanadas...">
          </div>
        </div>
  </div>
  <div class="row">
    <div class="col-sm-3">
    <div class="form-group">
      <label for="customers_statuses">Estado:</label>
      <select name="status_id" id="status_id" class="form-control">
        @foreach ($customers_statuses as $item)
        <option value="{{ $item->id }}">{{  $item->name }}</option>
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
        <option value="{{ $item->id }}">{{  $item->name }}</option>
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
        <option value="{{$item->id}}">{{$item->name}}</option>
      @endforeach
    </select>
  </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
    <label for="">Tamaño de empanadas:</label>
      <select name="empanadas_size" class="form-control">
        <option >Selecciona</option>
        <option value="5 cm">5 cm</option>
        <option value="7 cm">7 cm</option>
        <option value="8 cm">8 cm</option>
        <option value="10 cm">10 cm</option>
        <option value="otro">otro</option>
      </select> 
  </div>
</div>

      <div class="col-md-12">
      Perfil
    <div class="rating">
    <label >
      <input type="radio" name="scoring_profile" value="a" title="4 stars"> 4
    </label>
    <label >
      <input type="radio"  name="scoring_profile" value="b" title="3 stars"> 3
    </label>
    <label >
      <input type="radio"  name="scoring_profile" value="c" title="2 stars"> 2
    </label>
    <label >
      <input type="radio"  name="scoring_profile" value="d" title="1 star"> 1
    </label>
  </div>
    </div>


    <div class="col-sm-3">
      <div class="form-group">
        <label for="document">Interes:</label>
        <input type="number" class="form-control" id="scoring_interest" name="scoring_interest" placeholder="Interes">
      </div>
    </div>
    <div class="col-sm-3">
      <div class="form-group">
        <label for="">Número de sedes:</label>
        <select name="number_venues" class="form-control">
          <option >Selecciona</option>
          <option value="1 a 5">1 a 5</option>
          <option value="6 a 10">6 a 10</option>
          <option value="Más de 10">Más de 10</option>
       number_venues
        </select> 
      </div>
    </div>
    <div class="col-sm-3">
      <div class="form-group">
        <label for="document">URL RD Station:</label>
        <input type="text" class="form-control" id="rd_public_url" name="rd_public_url" value="">
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
          <input type="text" class="form-control" id="contact_name" name="contact_name" placeholder="Nombre del contacto" value="">
        </div>
        </div>
        <div class="col-sm-3">
           <div class="form-group">
            <label for="contact_phone2">Celular:</label>
            <input type="text" class="form-control" id="contact_phone2" name="contact_phone2" placeholder="Celular...">
          </div>
        </div>
        
        <div class="col-sm-3">
          <div class="form-group">
            <label for="contact_email">Correo Electrónico:</label>
            <input type="text" class="form-control" id="contact_email" name="contact_email" placeholder="Correo Electronico...">
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="contact_position">Cargo:</label>
            <input type="text" class="form-control" id="contact_position" name="contact_position" placeholder="Cargo..">
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
    <input type="text" class="form-control" name="bought_products" id="bought_products" placeholder="Producto">
  </div>
  <div class="form-group">
    <label for="bought_products">Valor Cotizado:</label>
    <input type="text" class="form-control" name="total_sold" id="total_sold" placeholder="Valor a Cotizar">
  </div>
  <div class="form-group">
    <label for="first_installment_date">Fecha de Compra:</label>
    <input type="date" class="form-control" name="date_bought" id="date_bought">
  </div>
   {{-- Asignado a --}}
  <div class="form-group">
    <label for="users">Asignado A:</label>
    <select name="user_id" id="user_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($users as $item)
      <option value="{{ $item->id }}">{{  $item->name }}</option>
      @endforeach
    </select>
  </div>
  {{-- fuente --}}
    <div class="">
    <label for="notes">Visitas Técnicas:</label>
    <textarea name="technical_visit" id="technical_visit" placeholder="" cols="30" rows="10" class="form-control"></textarea>
  </div>
  <div class="">
    <label for="notes">Notas:</label>
    <textarea name="notes" id="notes" placeholder="" cols="30" rows="10" class="form-control"></textarea>
  </div>
</fieldset>
  <button type="submit" class="btn btn-primary">Enviar</button>
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
</script>
@endsection
