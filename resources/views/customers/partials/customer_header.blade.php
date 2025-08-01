<div class="row ">
  <div class="col-md-12">

    {{-- Wrapper del header con fondo blanco unificado --}}
    <div class="row  bg-white rounded shadow-sm border p-4">

      {{-- Columna izquierda: datos principales --}}
      <div class="col-md-8">

        {{-- Nombre con ícono --}}
        <h2 class="mb-2">
          @if($customer->isBanned())
            <i class="fa fa-exclamation-circle text-danger"></i>
            <span class="text-danger">{{ $customer->name }}</span>
          @else
            @if($customer->maker == 1) 🥟 @endif
            @if($customer->maker == 0) 💡 @endif
            @if($customer->maker == 2) 🍗🥩⚙️ @endif
            {{ $customer->name }}
          @endif
        </h2>

        {{-- Empresa --}}
        @if(!empty($customer->business))
          <h5 class="text-muted">{{ $customer->business }}</h5>
        @endif

        {{-- Teléfonos y correo --}}
        @if($customer->phone || $customer->phone2 || $customer->email)
          <p class="mb-2">
            @if($customer->phone)
              <a href="/customers/{{ $customer->id }}/show" class="text-decoration-none me-2">📞 {{ $customer->phone }}</a>
            @endif
            @if($customer->phone2)
              <a href="/customers/{{ $customer->id }}/show" class="text-decoration-none me-2">📞 {{ $customer->phone2 }}</a>
            @endif
            @if($customer->email)
              <span class="text-muted">✉️ {{ $customer->email }}</span>
            @endif
          </p>
        @endif

        {{-- Ubicación --}}
        @if($customer->country || $customer->department || $customer->city || $customer->address)
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
        @if($customer->total_sold)
          <p class="mt-2"><strong>Valor de la cotización:</strong> ${{ $customer->total_sold }}</p>
        @endif

        {{-- Imagen LinkedIn --}}
        @if(!empty($customer->linkedin_url))
          <div class="my-3">
            <a href="{{ $customer->linkedin_url }}" target="_blank">
              <img src="{{ $customer->image_url }}" class="img-thumbnail rounded-circle" style="width: 120px;">
            </a>
          </div>
        @endif

        {{-- Fechas --}}
        <p class="text-muted small">
          <i class="fa fa-calendar"></i>
          creado: {{ $customer->created_at ?? 'N/A' }} / actualizado: {{ $customer->updated_at ?? 'N/A' }}
        </p>

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
        <p class="text-secondary fw-bold">
          @if($customer->maker == 1) Hace empanadas @endif
          @if($customer->maker == 0) Proyecto @endif
          @if($customer->maker == 2) Desmechadora @endif
        </p>

        {{-- Estrellas --}}
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

      </div>
    </div>

    {{-- Acciones rápidas --}}
    <div class="row">
      <div class="col-md-12">
        @include('customers.action_poorly_rated')
        @include('customers.action_opportunity')
        @include('customers.action_sale_form')
        @include('customers.action_spare')
        @include('customers.action_PQR')
        @include('customers.action_order')
      </div>
    </div>

  </div>
</div>
