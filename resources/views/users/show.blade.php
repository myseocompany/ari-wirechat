@extends('layout')

@section('content')
 <h1>{{$user->name}}</h1>
 @php
   $avatarUrl = $user->image_url;
   if ($avatarUrl && !preg_match('#^https?://#i', $avatarUrl)) {
     $avatarUrl = asset(ltrim($avatarUrl, '/'));
   }
 @endphp
 @if ($avatarUrl)
   <div class="mb-3">
     <img src="{{ $avatarUrl }}" alt="Foto de {{ $user->name }}" class="rounded-circle" style="width: 96px; height: 96px; object-fit: cover;">
   </div>
 @endif

    <form method="POST" action="/users/{{$user->id}}/edit">
    {{ csrf_field() }}
    <div class="form-group">
      <label for="name">Nombre:</label>
      <input type="text" class="form-control" id="name" name="name" placeholder="Nombre..." value="{{$user->name}}" readonly>
    </div>
    <div class="form-group">
      <label for="email">Correo Electrónico:</label>    
      <input type="text" class="form-control" id="email" name="email" placeholder="Correo Electrónico..." value="{{$user->email}}" readonly>    
     </div>
     <div class="form-group">
        <label for="channels_id">Channels ID:</label>
        <input type="text" class="form-control" id="channels_id" name="channels_id" placeholder="Channels ID" value="{{ $user->channels_id }}" readonly>
     </div>
      <div class="form-group">
        <label for="customer_status">Estado:</label>
              <input type="text" class="form-control" id="email" name="status_id" placeholder="Estado"  @if (isset($user->status_id) && ($user->status_id != ''))
                value="{{$user->status->name}}" 
              @endif readonly >
          </div>
            <div class="form-group">
        <label for="customer_status">Rol:</label>
              <input type="text" class="form-control" id="email" name="role_id" placeholder="Rol"  @if (isset($user->role_id) && ($user->role_id != ''))
                value="{{$user->role->name}}" 
              @endif readonly >
          </div>
    
          
          
       <button type="submit" class="btn btn-primary">Editar</button>
    </form>
@endsection
