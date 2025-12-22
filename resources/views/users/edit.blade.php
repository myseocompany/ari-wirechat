@extends('layout')

@section('content')
<h1>Edit Users</h1>
<form method="POST" action="/users/{{$user->id}}/update" enctype="multipart/form-data">
{{ csrf_field() }}
  
  <div class="form-group">
    <label for="name">Nombre:</label>
    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required="required" value="{{$user->name}}">
  </div>
  <div class="form-group">
    <label for="description">Correo Electronico:</label>    
   
    <input type="text" class="form-control" id="email" name="email" placeholder="email" required="required" value="{{$user->email}}">
  </div>
  <div class="mb-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="form-group mb-0">
      <label for="password">Nueva contraseña (opcional):</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="Dejar vacío para no cambiar">
      <small class="form-text text-muted">Solo completa este campo si deseas actualizar la contraseña.</small>
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
  <div class="form-group">
    <label for="profile_photo">Foto de perfil:</label>
    @php
      $currentAvatar = $user->image_url;
      if ($currentAvatar && !preg_match('#^https?://#i', $currentAvatar)) {
        $currentAvatar = asset(ltrim($currentAvatar, '/'));
      }
    @endphp
    @if ($currentAvatar)
      <div class="mb-2">
        <img src="{{ $currentAvatar }}" alt="Foto de {{ $user->name }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
      </div>
    @endif
    <input type="file" id="profile_photo" name="profile_photo" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:file:bg-slate-800 dark:file:text-slate-100 dark:hover:file:bg-slate-700" accept=".jpg,.jpeg,.png,.webp">
    <small class="form-text text-muted">Sube una nueva imagen para actualizar el perfil.</small>
  </div>

  <button type="submit" class="btn btn-primary">Submit</button>
</form>
@endsection
