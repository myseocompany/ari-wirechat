@extends('layout')

@section('content')
@push('styles')
  <x-design.styles />
@endpush

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
  <div class="flex flex-col gap-1">
    <h1 class="mb-1">Clientes con calculadora</h1>
    <div class="text-xs text-slate-500">{{ $model->total() }} clientes</div>
  </div>
</div>

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form action="/reports/views/customers_calculator" method="GET" class="flex flex-col gap-4">
    <div class="grid gap-4 lg:grid-cols-[repeat(12,minmax(0,1fr))]">
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="from_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desde</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="from_date" name="from_date" value="{{ $fromDate?->format('Y-m-d') ?? $request->from_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-3">
        <label for="to_date" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Hasta</label>
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="date" id="to_date" name="to_date" value="{{ $toDate?->format('Y-m-d') ?? $request->to_date }}">
      </div>
      <div class="col-span-12 flex flex-col gap-1 lg:col-span-4">
        <label for="user_id" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asesor</label>
        <select id="user_id" name="user_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none">
          <option value="">Todos</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}" @if ((string) $user->id === (string) $request->user_id) selected @endif>
              {{ $user->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="flex flex-wrap items-center justify-end gap-3">
      <button type="submit" class="inline-flex items-center rounded-xl bg-[color:var(--ds-coral)] px-4 py-2 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(255,92,92,0.35)]">Filtrar</button>
    </div>
  </form>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cliente</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Asesor</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Stage</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Score</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Completado</th>
          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Ultimo registro</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
        @foreach ($model as $item)
          @php
            $bestPhone = $item->getBestPhoneCandidate();
            $phoneDigits = $bestPhone ? preg_replace('/\D+/', '', $bestPhone) : null;
            $completedAt = $item->calculator_completed_at ? \Illuminate\Support\Carbon::parse($item->calculator_completed_at) : null;
            $lastCalculatorAt = $item->last_calculator_at ? \Illuminate\Support\Carbon::parse($item->last_calculator_at) : null;
          @endphp
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 font-semibold text-slate-900">
              <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                  <a href="{{ route('customers.show', $item->id) }}" class="hover:underline">{{ $item->name ?: 'Sin nombre' }}</a>
                  <a href="{{ route('customers.show', $item->id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Ver</a>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-normal text-slate-600">
                  <span>{{ $bestPhone ? $item->getInternationalPhone($bestPhone) : 'Sin telefono' }}</span>
                  @if ($phoneDigits)
                    <a
                      href="https://wa.me/{{ $phoneDigits }}"
                      target="_blank"
                      rel="noopener"
                      class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100"
                    >
                      WhatsApp
                    </a>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-4 py-3">{{ $item->user_name ?: 'Sin asesor' }}</td>
            <td class="px-4 py-3">
              @if ($item->status_name)
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold text-white" style="background-color: {{ $item->status_color ?: '#64748b' }}">
                  {{ $item->status_name }}
                </span>
              @else
                <span class="text-xs text-slate-500">Sin estado</span>
              @endif
            </td>
            <td class="px-4 py-3">{{ $item->calculator_stage ?: 'Sin stage' }}</td>
            <td class="px-4 py-3">
              @if (! is_null($item->calculator_score))
                {{ number_format((float) $item->calculator_score, 2) }}
              @else
                <span class="text-xs text-slate-500">-</span>
              @endif
            </td>
            <td class="px-4 py-3">
              @if ($completedAt)
                <div class="flex flex-col gap-1">
                  <span>{{ $completedAt->format('Y-m-d H:i') }}</span>
                  <span class="text-xs text-slate-500">{{ $completedAt->diffForHumans() }}</span>
                </div>
              @else
                <span class="text-xs text-slate-500">Sin fecha</span>
              @endif
            </td>
            <td class="px-4 py-3">
              @if ($lastCalculatorAt)
                <div class="flex flex-col gap-1">
                  <span>{{ $lastCalculatorAt->format('Y-m-d H:i') }}</span>
                  <span class="text-xs text-slate-500">{{ $lastCalculatorAt->diffForHumans() }}</span>
                </div>
              @else
                <span class="text-xs text-slate-500">Sin fecha</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">
  {{ $model->links() }}
</div>
@endsection
