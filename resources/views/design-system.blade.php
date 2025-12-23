@extends('layouts.tailwind')

@push('styles')
  <x-design.styles />
@endpush

@section('content')
<div class="ds-body min-h-screen bg-[color:var(--ds-cloud)]">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3">
            <x-design.eyebrow>Design system</x-design.eyebrow>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h1 class="ds-display text-3xl font-semibold text-[color:var(--ds-ink)] sm:text-4xl">Wirechat UI Kit</h1>
                <div class="flex items-center gap-2">
                    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-400">v2</span>
                    <x-design.badge class="border border-slate-200 bg-white text-xs font-semibold text-slate-600">Layouts + palette</x-design.badge>
                </div>
            </div>
            <p class="max-w-2xl text-sm text-slate-600">Componentes redondeados, chips de estado y sombras suaves con base clara.</p>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)]">
            <div class="flex items-center gap-4">
                <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-400">Palette</span>
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>
            <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-5">
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Primary</p>
                    <div class="flex gap-2">
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-primary)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-primary-soft)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#d7ddff]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#ecefff]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#f6f7ff]"></span>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Emerald</p>
                    <div class="flex gap-2">
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-emerald)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#6bd18f]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#a9e6c1]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-emerald-soft)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#eefbf4]"></span>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Orange</p>
                    <div class="flex gap-2">
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-orange)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#f6a56f]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#f8c9a8]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-orange-soft)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#fdf1e8]"></span>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Red</p>
                    <div class="flex gap-2">
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-red)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#f58ea0]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#f7b6c0]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-red-soft)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#fde9ec]"></span>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Secondary</p>
                    <div class="flex gap-2">
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-secondary)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[color:var(--ds-secondary-soft)]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#ee7ab4]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#27b0e5]"></span>
                        <span class="h-10 w-10 rounded-xl bg-[#a3a3a3]"></span>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_16px_32px_rgba(15,23,42,0.06)]">
                <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                    <span>Created by</span>
                    <span>Replies</span>
                    <span>Status</span>
                </div>
                <div class="mt-4 flex flex-col gap-3">
                    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-[color:var(--ds-cloud)] px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[color:var(--ds-primary)] text-xs font-semibold text-white">AQ</span>
                            <span class="text-sm font-medium text-slate-700">Adrian Q.</span>
                        </div>
                        <span class="text-xs text-slate-500">1</span>
                        <span class="rounded-full bg-[color:var(--ds-emerald-soft)] px-2 py-1 text-[11px] font-semibold text-[color:var(--ds-emerald)]">Replied</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[color:var(--ds-secondary)] text-xs font-semibold text-white">VR</span>
                            <span class="text-sm font-medium text-slate-700">Vicenza R.</span>
                        </div>
                        <span class="text-xs text-slate-500">0</span>
                        <span class="rounded-full bg-[color:var(--ds-orange-soft)] px-2 py-1 text-[11px] font-semibold text-[color:var(--ds-orange)]">Open</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[color:var(--ds-red)] text-xs font-semibold text-white">PS</span>
                            <span class="text-sm font-medium text-slate-700">Philip Smith</span>
                        </div>
                        <span class="text-xs text-slate-500">4</span>
                        <span class="rounded-full bg-[color:var(--ds-red-soft)] px-2 py-1 text-[11px] font-semibold text-[color:var(--ds-red)]">Closed</span>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_16px_32px_rgba(15,23,42,0.06)]">
                <div class="flex items-center justify-between">
                    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-400">Buttons</span>
                    <span class="text-xs font-semibold text-slate-400">Rounded</span>
                </div>
                <div class="mt-4 grid gap-3">
                    <button class="rounded-full bg-[color:var(--ds-primary)] px-4 py-3 text-sm font-semibold text-white">Large Button</button>
                    <button class="rounded-full bg-[color:var(--ds-secondary)] px-4 py-2 text-sm font-semibold text-white">Medium Button</button>
                    <button class="rounded-full bg-[color:var(--ds-primary-soft)] px-3 py-2 text-xs font-semibold text-[color:var(--ds-ink)]">Small Button</button>
                    <button class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500">Tiny Button</button>
                </div>
                <div class="mt-4 rounded-2xl border border-slate-100 bg-[color:var(--ds-cloud)] px-3 py-2 text-xs text-slate-500">
                    Invite to Conversation
                    <div class="mt-2 flex items-center gap-2 rounded-full bg-white px-3 py-2">
                        <span class="text-xs font-semibold text-slate-600">Philip Smith</span>
                        <span class="text-xs text-slate-400">x</span>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_16px_32px_rgba(15,23,42,0.06)]">
                <div class="flex items-center justify-between">
                    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-400">Tags</span>
                    <span class="text-xs text-slate-400">Status chips</span>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full bg-[color:var(--ds-orange-soft)] px-3 py-1 text-xs font-semibold text-[color:var(--ds-orange)]">Pending</span>
                    <span class="rounded-full bg-[color:var(--ds-emerald-soft)] px-3 py-1 text-xs font-semibold text-[color:var(--ds-emerald)]">Confirmed</span>
                    <span class="rounded-full bg-[color:var(--ds-red-soft)] px-3 py-1 text-xs font-semibold text-[color:var(--ds-red)]">Alert</span>
                    <span class="rounded-full bg-[color:var(--ds-primary-soft)] px-3 py-1 text-xs font-semibold text-[color:var(--ds-ink)]">Active</span>
                    <span class="rounded-full bg-[#f3f4f6] px-3 py-1 text-xs font-semibold text-slate-500">Closed</span>
                </div>
                <div class="mt-5 rounded-2xl border border-slate-100 bg-[color:var(--ds-cloud)] p-3 text-xs text-slate-500">
                    This booking has not yet been confirmed.
                    <span class="ml-2 rounded-full bg-[#fff3db] px-2 py-1 text-[11px] font-semibold text-[#b96a2f]">Waiting</span>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_16px_32px_rgba(15,23,42,0.06)]">
                <div class="flex items-center justify-between">
                    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-400">Benefits</span>
                    <span class="text-xs text-slate-400">View all</span>
                </div>
                <div class="mt-4 flex flex-col gap-4 text-sm text-slate-600">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                            <span>All day pass</span>
                            <span>6/10</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100">
                            <div class="h-full w-3/5 rounded-full bg-[color:var(--ds-primary)]"></div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                            <span>Booking credits</span>
                            <span>8/20</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100">
                            <div class="h-full w-2/5 rounded-full bg-[color:var(--ds-secondary)]"></div>
                        </div>
                    </div>
                </div>
                <button class="mt-5 rounded-full bg-[color:var(--ds-primary)] px-4 py-2 text-xs font-semibold text-white">Join a Plan</button>
            </section>

            <section class="rounded-3xl border border-dashed border-slate-200 bg-white p-5 text-center shadow-[0_16px_32px_rgba(15,23,42,0.06)]">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[color:var(--ds-cloud)] text-slate-400">
                    <span class="text-lg">+</span>
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-700">No messages yet</p>
                <p class="mt-1 text-xs text-slate-500">Start a new conversation to fill this space.</p>
                <button class="mt-4 rounded-full bg-[color:var(--ds-primary)] px-4 py-2 text-xs font-semibold text-white">Start Conversation</button>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_16px_32px_rgba(15,23,42,0.06)]">
                <div class="flex items-center justify-between">
                    <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-400">Quick actions</span>
                    <span class="text-xs text-slate-400">Desk 8</span>
                </div>
                <div class="mt-4 rounded-2xl border border-slate-100 bg-[color:var(--ds-cloud)] p-3 text-xs text-slate-500">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-700">Apple Calendar</span>
                        <span class="rounded-full bg-[color:var(--ds-emerald-soft)] px-2 py-1 text-[11px] font-semibold text-[color:var(--ds-emerald)]">Active</span>
                    </div>
                    <p class="mt-1 text-[11px]">Manage bookings directly from your calendar.</p>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500">Make a Booking</button>
                    <button class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500">Book an Event</button>
                    <button class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500">Buy a Product</button>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
