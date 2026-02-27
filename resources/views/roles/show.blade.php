@extends('layouts.tailwind')

@push('styles')
  <x-design.styles />
@endpush

@section('content')
@php
  $usersCount = $role->users->count();
  $menusWithPermissions = $role->menus->filter(function ($menu) {
      $pivot = $menu->pivot;

      return $pivot && ((int) $pivot->create === 1 || (int) $pivot->read === 1 || (int) $pivot->update === 1 || (int) $pivot->delete === 1);
  })->count();
@endphp

<div class="ds-body space-y-6">
  <section class="ds-shell relative overflow-hidden rounded-[28px] border border-slate-200 px-6 py-8 shadow-[0_25px_70px_rgba(15,23,42,0.12)] sm:px-10">
    <div class="pointer-events-none absolute -left-16 -top-10 h-60 w-60 rounded-full bg-[radial-gradient(circle,var(--ds-coral),transparent_70%)] opacity-30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-10 top-12 h-60 w-60 rounded-full bg-[radial-gradient(circle,var(--ds-rose),transparent_68%)] opacity-25 blur-3xl"></div>
    <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div class="flex flex-col gap-3">
        <x-design.eyebrow>Roles</x-design.eyebrow>
        <h1 class="ds-display text-3xl font-semibold text-[color:var(--ds-ink)] sm:text-4xl">Permisos de {{ $role->name }}</h1>
        <p class="max-w-2xl text-sm text-slate-600 sm:text-base">Configura el acceso global a clientes y permisos por módulo en un solo panel.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <x-design.badge tone="cloud">Rol #{{ $role->id }}</x-design.badge>
        <x-design.badge tone="outline">{{ $usersCount }} usuarios</x-design.badge>
        <x-design.badge tone="mint">{{ $menusWithPermissions }} menús activos</x-design.badge>
      </div>
    </div>
  </section>

  @if (session('success'))
    <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-mint)] p-4 text-sm text-[color:var(--ds-ink)] shadow-sm">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('roles.updatePermissions', $role->id) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <x-design.section class="space-y-4">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-[color:var(--ds-ink)]">Acceso de Customers</h2>
        <x-design.badge tone="blush">Global</x-design.badge>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4">
        <label for="can_view_all_customers" class="flex cursor-pointer items-center justify-between gap-4">
          <div>
            <p class="text-sm font-semibold text-[color:var(--ds-ink)]">Ver detalle completo de customers no propios</p>
            <p class="text-xs text-slate-600">Desactiva esta opción si el rol solo debe operar sobre sus clientes asignados.</p>
          </div>
          <span class="relative inline-flex items-center">
            <input
              id="can_view_all_customers"
              type="checkbox"
              name="can_view_all_customers"
              value="1"
              class="peer sr-only"
              @checked($role->can_view_all_customers)
            >
            <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-[color:var(--ds-coral)] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[color:var(--ds-blush)]"></span>
            <span class="pointer-events-none absolute left-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>
      </div>
    </x-design.section>

    <x-design.section class="space-y-4">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-[color:var(--ds-ink)]">Permisos por menú</h2>
        <x-design.badge tone="cloud">{{ $menus->count() }} módulos</x-design.badge>
      </div>

      <div class="overflow-x-auto rounded-2xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 bg-white text-sm">
          <thead class="bg-[color:var(--ds-cloud)]">
            <tr>
              <th class="px-4 py-3 text-left ds-mono text-xs uppercase tracking-[0.2em] text-slate-500">Menú</th>
              <th class="px-4 py-3 text-center ds-mono text-xs uppercase tracking-[0.2em] text-slate-500">Crear</th>
              <th class="px-4 py-3 text-center ds-mono text-xs uppercase tracking-[0.2em] text-slate-500">Leer</th>
              <th class="px-4 py-3 text-center ds-mono text-xs uppercase tracking-[0.2em] text-slate-500">Actualizar</th>
              <th class="px-4 py-3 text-center ds-mono text-xs uppercase tracking-[0.2em] text-slate-500">Eliminar</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach ($menus as $menu)
              @php
                $permissions = $role->menus->firstWhere('id', $menu->id)?->pivot;
              @endphp
              <tr class="hover:bg-slate-50/80">
                <td class="px-4 py-3 font-medium text-[color:var(--ds-ink)]">{{ $menu->name }}</td>
                @foreach (['create', 'read', 'update', 'delete'] as $perm)
                  <td class="px-4 py-3">
                    <label class="relative mx-auto inline-flex w-10 cursor-pointer items-center">
                      <input
                        type="checkbox"
                        name="permissions[{{ $menu->id }}][{{ $perm }}]"
                        value="1"
                        class="peer sr-only"
                        @checked($permissions && $permissions->$perm)
                      >
                      <span class="h-5 w-10 rounded-full bg-slate-300 transition peer-checked:bg-[color:var(--ds-coral)]"></span>
                      <span class="pointer-events-none absolute h-4 w-4 translate-x-0.5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </label>
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </x-design.section>

    <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
      <a href="/roles" class="text-sm font-semibold text-slate-600 transition hover:text-[color:var(--ds-ink)]">Volver a roles</a>
      <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[color:var(--ds-coral)] px-6 py-3 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(12,117,188,0.35)] transition hover:bg-[color:var(--ds-rose)]">Guardar cambios</button>
    </div>
  </form>

  <x-design.section class="space-y-4">
    <div class="flex items-center justify-between gap-3">
      <h2 class="text-lg font-semibold text-[color:var(--ds-ink)]">Usuarios con este rol</h2>
      <x-design.badge tone="outline">{{ $usersCount }}</x-design.badge>
    </div>
    @if ($role->users->isEmpty())
      <p class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] px-4 py-3 text-sm text-slate-600">No hay usuarios con este rol.</p>
    @else
      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($role->users as $user)
          <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] px-4 py-3">
            <p class="text-sm font-semibold text-[color:var(--ds-ink)]">{{ $user->name }}</p>
            <p class="text-xs text-slate-600">{{ $user->email }}</p>
          </div>
        @endforeach
      </div>
    @endif
  </x-design.section>
</div>
@endsection
