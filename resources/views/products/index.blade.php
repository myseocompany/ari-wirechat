@extends('layout')

@section('content')

<h1>Productos</h1>

<div><a href="/products/create">Crear+</a></div>
<table class="table table-hover">
	<thead>
		<tr>
			<th>Producto</th>
			<th>Referencia</th>
			<th class="">Precio</th>
			<th class="">Moneda</th>
			
			<th class="bg-primary">Precio en Colombia</th>
			<th class="bg-warning">Precio en Ámerica</th>
			<th class="bg-warning">Flete en Ámerica</th>
			<th class="bg-warning">Total precio en Ámerica</th>
			<th class="bg-secondary">Precio en Ecuador</th>
			<th class="bg-secondary">Flete en Ecuador</th>
			<th class="bg-secondary">Total precio en Ecuador</th>
			<th class="bg-success">Precio en Europa</th>
			<th class="bg-success">Flete en Europa</th>
			<th class="bg-success">Total precion en Europa</th>
		</tr>
	</thead>
	<tbody>
		
		@foreach($model as $item)
		<tr>
			<td><a href="/products/{{$item->id}}/edit/">{{$item->name}}</a></td>
			<td>{{$item->alias}}</td>
			<td class="table-primary">{{number_format($item->price)}}</td>
			<td class="table-primary">{{$item->coin}}</td>
			<td class="table-primary">{{number_format($item->colombia_price)}}</td>
			<td class="table-warning">{{number_format($item->america_price)}}</td>
			<td class="table-warning">{{number_format($item->america_shipping)}}</td>
			<td class="table-warning">{{number_format($item->america_shipping + $item->america_price)}}</td>
			<td class="table-secondary">{{number_format($item->ecuador_price)}}</td>
			<td class="table-secondary">{{number_format($item->ecuador_shipping)}}</td>
			<td class="table-secondary">{{number_format($item->ecuador_total_price)}}</td>
			<td class="table-success">{{number_format($item->europa_price)}}</td>
			<td class="table-success">{{number_format($item->europa_shipping)}}</td>
			<td class="table-success">{{number_format($item->europa_price + $item->europa_shipping)}}</td>
		</tr>
		@endforeach
	</tbody>
</table>

@endsection