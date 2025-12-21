@extends('layouts.guest')

@section('content')
<div class="min-h-screen bg-slate-50">
  <div class="grid min-h-screen grid-cols-1 lg:grid-cols-[1.05fr_0.95fr]">
    <section class="relative flex items-center overflow-hidden bg-gradient-to-br from-[#ff7a66] via-[#ff9a6b] to-[#2b6cb0] px-8 py-14 text-white sm:px-12 lg:px-14">
      <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 15% 20%, rgba(255, 255, 255, 0.55), transparent 45%), radial-gradient(circle at 80% 10%, rgba(255, 255, 255, 0.35), transparent 35%), radial-gradient(circle at 30% 85%, rgba(255, 255, 255, 0.3), transparent 45%);"></div>
      <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
      <div class="absolute -bottom-24 left-0 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>

      <div class="relative z-10 flex max-w-xl flex-col gap-8">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-2xl">✳︎</span>
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-white/70">Wirechat</p>
            <p class="text-lg font-semibold">CRM MQE</p>
          </div>
        </div>

        <div class="space-y-4">
          <h1 class="text-4xl font-semibold leading-tight sm:text-5xl">Hola, vuelve a Wirechat 👋</h1>
          <p class="text-base text-white/85 sm:text-lg">
            Centraliza conversaciones, acelera cierres y mantén el contexto de cada cliente en un solo lugar.
          </p>
        </div>

        <div class="flex flex-wrap gap-3 text-xs uppercase tracking-[0.2em] text-white/70">
          <span class="rounded-full border border-white/30 px-3 py-1">Conversaciones</span>
          <span class="rounded-full border border-white/30 px-3 py-1">Ventas</span>
          <span class="rounded-full border border-white/30 px-3 py-1">Seguimiento</span>
        </div>
      </div>
    </section>

    <section class="flex items-center justify-center px-6 py-12 sm:px-10">
      <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-xl shadow-slate-200/70 sm:p-10">
        <div class="mb-8 space-y-2">
          <h2 class="text-2xl font-semibold text-slate-900">Bienvenido de nuevo</h2>
          <p class="text-sm text-slate-500">Ingresa con tu correo y contraseña para continuar.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
          {{ csrf_field() }}

          <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-slate-700">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100">
            @if ($errors->has('email'))
              <p class="text-xs text-red-600">{{ $errors->first('email') }}</p>
            @endif
          </div>

          <div class="space-y-2">
            <label for="password" class="text-sm font-medium text-slate-700">Contraseña</label>
            <input id="password" type="password" name="password" required class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100">
            @if ($errors->has('password'))
              <p class="text-xs text-red-600">{{ $errors->first('password') }}</p>
            @endif
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-slate-600">
              <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-200" {{ old('remember') ? 'checked' : '' }}>
              Recordarme
            </label>
            <a href="{{ route('password.request') }}" class="font-medium text-blue-600 hover:text-blue-700">¿Olvidaste tu contraseña?</a>
          </div>

          <button type="submit" class="h-12 w-full rounded-xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800">
            Iniciar sesión
          </button>
        </form>

        <p class="mt-6 text-center text-xs text-slate-500">
          ¿Necesitas acceso? <span class="font-semibold text-slate-700">Contacta a tu administrador</span>
        </p>
      </div>
    </section>
  </div>
</div>
@endsection
