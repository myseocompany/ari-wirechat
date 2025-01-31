@extends('layout')

@section('content')

<div id="order-body">
<div><a href="/orders">Ordenes</a> >> ver</div>
<h1>Agregar Productos </h1>

		<div class="row">
			<div class="col-md-4 col-sm-12 group-container">
				<h3 class="title">Datos personales</h3>
				<div class="row">
	
					
			
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Nombre:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->name}}">
								<input type="text" value="{{$model->customer->name}}" id="name" name="name" class="form-control borderless readonly" readonly>
								<input type="hidden" value="{{$model->customer->id}}" id="customer_id" name="customer_id" class="form-control borderless readonly" readonly>
								
						</div>
						</div>
					</div>
					
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">No de identificación:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->document}}">
								{{$model->document}}
								<input type="text" value="{{$model->customer->document}}" id="document" name="document"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
					<div class="col-md-12 col-sm-12 d-none">
						<div class="row">
							<label class="col-6">Cargo:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->position}}">
								{{$model->position}}
								<input type="text" value="{{$model->customer->position}}" id="position" name="position"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
					<div class="col-md-12 col-sm-12 d-none">
						<div class="row">
							<label class="col-6">Indicativo:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->area_code}}">
								{{$model->area_code}}
								<input type="text" value="{{$model->customer->area_code}}" id="area_code" name="area_code"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Teléfono:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->phone}}">
								{{$model->phone}}
								<input type="text" value="{{$model->customer->phone}}" id="phone" name="phone"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Email:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->email}}">
								<input type="text" value="{{$model->customer->email}}" id="email" name="email"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Dirección:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->address}}">
								<input type="text" value="{{$model->customer->address}}" id="address" name="address"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
	
	
					<div class="col-md-12 col-sm-12 d-none">
						<div class="row">
							<label class="col-6">Ciudad:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->city}}">
								<input type="text" value="{{$model->customer->city}}" id="city" name="city"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
	
	
					<div class="col-md-12 col-sm-12 d-none">
						<div class="row">
							<label class="col-6">Provincia y/o estado:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->department}}">
								<input type="text" value="{{$model->customer->department}}" id="department" name="department"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">País:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->country}}">
								<input type="text" value="{{$model->customer->country}}" id="country" name="country"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Teléfono de contacto:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->contact_phone2}}">
								<input type="text" value="{{$model->customer->contact_phone2}}" id="contact_phone2" name="contact_phone2"  class="form-control borderless readonly" readonly>
								
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
								<input type="text" value="{{$model->customer->business}}" id="business" name="business" class="form-control borderless readonly" readonly>
							
						</div>
						</div>
					</div>
					
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">NIT:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->business_document}}">
								{{$model->business_document}}
								<input type="text" value="{{$model->customer->business_document}}" id="business_document" name="business_document"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
	
					<div class="col-md-12 col-sm-12 d-none">
						<div class="row">
							<label class="col-6">Indicativo:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->business_area_code}}">
								{{$model->business_area_code}}
								<input type="text" value="{{$model->customer->business_area_code}}" id="business_area_code" name="business_area_code"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Teléfono:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->business_phone}}">
								{{$model->business_phone}}
								<input type="text" value="{{$model->customer->business_phone}}" id="business_phone" name="business_phone"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Email:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->business_email}}">
								<input type="text" value="{{$model->customer->business_email}}" id="business_email" name="business_email"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Dirección:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->business_address}}">
								<input type="text" value="{{$model->customer->business_address}}" id="business_address" name="business_address"  class="form-control borderless readonly" readonly>
								
							</div>
						</div>
					</div>
	
	
	
					<div class="col-md-12 col-sm-12 d-none">
						<div class="row">
							<label class="col-6">Ciudad:</label>
							<div readonly id="" name="" class="col-6" value="{{$model->city}}">
								<input type="text" value="{{$model->customer->business_city}}" id="business_city" name="business_city"  class="form-control borderless readonly" readonly>
								
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
								
								<input type="text" class="form-control borderless readonly" readonly id="delivery_address" name="delivery_address" value="{{$model->delivery_address}}">
	
							</div>
						</div>
					</div>
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Ciudad:</label>
							<div readonly id="" name="" class="col-6" >
								
								<input type="text" class="form-control borderless readonly" readonly id="delivery_city" name="delivery_city" value="{{$model->delivery_city}}">
	
							</div>
						</div>
					</div>
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">País:</label>
							<div readonly id="" name="" class="col-6" >
								
								<input type="text" class="form-control borderless readonly" readonly id="delivery_country" name="delivery_country" value="{{$model->delivery_country}}">
	
							</div>
						</div>
					</div>
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Código postal:</label>
							<div readonly id="" name="" class="col-6" >
								
								<input type="text" class="form-control borderless readonly" readonly id="delivery_postal_code" name="delivery_postal_code" value="{{$model->delivery_postal_code}}">
	
							</div>
						</div>
					</div>
					<!--
					<div class="col-md-12 col-sm-12">
						<div class="row">
							<label class="col-6">Estado de la orden:</label>
							<div readonly id="" name="" class="col-6" >
								
								<select name="status_id" id="status_id"  class="form-control" readonly>
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
								<select name="user_id" id="user_id"  class="form-control" >
									@foreach($users as $item)
									<option value="{{$item->id}}"  @if ($model->user_id  == $item->id) selected="selected" @endif>{{$item->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
	
			-->
					
					
				</div>
			</div>
		</div>
		
		

<br>
<br>


<div class="row">
	<div class="col">
		
<table class="table table-striped">
	<thead>
		<tr>
			<th>Referencia</th>
			<th class="text-center ">Cantidad</th>
			<th class="text-center ">Descripción</th>
			<th class="text-center ">Valor Unitario</th>
			<th>Descuento</th>
			<th>Valor Total</th>
			
			<th></th>
			
		</tr>
	
	</thead>
	<tbody>

		
			
		<?php $total=0; ?>
		@if(isset($model->productList)) 
			@foreach($model->productList as $item)
			
			<tr>
				<td>
					@if(isset($item->product)){{$item->product->name}}@endif
				</td><td class="text-center ">{{$item->quantity}}
				</td>
				<td class="text-center "> {{$item->description}}
				</td>
				<td class="text-center "> $ {{number_format($item->price,0)}}
				</td>
				
				<td class="text-center ">{{$item->discount}}%
				</td>
				<td class="text-center ">$ {{number_format($item->total,0)}} </td>
				<td>  
				<input type="submit" value="-" data-toggle="modal" data-target="#destroy_{{$item->id}}" class="btn btn-primary btn-sm" size="3">
					
					<div class="modal" id="destroy_{{$item->id}}">
    				<div class="modal-dialog">
      				<div class="modal-content">
						  <div class="modal-header">
         					 <h4 class="modal-title">¿Esta seguro que desea eliminar?</h4>
        				  <button type="button" class="close" data-dismiss="modal">&times;</button>
       					 </div>
						 <div class="modal-body">
							<p>@if(isset($item->product)){{$item->product->name}}@endif</p>
						 <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
						 <a href="/order/product/{{ $item->id }}/destroy"> 
							<input type="" value="Sí" data-toggle="modal" data-target="#destroy_{{$item->id}}" class="btn btn-primary" size="3"></a>
						 </div>

						</div>
						</div>
						</div>
					</td>
					<td></td>
			</tr>
		
			<?php $total += $item->total; ?>
			@endforeach
		@endif
		<form action="/order/storeproduct"  method="POST">

			{{ csrf_field() }}
			
			<tr>
				
				<td class="text-center ">
					<input type="text" id="product" name="product" value="" size="10" required="required">

					<input type="hidden" id="order_id" name="order_id" value="{{$model->id}}" ></td>
				
				<td class="text-center "><input class="number" type="text" id="quantity" name="quantity" value="1" size="4" onkeyup="totalDiscount()" onchange="totalDiscount()"></td>
				<td class="text-center ">
					<textarea class="" cols="40" rows="10" type="text" id="description" name="description" value="1" onkeyup="totalDiscount()" onchange="totalDiscount()">
					</textarea>
				</td>
				
				<td class="text-center "><input class="number" type="number" id="price" name="price" value="" size="7" onkeyup="totalDiscount()" onchange="totalDiscount()"> </td>
				<td class="text-center "><input class="number" type="number" id="dscto" name="dscto" value="0" placeholder="0" style="width: 60%; margin-right: 10px;" onkeyup="totalDiscount()" onchange="totalDiscount()"></td>
				<td class="text-center "><input class="number" type="text" id="totalProduct" name="totalProduct" value="0" style="width:100px;" readonly  ></td>
				<td><input type="submit" value="+" class="btn btn-primary btn-sm" size="3"></td>
			</tr>
			
		</form>	

		<tr>
			<td colspan="1"></td>
			<td class="text-right display-4">Total</td>
			<td colspan="4" class=" display-4 text-right ">$ {{number_format($model->getTotal(), 0)}}</td>
			<td></td>
		</tr>
	
	</tbody>
</table>
<form id="updateOrder" method="POST" action="/orders/{{$model->id}}/update">
	{{ csrf_field() }}
	<div class="row">
		<div class="col">
			<textarea name="notes" id="notes" cols="100%" rows="4" class="form-control " >{{$model->notes}}</textarea>
		</div>
	
	</div>
	<div class="row">
	
	<div class="col-md-12 col-sm-12" style="text-align: -webkit-right;">
			<a href="/orders/{{$model->id}}/show">
				<input type="button" class="btn btn-primary" value="Guardar" >
				
			
			</a>
	</div>
	
</form>
</div>
	</div>
</div>




<script>
	function totalDiscount(){
		
		if($('#dscto').val() != 0)
			$('#totalProduct').val( ((100-$('#dscto').val())/100) * $('#price').val()  * $('#quantity').val());
		else
			$('#totalProduct').val($('#price').val()* $('#quantity').val());
		
	}
	function updateOrder(){
		var productPrice = -1;
		
		// busco el indice en los nomnbres
		index = availableProducts.indexOf(selectedProduct);
		console.log(index);

		// actualiza el precio que tenga el mismo indice
		$("#price").val(productPrices[index]);
		$("#description").val(productDescriptions[index]);
		
		totalDiscount();
	}
	var availableProducts=[];
	var productPrices =[];
	var productDescriptions =[];
	
	var selectedProduct = "";

	availableProducts = [
		@foreach($products as $item) '{{$item->name}}', @endforeach
		];

	productPrices = [
		@foreach($products as $item) '{{$item->price}}', @endforeach
	];

	productDescriptions = [
		@foreach($products as $item) '{{$item->description}}', @endforeach
	];

	$(document).ready(function() {
		$( "#product" ).autocomplete({
			source:  availableProducts,
			select: function(event, ui) {
				selectedProduct = ui.item.value;
				updateOrder();
			}
		});
		
	});

  
  </script>

<script>




</script>

</div> <!-- fin de order body --->
@endsection	