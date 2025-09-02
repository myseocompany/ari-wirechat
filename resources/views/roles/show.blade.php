<@extends('layout')

@section('content')
<h1>Editar permisos del rol: {{ $role->name }}</h1>

<form method="POST" action="{{ route('roles.updatePermissions', $role->id) }}">
  @csrf
  @method('PUT')

  <table class="table">
    <thead>
      <tr>
        <th>Menú</th>
        <th>Crear</th>
        <th>Leer</th>
        <th>Actualizar</th>
        <th>Eliminar</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($menus as $menu)
        @php
          $permissions = $role->menus->firstWhere('id', $menu->id)?->pivot ?? null;
        @endphp
        <tr>
          <td>{{ $menu->name }}</td>
          @foreach (['create', 'read', 'update', 'delete'] as $perm)
            <td>
              <label class="switch">
                <input type="checkbox"
                       name="permissions[{{ $menu->id }}][{{ $perm }}]"
                       value="1"
                       {{ $permissions && $permissions->$perm ? 'checked' : '' }}>
                <span class="slider round"></span>
              </label>
            </td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  </table>

  <button type="submit" class="btn btn-success">Guardar cambios</button>
</form>


<h2>Usuarios con este rol</h2>
<ul>
  @forelse ($role->users as $user)
    <li>{{ $user->name }} – {{ $user->email }}</li>
  @empty
    <li>No hay usuarios con este rol.</li>
  @endforelse
</ul>

<style>
  table.table tr {
    height: 32px; /* más bajo */
  }

  table.table td, table.table th {
    padding: 4px 8px;
    vertical-align: middle;
  }

  /* Switch estilo iOS ajustado */
  .switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
  }

  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider {
    position: absolute;
    cursor: pointer;
    background-color: #ccc;
    border-radius: 34px;
    transition: 0.4s;
    top: 0; left: 0; right: 0; bottom: 0;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.4s;
  }

  input:checked + .slider {
    background-color: #2196F3;
  }

  input:checked + .slider:before {
    transform: translateX(20px);
  }
</style>


@endsection
