@extends('layout')

@section('content')
<div class="container-fluid" style="max-width:1320px;padding-top:24px;padding-bottom:48px;">

  <div class="d-flex flex-wrap align-items-end justify-content-between mb-1">
    <div>
      <h2 style="margin:0;font-weight:600;">Mapa de ventas — ¿dónde ha vendido MQE?</h2>
      <p class="text-muted" style="margin:6px 0 0;font-size:14px;">
        Página interna de análisis de territorio. Datos agregados por país; no se muestra ningún cliente individual.
      </p>
    </div>
    <div class="text-muted" style="font-size:12.5px;text-align:right;">
      <strong>Fuente:</strong> tabla <code>customers</code>, estado <em>Ganado Maquinas</em><br>
      <strong>Corte:</strong> {{ now()->format('d/m/Y H:i') }} ·
      <strong>Método:</strong> campo <code>country</code> del CRM; indicativo telefónico como respaldo
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-3 mb-3">
      <div class="card h-100"><div class="card-body">
        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">Ventas ganadas</div>
        <div style="font-size:34px;font-weight:600;line-height:1.1;">{{ number_format($total) }}</div>
        <div class="text-muted" style="font-size:12.5px;">registros en estado Ganado Maquinas</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card h-100"><div class="card-body">
        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">Con país identificado</div>
        <div style="font-size:34px;font-weight:600;line-height:1.1;">{{ number_format($identificados) }}</div>
        <div class="text-muted" style="font-size:12.5px;">
          {{ $total > 0 ? round($identificados / $total * 100) : 0 }}% de cobertura ·
          {{ number_format($porFuente['sin_dato']) }} sin país determinable
        </div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card h-100"><div class="card-body">
        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">Países con venta</div>
        <div style="font-size:34px;font-weight:600;line-height:1.1;">{{ $countries->count() }}</div>
        <div class="text-muted" style="font-size:12.5px;">
          el reporte SEO analiza {{ $mercadosReporteSeo }}
          @if($soloInferidos->isNotEmpty())
            · {{ $soloInferidos->count() }} sin respaldo del CRM
          @endif
        </div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card h-100" style="border-left:4px solid #B45309;"><div class="card-body">
        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">Venta en puntos ciegos</div>
        <div style="font-size:34px;font-weight:600;line-height:1.1;">{{ number_format($puntosCiegos->sum('ventas')) }}</div>
        <div class="text-muted" style="font-size:12.5px;">
          ventas en países que el reporte SEO no analiza
          ({{ $identificados > 0 ? round($puntosCiegos->sum('ventas') / $identificados * 100) : 0 }}% del total)
        </div>
      </div></div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-7 mb-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 style="font-weight:600;margin-bottom:4px;">Distribución geográfica</h5>
          <p class="text-muted" style="font-size:12.5px;">
            El área de cada círculo es proporcional al número de ventas. El círculo se ubica en el centroide del país,
            no en la ubicación del cliente.
            @if($sinCentroide->isNotEmpty())
              <br><strong>{{ $sinCentroide->count() }}</strong> país(es) sin coordenada registrada no aparecen en el mapa
              pero sí en la tabla: {{ $sinCentroide->implode(', ') }}.
            @endif
          </p>
          <div id="mapa-ventas" style="height:460px;width:100%;border-radius:6px;"></div>
        </div>
      </div>
    </div>

    <div class="col-lg-5 mb-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 style="font-weight:600;margin-bottom:12px;">Ventas por país</h5>
          <div style="max-height:470px;overflow-y:auto;">
            <table class="table table-sm mb-0" style="font-size:13.5px;">
              <thead>
                <tr>
                  <th style="width:38%;">País</th>
                  <th class="text-right">Ventas</th>
                  <th class="text-right">Share</th>
                  <th class="text-center">Evidencia</th>
                  <th class="text-center">SEO</th>
                </tr>
              </thead>
              <tbody>
                @foreach($countries as $country)
                  <tr @if($country['solo_inferido']) style="background:#FFFBEB;" @endif>
                    <td>
                      <span class="text-muted" style="font-size:11.5px;">{{ $country['iso2'] }}</span>
                      {{ $country['name'] }}
                    </td>
                    <td class="text-right"><strong>{{ number_format($country['ventas']) }}</strong></td>
                    <td class="text-right text-muted">{{ number_format($country['share'], 1) }}%</td>
                    <td class="text-center" style="font-size:11.5px;">
                      @if($country['solo_inferido'])
                        <span style="color:#92400E;" title="Ningún registro del CRM: solo inferencia telefónica">
                          solo teléfono
                        </span>
                      @else
                        <span class="text-muted" title="Registros del CRM / inferidos por teléfono">
                          {{ $country['desde_crm'] }} CRM
                          @if($country['inferido_telefono'] > 0)
                            + {{ $country['inferido_telefono'] }} tel
                          @endif
                        </span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($country['en_reporte_seo'])
                        <span class="badge badge-secondary" style="font-weight:500;">sí</span>
                      @else
                        <span class="badge" style="background:#FEF3C7;color:#92400E;font-weight:500;">no</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card h-100" style="border-top:3px solid #B45309;">
        <div class="card-body">
          <h5 style="font-weight:600;">Puntos ciegos — venden y no los miramos</h5>
          <p class="text-muted" style="font-size:13px;">
            Países con venta registrada que el reporte SEO de maquiempanadas.com no analiza ni prioriza.
          </p>
          @if($puntosCiegos->isEmpty())
            <p class="text-muted mb-0">Ninguno: todos los países con venta están en el reporte.</p>
          @else
            <ul style="font-size:14px;padding-left:18px;margin-bottom:0;">
              @foreach($puntosCiegos->take(12) as $country)
                <li style="margin-bottom:4px;">
                  <strong>{{ $country['name'] }}</strong> — {{ number_format($country['ventas']) }}
                  {{ $country['ventas'] == 1 ? 'venta' : 'ventas' }}
                  ({{ number_format($country['share'], 1) }}%)
                  @if($country['solo_inferido'])
                    <span style="color:#92400E;font-size:12px;">· sin respaldo del CRM</span>
                  @endif
                </li>
              @endforeach
              @if($puntosCiegos->count() > 12)
                <li class="text-muted">y {{ $puntosCiegos->count() - 12 }} país(es) más.</li>
              @endif
            </ul>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card h-100" style="border-top:3px solid #1D4ED8;">
        <div class="card-body">
          <h5 style="font-weight:600;">Priorizados por SEO, sin venta registrada</h5>
          <p class="text-muted" style="font-size:13px;">
            Mercados que el reporte SEO analiza y donde el CRM no registra ninguna venta ganada.
          </p>
          @if($sinVenta->isEmpty())
            <p class="text-muted mb-0">Ninguno: todos los mercados analizados tienen al menos una venta.</p>
          @else
            <ul style="font-size:14px;padding-left:18px;margin-bottom:0;">
              @foreach($sinVenta as $mercado)
                <li style="margin-bottom:4px;">{{ $mercado }}</li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h6 style="font-weight:600;">Cómo leer esta página — límites del dato</h6>
      <ul class="text-muted mb-0" style="font-size:13px;padding-left:18px;">
        <li><strong>Procedencia del país:</strong>
            {{ number_format($porFuente['crm']) }} registros traen el país en el campo <code>country</code> del CRM,
            {{ number_format($porFuente['telefono']) }} se infieren del indicativo telefónico, y
            {{ number_format($porFuente['sin_dato']) }} no tienen país determinable.
            El indicativo da país, nunca ciudad.</li>
        @if($soloInferidos->isNotEmpty())
          <li><strong>{{ $soloInferidos->count() }} país(es) se sostienen solo en el indicativo telefónico</strong>
              ({{ $soloInferidos->pluck('iso2')->implode(', ') }}), sin un solo registro del CRM.
              Trátalos como probable ruido de números mal digitados, no como mercados.</li>
        @endif
        <li><strong>Una venta es un registro en estado Ganado Maquinas</strong>, no una factura. No representa monto ni unidades.</li>
        <li><strong>Sin dimensión temporal.</strong> Es el acumulado histórico al corte, no ventas del periodo.</li>
        <li><strong>El indicativo +1 no separa EE. UU. de Canadá.</strong> Sin el código de área se atribuye a
            Estados Unidos y queda marcado como inferido.</li>
        <li><strong>Esta página lee la base a la que apunte el entorno.</strong> En local el repo apunta a una copia
            parcial y renumerada; las cifras reales son las del CRM de producción.</li>
      </ul>
    </div>
  </div>

</div>
@endsection

@section('footer_scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  var datos = @json($mapeables);
  var maxVentas = {{ $maxVentas }};
  if (!datos.length) { return; }

  var mapa = L.map('mapa-ventas', { scrollWheelZoom: false, zoomSnap: 0.25 }).setView([2, -70], 3);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 10,
    attribution: '&copy; OpenStreetMap'
  }).addTo(mapa);

  var limites = [];

  // De mayor a menor, para que los países pequeños queden encima y sigan siendo apuntables.
  datos.slice().sort(function (a, b) { return b.ventas - a.ventas; }).forEach(function (pais) {
    // Radio en pixeles: el area crece con la raiz cuadrada y el rango visual se
    // mantiene legible tanto con un maximo de 60 como de 1.500 ventas.
    var radio = 5 + Math.sqrt(pais.ventas / maxVentas) * 19;
    var color = pais.solo_inferido ? '#92400E' : (pais.en_reporte_seo ? '#1D4ED8' : '#B45309');

    var marcador = L.circleMarker([pais.lat, pais.lng], {
      radius: radio,
      color: color,
      weight: 1.5,
      fillColor: color,
      fillOpacity: 0.32
    }).addTo(mapa);

    // Tooltip construido con nodos de texto: nada de concatenar HTML con datos.
    var caja = document.createElement('div');
    var titulo = document.createElement('strong');
    titulo.textContent = pais.name + ' (' + pais.iso2 + ')';
    caja.appendChild(titulo);
    caja.appendChild(document.createElement('br'));
    var cifras = document.createElement('span');
    cifras.textContent = pais.ventas + (pais.ventas === 1 ? ' venta' : ' ventas') + ' · ' + pais.share + '%';
    caja.appendChild(cifras);
    caja.appendChild(document.createElement('br'));
    var nota = document.createElement('span');
    nota.style.color = '#6b7280';
    nota.textContent = pais.solo_inferido
      ? 'sin respaldo del CRM: probable ruido'
      : (pais.en_reporte_seo ? 'analizado en el reporte SEO' : 'NO analizado en el reporte SEO');
    caja.appendChild(nota);

    marcador.bindTooltip(caja, { sticky: true });
    limites.push([pais.lat, pais.lng]);
  });

  mapa.fitBounds(limites, { padding: [40, 40], maxZoom: 4 });

  var leyenda = L.control({ position: 'bottomright' });
  leyenda.onAdd = function () {
    var div = L.DomUtil.create('div');
    div.style.cssText = 'background:#fff;padding:8px 10px;border-radius:5px;font-size:12px;box-shadow:0 1px 4px rgba(0,0,0,.25);line-height:1.6;';
    div.innerHTML =
      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#1D4ED8;opacity:.55;"></span> analizado en reporte SEO<br>' +
      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#B45309;opacity:.55;"></span> punto ciego<br>' +
      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#92400E;opacity:.55;"></span> solo inferido';
    return div;
  };
  leyenda.addTo(mapa);
})();
</script>
@endsection
