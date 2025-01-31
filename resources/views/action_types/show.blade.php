@extends('layout')

@section('content')

<div style="margin-top: 20px;"><h1>Acción {{$model->name}}</h1></div>
<div class="card-block">
  <form>
  <div class="form-inline">
    <div class="form-group">
      <label for="name" style="margin-right: 10px;">Nombre:</label>
      <input type="text" class="form-control" id="name" name="name" style="margin-right: 10px;" value="{{$model->name}}" readonly />
    </div>
    <a href="/action_type/{{$model->id}}/edit" type="submit" class="btn btn-primary">Editar</a>
  </div>
  </form>
  </div>

@endsection