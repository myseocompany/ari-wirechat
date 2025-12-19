@php
  $authUser = auth()->user();
  $limited = ! $customer->hasFullAccess($authUser);
  $visibleName = $customer->getVisibleName($authUser);
  $visibleEmail = $customer->getVisibleEmail($authUser);
  $visiblePhone = $customer->getVisiblePhone($authUser);
@endphp

@if(!$customer instanceof \App\Models\Customer)
  {{-- Sin cliente válido, no renderizamos header --}}
  @php return; @endphp
@endif

<div class="container">
    <div class="row mb-2">
    <div class="col-md-12">

      {{-- Wrapper del header con fondo blanco unificado --}}
      <div class="row  bg-white rounded shadow-sm border-0 p-4">

        {{-- Columna izquierda: datos principales --}}
        <div class="col-md-8">

          {{-- Nombre con ícono --}}
          <h2 class="mb-2">
            @if(!$limited)
              @if($customer->maker == 1) 🥟 @endif
              @if($customer->maker == 0) 💡 @endif
              @if($customer->maker == 2) 🍗🥩⚙️ @endif
            @endif
            <a href="/customers/{{ $customer->id }}/show" class="text-decoration-none text-dark">
              {{ $visibleName }}
            </a>
          </h2>

          {{-- Empresa --}}
          @if(!$limited && !empty($customer->business))
            <h5 class="text-muted">{{ $customer->business }}</h5>
          @endif

          {{-- Teléfonos y correo --}}
@if($visiblePhone || $visibleEmail)
  <p class="mb-2">
    @if($visiblePhone)
      📞
      <span class="d-inline-flex align-items-center phone-copy-wrapper" style="gap:8px;">
        <span class="badge badge-success phone-pill" style="background-color:#21d196;color:#fff;border-radius:4px;padding:2px 6px;font-weight:500;">
          {{ $visiblePhone }}
        </span>
        <button type="button" class="btn btn-link p-0 text-secondary copy-phone" data-phone="{{ $visiblePhone }}" aria-label="Copiar teléfono">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"></path>
          </svg>
        </button>
      </span>
    @endif

    @if($visibleEmail)
      <span class="text-muted">✉️ {{ $visibleEmail }}</span>
    @endif
  </p>
@endif


          {{-- Ubicación --}}
          @if(!$limited && ($customer->country || $customer->department || $customer->city || $customer->address))
            <p class="text-muted mb-2">
              {{ $customer->country }}
              {{ $customer->department ? ', ' . $customer->department : '' }}
              {{ $customer->city ? ', ' . $customer->city : '' }}
              {{ $customer->address ? ', ' . $customer->address : '' }}
            </p>
          @endif

          {{-- Asignado a --}}
          @if($customer->user)
            <p class="text-muted mb-2"><i class="fa fa-user"></i> {{ $customer->user->name }}</p>
          @endif

          {{-- Cotización --}}
          @if(!$limited && $customer->total_sold)
            <p class="mt-2"><strong>Valor de la cotización:</strong> ${{ $customer->total_sold }}</p>
          @endif

          {{-- Imagen LinkedIn --}}
          @if(!$limited && !empty($customer->linkedin_url))
            <div class="my-3">
              <a href="{{ $customer->linkedin_url }}" target="_blank">
                <img src="{{ $customer->image_url }}" class="img-thumbnail rounded-circle" style="width: 120px;">
              </a>
            </div>
          @endif

          {{-- Fechas --}}
          @if(!$limited)
            <p class="text-muted small">
              <i class="fa fa-calendar"></i>
              creado: {{ $customer->created_at ?? 'N/A' }} / actualizado: {{ $customer->updated_at ?? 'N/A' }}
            </p>
          @endif

        </div>

        {{-- Columna derecha: estado, tipo y estrellas --}}
        <div class="col-md-4 text-end">

          {{-- Estado --}}
          @if($customer->status)
            <p>
              <span class="badge" style="background-color: {{ $customer->status->color }}">
                {{ $customer->status->name }}
              </span>
            </p>
          @endif

          {{-- Tipo de cliente --}}
          @if(!$limited)
            <p class="text-secondary fw-bold">
              @if($customer->maker == 1) Hace empanadas @endif
              @if($customer->maker == 0) Proyecto @endif
              @if($customer->maker == 2) Desmechadora @endif
            </p>
          @endif

          {{-- Estrellas --}}
          @if(!$limited)
            @php $stars = $customer->getScoringToNumber(); @endphp
            <div class="mb-2">
              @for ($i = 1; $i <= 4; $i++)
                <span style="color: {{ $i <= $stars ? 'gold' : 'lightgray' }}; font-size: 18px;">
                  {{ $i <= $stars ? '★' : '☆' }}
                </span>
              @endfor

              @if($customer->scoring_interest)
                <span class="badge bg-secondary ms-2">{{ $customer->scoring_interest }}</span>
              @endif
            </div>
          @endif

        </div>
      </div>


      <div class="row mt-3">
        <div class="col-md-12">
          {{-- Acciones rápidas deshabilitadas --}}
        </div>
      </div>

    
    </div>
     
  </div>

</div>
