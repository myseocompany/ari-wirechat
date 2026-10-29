@php

	$questions = $group->customerMetaDataChildren->keyBy('id');
	$residenceQuestion = $questions->get(\App\Models\CustomerMetaData::POE_EU_RESIDENT_QUESTION_ID);
	$eoriQuestion = $questions->get(\App\Models\CustomerMetaData::POE_EORI_QUESTION_ID);
@endphp

@if($residenceQuestion && $eoriQuestion)
<tr class="metadata_item" style="display:none">
	<td>
		<table class="table table-hover" id="table_hover_{{$group->id}}">
			<tr>
				<td><h2>{{$group->value}}</h2></td>
			</tr>
			<tr>
				<td>
					<strong>{{$residenceQuestion->value}}</strong>
					@foreach($residenceQuestion->customerMetaDataChildren as $option)
						<div class="form-check">
							<input class="form-check-input poe-eu-resident" type="radio" required
								name="meta_{{$residenceQuestion->id}}" id="meta_{{$residenceQuestion->id}}_{{$option->id}}" value="{{$option->value}}">
							<label class="form-check-label" for="meta_{{$residenceQuestion->id}}_{{$option->id}}">{{$option->value}}</label>
						</div>
					@endforeach
				</td>
			</tr>
			<tr id="poe_eori_block" style="display:none">
				<td>
					<div class="alert alert-warning" role="alert" id="poe_eori_notice">
						<strong>Importante: número EORI</strong>
						@include('POE.partials.eori_notice')
					</div>

					<strong>{{$eoriQuestion->value}}</strong>
					@foreach($eoriQuestion->customerMetaDataChildren as $option)
						<div class="form-check">
							<input class="form-check-input poe-eori" type="radio" required disabled
								name="meta_{{$eoriQuestion->id}}" id="meta_{{$eoriQuestion->id}}_{{$option->id}}" value="{{$option->value}}">
							<label class="form-check-label" for="meta_{{$eoriQuestion->id}}_{{$option->id}}">{{$option->value}}</label>
						</div>
					@endforeach

					<div class="alert alert-danger mt-3" role="alert" id="poe_eori_reminder" style="display:none">
						@include('POE.partials.eori_notice')
					</div>

					<script>
						// Residencia UE/Reino Unido = Sí muestra el aviso y la pregunta EORI;
						// si no, se deshabilita para que no se envíe ni bloquee el formulario.
						$(function () {
							function updateEoriBlock() {
								var isResident = $('.poe-eu-resident:checked').val() === @json(\App\Models\CustomerMetaData::POE_EU_RESIDENT_YES);
								$('#poe_eori_block').toggle(isResident);
								$('.poe-eori').prop('disabled', !isResident);
								if (!isResident) {
									$('.poe-eori').prop('checked', false);
								}
								var lacksEori = isResident && $('.poe-eori:checked').val() === @json(\App\Models\CustomerMetaData::POE_EORI_NO);
								$('#poe_eori_reminder').toggle(lacksEori);
							}

							$('.poe-eu-resident, .poe-eori').on('change', updateEoriBlock);
							updateEoriBlock();
						});
					</script>
				</td>
			</tr>
		</table>
	</td>
</tr>
@endif
