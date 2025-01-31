@extends('layout')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.js"></script>

<h1>Crear Estado de los Usuarios</h1>
<form method="POST" action="/customer_statuses">
{{ csrf_field() }}
  <div class="form-group">
    <label for="name">Nombre:</label>
    <input type="text" class="form-control" id="name" name="name" placeholder="Nombre" required="required">
  </div>
  <div class="form-group">
    <label for="description">Description:</label>
    <input type="text" class="form-control" id="description" name="description" placeholder="Descripción" required="required">
  </div>
   <div class="form-group">
    <label for="weight">Peso:</label>
    <input type="text" class="form-control" id="weight" name="weight" placeholder="Peso" required="required">
  </div>

  <div class="form-group">
  	<label for="color">Color:</label>
  	<input id="showColor" style="background-color: rgb(255, 255, 255);width: 3%; border: 1px solid transparent;" readonly>
  	<input type="text" class="form-control" id="color" name="color" value="rgb(255, 255, 255)" style="width: 30%;">
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