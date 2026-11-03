@extends('layouts.tailwind')

@section('title', 'Mapa de ventas')

@push('styles')
  <x-design.styles />
  <style>
    /* Clases propias: el build de Tailwind del servidor no incluye los valores
       arbitrarios de esta vista y aqui los despliegues son manuales, asi que la
       maqueta no debe depender de un `npm run build`. */
    #mapa-ventas { height: 520px; border-radius: 20px; }
    .mapa-pais-item.is-activo { border-color: var(--ds-primary); background: var(--ds-lilac); }
    .mapa-lista { max-height: 520px; overflow-y: auto; }
    .mapa-rotulo { letter-spacing: 0.18em; }
    .mapa-panel-compacto { padding: 1rem; }
    .mapa-clientes-lista { max-height: 560px; overflow-y: auto; }
    .mapa-cliente-fila { display: grid; grid-template-columns: minmax(0,1fr) 150px 110px 90px; gap: .75rem; align-items: baseline; }
    @media (max-width: 900px) { .mapa-cliente-fila { grid-template-columns: minmax(0,1fr) 90px; } .mapa-cliente-oculta-movil { display: none; } }
    .leaflet-container { font-family: inherit; }
    @media (min-width: 1024px) {
      .mapa-layout { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 1.5rem; align-items: start; }
      .mapa-aside { position: sticky; top: 6rem; }
    }
  </style>
@endpush

@section('content')
<div class="ds-body flex flex-col gap-6">

  {{-- Encabezado --}}
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <x-design.eyebrow>Análisis de territorio</x-design.eyebrow>
      <h1 class="mb-1 mt-2 text-2xl font-semibold text-[color:var(--ds-navy)]">
        Mapa de ventas — ¿dónde ha vendido MQE?
      </h1>
      <p class="text-sm text-slate-500">
        Datos agregados por país. No se muestra ningún cliente individual.
      </p>
    </div>
    <div class="text-right text-xs leading-relaxed text-slate-500">
      <span class="font-semibold text-[color:var(--ds-navy)]">Fuente:</span>
      <code>customers</code>, estado <em>Ganado Maquinas</em><br>
      <span class="font-semibold text-[color:var(--ds-navy)]">Corte:</span>
      {{ now()->format('d/m/Y H:i') }}<br>
      <span class="font-semibold text-[color:var(--ds-navy)]">Método:</span>
      campo <code>country</code>; indicativo telefónico como respaldo
    </div>
  </div>

  {{-- Indicadores --}}
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @php
      $tarjetas = [
        ['Ventas ganadas', number_format($total), 'registros en estado Ganado Maquinas', null],
        ['Con país identificado', number_format($identificados),
          ($total > 0 ? round($identificados / $total * 100) : 0).'% de cobertura · '
          .number_format($porFuente['sin_dato']).' sin país determinable', null],
        ['Países con venta', (string) $countries->count(),
          'el reporte SEO analiza '.$mercadosReporteSeo
          .($soloInferidos->isNotEmpty() ? ' · '.$soloInferidos->count().' sin respaldo del CRM' : ''), null],
        ['Venta en puntos ciegos', number_format($puntosCiegos->sum('ventas')),
          'en países que el reporte SEO no analiza ('
          .($identificados > 0 ? round($puntosCiegos->sum('ventas') / $identificados * 100) : 0).'%)',
          'var(--ds-orange)'],
      ];
    @endphp
    @foreach($tarjetas as [$rotulo, $cifra, $pie, $acento])
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
           @if($acento) style="border-left:4px solid {{ $acento }};" @endif>
        <div class="ds-mono mapa-rotulo text-xs uppercase text-slate-500">{{ $rotulo }}</div>
        <div class="mt-1 text-3xl font-semibold leading-tight text-[color:var(--ds-navy)]">{{ $cifra }}</div>
        <div class="mt-1 text-xs text-slate-500">{{ $pie }}</div>
      </div>
    @endforeach
  </div>

  {{-- Barra lateral de países + mapa --}}
  <div class="mapa-layout flex flex-col gap-6">

    <aside class="mapa-aside">
      <x-design.section class="mapa-panel-compacto">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-[color:var(--ds-navy)]">Países</h2>
          <span class="ds-mono text-xs text-slate-500">{{ $countries->count() }}</span>
        </div>

        <input type="text" id="filtro-paises" placeholder="Buscar país o código…"
               class="mb-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-[color:var(--ds-navy)] placeholder:text-slate-400 focus:border-[color:var(--ds-primary)] focus:outline-none">

        <ul class="mapa-lista flex flex-col gap-1 pr-1" id="lista-paises">
          @foreach($countries as $country)
            @php
              $tono = $country['solo_inferido']
                ? 'var(--ds-red)'
                : ($country['en_reporte_seo'] ? 'var(--ds-primary)' : 'var(--ds-orange)');
            @endphp
            <li>
              <button type="button"
                      class="mapa-pais-item w-full rounded-xl border border-transparent px-3 py-2 text-left transition hover:border-slate-200 hover:bg-[color:var(--ds-cloud)]"
                      data-iso2="{{ $country['iso2'] }}"
                      @if($country['lat'] !== null) data-lat="{{ $country['lat'] }}" data-lng="{{ $country['lng'] }}" @endif
                      data-buscar="{{ mb_strtolower($country['name'].' '.$country['iso2']) }}">
                <div class="flex items-baseline justify-between gap-2">
                  <span class="flex items-baseline gap-2 truncate">
                    <span class="ds-mono text-xs font-semibold" style="color:{{ $tono }}">{{ $country['iso2'] }}</span>
                    <span class="truncate text-sm text-[color:var(--ds-navy)]">{{ $country['name'] }}</span>
                  </span>
                  <span class="text-sm font-semibold text-[color:var(--ds-navy)]">{{ number_format($country['ventas']) }}</span>
                </div>
                <div class="mt-1.5 h-1 w-full overflow-hidden rounded-full bg-[color:var(--ds-cloud)]">
                  <div class="h-full rounded-full"
                       style="width:{{ max(2, (int) round($country['ventas'] / max(1, $maxVentas) * 100)) }}%;background:{{ $tono }}"></div>
                </div>
                <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                  <span>{{ number_format($country['share'], 1) }}%</span>
                  <span>
                    @if($country['solo_inferido'])
                      solo teléfono
                    @else
                      {{ $country['desde_crm'] }} CRM
                      @if($country['inferido_telefono'] > 0) + {{ $country['inferido_telefono'] }} tel @endif
                    @endif
                  </span>
                </div>
              </button>
            </li>
          @endforeach
        </ul>

        <p id="sin-resultados" class="mt-3 hidden text-xs text-slate-500">Ningún país coincide.</p>

        @if($porFuente['sin_dato'] > 0)
          <button type="button"
                  class="mapa-pais-item mt-3 w-full rounded-xl border border-dashed border-slate-300 px-3 py-2 text-left transition hover:bg-[color:var(--ds-cloud)]"
                  data-iso2="SIN_PAIS" data-buscar="sin pais sin país">
            <div class="flex items-baseline justify-between gap-2">
              <span class="text-sm text-slate-600">Sin país determinable</span>
              <span class="text-sm font-semibold text-slate-600">{{ number_format($porFuente['sin_dato']) }}</span>
            </div>
            <div class="mt-1 text-xs text-slate-500">pendientes de completar en su ficha</div>
          </button>
        @endif

        <div class="mt-4 flex flex-col gap-1.5 border-t border-slate-100 pt-3 text-xs text-slate-500">
          @foreach([['var(--ds-primary)','analizado en el reporte SEO'],['var(--ds-orange)','punto ciego'],['var(--ds-red)','solo inferido del teléfono']] as [$c, $txt])
            <span class="flex items-center gap-2">
              <span class="inline-block h-2 w-2 rounded-full" style="background:{{ $c }}"></span>{{ $txt }}
            </span>
          @endforeach
        </div>
      </x-design.section>
    </aside>

    <div class="flex flex-col gap-6">
      <x-design.section>
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
          <h2 class="text-sm font-semibold text-[color:var(--ds-navy)]">Distribución geográfica</h2>
          <p class="text-xs text-slate-500">
            El área del círculo es proporcional a las ventas y se ubica en el centroide del país,
            no en la ubicación del cliente.
            @if($sinCentroide->isNotEmpty())
              {{ $sinCentroide->count() }} sin coordenada: {{ $sinCentroide->implode(', ') }}.
            @endif
          </p>
        </div>
        <div id="mapa-ventas"></div>
      </x-design.section>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-design.section style="border-top:3px solid var(--ds-orange)">
          <h2 class="text-sm font-semibold text-[color:var(--ds-navy)]">Puntos ciegos — venden y no los miramos</h2>
          <p class="mb-3 mt-1 text-xs text-slate-500">
            Países con venta que el reporte SEO de maquiempanadas.com no analiza.
          </p>
          @if($puntosCiegos->isEmpty())
            <p class="text-sm text-slate-500">Ninguno: todos los países con venta están en el reporte.</p>
          @else
            <ul class="flex flex-col gap-1.5 text-sm text-[color:var(--ds-navy)]">
              @foreach($puntosCiegos->take(10) as $country)
                <li class="flex items-baseline justify-between gap-3">
                  <span>
                    {{ $country['name'] }}
                    @if($country['solo_inferido'])
                      <x-design.badge tone="outline" class="!px-2 !py-0.5 !text-xs">sin CRM</x-design.badge>
                    @endif
                  </span>
                  <span class="whitespace-nowrap font-semibold">
                    {{ number_format($country['ventas']) }}
                    <span class="font-normal text-slate-500">({{ number_format($country['share'], 1) }}%)</span>
                  </span>
                </li>
              @endforeach
              @if($puntosCiegos->count() > 10)
                <li class="text-xs text-slate-500">y {{ $puntosCiegos->count() - 10 }} país(es) más en la barra lateral.</li>
              @endif
            </ul>
          @endif
        </x-design.section>

        <x-design.section style="border-top:3px solid var(--ds-primary)">
          <h2 class="text-sm font-semibold text-[color:var(--ds-navy)]">Priorizados por SEO, sin venta</h2>
          <p class="mb-3 mt-1 text-xs text-slate-500">
            Mercados que el reporte analiza y donde el CRM no registra ninguna venta ganada.
          </p>
          @if($sinVenta->isEmpty())
            <p class="text-sm text-slate-500">Ninguno: todos los mercados analizados tienen al menos una venta.</p>
          @else
            <div class="flex flex-wrap gap-2">
              @foreach($sinVenta as $mercado)
                <x-design.badge tone="cloud">{{ $mercado }}</x-design.badge>
              @endforeach
            </div>
          @endif
        </x-design.section>
      </div>

      <x-design.section class="!bg-[color:var(--ds-cloud)]">
        <h2 class="text-sm font-semibold text-[color:var(--ds-navy)]">Cómo leer esta página — límites del dato</h2>
        <ul class="mt-2 flex list-disc flex-col gap-1.5 pl-5 text-xs text-slate-600">
          <li><strong>Procedencia:</strong> {{ number_format($porFuente['crm']) }} registros traen el país en
              <code>country</code>, {{ number_format($porFuente['telefono']) }} se infieren del indicativo y
              {{ number_format($porFuente['sin_dato']) }} no tienen país determinable. El indicativo da país, nunca ciudad.</li>
          @if($soloInferidos->isNotEmpty())
            <li><strong>{{ $soloInferidos->count() }} país(es) se sostienen solo en el indicativo</strong>
                ({{ $soloInferidos->pluck('iso2')->implode(', ') }}), sin un registro del CRM. Probable ruido de
                números mal digitados, no mercados.</li>
          @endif
          <li><strong>Una venta es un registro en estado Ganado Maquinas</strong>, no una factura. No representa monto ni unidades.</li>
          <li><strong>Sin dimensión temporal:</strong> acumulado histórico al corte, no ventas del periodo.</li>
          <li><strong>El indicativo +1 no separa EE. UU. de Canadá:</strong> se atribuye a Estados Unidos y queda marcado como inferido.</li>
          <li><strong>Lee la base a la que apunte el entorno.</strong> En local el repo apunta a una copia parcial y
              renumerada; las cifras reales son las del CRM de producción.</li>
        </ul>
      </x-design.section>
    </div>
  </div>

  {{-- Listado de clientes. Pagina interna tras `auth`: aqui si corresponde
       identificar al cliente y enlazar a su ficha. --}}
  <x-design.section>
    <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
      <div>
        <h2 class="text-sm font-semibold text-[color:var(--ds-navy)]">Clientes</h2>
        <p id="clientes-subtitulo" class="mt-1 text-xs text-slate-500">
          Elige un país en la barra lateral para ver sus clientes.
        </p>
      </div>
      <input type="text" id="filtro-clientes" placeholder="Buscar nombre, empresa o ciudad…"
             class="w-full max-w-xs rounded-xl border border-slate-200 px-3 py-2 text-sm text-[color:var(--ds-navy)] placeholder:text-slate-400 focus:border-[color:var(--ds-primary)] focus:outline-none">
    </div>

    <div id="clientes-contenedor" class="mapa-clientes-lista hidden flex-col divide-y divide-slate-100"></div>

    <p id="clientes-vacio" class="hidden pt-2 text-xs text-slate-500">Ningún cliente coincide con la búsqueda.</p>
  </x-design.section>
</div>
@endsection

@section('footer_scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  var datos = @json($mapeables);
  var maxVentas = {{ $maxVentas }};
  var nombrePorIso2 = @json($countries->pluck('name', 'iso2'));
  var conteoPorIso2 = @json($clientesPorPais);
  // Payload compacto en vez de 2.500 filas renderizadas en servidor: el HTML
  // pasaria de ~50 KB a ~2,4 MB y solo se mira un pais a la vez.
  // i=id, n=nombre, e=empresa, c=ciudad, p=iso2, f=fuente
  var clientes = @json($clientesPayload);
  var urlFicha = @json(route('customers.show', '__ID__'));

  var items = Array.prototype.slice.call(document.querySelectorAll('.mapa-pais-item'));
  var contenedorClientes = document.getElementById('clientes-contenedor');
  var subtitulo = document.getElementById('clientes-subtitulo');
  var buscadorClientes = document.getElementById('filtro-clientes');
  var avisoClientesVacio = document.getElementById('clientes-vacio');
  var isoSeleccionado = null;

  // --- Listado de clientes: independiente del mapa, para que funcione aunque
  //     Leaflet no cargue (CDN bloqueado, sin red, etc.). ---
  function pintarClientes() {
    if (!contenedorClientes) { return; }

    if (!isoSeleccionado) {
      contenedorClientes.classList.add('hidden');
      if (avisoClientesVacio) { avisoClientesVacio.classList.add('hidden'); }
      return;
    }

    var q = buscadorClientes ? buscadorClientes.value.toLowerCase().trim() : '';

    var coincidencias = clientes.filter(function (c) {
      if (c.p !== isoSeleccionado) { return false; }
      if (!q) { return true; }
      return (c.n + ' ' + c.e + ' ' + c.c).toLowerCase().indexOf(q) !== -1;
    });
    var visibles = coincidencias.length;

    // Se reconstruye solo el pais seleccionado: el DOM nunca pasa de unos pocos
    // miles de nodos aunque el CRM tenga decenas de miles de clientes.
    contenedorClientes.textContent = '';
    var fragmento = document.createDocumentFragment();

    coincidencias.forEach(function (c) {
      var fila = document.createElement('div');
      fila.className = 'mapa-cliente-fila px-1 py-2';

      var titulo = document.createElement('span');
      titulo.className = 'truncate text-sm';
      titulo.style.color = 'var(--ds-navy)';
      titulo.appendChild(document.createTextNode(c.n || '(sin nombre)'));
      if (c.e && c.e !== c.n) {
        var empresa = document.createElement('span');
        empresa.className = 'text-slate-500';
        empresa.textContent = ' \u00b7 ' + c.e;
        titulo.appendChild(empresa);
      }

      var ciudad = document.createElement('span');
      ciudad.className = 'mapa-cliente-oculta-movil truncate text-xs text-slate-500';
      ciudad.textContent = c.c || '\u2014';

      var origen = document.createElement('span');
      origen.className = 'mapa-cliente-oculta-movil text-xs';
      if (c.f === 'crm') {
        origen.className += ' text-slate-400';
        origen.textContent = 'pais en ficha';
      } else if (c.f === 'telefono') {
        origen.style.color = 'var(--ds-orange)';
        origen.textContent = 'inferido';
      } else {
        origen.style.color = 'var(--ds-red)';
        origen.textContent = 'sin pais';
      }

      var enlace = document.createElement('a');
      enlace.className = 'text-right text-xs font-semibold hover:underline';
      enlace.style.color = 'var(--ds-primary)';
      enlace.href = urlFicha.replace('__ID__', encodeURIComponent(c.i));
      enlace.target = '_blank';
      enlace.rel = 'noopener';
      enlace.textContent = 'Ver ficha \u2192';

      fila.appendChild(titulo);
      fila.appendChild(ciudad);
      fila.appendChild(origen);
      fila.appendChild(enlace);
      fragmento.appendChild(fila);
    });

    contenedorClientes.appendChild(fragmento);
    contenedorClientes.classList.remove('hidden');
    contenedorClientes.classList.add('flex');
    if (avisoClientesVacio) { avisoClientesVacio.classList.toggle('hidden', visibles > 0); }

    if (subtitulo) {
      var etiqueta = isoSeleccionado === 'SIN_PAIS'
        ? 'Sin país determinable'
        : (nombrePorIso2[isoSeleccionado] || isoSeleccionado);
      var total = conteoPorIso2[isoSeleccionado] || 0;
      subtitulo.textContent = etiqueta + ' · ' + visibles + ' de ' + total
        + (total === 1 ? ' cliente' : ' clientes')
        + (q ? ' (filtrado)' : '');
    }
  }

  function seleccionarPais(iso2) {
    isoSeleccionado = iso2;
    items.forEach(function (otro) {
      otro.classList.toggle('is-activo', otro.dataset.iso2 === iso2);
    });
    pintarClientes();
  }

  items.forEach(function (item) {
    item.addEventListener('click', function () {
      seleccionarPais(this.dataset.iso2);
      if (typeof window.mapaEnfocarPais === 'function') {
        window.mapaEnfocarPais(this.dataset.iso2);
      }
      if (contenedorClientes) {
        contenedorClientes.scrollTop = 0;
      }
    });
  });

  if (buscadorClientes) {
    buscadorClientes.addEventListener('input', pintarClientes);
  }

  // --- Filtro de la barra lateral de paises ---
  var entrada = document.getElementById('filtro-paises');
  var avisoVacio = document.getElementById('sin-resultados');

  if (entrada) {
    entrada.addEventListener('input', function () {
      var q = this.value.toLowerCase().trim();
      var visibles = 0;
      items.forEach(function (item) {
        var coincide = !q || item.dataset.buscar.indexOf(q) !== -1;
        var contenedor = item.parentElement.tagName === 'LI' ? item.parentElement : item;
        contenedor.hidden = !coincide;
        if (coincide) { visibles++; }
      });
      if (avisoVacio) { avisoVacio.classList.toggle('hidden', visibles > 0); }
    });
  }

  // --- Mapa: realce. Si falla, el listado y los filtros siguen funcionando. ---
  var contenedorMapa = document.getElementById('mapa-ventas');
  if (!contenedorMapa || !datos.length || typeof L === 'undefined') { return; }

  var raiz = getComputedStyle(document.documentElement);
  var paleta = {
    seo: raiz.getPropertyValue('--ds-primary').trim() || '#4f6cf7',
    ciego: raiz.getPropertyValue('--ds-orange').trim() || '#f28c4b',
    inferido: raiz.getPropertyValue('--ds-red').trim() || '#f26a7a'
  };

  var mapa = L.map('mapa-ventas', { scrollWheelZoom: false, zoomSnap: 0.25 }).setView([2, -70], 3);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 10, attribution: '&copy; OpenStreetMap'
  }).addTo(mapa);

  var marcadores = {};
  var limites = [];

  // De mayor a menor: los paises pequenos quedan encima y siguen siendo apuntables.
  datos.slice().sort(function (a, b) { return b.ventas - a.ventas; }).forEach(function (pais) {
    // Radio en pixeles: el area crece con la raiz cuadrada y el rango visual se
    // mantiene legible con un maximo de 60 o de 1.500 ventas.
    var radio = 5 + Math.sqrt(pais.ventas / maxVentas) * 19;
    var color = pais.solo_inferido ? paleta.inferido : (pais.en_reporte_seo ? paleta.seo : paleta.ciego);

    var marcador = L.circleMarker([pais.lat, pais.lng], {
      radius: radio, color: color, weight: 1.5, fillColor: color, fillOpacity: 0.32
    }).addTo(mapa);

    // Tooltip con nodos de texto: nunca concatenando HTML con datos.
    var caja = document.createElement('div');
    var titulo = document.createElement('strong');
    titulo.textContent = pais.name + ' (' + pais.iso2 + ')';
    var cifras = document.createElement('span');
    cifras.textContent = pais.ventas + (pais.ventas === 1 ? ' venta' : ' ventas') + ' \u00b7 ' + pais.share + '%';
    var nota = document.createElement('span');
    nota.style.color = '#6b7280';
    nota.textContent = pais.solo_inferido
      ? 'sin respaldo del CRM: probable ruido'
      : (pais.en_reporte_seo ? 'analizado en el reporte SEO' : 'NO analizado en el reporte SEO');
    caja.appendChild(titulo); caja.appendChild(document.createElement('br'));
    caja.appendChild(cifras); caja.appendChild(document.createElement('br'));
    caja.appendChild(nota);

    marcador.bindTooltip(caja, { sticky: true });
    marcadores[pais.iso2] = marcador;
    limites.push([pais.lat, pais.lng]);
  });

  mapa.fitBounds(limites, { padding: [40, 40], maxZoom: 4 });

  // Expuesto para que el clic de la barra lateral pueda enfocar sin acoplarse al mapa.
  window.mapaEnfocarPais = function (iso2) {
    var marcador = marcadores[iso2];
    if (!marcador) { return; }
    mapa.setView(marcador.getLatLng(), Math.max(mapa.getZoom(), 4), { animate: true });
    marcador.openTooltip();
  };

  // Clic en un circulo: selecciona el pais tambien en la barra lateral y el listado.
  Object.keys(marcadores).forEach(function (iso2) {
    marcadores[iso2].on('click', function () { seleccionarPais(iso2); });
  });

  var leyenda = L.control({ position: 'bottomright' });
  leyenda.onAdd = function () {
    var div = L.DomUtil.create('div');
    div.style.cssText = 'background:#fff;padding:8px 10px;border-radius:5px;font-size:12px;box-shadow:0 1px 4px rgba(0,0,0,.25);line-height:1.6;';
    div.innerHTML =
      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + paleta.seo + ';opacity:.55;"></span> analizado en reporte SEO<br>' +
      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + paleta.ciego + ';opacity:.55;"></span> punto ciego<br>' +
      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:' + paleta.inferido + ';opacity:.55;"></span> solo inferido';
    return div;
  };
  leyenda.addTo(mapa);
})();
</script>
@endsection
