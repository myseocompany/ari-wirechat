@extends('layouts.tailwind')

@push('styles')
  <x-design.styles />
@endpush

@section('content')
<div class="ds-body space-y-6">
  <section class="ds-shell relative overflow-hidden rounded-[28px] border border-slate-200 px-6 py-8 shadow-[0_25px_70px_rgba(15,23,42,0.12)] sm:px-10">
    <div class="pointer-events-none absolute -left-16 -top-10 h-60 w-60 rounded-full bg-[radial-gradient(circle,var(--ds-coral),transparent_70%)] opacity-30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-10 top-12 h-60 w-60 rounded-full bg-[radial-gradient(circle,var(--ds-rose),transparent_68%)] opacity-25 blur-3xl"></div>
    <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
      <div class="flex flex-col gap-3">
        <x-design.eyebrow>Usuarios</x-design.eyebrow>
        <h1 class="ds-display text-3xl font-semibold text-[color:var(--ds-ink)] sm:text-4xl">Editar perfil de usuario</h1>
        <p class="max-w-2xl text-sm text-slate-600 sm:text-base">Actualiza los datos, permisos y credenciales para mantener al equipo sincronizado.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <x-design.badge tone="cloud">{{ $user->email }}</x-design.badge>
        @if($user->role)
          <x-design.badge tone="outline">{{ $user->role->name }}</x-design.badge>
        @endif
        @if($user->status)
          <x-design.badge tone="mint">{{ $user->status->name }}</x-design.badge>
        @endif
      </div>
    </div>
  </section>

  <form method="POST" action="/users/{{ $user->id }}/update" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
      <x-design.section class="space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-[color:var(--ds-ink)]">Datos principales</h2>
          <x-design.badge tone="blush">Obligatorio</x-design.badge>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
            Nombre completo
            <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}" placeholder="Ej. Laura Mejía" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]">
          </label>
          <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
            Correo electrónico
            <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}" placeholder="laura@wirechat.co" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]">
          </label>
          <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
            Channels ID
            <input type="number" id="channels_id" name="channels_id" value="{{ old('channels_id', $user->channels_id) }}" placeholder="Ej. 2345" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]">
          </label>
        </div>
      </x-design.section>

      <x-design.section class="space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-[color:var(--ds-ink)]">Estado y rol</h2>
          <x-design.badge tone="cloud">Permisos</x-design.badge>
        </div>
        <div class="grid gap-4">
          <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
            Estado del usuario
            <select name="status_id" id="status_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]">
              <option value="">Selecciona un estado</option>
              @foreach($user_statuses as $item)
                <option value="{{ $item->id }}" @selected((string) old('status_id', $user->status_id) === (string) $item->id)>{{ $item->name }}</option>
              @endforeach
            </select>
          </label>
          <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
            Rol del usuario
            <select name="role_id" id="role_id" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]">
              <option value="">Selecciona un rol</option>
              @foreach ($roles as $item)
                <option value="{{ $item->id }}" @selected((string) old('role_id', $user->role_id) === (string) $item->id)>{{ $item->name }}</option>
              @endforeach
            </select>
          </label>
        </div>
      </x-design.section>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
      <x-design.section class="space-y-4">
        <h2 class="text-lg font-semibold text-[color:var(--ds-ink)]">Seguridad</h2>
        <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
          Nueva contraseña (opcional)
          <input type="password" id="password" name="password" placeholder="Dejar vacío para no cambiar" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]">
          <span class="text-xs text-slate-500">Solo completa este campo si deseas actualizar la contraseña.</span>
        </label>
      </x-design.section>

      <x-design.section class="space-y-4">
        <h2 class="text-lg font-semibold text-[color:var(--ds-ink)]">Foto de perfil</h2>
        @php
          $currentAvatar = $user->image_url;
          if ($currentAvatar && !preg_match('#^https?://#i', $currentAvatar)) {
            $currentAvatar = asset(ltrim($currentAvatar, '/'));
          }
        @endphp
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4">
          @if ($currentAvatar)
            <img src="{{ $currentAvatar }}" alt="Foto de {{ $user->name }}" class="h-16 w-16 rounded-full object-cover shadow-sm">
          @else
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[color:var(--ds-lilac)] text-base font-semibold text-[color:var(--ds-ink)]">
              {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
            </div>
          @endif
          <div>
            <p class="text-sm font-semibold text-[color:var(--ds-ink)]">Imagen actual</p>
            <p class="text-xs text-slate-600">Formatos: JPG, PNG o WebP.</p>
          </div>
        </div>
        <label class="flex flex-col gap-2 text-sm font-medium text-slate-700">
          Cambiar foto
          <input type="file" id="profile_photo" name="profile_photo" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[color:var(--ds-lilac)] file:px-4 file:py-2 file:text-xs file:font-semibold file:text-[color:var(--ds-ink)] hover:file:bg-[color:var(--ds-cloud)] focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" accept=".jpg,.jpeg,.png,.webp">
          <span class="text-xs text-slate-500">Sube una nueva imagen para actualizar el perfil.</span>
        </label>
      </x-design.section>
    </div>

    <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
      <a href="/users" class="text-sm font-semibold text-slate-600 transition hover:text-[color:var(--ds-ink)]">Volver a usuarios</a>
      <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[color:var(--ds-coral)] px-6 py-3 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(12,117,188,0.35)] transition hover:bg-[color:var(--ds-rose)]">Guardar cambios</button>
    </div>
  </form>
</div>
@endsection
