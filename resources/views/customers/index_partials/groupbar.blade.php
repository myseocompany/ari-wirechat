@if($customersGroup->count()!=0)
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-2 gap-3">
      @php
        $count = 0;
        $sum_g = 0;
      @endphp
      @foreach($customersGroup as $item)
        @if($item->count > 0)
          @php $count++; @endphp
        @endif
      @endforeach

      @foreach($customersGroup as $item)
        @if($item->count!=0)
          <div class="rounded-2xl p-3 text-white shadow-sm" style="background-color: {{ $item->status_color ?? '#0f172a' }}">
            <p class="text-lg font-semibold">{{ $item->count }}</p>
            <a href="#" class="text-xs uppercase tracking-[0.2em] text-white/80 transition hover:text-white" onclick="changeStatus({{ $item->status_id }})">
              {{ $item->status_name ?? 'sin estado' }}
            </a>
          </div>
          @php $sum_g += $item->count; @endphp
        @endif
      @endforeach
    </div>
  </div>
@else
  <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4 text-sm text-[color:var(--ds-navy)] shadow-sm">
    Sin estados
  </div>
@endif
