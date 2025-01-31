@extends('empty')@section('content')
<?php 
	function hasSemi($model){
		$response = false;

		if(isset($model->product) && ($model->product->getPrice("semi")!=0) && ($model->product->getPrice("semi")!=""))
			$response = true;

		return $response;
	}

	function hasFull($model){
		$response = false;
		if(isset($model->product) && ($model->product->getPrice("full")!=0) && ($model->product->getPrice("full")!=""))
			$response = true;

		return $response;
	}
 ?>
<style type="text/css">
	thead tr td:nth-child(odd){
		/*background-color: #ccc;
		*/
		font-weight: bolder;
		text-transform: uppercase;
		text-align: right;
	}
	thead tr td:nth-child(even){
		background-color: #eee;
		
	}
	tbody tr td{
		background-color: #eee;
		text-align: center;
	}

	tbody tr td:nth-child(1){
		font-weight: bolder;
		text-transform: uppercase;
		background-color: #fff;
		text-align: right;
		
	}
</style>
<table class="" border="1" id="content">
	<thead>
	<tr>
		<td colspan="4" >
			<img src="https://trujillogutierrez.com.co/site/images/logo_trujillo_gutierrez_gris_rojo.png" alt="" class="d-inline" width="200">
		</td>
	</tr>
	<td class="text-center subtitle" colspan="4"><h2>PRODUCTOS</h2></td>
	<tr>
		<td>PROYECTO</td>
		
		<td>@if(isset($model->product)) {{ $model->product->getProjectName() }} @endif</td>
		
		<td>FECHA</td>
		<td>{{ $model->getQuoteDate() }}</td>
		
	</tr>
	<tr>
		<td>COTIZACIÓN No</td>
		<td>{{ $model->id }}</td>
		<td>VIGENCIA</td>
		<td>{{ $model->getValidityDate() }}</td>
	</tr>
	@if(isset($model->customer))
	<tr>
		<td>NOMBRE CLIENTE</td>
		<td>{{ $model->customer->name }}</td>
		<td>CELULAR CLIENTE</td>
		<td>{{ $model->customer->phone }}</td>
	</tr>
	<tr>
		<td>EMAIL CLIENTE</td>
		<td>{{ $model->customer->email }}</td>
		<td>% DE CUOTA INICIAL</td>
		<td>{{ $model->initial_installment_percent}} % </td>
	</tr>
	@endif
	<!-- DATOS DEL APARTAMENTO -->
	<tr>
		<td>Apto No</td>
		
		{{-- {{dd($model->product)}} --}}
		<td>@if(isset($model->product)){{$model->product->name}}@endif</td>
		<td>UBICACIÓN</td>
		<td>@if(isset($model->product)){{$model->product->location}}@endif</td>
	</tr>
	
	<tr>
		<td>ÁREA CONSTRUIDA</td>
		<td>{{ $model->product->built_area }}</td>
		<td>ÁREA PRIVADA</td>
		<td>@if(isset($model->product)){{ $model->product->private_area }}@endif</td>
	</tr>
	<tr>
		<td>TIPO</td>
		<td>{{ $model->product->getApartmentTypeName() }}</td>
		<td>DESCRIPCION</td>
		<td>{{$model->product->notes}}</td>
	</tr>
	<tr>
		<td>FECHA ENTREGA ESTIMADA *</td>
		<td>{{ \Carbon\Carbon::parse($model->product->category->delivery_date)->toDateString() }}</td>
		<td>MESES A DIFERIR CUOTA inicial</td>
		{{-- <td>{{ \Carbon\Carbon::parse($model->product->category->delivery_date)->subMonths(2)->toDateString() }}</td> --}}

		<td>{{ $model->getMonthsToDefer() }}</td>
	</tr>
	<tr><td colspan="4"> * Sujeta a cumplimiento punto de equilibrio de ventas</td></tr>
	</thead>
	<tbody>
	<td class="text-center subtitle" colspan="4"><h2>CIERRE FINANCIERO</h2></td>
	
	<tr>
		<td>&nbsp;</td>
		<td>Obra Negra</td>
		<td>@if( hasSemi($model)) Semiterminado @endif</td>
		<td>@if( hasFull($model)) Full Acabado @endif</td>
	</tr>
	<tr>
		<td>Apartamento</td>
		<td>$ {{number_format($model->product->getPrice("black"),0)}}</td>
		<td>@if( hasSemi($model))$ {{number_format($model->product->getPrice("semi"),0)}}@endif</td>
		<td>@if( hasFull($model))$ {{number_format($model->product->getPrice("full"),0)}}@endif</td>
	</tr>

	<tr>
		<td>@if(isset($parking->type->name)) {{$parking->type->name}} @endif</td>
		<td>$ @if(isset($model->parking)){{number_format($model->parking->price,0)}}@endif</td>
		<td>@if(isset($model->parking) && hasSemi($model) )$ {{number_format($model->parking->price,0)}}@endif</td>
		<td>@if(isset($model->parking) && hasFull($model))$ {{number_format($model->parking->price,0)}}@endif</td>
	</tr>
	<tr>
		<td>Deposito</td>
		<td>$ @if(isset($model->deposit)){{number_format($model->deposit->price,0)}}@endif</td>
		<td>@if(isset($model->deposit) && hasSemi($model))$ {{number_format($model->deposit->price,0)}}@endif</td>
		<td>@if(isset($model->deposit) && hasFull($model))$ {{number_format($model->deposit->price,0)}}@endif</td>
	</tr>
	<tr>
		<td>TOTAL</td>
		<td>$ {{number_format($model->getSubtotal("black"),0)}} </td>
		<td>@if( hasSemi($model)) $ {{number_format($model->getSubtotal("semi"),0)}} @endif</td>
		<td>@if( hasFull($model)) $ {{number_format($model->getSubtotal("full"),0)}} @endif</td>
	</tr>

	
	<tr>
		<td class="text-center subtitle" colspan="4"><h2>CUOTA INICIAL</h2></td>
	</tr>
	<tr>
		<td>Cuota inicial {{$model->initial_installment_percent}}%</td>
		<td>$ {{number_format($model->getSubtotal("black")*0.3,0)}} </td>
		<td>@if( hasSemi($model)) $ {{number_format($model->getSubtotal("semi")*0.3,0)}} @endif</td>
		<td>@if( hasFull($model)) $ {{number_format($model->getSubtotal("full")*0.3,0)}} @endif</td>
	</tr>
	<tr>
		<td>Separación</td>
		<td>- $ {{number_format($model->down_payment,0)}} </td>
		<td>@if( hasSemi($model)) - $ {{number_format($model->down_payment,0)}} @endif</td>
		<td>@if( hasFull($model)) - $ {{number_format($model->down_payment,0)}} @endif</td>
	</tr>


	<tr>
		<td>Cesantías o primas</td>
		<td> - $ {{number_format($model->savings_value,0)}} </td>
		<td>@if( hasSemi($model))  - $ {{number_format($model->savings_value,0)}}  @endif</td>
		<td>@if( hasFull($model))  - $ {{number_format($model->savings_value,0)}} @endif</td>
	</tr>

	<tr>
		<td>Subsidios</td>
		<td> - $ {{number_format($model->initial_installment_subsidy,0)}} </td>
		<td>@if( hasSemi($model))  - $ {{number_format($model->initial_installment_subsidy,0)}}  @endif</td>
		<td>@if( hasFull($model))  - $ {{number_format($model->initial_installment_subsidy,0)}} @endif</td>
	</tr>

	<tr>
		<td>Subtotal</td>
		<td>$ {{number_format($model->getInitialInstallment("black"),0)}} </td>
		<td>@if( hasSemi($model)) $ {{number_format($model->getInitialInstallment("semi"),0)}}  @endif</td>
		<td>@if( hasFull($model)) $ {{number_format($model->getInitialInstallment("full"),0)}} @endif</td>
	</tr>

	<tr>
		<td>Valor de la cuota mensual</td>
		<td>$ {{number_format($model->getMontlyInstallment("black"),0)}} </td>
		<td>@if( hasSemi($model)) $ {{number_format($model->getMontlyInstallment("semi"),0)}}  @endif</td>
		<td>@if( hasFull($model)) $ {{number_format($model->getMontlyInstallment("full"),0)}} @endif</td>
	</tr>
		<tr>
		<td colspan="4">
		Los precios están sujetos a cambios sin previo aviso y revisión por parte de la constructora, en todo caso se respetarán los precios para las ventas que se hayan separado con consignación.				
		</td>
	</tr>

	<tr>
		<td class="text-center subtitle" colspan="4"><h2>SIMULACIÓN DE CRÉDITO</h2></td>
	</tr>
	<tr>
		<td>Crédito hipotecario {{100-$model->initial_installment_percent}}%</td>
		<td>$ {{number_format($model->getSubtotal("black")*0.7,0)}} </td>
		<td>@if( hasSemi($model)) $ {{number_format($model->getSubtotal("semi")*0.7,0)}}  @endif</td>
		<td>@if( hasFull($model)) $ {{number_format($model->getSubtotal("full")*0.7,0)}} @endif</td>
	</tr>


	<tr>
		<td>Cuota mensual 30 años UVR aprox</td>
		<td>$ {{number_format($model->getMontlyUVRInstallment("black"),0)}} </td>
		<td>@if( hasSemi($model)) $ {{number_format($model->getMontlyUVRInstallment("semi"),0)}}  @endif</td>
		<td>@if( hasFull($model)) $ {{number_format($model->getMontlyUVRInstallment("full"),0)}} @endif</td>
	</tr>
	<tr>
		<td>Subsidio NO VIS <br> (primero 7 años)</td>
		<td> - $ {{number_format($model->balance_subsidy,0)}} </td>
		<td>@if( hasSemi($model))  - $ {{number_format($model->balance_subsidy,0)}}  @endif</td>
		<td>@if( hasFull($model))  - $ {{number_format($model->balance_subsidy,0)}} @endif</td>
	</tr>
	<tr>
		<td>Cuota mensual aprox <br> (sin seguro de vida)</td>
		<td>$ {{number_format(($model->getMontlyUVRInstallment("black"))-$model->balance_subsidy,0)}} </td>
		<td>@if( hasSemi($model)) $ {{number_format(($model->getMontlyUVRInstallment("semi"))-$model->balance_subsidy,0)}}  @endif</td>
		<td>@if( hasFull($model)) $ {{number_format(($model->getMontlyUVRInstallment("full"))-$model->balance_subsidy,0)}} @endif</td>
	</tr>
	<tr>
		<td>Ingresos necesarios aprox <br> (varían según banco)</td>
		<td>$ {{number_format((($model->getMontlyUVRInstallment("black"))*100)/30,0)}} </td>
		<td>@if( hasSemi($model)) $ {{number_format((($model->getMontlyUVRInstallment("semi"))*100)/30,0)}}  @endif</td>
		<td>@if( hasFull($model)) $ {{number_format((($model->getMontlyUVRInstallment("full"))*100)/30,0)}} @endif</td>
	</tr>
	
	
	
	<td class="text-center subtitle" colspan="4"><h2>OBSERVACIONES</h2></td>
	<tr>
		<td>DESCRIPCION</td>
		<td colspan="4">{{$model->notes}}</td>
	</tr>
		<tr>
	<td colspan="4">
		La simulación del crédito tiene fines informativos y estimaciones de carácter general. Puede presentar variaciones según políticas de la entidad financiera.<br>
		Subsidios de vivienda y de cobertura de tasa están sujetos a vigencias y disponibilidad<br>
		Lo anterior no compromete al promotor o vendedor				
		</td>
	</tr>

	<td class="text-center subtitle" colspan="4"><h2>CONTACTO ASESORA COMERCIAL</h2></td>

	<tr><td>Nombre:	</td><td colspan="3">@if(isset($model->user)){{$model->user->name}} @endif</td></tr>
	<tr><td>Celular:</td><td colspan="3">@if(isset($model->user)){{$model->user->phone}} @endif</td></tr>
	<tr><td>Email:</td><td colspan="3">@if(isset($model->user)){{$model->user->email}} @endif</td></tr>
	<tr><td colspan="4">www.trujillogutierrez.com.co</td></tr>
	<tr><td colspan="4">SIGUENOS EN </td></tr>
	<tr><td colspan="4">facebook.com/trujillogutierrezasociados						</td></tr>
	<tr><td colspan="4">instagram.com/trujillogutierrezsas/						</td></tr>	
	<tr>
		
		<td colspan="4"><img src="{{asset("/public/product_types/" . $model->product->type->image_url)}}" width="800" ></td>
	</tr>
	</tbody>
</table>



@endsection