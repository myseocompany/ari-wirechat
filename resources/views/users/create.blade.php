@extends('layout')

@section('content')
<h1>Create User</h1>
<form method="POST" action="/users">
{{ csrf_field() }}
  <div class="form-group">
    <label for="name">Nombre:</label>
    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required="required">
  </div>
  <div class="form-group">
    <label for="email">Correo Electrónico:</label>    
    <input type="text" class="form-control" id="email" name="email" placeholder="Email" required="required">
  </div>
  <div class="form-group">
    <label for="budget">Contraseña:</label>
    <input type="password" class="form-control" id="password" name="password" placeholder="Password">  
  </div>

  <div class="form-group">
    <label for="status">Estado:</label>
    <select name="status_id" id="status_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($user_statuses as $item)
        <option value="{{$item->id}}">{{$item->name}}</option>
      @endforeach
    </select>
  </div>

  <div class="form-group">
    <label for="roles">Rol:</label>
    <select name="role_id" id="role_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($roles as $item)
        <option value="{{$item->id}}">{{$item->name}}</option>
      @endforeach
    </select>
  </div>
  
  <button type="submit" class="btn btn-primary">Crear</button>
</form>
@endsection