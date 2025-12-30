@php
  $customersGroup = collect($customersGroup ?? []);
  $parentStatuses = collect($parent_statuses ?? []);
  $statusGroups = collect($statusGroups ?? []);
  $groupedParents = $customersGroup->keyBy('status_id');
  $parentCards = $parentStatuses->map(function ($status) use ($groupedParents) {
      $summary = $groupedParents->get($status->id);

      return (object) [
          'status_id' => $status->id,
          'status_name' => $status->name,
          'status_color' => $status->color ?? '#0f172a',
          'count' => (int) ($summary->count ?? 0),
      ];
  })->values();
  $totalParentCount = $parentCards->sum('count');
  $sum_g = $totalParentCount;
  $statusSummary = $statusGroups
      ->map(function ($item) {
          return (object) [
              'status_id' => $item->status_id ?? null,
              'status_name' => $item->status_name ?? 'Sin estado',
              'status_color' => $item->status_color ?? '#0f172a',
              'count' => (int) ($item->count ?? 0),
              'weight' => $item->weight ?? 9999,
          ];
      })
      ->filter(fn ($item) => $item->count > 0)
      ->sortBy('weight')
      ->values();
  $showParents = $showParents ?? true;
  $showChildren = $showChildren ?? true;
@endphp

@if($showParents)
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-dashboard="status-summary">
    @if(!empty($parentHeader) || !empty($parentSummary))
      <div class="mb-3 flex flex-col gap-1">
        @if(!empty($parentHeader))
          <span class="ds-mono text-xs uppercase tracking-[0.3em] text-slate-500">{{ $parentHeader }}</span>
        @endif
        @if(!empty($parentSummary))
          <p class="text-sm text-slate-600">{{ $parentSummary }}</p>
        @endif
      </div>
    @else
      <div class="mb-3 flex items-center justify-end text-[0.7rem] font-semibold uppercase tracking-[0.3em] text-slate-500">
        <span class="text-[color:var(--ds-navy)]">{{ number_format($totalParentCount) }}</span>
      </div>
    @endif
    @if($parentCards->isNotEmpty())
      <div class="grid gap-2 sm:grid-cols-2">
        @foreach($parentCards as $item)
          <button type="button"
            class="inline-flex items-center justify-between gap-3 rounded-2xl px-4 py-3 text-left text-white shadow-sm transition hover:brightness-110"
            style="background-color: {{ $item->status_color }}"
            onclick="changeParentStatus({{ $item->status_id }})">
            <span class="flex flex-col leading-tight">
              <span class="text-xs uppercase tracking-[0.25em] text-white/80">{{ $item->status_name }}</span>
              <span class="text-lg font-semibold">{{ number_format($item->count) }}</span>
            </span>
            <span class="text-xl">→</span>
          </button>
        @endforeach
      </div>
    @else
      <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4 text-sm text-[color:var(--ds-navy)] shadow-sm">
        Sin estados
      </div>
    @endif
  </div>
@endif

@if($showChildren)
  <div class="flex flex-col gap-3" data-dashboard="status-summary-children">
    <div class="flex items-center justify-between text-[0.7rem] font-semibold uppercase tracking-[0.3em] text-slate-500">
      <span>Estados filtrados</span>
    </div>

    @if($statusSummary->isNotEmpty())
      <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm font-sans" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';">
        <div class="flex flex-wrap sm:flex-nowrap">
        @foreach($statusSummary as $child)
          @php
            $isFirst = $loop->first;
            $overlap = 14;
            $clipPath = 'polygon(0 0, 100% 0, 100% 100%, 0 100%)';
            $borderRadius = ($isFirst ? '12px 0 0 12px' : '0') . ' ' . ($loop->last ? '0 12px 12px 0' : '0');
          @endphp
          <button type="button"
            class="relative flex min-w-[140px] flex-1 items-center justify-between gap-3 py-3 pl-4 pr-3 text-left text-white transition hover:brightness-110 focus:outline-none"
            style="
              font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
              background-color: {{ $child->status_color }};
              clip-path: {{ $clipPath }};
              margin-left: {{ $isFirst ? '0' : "-{$overlap}px" }};
              border-radius: {{ $borderRadius }};
              z-index: {{ 100 - $loop->index }};
              text-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
            "
            onclick="changeStatus({{ $child->status_id }})">
            <span class="flex flex-col leading-tight">
              <span class="text-sm font-semibold" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';">{{ number_format($child->count) }}</span>
              <span class="text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-white/90" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';">{{ $child->status_name }}</span>
            </span>
          </button>
        @endforeach
        </div>
      </div>
    @else
      <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4 text-sm text-[color:var(--ds-navy)] shadow-sm">
        Sin estados filtrados
      </div>
    @endif
  </div>
@endif
