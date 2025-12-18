@extends('layout')

@section('content')
@php
    $totalUsers = count($users);
    $activeUsers = collect($users)->filter(function ($user) {
        $statusName = strtolower($user->status->name ?? '');
        return in_array($statusName, ['activo', 'activa', 'active']);
    })->count();
    $roleVariety = collect($users)->map(function ($user) {
        return $user->role->name ?? null;
    })->filter()->unique()->count();
    $pendingSetup = $totalUsers - collect($users)->filter(function ($user) {
        return !empty($user->status_id) && !empty($user->status);
    })->count();
@endphp

<div class="users-dashboard my-4">
  <div class="users-hero shadow-sm">
    <div>
      <p class="text-uppercase text-muted mb-1 small font-weight-bold">Usuarios y permisos</p>
      <h1 class="h3 mb-2">Gestiona los accesos del equipo en un solo lugar</h1>
      <p class="mb-0 text-muted">Sincroniza roles, revisa estados y mantén actualizada tu base de usuarios.</p>
    </div>
    <div class="users-hero__actions">
      <a href="/users/create" class="btn btn-primary btn-sm">Crear usuario</a>
      <a href="/config" class="btn btn-outline-secondary btn-sm">Ir a configuración</a>
    </div>
  </div>

  <div class="users-stats">
    <article class="users-stat-card">
      <p class="text-muted mb-1">Usuarios activos</p>
      <h3>{{ $activeUsers }}</h3>
      <small class="text-muted">de {{ $totalUsers }} registrados</small>
    </article>
    <article class="users-stat-card">
      <p class="text-muted mb-1">Roles configurados</p>
      <h3>{{ $roleVariety }}</h3>
      <small class="text-muted">roles distintos asignados</small>
    </article>
    <article class="users-stat-card">
      <p class="text-muted mb-1">Pendientes de estado</p>
      <h3>{{ $pendingSetup }}</h3>
      <small class="text-muted">usuarios sin estado definido</small>
    </article>
  </div>

  <div class="users-table-card shadow-sm">
    <div class="users-table-card__header">
      <div>
        <h2>Listado de usuarios</h2>
        <p class="text-muted mb-0">Controla roles, estados y accesos desde un único panel.</p>
      </div>
      <div class="users-table-card__actions">
        <input type="search" id="users-search" class="form-control form-control-sm" placeholder="Buscar por nombre, email o rol">
        <div class="users-status-filter btn-group btn-group-toggle" role="group" aria-label="Filtro de estado">
          <button type="button" class="btn btn-light btn-sm active" data-status-filter="active">Activos</button>
          <button type="button" class="btn btn-light btn-sm" data-status-filter="inactive">Inactivos</button>
          <button type="button" class="btn btn-light btn-sm" data-status-filter="all">Todos</button>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table users-table mb-0">
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Último acceso</th>
            <th>Estado</th>
            <th>Rol</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="users-table-body">
          @forelse($users as $user)
            @php
              $statusName = strtolower($user->status->name ?? '');
              $isActive = in_array($statusName, ['activo', 'activa', 'active']);
              $avatarUrl = $user->image_url;
              if ($avatarUrl && !preg_match('#^https?://#i', $avatarUrl)) {
                $avatarUrl = asset(ltrim($avatarUrl, '/'));
              }
            @endphp
            <tr data-status="{{ $isActive ? 'active' : 'inactive' }}">
              <td>
                <div class="users-identity">
                  @if ($avatarUrl)
                    <span class="users-avatar" style="background-image: url('{{ $avatarUrl }}')"></span>
                  @else
                    <span class="users-avatar users-avatar--placeholder">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name ?? '', 0, 1)) }}</span>
                  @endif
                  <div>
                    <strong><a href="/users/{{ $user->id }}">{{ $user->name }}</a></strong>
                    <div class="users-email text-muted">{{ $user->email }}</div>
                  </div>
                </div>
              </td>
              @php
                $lastLogin = $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('d M Y H:i') : null;
              @endphp
              <td class="text-muted">
                @if($lastLogin)
                  {{ $lastLogin }}
                @else
                  <span class="text-muted">Sin registro</span>
                @endif
              </td>
              <td>
                @if(isset($user->status_id) && $user->status_id && !is_null($user->status))
                  <span class="badge {{ $isActive ? 'badge-soft-success' : 'badge-soft-warning' }}">{{ $user->status->name }}</span>
                @else
                  <span class="badge badge-soft-muted">Sin estado</span>
                @endif
              </td>
              <td>
                <span class="role-chip">{{ $user->role->name ?? 'Sin rol' }}</span>
              </td>
              <td class="text-right">
                <a href="/users/{{ $user->id }}/edit" class="btn btn-light btn-sm users-edit-btn">Editar</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">Aún no hay usuarios registrados.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .users-dashboard {
    max-width: 1100px;
    margin: 0 auto;
  }
  .users-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fbff;
    border-radius: 18px;
    padding: 28px 32px;
    border: 1px solid #e6edf6;
  }
  .users-hero__actions a + a {
    margin-left: 8px;
  }
  .users-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-top: 20px;
  }
  .users-stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 20px;
    border: 1px solid #e6edf6;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
  }
  .users-stat-card h3 {
    margin-bottom: 4px;
    color: #0f172a;
  }
  .users-table-card {
    margin-top: 28px;
    background: #fff;
    border-radius: 20px;
    border: 1px solid #e6edf6;
    padding: 24px;
  }
  .users-table-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 16px;
  }
  .users-table-card__header h2 {
    margin-bottom: 4px;
    font-size: 1.15rem;
  }
  .users-table-card__actions {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .users-status-filter .btn {
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    font-weight: 600;
    color: #475569;
    background: #fff;
  }
  .users-status-filter .btn.active,
  .users-status-filter .btn:hover {
    background: #1d4ed8;
    color: #fff;
    border-color: #1d4ed8;
  }
  .users-table {
    border-collapse: separate;
    border-spacing: 0 6px;
  }
  .users-identity {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .users-email {
    font-size: 0.85rem;
  }
  .users-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    background-color: #e2e8f0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
  }
  .users-avatar--placeholder {
    background-color: #e0e7ff;
    color: #312e81;
  }
  .users-table thead th {
    border: none;
    font-size: 0.85rem;
    text-transform: uppercase;
    color: #94a3b8;
    font-weight: 600;
    letter-spacing: 0.04em;
    background: transparent;
  }
  .users-table tbody tr {
    background: #f9fbff;
    border-radius: 12px;
  }
  .users-table tbody tr td {
    border: none;
    padding: 14px;
    vertical-align: middle;
  }
  .users-table tbody tr td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
  }
  .users-table tbody tr td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
  }
  .role-chip {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    background: #eef2ff;
    border-radius: 999px;
    font-size: 0.85rem;
    color: #312e81;
    font-weight: 600;
  }
  .badge-soft-success {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(34, 197, 94, 0.15);
    color: #15803d;
    font-weight: 600;
    font-size: 0.85rem;
  }
  .badge-soft-warning {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(251, 191, 36, 0.2);
    color: #92400e;
    font-weight: 600;
    font-size: 0.85rem;
  }
  .badge-soft-muted {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    background: #e2e8f0;
    color: #475569;
    font-weight: 600;
    font-size: 0.85rem;
  }
  .users-edit-btn {
    border: 1px solid #94a3b8;
    color: #0f172a;
    font-weight: 600;
    border-radius: 999px;
    padding: 6px 18px;
  }
  .users-edit-btn:hover {
    background: #1d4ed8;
    color: #fff;
    border-color: #1d4ed8;
  }
  @media (max-width: 768px) {
    .users-hero {
      flex-direction: column;
      text-align: center;
      gap: 16px;
    }
    .users-hero__actions {
      width: 100%;
      display: flex;
      justify-content: center;
    }
    .users-table-card__header {
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
    }
    .users-table-card__actions {
      width: 100%;
      justify-content: flex-start;
    }
    .users-table tbody tr td {
      padding: 12px 10px;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('users-search');
    var statusButtons = document.querySelectorAll('[data-status-filter]');
    var rows = document.querySelectorAll('#users-table-body tr');
    var activeFilter = 'active';

    function applyFilters() {
      rows.forEach(function (row) {
        var matchesStatus = activeFilter === 'all' ? true : row.getAttribute('data-status') === activeFilter;
        var matchesSearch = true;
        if (searchInput) {
          var term = searchInput.value.toLowerCase();
          matchesSearch = row.textContent.toLowerCase().indexOf(term) > -1;
        }
        row.style.display = matchesStatus && matchesSearch ? '' : 'none';
      });
    }

    if (searchInput) {
      searchInput.addEventListener('input', applyFilters);
    }

    statusButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        activeFilter = button.getAttribute('data-status-filter');
        statusButtons.forEach(function (btn) {
          btn.classList.toggle('active', btn === button);
        });
        applyFilters();
      });
    });

    applyFilters();
  });
</script>
@endpush
