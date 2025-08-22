@php
  $lastAction = $item->getLastUserAction();
  $diasSinContacto = $lastAction ? \Carbon\Carbon::parse($lastAction->created_at)->diffInDays(now()) : null;
@endphp

<div class="card mb-3 bg-light" onmouseover="showEditIcon({{ $item->id }})" onmouseout="hideEditIcon({{ $item->id }})">
  <div class="card-body p-2">
    <div class="row no-gutters align-items-center">

      {{-- Cliente: Iniciales + Nombre en una sola línea --}}
      <div class="col d-flex align-items-center">
        <div class="customer-circle small-circle mr-2" style="background-color: {{ $item->getStatusColor() }}">
          {{ $item->getInitials() }}
        </div>
        <div class="position-relative w-100">

          {{-- Estrellas arriba a la derecha --}}
          @php $stars = $item->getScoringToNumber(); @endphp
          <div class="text-right">
            @for ($i = 1; $i <= 4; $i++)
              <span style="color: {{ $i <= $stars ? 'gold' : 'lightgray' }}; font-size: 16px;">
                {{ $i <= $stars ? '★' : '☆' }}
              </span>
            @endfor

            @if($item->scoring_interest)
              <span class="badge bg-secondary ml-1">{{ $item->scoring_interest }}</span>
            @endif
          </div>


          {{-- Nombre del cliente y maker --}}
          <a href="{{ request()->fullUrlWithQuery(['customer_id' => $item->id]) }}" class="font-weight-bold">
            {!! $item->maker === 1 ? '🥟' : ($item->maker === 0 ? '💡' : ($item->maker === 2 ? '🍗🥩⚙️' : '')) !!}
            &nbsp;{{ Str::limit($item->name ?? 'Sin nombre', 21) }}
          </a>

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
            <a href="{{ request()->fullUrlWithQuery(['customer_id' => $item->id]) }}">
              {{ $item->getBestPhoneCandidate()
                ? $item->getInternationalPhone($item->getBestPhoneCandidate())
                : 'Sin teléfono válido' }}
            </a>
            
          </div>

          {{-- Nota --}}
          @if($item->note)
            <div class="small text-muted mt-1">
              Nota: "{{ \Illuminate\Support\Str::limit($item->note, 40) }}"
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>

  {{-- Línea inferior: última acción + asesor --}}
  @if($lastAction)
    <div class="border-top px-3 py-2">
      <div class="d-flex justify-content-between align-items-center">

        {{-- Fecha última acción --}}
        @php
          $createdAt = \Carbon\Carbon::parse($lastAction->created_at);
          $diffDays = $createdAt->diffInDays(now());
          $color = $diffDays < 2 ? 'success' : ($diffDays < 90 ? 'warning' : 'danger');
          $icon = $color === 'success' ? 'fa-check-circle' : ($color === 'warning' ? 'fa-clock-o' : 'fa-exclamation-triangle');
        @endphp

        <div>
          <span class="badge badge-{{ $color }}">
            <i class="fa {{ $icon }}"></i> {{ $createdAt->diffForHumans() }}
          </span>
        </div>

        {{-- Asesor --}}
        <a href="/customers/{{$item->id}}/show">
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
      @if($lastAction->note)
        <div class="mt-1 text-muted" style="font-size: 0.85rem;">
          <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
          "{{ \Illuminate\Support\Str::limit($lastAction->note, 80) }}"
        </div>
      @endif
    </div>
  @endif
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
