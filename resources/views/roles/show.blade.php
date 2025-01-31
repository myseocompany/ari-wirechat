@extends('layout')

@section('content')
<h1>{{ $model->name}}</h1>
<form method="POST" action="/roles/{{ $model->id}}/edit">
  {{ csrf_field() }}
  <div class="form-group">
    <label for="name"><strong>Nombre Rol:</strong></label>
    <input type="text" name="name" id="name" value="{{$model->name}}" readonly="" placeholder="Nombre de Rol" class="form-control">
  </div>
  <button type="submit" class="btn btn-primary">Editar</button>
</form>

@endsection