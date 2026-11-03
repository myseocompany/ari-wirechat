@extends('api')




@section('content')

<div class="container mt-4">
	<div class="poe_logo" style="text-align:center"><img src="{{ asset('img/logo-mqe-2026.png') }}" alt="MQE Maquiempanadas" style="height:60px;width:auto"></div>
	

	@if($errors->any())
	<div class="alert alert-danger" role="alert" id="poe_errors">
		<strong>Revise el formulario:</strong>
		<ul class="mb-0">
			@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
		</ul>
	</div>
	@endif

	<form method="POST"  action="{{ $storeUrl }}" class="form-control">
	 {{ csrf_field() }}




	<table class="table">
		<tbody>

<br><br>
      <tr>
		<center><h1>{{$model->name}}</h1></center>
    <div>Con este formulario se realizarán todos lo procesos aduaneros de legalización de la mercancía del país de destino.
		</div>
		<div>Por favor marque la categoría a la que corresponde: </div>
	<label ><strong>Importación de manera: </strong></label>
      <select name="meta_{{ \App\Models\CustomerMetaData::POE_IMPORT_TYPE_ID }}" id="category" class="form-control" onchange="updateForm(this)">
        <option value="">Seleccione..</option>
        @foreach(\App\Models\CustomerMetaData::POE_IMPORT_TYPES as $importType)
        <option value="{{ $importType }}" @selected(old('meta_'.\App\Models\CustomerMetaData::POE_IMPORT_TYPE_ID) === $importType)>{{ $importType }}</option>
        @endforeach
    </select>
    </tr>

			@if(isset($campaign->customerMetaData))
			@foreach($campaign->customerMetaData as $item)
			@if($item->id == \App\Models\CustomerMetaData::POE_RESIDENCE_GROUP_ID)
				@include('POE.partials.residencia_eori', ['group' => $item])
			@else

			<tr class="metadata_item" style="display:none">
				<td>
					<table class="table table-hover"  id="table_hover_{{$item->id}}">
								<tr>			
									<td><h2>{{$item->value}}</h2></td>

								</tr>
						<tr>

							@if(true)
							<?php //dd($item->customerMetaDataChildren); ?>
							@foreach($item->customerMetaDataChildren as $itemChild)<!-- hijos -->
							
								@if($item->id==$itemChild->parent_id)

									<tr>
										<td>

										@if($item->isMultiple())
                    <strong>{{$itemChild->value}}</strong>
					
											<input required="required" type="{{$itemChild->getInputType()}}" 
											name="meta_{{$itemChild->id}}" placeholder="{{$itemChild->value}}" @if($itemChild->getInputType()=="text") value="{{ old('meta_'.$itemChild->id) }}" @elseif($itemChild->getInputType()=="checkbox" && old('meta_'.$itemChild->id)) checked @endif 
											
											class="@if($itemChild->getInputType()!="checkbox")form-control @else form-check-input @endif @if($itemChild->parent_id==14)bussinesMetaData @endif"
											@if($itemChild->getInputType()=="checkbox") required="required" @endif>
												
											  
											 @else
											  
											  	@if($item->getInputType()=='text')

											  		@if($itemChild->parent_id==31)
																										
																 <div class="row" id="{{$itemChild->id}}">

															 	</div>
											  				
											  			@else	

												 		<input required type="{{$item->getInputType()}}"  name="meta_{{$itemChild->id}}" class="input form-control campo" value="{{ old('meta_'.$itemChild->id) }}"  >
												 	@endif	
												 				
												@else
												{{$itemChild->value}}

												@if($item->getInputType()=='file')

												@else
													<textarea id="msg"  name="meta_{{$itemChild->id}}" class="textinput form-control campo" value=""  required>{{ old('meta_'.$itemChild->id) }}</textarea>
												 @endif	
											 @endif		
										@endif
											
										</td>
									</tr>
								@endif
						
							@endforeach	
							@endif
				 
						</tr>
					</table>
				</td>
			</tr>
			@endif
			@endforeach
				@endif
		</tbody>

	</table>

	<center>
			<input type="submit" class="btn btn-primary campo" value="Guardar" id="btn_submit" style="display:none">
	</center>
</form>

@include('POE.partials.footer')
</div>

<style>
	.table td, .table th {
    border-top: 1px solid #f4f4f8 !important;
	}

	body {
    background-color: #f4f4f8;
	}
		
	.textinput {
		width: 100%;
		min-height: 75px;
		outline: none;
		resize: none;
	}
	.input {
		width: 100%;
		min-height: 40px;
		outline: none;
		resize: none;
	}
	section#main-content {
    	margin: 0px 0px 0px 0px !important;
	}
	nav.navbar.navbar-expand-md.navbar-white.fixed-top.bg-white.container-fluid {
		/*visibility: hidden;*/
	}
	</style>




<script>
	
	function updateForm(  ){
		// oculto / muestro la fila del formulario
		
		select = document.getElementById("category");
		console.log(select.value + " ini");
		if(select.value === "" ){
			$('.metadata_item').hide();
			$("#btn_submit").hide();
		}
		else{
			$('.metadata_item').show();
			$("#btn_submit").show();
		}
			

		if (select.value !== "Individual") {
            $("#table_hover_14").show();
            $('.bussinesMetaData').prop('required', true);
        } else {
            $("#table_hover_14").hide();
            $('.bussinesMetaData').removeAttr('required');
            }

	}
	
	$( document ).ready(function() {
        console.log( "document loaded" );
		$('.metadata_item').hide();
		// Tras un error de validación el formulario vuelve con el tipo ya elegido
		if ($('#category').val() !== "") {
			updateForm();
		}

    });
	
</script>
@endsection
