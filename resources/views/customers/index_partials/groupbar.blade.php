@php
  $customersGroup = collect($customersGroup ?? []);
  $parentStatuses = collect($parent_statuses ?? []);
  $statusGroups = collect($statusGroups ?? []);
  $groupedParents = $customersGroup->keyBy('status_id');
  $parentSummary = $parentStatuses->map(function ($status) use ($groupedParents) {
      $summary = $groupedParents->get($status->id);

      return (object) [
          'status_id' => $status->id,
          'status_name' => $status->name,
          'status_color' => $status->color ?? '#0f172a',
          'count' => (int) ($summary->count ?? 0),
      ];
  })->values();
  $totalParentCount = $parentSummary->sum('count');
  $sum_g = $totalParentCount;
  $statusSummary = $statusGroups
      ->map(function ($item) {
          return (object) [
              'status_id' => $item->status_id ?? null,
              'status_name' => $item->status_name ?? 'Sin estado',
              'status_color' => $item->status_color ?? '#0f172a',
              'count' => (int) ($item->count ?? 0),
          ];
      })
      ->filter(fn ($item) => $item->count > 0)
      ->sortByDesc('count')
      ->values();
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-dashboard="status-summary">
  <div class="mb-3 flex items-center justify-between text-[0.7rem] font-semibold uppercase tracking-[0.3em] text-slate-500">
    <span>Estados principales</span>
    <span class="text-[color:var(--ds-navy)]">{{ number_format($totalParentCount) }}</span>
  </div>
  @if($parentSummary->isNotEmpty())
    <div class="grid gap-3 sm:grid-cols-2">
      @foreach($parentSummary as $item)
        <div class="rounded-2xl p-3 text-white shadow-sm" style="background-color: {{ $item->status_color }}">
          <p class="text-lg font-semibold">{{ number_format($item->count) }}</p>
          <a href="#" class="text-xs uppercase tracking-[0.2em] text-white/80 transition hover:text-white" onclick="changeParentStatus({{ $item->status_id }})">
            {{ $item->status_name }}
          </a>
        </div>
      @endforeach
    </div>
  @else
    <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4 text-sm text-[color:var(--ds-navy)] shadow-sm">
      Sin estados
    </div>
  @endif
</div>

<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-dashboard="status-summary-children">
  <div class="mb-3 flex items-center justify-between text-[0.7rem] font-semibold uppercase tracking-[0.3em] text-slate-500">
    <span>Estados filtrados</span>
    <span class="text-[color:var(--ds-navy)]">{{ number_format($statusSummary->sum('count')) }}</span>
  </div>

  @if($statusSummary->isNotEmpty())
    <div class="flex flex-col gap-2">
      @foreach($statusSummary as $child)
        <button type="button"
          class="flex w-full items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-[color:var(--ds-coral)] hover:text-[color:var(--ds-coral)]"
          onclick="changeStatus({{ $child->status_id }})">
          <span class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $child->status_color }}"></span>
            <span>{{ $child->status_name }}</span>
          </span>
          <span>{{ number_format($child->count) }}</span>
        </button>
      @endforeach
    </div>
  @else
    <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4 text-sm text-[color:var(--ds-navy)] shadow-sm">
      Sin estados filtrados
    </div>
  @endif
</div>
