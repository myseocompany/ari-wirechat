@php
  $lastAction = $item->getLastUserAction();
  $diasSinContacto = $lastAction ? \Carbon\Carbon::parse($lastAction->created_at)->diffInDays(now()) : null;
  $authUser = auth()->user();
  $limited = $item->limited_access ?? ! $item->hasFullAccess($authUser);
  $visiblePhone = $item->getVisiblePhone($authUser);
  $visibleEmail = $item->getVisibleEmail($authUser);
  $visibleName = $item->getVisibleName($authUser);
@endphp

@php $cardUrl = request()->fullUrlWithQuery(['customer_id' => $item->id]); @endphp
<div class="card mb-3 bg-light customer-card" data-url="{{ $cardUrl }}" onmouseover="showEditIcon({{ $item->id }})" onmouseout="hideEditIcon({{ $item->id }})" style="cursor: pointer;">
  <div class="card-body p-2">
    <div class="row no-gutters align-items-center">

      {{-- Cliente: Iniciales + Nombre en una sola línea --}}
      <div class="col d-flex align-items-center">
        <div class="customer-circle small-circle mr-2" style="background-color: {{ $item->getStatusColor() }}">
          {{ $item->getInitials() }}
        </div>
        <div class="position-relative w-100">


          {{-- Nombre del cliente + estrellas en una sola línea --}}
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <a href="{{ request()->fullUrlWithQuery(['customer_id' => $item->id]) }}" class="font-weight-bold">
                {!! $item->maker === 1 ? '🥟' : ($item->maker === 0 ? '💡' : ($item->maker === 2 ? '🍗🥩⚙️' : '')) !!}
                &nbsp;{{ Str::limit($visibleName ?? 'Sin nombre', 21) }}
              </a>
            </div>

            @unless($limited)
              <div>
                @php $stars = $item->getScoringToNumber(); @endphp
                @for ($i = 1; $i <= 4; $i++)
                  <span style="color: {{ $i <= $stars ? 'gold' : 'lightgray' }}; font-size: 16px;">
                    {{ $i <= $stars ? '★' : '☆' }}
                  </span>
                @endfor

                @if($item->scoring_interest)
                  <span class="badge bg-secondary ml-1">{{ $item->scoring_interest }}</span>
                @endif
              </div>
            @endunless
          </div>




          {{-- País + Teléfono + Fecha --}}
          <div class="small text-muted mt-1">
            <i class="fa fa-calendar-plus-o ml-2"></i>
            {{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}

            @if($item->country && strlen($item->country) === 2)
              <img src="/img/flags/{{ strtolower($item->country) }}.svg" height="10">
            @else
              {{ $item->country }}
            @endif
            &nbsp;|&nbsp;
            @if($visiblePhone)
              <span class="d-inline-flex align-items-center phone-copy-wrapper" style="gap:8px;">
                <span class="badge badge-success mb-0 phone-pill" style="background-color:#21d196;color:#fff;border-radius:4px;padding:2px 6px;font-weight:500;">
                  {{ $visiblePhone }}
                </span>
                <button type="button" class="btn btn-link p-0 text-secondary copy-phone" data-phone="{{ $visiblePhone }}" aria-label="Copiar teléfono">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"></path>
                  </svg>
                </button>
              </span>
            @else
              <span class="text-muted">Teléfono restringido</span>
            @endif

            @if($visibleEmail)
              &nbsp;|&nbsp;
              <span class="text-muted">{{ $visibleEmail }}</span>
            @endif
            
          </div>

          {{-- Nota --}}
          @if(!$limited && $item->note)
            <div class="small text-muted mt-1">
              Nota: "{{ \Illuminate\Support\Str::limit($item->note, 40) }}"
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>

  {{-- Línea inferior: última acción + asesor (si no hay acción, muestra placeholder igual) --}}
  <div class="border-top px-3 py-2">
    <div class="d-flex justify-content-between align-items-center">

      {{-- Fecha última acción --}}
      @php
        $createdAt = $lastAction ? \Carbon\Carbon::parse($lastAction->created_at) : null;
        $diffDays = $createdAt ? $createdAt->diffInDays(now()) : null;
        $color = $createdAt
          ? ($diffDays < 2 ? 'success' : ($diffDays < 90 ? 'warning' : 'danger'))
          : 'secondary';
        $icon = $createdAt
          ? ($color === 'success' ? 'fa-check-circle' : ($color === 'warning' ? 'fa-clock-o' : 'fa-exclamation-triangle'))
          : 'fa-clock-o';
      @endphp

      @if($limited)
        <div>
          <span class="badge badge-secondary">
            <i class="fa fa-lock"></i> Acceso restringido
          </span>
        </div>
      @else
        <div>
          <span class="badge badge-{{ $color }}">
            <i class="fa {{ $icon }}"></i>
            {{ $createdAt ? $createdAt->diffForHumans() : 'Sin acciones' }}
          </span>
        </div>
      @endif

      {{-- Asesor --}}
      <a href="/customers/{{$item->id}}/show" class="advisor-link">
        <div class="d-flex align-items-center">
          @if($item->user)
            <small class="mr-2">{{ $item->user->name }}</small>
            <div class="customer-circle assessor-circle" style="background-color: #6c757d;">
              {{ $item->user->getInitials() }}
            </div>
          @else
            <small class="text-muted mr-2">Sin asesor</small>
            <div class="customer-circle assessor-circle" style="background-color: #ccc;">
              <i class="fa fa-user" aria-hidden="true"></i>
            </div>
          @endif
        </div>
      </a>

    </div>
    @if(!$limited && $lastAction && $lastAction->note)
      <div class="mt-1 text-muted" style="font-size: 0.85rem;">
        <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
        "{{ \Illuminate\Support\Str::limit($lastAction->note, 80) }}"
      </div>
    @endif
  </div>
</div>

<style>
  .customer-circle {
    border-radius: 50%;
    font-size: 0.75rem;
    text-align: center;
    color: #fff;
    font-weight: bold;
    line-height: 28px;
  }
  .small-circle {
    width: 28px;
    height: 28px;
    line-height: 28px;
    font-size: 0.75rem;
  }
  .assessor-circle {
    width: 28px;
    height: 28px;
    line-height: 28px;
    font-size: 0.75rem;
  }
  .stars-outer {
    position: relative;
    display: inline-block;
    width: 80px;
    height: 16px;
    background: url('/img/star-empty.png') repeat-x;
    background-size: contain;
  }
  .stars-inner {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: url('/img/star-filled.png') repeat-x;
    background-size: contain;
  }
</style>
