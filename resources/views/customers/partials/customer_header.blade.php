@if($customer != null)
<div class="card mb-4 shadow-sm">
  <div class="card-body">

    {{-- NOMBRE + ICONO DE PERFIL --}}
    <h3 class="mb-0">
      @if($customer->isBanned())
        <i class="fa fa-exclamation-circle text-danger"></i>
        <span class="text-danger">{{ $customer->name }}</span>
      @else
        @switch($customer->maker)
          @case(1) 🥟 @break
          @case(0) 💡 @break
          @case(2) 🍗🥩⚙️ @break
        @endswitch
        {{ $customer->name }}
      @endif
    </h3>

    {{-- EMPRESA --}}
    @if(!empty($customer->business))
      <h5 class="text-muted">{{ $customer->business }}</h5>
    @endif

    {{-- TIPO DE PERFIL --}}
    @if($customer->maker !== null)
      <p class="mb-2">
        <span class="badge bg-info text-dark">
          @if($customer->maker == 1) Hace empanadas
          @elseif($customer->maker == 0) Proyecto
          @elseif($customer->maker == 2) Desmechadora
          @endif
        </span>
      </p>
    @endif

    {{-- USUARIO ASIGNADO --}}
    @if($customer->user)
      <p class="text-secondary mb-1">
        <i class="fa fa-user"></i> {{ $customer->user->name }}
      </p>
    @endif

    {{-- PUNTAJE DE INTERÉS --}}
    @if($customer->scoring_interest)
      <p>
        <span class="badge bg-secondary rounded-circle">
          {{ $customer->scoring_interest }}
        </span>
      </p>
    @endif

    {{-- SCORING VISUAL --}}
    <div class="scoring mb-2">
      @php $stars = $customer->getScoringToNumber(); @endphp
      @for ($i = 1; $i <= 4; $i++)
        @if ($i <= $stars)
          <span style="color: gold;">★</span>
        @else
          <span style="color: lightgray;">☆</span>
        @endif
      @endfor
    </div>

    {{-- ESTADO DEL CLIENTE --}}
    @if($customer->status)
      <span class="badge" style="background-color: {{ $customer->status->color }}">{{ $customer->status->name }}</span>
    @endif

    {{-- TOTAL COTIZADO --}}
    @if($customer->total_sold)
      <p class="mt-2"><strong>Valor de la cotización:</strong> ${{ number_format($customer->total_sold, 0, ',', '.') }}</p>
    @endif

    {{-- UBICACIÓN --}}
    @php
      $location = collect([$customer->country, $customer->department, $customer->city, $customer->address])
        ->filter()->implode(', ');
    @endphp
    @if($location)
      <p class="text-muted"><i class="fa fa-map-marker"></i> {{ $location }}</p>
    @endif

    {{-- CONTACTO --}}
    @if($customer->phone || $customer->phone2 || $customer->email)
      <p>
        @if($customer->phone)
          <a href="/customers/{{ $customer->id }}/show">{{ $customer->phone }}</a>
        @endif
        @if($customer->phone2)
          / <a href="/customers/{{ $customer->id }}/show">{{ $customer->phone2 }}</a>
        @endif
        @if($customer->email)
          / {{ $customer->email }}
        @endif
      </p>
    @endif

    {{-- FECHAS --}}
    <p class="text-muted small">
      <i class="fa fa-calendar"></i> Creado: {{ $customer->created_at }} / Actualizado: {{ $customer->updated_at }}
    </p>

    {{-- IMAGEN DE LINKEDIN --}}
    @if($customer->linkedin_url && $customer->image_url)
      <a href="{{ $customer->linkedin_url }}" target="_blank">
        <img src="{{ $customer->image_url }}" class="rounded-circle" style="width: 80px;">
      </a>
    @endif

    {{-- INCLUDES DE ACCIONES VISUALES --}}
    <div class="mt-3">
      @include('customers.action_poorly_rated')
      @include('customers.action_opportunity')
      @include('customers.action_sale_form')
      @include('customers.action_spare')
      @include('customers.action_PQR')
      @include('customers.action_order')
    </div>

  </div>
</div>
@endif
