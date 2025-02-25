
@extends('layout')
@section('content')
<!--
<select class="custom-select custom-select-lg mb-12" name = "selectBy">
	
	<option selected disabled>Ver Duplicados por</option>
	<option value="1">Email</option>
	<option value="2">Phone</option>

	
</select>
	-->
	<br>
<input class="form-control" id="myInput" type="text" placeholder="Busca o escribe...">


<div class="table-wrapper-scroll-y my-custom-scrollbar">

	<table class="table table-striped mb-0">
		<thead>
			<tr>
				<th class="th-sm">#</th>
				<th id="title" class="th-sm">Email</th>
				<th class="th-sm">Count duplicate</th>

			</tr>
		</thead>

		<tbody class="listEmail">
			<?php $i = 1?>
			@foreach($model as $item)


			<tr >

				<td>{{$i++}}</td>
				<td class="col-11">

					<h5 class="mb-0">
						<button class="col-md-12 text-left getEmail btn btn-link" data-email="{{$item->email}}">
							<a href="/optimize/customers/consolidateDuplicates/?email={{$item->email}}">{{$item->email}}</a>	
						</button>
					</h5>

				</td>
				<td class="col-1 center">
					{{$item->count}}
				</td>


			</tr>

			@endforeach
		</tbody>
		

	</table>
</div>

@endsection