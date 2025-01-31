@extends('layout')

@section('content')

<h1>Tipos de acciones</h1>

<div class="col-12" style="margin-bottom: 10px;">
  <div><h5>Agregar acción</h5></div>
  <form method="POST" action="/action_type/create">
  {{ csrf_field() }}
  <div class="form-inline">
    <div class="form-group">
      <label for="name" style="margin-right: 10px;">Nombre:</label>
      <input type="text" class="form-control" id="name" name="name" style="margin-right: 10px;" />
    </div>
    <div class="form-group">
      <label for="name" style="margin-right: 10px;">Descripción:</label>
      <input type="text" class="form-control" id="description" name="description" style="margin-right: 10px;" />
    </div>
    <input type="hidden" id="updated_at" name="updated_at" value="" readonly />
    <button type="submit" class="btn btn-primary">Agregar</button>
  </div>
  </form>
</div>

<table class="table table-striped">
	<thead>
		<td><strong>Id</strong></td>
		<td><strong>Nombre</strong></td>
    <td><strong>Descripción</strong></td>
		<td><strong>Opciones</strong></td>
	</thead>
	<tbody>
	@foreach ($model as $item)
		<tr>
			<td>{{$item->id}}</td>
			<td><a href="/action_type/{{$item->id}}/show">{{$item->name}}</a></td>
      <td>{{$item->description}}</td>
			<td>
        <a href="/action_type/{{$item->id}}/destroy"><span class="btn btn-sm btn-danger fa fa-trash-o" aria-hidden="true" title="Eliminar"></span></a>
        <a href="/action_type/{{$item->id}}/edit"><span class="btn btn-sm btn-warning fa fa-pencil" aria-hidden="true" title="Editar"></span></a>
      </td>
		</tr>
	@endforeach
	</tbody>
</table>

<div class="col-12" style="margin-bottom: 10px;">
  <div><h5>Agregar acción</h5></div>
  <form method="POST" action="/action_type/create">
  {{ csrf_field() }}
  <div class="form-inline">
    <div class="form-group">
      <label for="name" style="margin-right: 10px;">Nombre:</label>
      <input type="text" class="form-control" id="name" name="name" style="margin-right: 10px;" />
    </div>
    <div class="form-group">
      <label for="name" style="margin-right: 10px;">Descripción:</label>
      <input type="text" class="form-control" id="description" name="description" style="margin-right: 10px;" />
    </div>
    <button type="submit" class="btn btn-primary">Agregar</button>
  </div>
  </form>
</div>

@endsection