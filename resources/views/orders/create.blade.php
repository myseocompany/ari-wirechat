@extends('layout')

@section('content')

<div><a href="/orders">Ordenes</a> >> ver</div>
<h1>Crear Orden </h1>



<form class="form-group" action="/orders" method="POST">

{{ csrf_field() }}
	<div class="row">
		<div class="col-md-4 col-sm-12 group-container">
			<h3 class="title">Datos personales</h3>
			<div class="row">

				
		
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Nombre:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->name}}">
							<input type="text" value="{{$model->customer->name}}" id="name" name="name" class="form-control">
							<input type="hidden" value="{{$model->customer->id}}" id="customer_id" name="customer_id" class="form-control">
							
					</div>
					</div>
				</div>
				
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">No de identificación:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->document}}">
							{{$model->document}}
							<input type="text" value="{{$model->customer->document}}" id="document" name="document"  class="form-control">
							
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Cargo:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->position}}">
							{{$model->position}}
							<input type="text" value="{{$model->customer->position}}" id="position" name="position"  class="form-control">
							
						</div>
					</div>
				</div>
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Indicativo:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->area_code}}">
							{{$model->area_code}}
							<input type="text" value="{{$model->customer->area_code}}" id="area_code" name="area_code"  class="form-control">
							
						</div>
					</div>
				</div>
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Teléfono:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->phone}}">
							{{$model->phone}}
							<input type="text" value="{{$model->customer->phone}}" id="phone" name="phone"  class="form-control">
							
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Email:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->email}}">
							<input type="text" value="{{$model->customer->email}}" id="email" name="email"  class="form-control">
							
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Dirección:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->address}}">
							<input type="text" value="{{$model->customer->address}}" id="address" name="address"  class="form-control">
							
						</div>
					</div>
				</div>



				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Ciudad:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->city}}">
							<input type="text" value="{{$model->customer->city}}" id="city" name="city"  class="form-control">
							
						</div>
					</div>
				</div>



				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Provincia y/o estado:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->department}}">
							<input type="text" value="{{$model->customer->department}}" id="department" name="department"  class="form-control">
							
						</div>
					</div>
				</div>
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">País:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->country}}">
							<input type="text" value="{{$model->customer->country}}" id="country" name="country"  class="form-control">
							
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Teléfono de contacto:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->contact_phone2}}">
							<input type="text" value="{{$model->customer->contact_phone2}}" id="contact_phone2" name="contact_phone2"  class="form-control">
							
						</div>
					</div>
				</div>

				
			</div>
		</div>

		<div class="col-md-4 col-sm-12 group-container">
			<h3 class="title">Datos empresariales</h3>
			<div class="row">

				
		
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Nombre:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->business}}">
							<input type="text" value="{{$model->customer->business}}" id="business" name="business" class="form-control">
						
					</div>
					</div>
				</div>
				
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">NIT:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->business_document}}">
							{{$model->business_document}}
							<input type="text" value="{{$model->customer->business_document}}" id="business_document" name="business_document"  class="form-control">
							
						</div>
					</div>
				</div>


				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Indicativo:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->business_area_code}}">
							{{$model->business_area_code}}
							<input type="text" value="{{$model->customer->business_area_code}}" id="business_area_code" name="business_area_code"  class="form-control">
							
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Teléfono:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->business_phone}}">
							{{$model->business_phone}}
							<input type="text" value="{{$model->customer->business_phone}}" id="business_phone" name="business_phone"  class="form-control">
							
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Email:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->business_email}}">
							<input type="text" value="{{$model->customer->business_email}}" id="business_email" name="business_email"  class="form-control">
							
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Dirección:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->business_address}}">
							<input type="text" value="{{$model->customer->business_address}}" id="business_address" name="business_address"  class="form-control">
							
						</div>
					</div>
				</div>



				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Ciudad:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->city}}">
							<input type="text" value="{{$model->customer->business_city}}" id="business_city" name="business_city"  class="form-control">
							
						</div>
					</div>
				</div>



				

				

				
			</div>
		</div>
		<div class="col-md-4 col-sm-12 group-container">
			<h3 class="title">Envío / Destino</h3>
			<div class="row">

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Dirección:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->delivery_address}}">
							
							<input type="input" class="form-control" id="delivery_address" name="delivery_address">

						</div>
					</div>
				</div>
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Ciudad:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->city}}">
							
							<input type="input" class="form-control" id="delivery_city" name="delivery_city">

						</div>
					</div>
				</div>
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">País:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->country}}">
							
							<select class="form-control" id="delivery_country" name="delivery_country">
								<option value="">Seleccione el pais</option>
								@foreach ($countries as $item)
								<option value="{{$item->iso2}}">{{$item->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Código postal:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->delivery_postal_code}}">
							
							<input type="input" class="form-control" id="delivery_postal_code" name="delivery_postal_code">
							
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Estado de la orden:</label>
						<div readonly id="" name="" class="col-6" >
							
							<select name="status_id" id="status_id"  class="form-control">
								@foreach($statuses as $item)
								<option value="{{$item->id}}">{{$item->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>

				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Atendido por:</label>
						<div readonly id="" name="" class="col-6">
							<select name="user_id" id="user_id"  class="form-control">
								@foreach($users as $item)
								<option value="{{$item->id}}"  @if ($user_id  == $item->id) selected="selected" @endif>{{$item->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>


				
				
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<textarea name="notes" id="notes" cols="100%" rows="4" class="form-control"></textarea>
		</div>

	</div>
	<div class="text-center"><input type="submit"  class="btn btn-primary btn-sm" value="Guardar"></div>
</form>
<br>
<br>




<script>
  $( function() {
    var availableProductos = [
	@foreach($products as $item)
	'{{$item->name}}',
	@endforeach
    ];
    $( "#product" ).autocomplete({
      source: availableProductos
    });
  } );
  </script>


@endsection	