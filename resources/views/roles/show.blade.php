@extends('layout')

@section('content')
<h1 class="mb-3">Editar permisos del rol: {{ $role->name }}</h1>

@if (session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
@endif

<form method="POST" action="{{ route('roles.updatePermissions', $role->id) }}">
  @csrf
  @method('PUT')

  <div class="mb-3 rounded border bg-light p-3">
    <h5 class="mb-2">Permisos de Customers</h5>
    <div class="d-flex align-items-center">
      <label class="switch mb-0 mr-2">
        <input
          type="checkbox"
          name="can_view_all_customers"
          value="1"
          {{ $role->can_view_all_customers ? 'checked' : '' }}
        >
        <span class="slider round"></span>
      </label>
      <span>Puede ver el detalle completo de customers no propios</span>
    </div>
    <small class="text-muted">Si está desactivado, solo verá detalle completo de sus propios customers.</small>
  </div>

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
                <input
                  type="checkbox"
                  name="permissions[{{ $menu->id }}][{{ $perm }}]"
                  value="1"
                  {{ $permissions && $permissions->$perm ? 'checked' : '' }}
                >
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

<h2 class="mt-4">Usuarios con este rol</h2>
<ul>
  @forelse ($role->users as $user)
    <li>{{ $user->name }} - {{ $user->email }}</li>
  @empty
    <li>No hay usuarios con este rol.</li>
  @endforelse
</ul>

<style>
  table.table tr {
    height: 32px;
  }

  table.table td,
  table.table th {
    padding: 4px 8px;
    vertical-align: middle;
  }

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
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: #fff;
    border-radius: 50%;
    transition: 0.4s;
  }

  input:checked + .slider {
    background-color: #2196f3;
  }

  input:checked + .slider:before {
    transform: translateX(20px);
  }
</style>
@endsection
