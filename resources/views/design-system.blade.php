@extends('layouts.tailwind')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --ds-ink: #0f172a;
        --ds-navy: #1c2640;
        --ds-slate: #97a3b6;
        --ds-cloud: #e9eef7;
        --ds-coral: #ff5c5c;
        --ds-rose: #ff8d7a;
        --ds-blush: #ffd2c9;
        --ds-mint: #d9f3ee;
        --ds-lilac: #d8d7ff;
    }

    .ds-shell {
        background:
            radial-gradient(circle at 5% 15%, rgba(255, 92, 92, 0.28), transparent 45%),
            radial-gradient(circle at 90% 10%, rgba(28, 38, 64, 0.22), transparent 45%),
            linear-gradient(120deg, #fff3f0 0%, #eef3ff 52%, #ffffff 100%);
    }

    .ds-body {
        font-family: "Space Grotesk", "Helvetica Neue", Arial, sans-serif;
        color: var(--ds-ink);
    }

    .ds-display {
        font-family: "Space Grotesk", "Helvetica Neue", Arial, sans-serif;
        letter-spacing: -0.02em;
    }

    .ds-mono {
        font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono",
            "Courier New", monospace;
    }

    .ds-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, 0.05) 1px, transparent 1px);
        background-size: 32px 32px;
    }
</style>
@endpush

@section('content')
<div class="ds-body relative flex flex-col gap-6">
    <section class="ds-shell ds-grid relative overflow-hidden rounded-[32px] border border-slate-200 px-6 py-10 shadow-[0_25px_70px_rgba(15,23,42,0.12)] sm:px-10">
        <div class="pointer-events-none absolute -left-24 -top-10 h-72 w-72 rounded-full bg-[radial-gradient(circle,#ff8d7a,transparent_70%)] opacity-40 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-20 top-12 h-72 w-72 rounded-full bg-[radial-gradient(circle,#1c2640,transparent_68%)] opacity-25 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-6 left-1/2 h-20 w-3/4 -translate-x-1/2 rounded-full bg-[radial-gradient(circle,#ffd2c9,transparent_70%)] opacity-40 blur-2xl"></div>
        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex max-w-3xl flex-col gap-4">
                <x-design.eyebrow>Design system</x-design.eyebrow>
                <h1 class="ds-display text-4xl font-semibold text-[color:var(--ds-ink)] sm:text-6xl">Wirechat UI Kit</h1>
                <p class="text-base text-slate-700 sm:text-lg">Una guia visual para mantener consistencia en tipografia, color y componentes clave del producto.</p>
                <div class="flex flex-wrap gap-3">
                    <x-design.badge class="border border-white/60 bg-white/90 text-sm font-medium text-slate-700 shadow-sm">Version 1.0</x-design.badge>
                    <x-design.badge class="border border-white/60 bg-white/90 text-sm font-medium text-slate-700 shadow-sm">Tailwind v3</x-design.badge>
                    <x-design.badge class="border border-white/60 bg-white/90 text-sm font-medium text-slate-700 shadow-sm">Livewire ready</x-design.badge>
                </div>
            </div>
            <div class="flex flex-col gap-3 rounded-2xl border border-white/60 bg-white/80 p-4 text-sm text-slate-700 shadow-sm sm:max-w-xs">
                <div class="flex items-center justify-between">
                    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Estado</span>
                    <x-design.badge tone="blush">En progreso</x-design.badge>
                </div>
                <p class="text-sm font-medium text-[color:var(--ds-ink)]">Componentes listos para nuevas vistas.</p>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full w-2/3 rounded-full bg-[linear-gradient(90deg,#ff5c5c,#ff8d7a)]"></div>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[1.2fr_1fr]">
        <x-design.section>
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <x-design.eyebrow>Tipografia</x-design.eyebrow>
                    <div class="flex flex-wrap items-center gap-4">
                        <h2 class="ds-display text-3xl font-semibold text-[color:var(--ds-ink)]">Space Grotesk</h2>
                        <x-design.badge tone="cloud">Regular / Semibold</x-design.badge>
                    </div>
                    <p class="ds-mono text-sm text-slate-600">ABCDEFGHIJKLMNOPQRSTUVWXYZ</p>
                    <p class="ds-mono text-sm text-slate-600">abcdefghijklmnopqrstuvwxyz 0123456789</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4">
                        <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Heading 1</span>
                        <p class="ds-display text-4xl font-semibold">Wirechat UI</p>
                    </div>
                    <div class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Heading 2</span>
                        <p class="ds-display text-3xl font-semibold">Wirechat UI</p>
                    </div>
                    <div class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Heading 3</span>
                        <p class="ds-display text-2xl font-semibold">Wirechat UI</p>
                    </div>
                    <div class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-[color:var(--ds-blush)] p-4">
                        <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-600">Body</span>
                        <p class="text-base text-slate-700">Conversaciones claras, seguimiento organizado y acciones que no se pierden.</p>
                    </div>
                </div>
            </div>
        </x-design.section>

        <x-design.section>
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <x-design.eyebrow>Iconos</x-design.eyebrow>
                    <x-design.badge class="border border-slate-200 bg-white text-xs font-semibold text-slate-500">24px grid</x-design.badge>
                </div>
                <div class="grid grid-cols-4 gap-3">
                    <x-design.icon-tile>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l9 7h-3v9h-5v-6H11v6H6v-9H3l9-7z"/></svg>
                    </x-design.icon-tile>
                    <x-design.icon-tile>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7-4.35-9-8.5C1.5 8.5 4.5 6 7.5 6c1.7 0 3.3.8 4.5 2 1.2-1.2 2.8-2 4.5-2 3 0 6 2.5 4.5 6.5-2 4.15-9 8.5-9 8.5z"/></svg>
                    </x-design.icon-tile>
                    <x-design.icon-tile>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.4 12.9a7.96 7.96 0 0 0 .1-1 7.96 7.96 0 0 0-.1-1l2.1-1.6-2-3.4-2.5 1a8.1 8.1 0 0 0-1.7-1l-.4-2.6h-4l-.4 2.6a8.1 8.1 0 0 0-1.7 1l-2.5-1-2 3.4 2.1 1.6a7.96 7.96 0 0 0-.1 1 7.96 7.96 0 0 0 .1 1L2.5 14.5l2 3.4 2.5-1a8.1 8.1 0 0 0 1.7 1l.4 2.6h4l.4-2.6a8.1 8.1 0 0 0 1.7-1l2.5 1 2-3.4-2.1-1.6zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/></svg>
                    </x-design.icon-tile>
                    <x-design.icon-tile>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 10V8a6 6 0 1 1 12 0v2h2v12H4V10h2zm2 0h8V8a4 4 0 1 0-8 0v2z"/></svg>
                    </x-design.icon-tile>
                    <x-design.icon-tile tone="blush">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9l-5 4V6a2 2 0 0 1 2-2z"/></svg>
                    </x-design.icon-tile>
                    <x-design.icon-tile tone="blush">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-4 0-8 2-8 5v1h16v-1c0-3-4-5-8-5z"/></svg>
                    </x-design.icon-tile>
                    <x-design.icon-tile tone="blush">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10 14h4v4h-4v-4zm0-8h4v6h-4V6zm2-4C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/></svg>
                    </x-design.icon-tile>
                    <x-design.icon-tile>
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15-6.7L21 3v6h-6l2.2-2.2A7 7 0 1 0 19 12h2a9 9 0 0 1-18 0z"/></svg>
                    </x-design.icon-tile>
                </div>
                <p class="text-sm text-slate-600">Iconos monolineales con alto contraste y esquinas redondeadas.</p>
            </div>
        </x-design.section>

        <x-design.section>
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <x-design.eyebrow>Botones</x-design.eyebrow>
                    <x-design.badge class="bg-[color:var(--ds-lilac)] text-xs font-semibold text-[color:var(--ds-ink)]">Altura 44px</x-design.badge>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <button class="rounded-xl bg-[color:var(--ds-coral)] px-4 py-3 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(255,92,92,0.35)]">Principal</button>
                    <button class="rounded-xl bg-[color:var(--ds-navy)] px-4 py-3 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(28,38,64,0.25)]">Oscuro</button>
                    <button class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-[color:var(--ds-ink)]">Neutro</button>
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-design.badge tone="cloud">Small</x-design.badge>
                    <x-design.badge tone="outline">Outline</x-design.badge>
                    <x-design.badge tone="mint">Success</x-design.badge>
                </div>
            </div>
        </x-design.section>

        <x-design.section>
            <div class="flex flex-col gap-6">
                <x-design.eyebrow>Campos de formulario</x-design.eyebrow>
                <div class="grid gap-4">
                    <label class="flex flex-col gap-2">
                        <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Email</span>
                        <input type="email" placeholder="correo@wirechat.co" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-[color:var(--ds-coral)] focus:outline-none focus:ring-2 focus:ring-[color:var(--ds-blush)]" />
                    </label>
                    <label class="flex flex-col gap-2">
                        <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Busqueda</span>
                        <div class="flex items-center gap-3 rounded-xl border border-slate-300 px-4 py-3 shadow-sm">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[color:var(--ds-cloud)] text-[color:var(--ds-navy)]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10 2a8 8 0 1 0 4.9 14.3l4.4 4.4 1.4-1.4-4.4-4.4A8 8 0 0 0 10 2zm0 2a6 6 0 1 1 0 12 6 6 0 0 1 0-12z"/></svg>
                            </span>
                            <input type="text" placeholder="Busca conversaciones" class="w-full text-sm text-slate-700 focus:outline-none" />
                            <span class="rounded-full bg-[color:var(--ds-lilac)] px-3 py-1 text-xs font-semibold text-[color:var(--ds-ink)]">/</span>
                        </div>
                    </label>
                </div>
            </div>
        </x-design.section>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
        <x-design.section>
            <div class="flex flex-col gap-6">
                <x-design.eyebrow>Paleta cromatica</x-design.eyebrow>
                <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <x-design.color-swatch name="Ink" hex="#0f172a" />
                    <x-design.color-swatch name="Navy" hex="#1c2640" />
                    <x-design.color-swatch name="Slate" hex="#97a3b6" />
                    <x-design.color-swatch name="Coral" hex="#ff5c5c" />
                    <x-design.color-swatch name="Rose" hex="#ff8d7a" label-class="text-[color:var(--ds-ink)]/60" value-class="text-[color:var(--ds-ink)]" />
                    <x-design.color-swatch name="Blush" hex="#ffd2c9" label-class="text-[color:var(--ds-ink)]/60" value-class="text-[color:var(--ds-ink)]" />
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4">
                        <p class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Cloud</p>
                        <p class="text-sm text-slate-600">Superficies y tarjetas.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-mint)] p-4">
                        <p class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Mint</p>
                        <p class="text-sm text-slate-600">Estados positivos y badges.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-lilac)] p-4">
                        <p class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Lilac</p>
                        <p class="text-sm text-slate-600">Acciones secundarias.</p>
                    </div>
                </div>
            </div>
        </x-design.section>

        <x-design.section>
            <div class="flex flex-col gap-6">
                <x-design.eyebrow>Avatares</x-design.eyebrow>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4">
                        <x-design.avatar initials="AC" variant="coral" />
                        <div>
                            <p class="text-sm font-semibold text-[color:var(--ds-ink)]">Ana Cruz</p>
                            <p class="text-xs text-slate-600">Ventas</p>
                        </div>
                        <x-design.badge tone="mint" class="ml-auto">Activo</x-design.badge>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <x-design.avatar initials="MS" variant="navy" />
                        <div>
                            <p class="text-sm font-semibold text-[color:var(--ds-ink)]">M. Salazar</p>
                            <p class="text-xs text-slate-600">Soporte</p>
                        </div>
                        <x-design.badge tone="blush" class="ml-auto">Pendiente</x-design.badge>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-design.badge tone="cloud">Activo</x-design.badge>
                    <x-design.badge tone="blush">Pendiente</x-design.badge>
                    <x-design.badge tone="outline">Archivado</x-design.badge>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex flex-col gap-1">
                            <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">Etiquetas</span>
                            <p class="text-sm font-medium text-[color:var(--ds-navy)]">Filtra conversaciones por estado y prioridad.</p>
                        </div>
                        <button class="rounded-full bg-white px-4 py-2 text-xs font-semibold text-[color:var(--ds-navy)] shadow-sm">Editar</button>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <x-design.badge tone="navy">Importante</x-design.badge>
                        <x-design.badge tone="cloud">Seguimiento</x-design.badge>
                        <x-design.badge tone="blush">Nuevo</x-design.badge>
                        <x-design.badge tone="outline">Backlog</x-design.badge>
                    </div>
                </div>
            </div>
        </x-design.section>
    </div>
</div>
@endsection
