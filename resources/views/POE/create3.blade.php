@extends('api')




@section('content')

<div class="container mt-4">
	<div class="poe_logo" style="text-align:center"><img src="{{ asset('img/Logo_MQE_normal-40px.png') }}" alt=""></div>
	

	<form method="POST"  action="/metadata/{{$model->id}}/store/poe" class="form-control">
	 {{ csrf_field() }}




	<table class="table">
		<tbody>
			<input type="hidden" name="campaign_id" value="{{$campaign->id}}" class="form-control">
			<input type="hidden" name="audience_id" value="67" class="form-control">

<br><br>
      <tr>
		<center><h1>{{$model->name}}</h1></center>
    <div>Con este formulario se realizarán todos lo procesos aduaneros de legalización de la mercancía del país de destino.
		</div>
		<div>Por favor marque la categoría a la que corresponde: </div>
	<label ><strong>Importación de manera: </strong></label>
      <select name="meta_34" id="category" class="form-control" onchange="updateForm(this)">
        <option value="">Selecione..</option>
        <option value="Individual">Individual</option>
        <option value="Sociedad">Sociedad</option>
        <option value="Sociedad de responsabilidad limitada">Sociedad de responsabilidad limitada</option>
        <option value="Corporación">Corporación</option>
        <option value="Propietario único">Propietario único</option>
        <option value="Compañía de responsabilidad limitada">Compañía de responsabilidad limitada</option>
    </select>
    </tr>

			@if(isset($campaign->customerMetaData))
			@foreach($campaign->customerMetaData as $item)
		
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
					
											<input type="{{$itemChild->getInputType()}}" 
											name="meta_{{$itemChild->id}}" placeholder="{{$itemChild->value}}"  
											
											class="@if($itemChild->getInputType()!="checkbox")form-control @else form-check-input @endif" 
											@if($itemChild->getInputType()=="checkbox") required="required" @endif>
												
											  
											 @else
											  
											  	@if($item->getInputType()=='text')

											  		@if($itemChild->parent_id==31)
																										
																 <div class="row" id="{{$itemChild->id}}">

															 	</div>
											  				
											  			@else	

												 		<input type="{{$item->getInputType()}}"  name="meta_{{$itemChild->id}}" class="input form-control campo" value=""  >
												 	@endif	
												 				
												@else
												{{$itemChild->value}}

												@if($item->getInputType()=='file')

												@else
													<textarea id="msg"  name="meta_{{$itemChild->id}}" class="textinput form-control campo" value=""  required></textarea>
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
			@endforeach
				@endif
		</tbody>

	</table>

	<center>
			<input type="submit" class="btn btn-primary campo" value="Guardar" id="btn_submit" style="display:none">
	</center>
</form>
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
	nav.navbar.navbar-expand-md.navbar-white.fixed-top.bg-white.container {
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
			

		if(select.value !== "Individual" )
			$("#table_hover_14").show();
		else
			$("#table_hover_14").hide();
		
	}
	
	$( document ).ready(function() {
        console.log( "document loaded" );
		$('.metadata_item').hide();

    });
	
</script>
@endsection