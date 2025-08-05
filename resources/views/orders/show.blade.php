@php
    \Carbon\Carbon::setLocale('es');
@endphp
@extends('layout')

@section('content')
<div id="show-order-body">
	<div><a href="/orders">Ordenes</a> >> ver</div>
<h1>Orden {{$model->id}}</h1>


<div class="col-md-12 col-sm-12">
		<a href="/orders/{{$model->id}}/edit">
			Editar 
		</input>
		</a>
		|
		<a href="/orders/{{ $model->id }}/delete"> Eliminar</a>
		<!--
		|
		<a href="/orders/{{ $model->id }}/proforma"> Proforma Internacional</a>
		|
		<a href="/orders/{{ $model->id }}/proforma_co"> Proforma Nacional</a>
-->

	</div>
<form action="/orders/{{$model->id}}/update" method="POST" class="form-group">
	<div class="row">
		<div class="col-md-4 col-sm-12 group-container">
    <h3 class="title">Datos de facturación</h3>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="row">
                <label class="col-6">Nombre:</label>
                <div class="col-6">
                    <input type="text" value="{{ $model->billing_name }}" class="form-control borderless readonly" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="row">
                <label class="col-6">Documento:</label>
                <div class="col-6">
                    <input type="text" value="{{ $model->billing_document }}" class="form-control borderless readonly" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="row">
                <label class="col-6">Teléfono:</label>
                <div class="col-6">
                    <input type="text" value="{{ $model->billing_phone }}" class="form-control borderless readonly" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="row">
                <label class="col-6">Celular:</label>
                <div class="col-6">
                    <input type="text" value="{{ $model->billing_phone2 }}" class="form-control borderless readonly" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="row">
                <label class="col-6">Email:</label>
                <div class="col-6">
                    <input type="text" value="{{ $model->billing_email }}" class="form-control borderless readonly" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="row">
                <label class="col-6">Dirección:</label>
                <div class="col-6">
                    <input type="text" value="{{ $model->billing_address }}" class="form-control borderless readonly" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="row">
                <label class="col-6">Ciudad:</label>
                <div class="col-6">
                    <input type="text" value="{{ $model->billing_city }}" class="form-control borderless readonly" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="row">
                <label class="col-6">País:</label>
                <div class="col-6">
                    <input type="text" value="{{ $model->billing_country }}" class="form-control borderless readonly" readonly>
                </div>
            </div>
        </div>
    </div>
</div>

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

				<div class="col-md-12 col-sm-12 d-nones">
					<div class="row">
						<label class="col-6">Cargo:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->position}}">
							{{$model->position}}
							<input type="text" value="{{$model->customer->position}}" id="position" name="position"  class="form-control borderless readonly" readonly>
							
						</div>
					</div>
				</div>
				<div class="col-md-12 col-sm-12 d-nones">
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



				<div class="col-md-12 col-sm-12 d-nones">
					<div class="row">
						<label class="col-6">Ciudad:</label>
						<div readonly id="" name="" class="col-6" value="{{$model->city}}">
							<input type="text" value="{{$model->customer->city}}" id="city" name="city"  class="form-control borderless readonly" readonly>
							
						</div>
					</div>
				</div>



				<div class="col-md-12 col-sm-12 d-nones">
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


				<div class="col-md-12 col-sm-12 d-nones">
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



				<div class="col-md-12 col-sm-12 d-nones">
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
						<div readonly id="" name="" class="col-6" >
							
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
						<div readonly id="" name="" class="col-6" value="{{$model->delivery_postal_code}}">
							
							<input type="text" class="form-control borderless readonly" readonly id="delivery_postal_code" name="delivery_postal_code"  value="{{$model->delivery_postal_code}}">

						</div>
					</div>
				</div>
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Estado de la orden:</label>
						<div readonly id="" name="" class="col-6" >
							
							<select disabled name="status_id" id="status_id"  class="form-control">
								@foreach($statuses as $item)
								<option value="{{$item->id}}" @if($model->status_id == $item->id) selected @endif>{{$item->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>
									
				<div class="col-md-12 col-sm-12">
					<div class="row">
						<label class="col-6">Atendido por:</label>
						<div  id="" name="" class="col-6">
							
							<select readonly disabled name="user_id" id="user_id"  class="form-control">
								@foreach($users as $item)
								<option value="{{$item->id}}"  @if ($model->user_id  == $item->id) selected="selected" @endif>{{$item->name}}</option>
								@endforeach
							</select>
						
						</div>
					</div>
				</div>				
				
				
				
			</div>
		</div>

	</div>
</form>
<br>
<br>
<div class="row">
	<div class="col-md-12">
		<label > Notas:</label>
		<div readonly id="" name="" >
			{{$model->notes}}
		</div>
		
	</div>	
</div>			
			


<br>
<br>
<h2>Productos en la orden</h2>

<table class="table table-striped">
	<thead>
		<tr>
			<th>Referencia</th>
			<th class="text-center ">Cantidad</th>
			<th class="text-center ">Descripción</th>
			
			<th class="text-center ">Valor Unitario</th>
			<th class="text-center "> Descuento</th>
			<th class="text-center "> Valor Total</th>
			<th></th>
		</tr>
	
	</thead>
	<tbody>
		<?php $total=0; ?>
		@if(isset($model->productList)) 
		@foreach($model->productList as $item)
			
			<tr>
				<td>@if(isset($item->product)){{$item->product->name}}@endif</td>
				<td class="text-center ">{{$item->quantity}}</td>
				<td class="text-center ">{{$item->description}}</td>
				<td class="text-center "> $ {{number_format($item->price,0)}}</td>
				<td class="text-center ">{{$item->discount}}%</td>
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
			</tr>
		
			<?php $total += $item->total; ?>
			@endforeach
		@endif
		

		<tr>
			<td></td>
			<td class="text-right display-4" colspan="2">Total</td><td class=" display-4 text-center " colspan="5">$ {{number_format($model->getTotal(), 0)}}</td>
		</tr>
	</tbody>
</table>
<div class="row">

<div class="col-md-12 col-sm-12" style="text-align: -webkit-right;">
		<a href="/orders/{{$model->id}}/add/product">
			<input type="button" class="btn btn-primary" value="Agregar productos" >
			
		</input>
		</a>
	</div>

</div>
<br>
<br>

</div>

@include('orders.histories_list')




@endsection