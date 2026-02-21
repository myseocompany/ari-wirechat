@extends('layout')

@section('content')
<h1>Create User</h1>
@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
<form method="POST" action="/users" enctype="multipart/form-data">
@csrf
  <div class="form-group">
    <label for="name">Nombre:</label>
    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required="required" value="{{ old('name') }}">
  </div>
  <div class="form-group">
    <label for="email">Correo Electrónico:</label>    
    <input type="text" class="form-control" id="email" name="email" placeholder="Email" required="required" value="{{ old('email') }}">
  </div>
  <div class="form-group">
    <label for="channels_id">Channels ID:</label>
    <input type="number" class="form-control" id="channels_id" name="channels_id" placeholder="Channels ID" value="{{ old('channels_id') }}">
  </div>
  <div class="form-group">
    <label for="channels_email">Channels Email / Username:</label>
    <input type="text" class="form-control" id="channels_email" name="channels_email" placeholder="agent@channels.app o usuario" value="{{ old('channels_email') }}">
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
        <option value="{{$item->id}}" @selected(old('status_id') == $item->id)>{{$item->name}}</option>
      @endforeach
    </select>
  </div>

  <div class="form-group">
    <label for="roles">Rol:</label>
    <select name="role_id" id="role_id" class="form-control">
      <option value="">Seleccione...</option>
      @foreach ($roles as $item)
        <option value="{{$item->id}}" @selected(old('role_id') == $item->id)>{{$item->name}}</option>
      @endforeach
    </select>
  </div>

  <div class="form-group">
    <label for="profile_photo">Foto de perfil:</label>
    <input type="file" class="form-control-file" id="profile_photo" name="profile_photo" accept=".jpg,.jpeg,.png,.webp">
    <small class="form-text text-muted">Formatos permitidos: JPG, PNG o WebP (máx. 4 MB).</small>
  </div>
  
  <button type="submit" class="btn btn-primary">Crear</button>
</form>
@endsection
