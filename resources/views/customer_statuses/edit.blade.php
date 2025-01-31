@extends('layout')

@section('content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.js"></script>

<h1>Editar Estado de la Herramientas</h1>
<form method="POST" action="/customer_statuses/{{$customer_statuses->id}}/update">
{{ csrf_field() }}
  <div class="form-group">
    <label for="name">Nombre:</label>
    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required="required" value="{{$customer_statuses->name}}">
  </div>
  <div class="form-group">
    <label for="description">Descripción:</label>
    <input type="text" class="form-control" id="description" name="description" placeholder="Name" required="required" value="{{$customer_statuses->description}}">
  </div>
     <div class="form-group">
    <label for="weight">Peso:</label>
    <input type="text" class="form-control" id="weight" name="weight" placeholder="Peso" required="required" value="{{$customer_statuses->weight}}">
  </div>

  <div class="form-group">
  	<label for="color">Color:</label>
  	<input id="showColor" style="background-color: {{$customer_statuses->color}};width: 3%; border: 1px solid transparent;" readonly>
  	<input type="text" class="form-control" id="color" name="color" value="{{$customer_statuses->color}}" style="width: 30%;">
  </div>
  <input type="hidden" name="stage_id" id="stage_id" value="1">
  
  <button type="submit" class="btn btn-sm btn-primary">Enviar</button>
</form>

<script>
	$('#color').colorpicker({});
	$('#color').on('change', function() {
		$('#showColor').css('background-color',$(this).val())
	});
</script>
@endsection