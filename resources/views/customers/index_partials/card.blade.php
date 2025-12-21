@php
  $lastAction = $item->getLastUserAction();
  $diasSinContacto = $lastAction ? \Carbon\Carbon::parse($lastAction->created_at)->diffInDays(now()) : null;
  $authUser = auth()->user();
  $limited = $item->limited_access ?? ! $item->hasFullAccess($authUser);
  $visiblePhone = $item->getVisiblePhone($authUser);
  $visibleEmail = $item->getVisibleEmail($authUser);
  $visibleName = $item->getVisibleName($authUser);
@endphp

@php $cardUrl = route('customers.show', $item->id); @endphp
@php
  $createdAt = $lastAction ? \Carbon\Carbon::parse($lastAction->created_at) : null;
  $diffDays = $createdAt ? $createdAt->diffInDays(now()) : null;
  $color = $createdAt
    ? ($diffDays < 2 ? 'success' : ($diffDays < 90 ? 'warning' : 'danger'))
    : 'secondary';
  $icon = $createdAt
    ? ($color === 'success' ? 'fa-check-circle' : ($color === 'warning' ? 'fa-clock-o' : 'fa-exclamation-triangle'))
    : 'fa-clock-o';
  $toneMap = [
    'success' => 'mint',
    'warning' => 'blush',
    'danger' => 'coral',
    'secondary' => 'cloud',
  ];
  $badgeTone = $toneMap[$color] ?? 'cloud';
@endphp

<div class="customer-card group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md" data-url="{{ $cardUrl }}" onmouseover="showEditIcon({{ $item->id }})" onmouseout="hideEditIcon({{ $item->id }})" style="cursor: pointer;">
  <div class="flex gap-3">
    <div class="flex h-10 w-10 items-center justify-center rounded-full text-xs font-semibold text-white" style="background-color: {{ $item->getStatusColor() }}">
      {{ $item->getInitials() }}
    </div>
    <div class="flex-1">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
          <a href="{{ $cardUrl }}" class="customer-overlay-link block truncate text-sm font-semibold text-[color:var(--ds-ink)] transition hover:text-[color:var(--ds-coral)]" data-url="{{ $cardUrl }}">
            {!! $item->maker === 1 ? '🥟' : ($item->maker === 0 ? '💡' : ($item->maker === 2 ? '🍗🥩⚙️' : '')) !!}
            &nbsp;{{ Str::limit($visibleName ?? 'Sin nombre', 21) }}
          </a>
          <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1">
              <i class="fa fa-calendar-plus-o"></i>
              {{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}
            </span>
            <span class="text-slate-300">|</span>
            <span class="inline-flex items-center gap-1">
              @if($item->country && strlen($item->country) === 2)
                <img src="/img/flags/{{ strtolower($item->country) }}.svg" height="10" class="inline-block">
              @else
                {{ $item->country }}
              @endif
            </span>
            <span class="text-slate-300">|</span>
            @if($visiblePhone)
              <span class="inline-flex items-center gap-2">
                <x-design.badge tone="cloud" class="px-3 py-1 text-[0.7rem]">{{ $visiblePhone }}</x-design.badge>
                <button type="button" class="copy-phone rounded-full border border-slate-200 p-1 text-slate-400 transition hover:text-[color:var(--ds-coral)]" data-phone="{{ $visiblePhone }}" aria-label="Copiar teléfono">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"></path>
                  </svg>
                </button>
              </span>
            @else
              <span class="text-slate-400">Teléfono restringido</span>
            @endif

            @if($visibleEmail)
              <span class="text-slate-300">|</span>
              <span class="truncate text-slate-400">{{ $visibleEmail }}</span>
            @endif
          </div>

          @if(!$limited && $item->note)
            <p class="mt-2 text-xs text-slate-500">Nota: "{{ \Illuminate\Support\Str::limit($item->note, 40) }}"</p>
          @endif
        </div>

        @unless($limited)
          <div class="flex items-center gap-1 text-xs">
            @php $stars = $item->getScoringToNumber(); @endphp
            @for ($i = 1; $i <= 4; $i++)
              <span class="{{ $i <= $stars ? 'text-yellow-400' : 'text-slate-300' }}">{{ $i <= $stars ? '★' : '☆' }}</span>
            @endfor
            @if($item->scoring_interest)
              <x-design.badge tone="outline" class="px-2 py-1 text-[0.65rem]">{{ $item->scoring_interest }}</x-design.badge>
            @endif
          </div>
        @endunless
      </div>
    </div>
  </div>

  <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-3">
    @if($limited)
      <x-design.badge tone="outline" class="px-3 py-1 text-[0.7rem]">
        <i class="fa fa-lock mr-1"></i> Acceso restringido
      </x-design.badge>
    @else
      <x-design.badge tone="{{ $badgeTone }}" class="px-3 py-1 text-[0.7rem]">
        <i class="fa {{ $icon }} mr-1"></i>
        {{ $createdAt ? $createdAt->diffForHumans() : 'Sin acciones' }}
      </x-design.badge>
    @endif

    <a href="/customers/{{ $item->id }}/show" class="advisor-link">
      <div class="flex items-center gap-2">
        @if($item->user)
          <span class="text-xs font-semibold text-slate-600">{{ $item->user->name }}</span>
          <x-design.avatar initials="{{ $item->user->getInitials() }}" variant="navy" class="h-8 w-8 text-[0.65rem]" />
        @else
          <span class="text-xs text-slate-400">Sin asesor</span>
          <div class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400">
            <i class="fa fa-user" aria-hidden="true"></i>
          </div>
        @endif
      </div>
    </a>
  </div>

  @if(!$limited && $lastAction && $lastAction->note)
    <p class="mt-2 text-xs text-slate-500">
      <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
      "{{ \Illuminate\Support\Str::limit($lastAction->note, 80) }}"
    </p>
  @endif
</div>
