@extends('layout')

@section('content')
<h1>Edit Users</h1>
<form method="POST" action="/users/{{$user->id}}/update">
{{ csrf_field() }}
  
  <div class="form-group">
    <label for="name">Nombre:</label>
    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required="required" value="{{$user->name}}">
  </div>
  <div class="form-group">
    <label for="description">Correo Electronico:</label>    
   
    <input type="text" class="form-control" id="email" name="email" placeholder="email" required="required" value="{{$user->email}}">
  </div>
  <div class="card mb-3">
    <div class="card-header p-2">
      <button class="btn btn-link p-0" type="button" data-toggle="collapse" data-target="#passwordCollapse" aria-expanded="false" aria-controls="passwordCollapse">
        Cambiar contraseña (opcional)
      </button>
    </div>
    <div id="passwordCollapse" class="collapse">
      <div class="card-body">
        <div class="form-group mb-0">
          <label for="password">Nueva contraseña:</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Dejar vacío para no cambiar">
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="budget">Estado:</label>
    <select class="form-control" name="status_id" id="status_id">
      <option value="">Seleccione...</option>
      @foreach($user_statuses as $item)
      <option value="{{$item->id}}" @if($item->id==$user->status_id)selected="selected" @endif>{{$item->name}}</option>
      @endforeach
    </select>
  </div>
 
  <div class="form-group">
    <label for="budget">Rol:</label>
    <select name="role_id" id="role_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($roles as $item)
        <option value="{{$item->id}}" @if($item->id==$user->role_id)selected="selected" @endif>{{$item->name}}</option>
      @endforeach
    </select>
  </div>
  <!--
  <div class="form-group">
    <label for="image_url">Foto</label>    
    <input type="file" id="image_url" name="image_url" placeholder="Foto">
  </div>
   -->
  <button type="submit" class="btn btn-primary">Submit</button>
</form>
@endsection
