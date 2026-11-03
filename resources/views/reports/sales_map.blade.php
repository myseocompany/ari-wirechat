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
      <strong>Fuente:</strong> tabla <code>customers</code> del CRM, estado <em>Ganado Maquinas</em><br>
      <strong>Corte:</strong> {{ now()->format('d/m/Y') }} ·
      <strong>Método:</strong> país derivado del indicativo telefónico
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-3 mb-3">
      <div class="card h-100"><div class="card-body">
        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">Ventas ganadas</div>
        <div style="font-size:34px;font-weight:600;line-height:1.1;">{{ $total }}</div>
        <div class="text-muted" style="font-size:12.5px;">registros en estado Ganado Maquinas</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card h-100"><div class="card-body">
        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">Con país identificado</div>
        <div style="font-size:34px;font-weight:600;line-height:1.1;">{{ $identificados }}</div>
        <div class="text-muted" style="font-size:12.5px;">
          {{ $total > 0 ? round($identificados / $total * 100) : 0 }}% de cobertura · {{ $sinIdentificar }} sin teléfono usable
        </div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card h-100"><div class="card-body">
        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">Países con venta</div>
        <div style="font-size:34px;font-weight:600;line-height:1.1;">{{ $countries->count() }}</div>
        <div class="text-muted" style="font-size:12.5px;">el reporte SEO analiza {{ $mercadosReporteSeo }}</div>
      </div></div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card h-100" style="border-left:4px solid #B45309;"><div class="card-body">
        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">Venta en puntos ciegos</div>
        <div style="font-size:34px;font-weight:600;line-height:1.1;">{{ $puntosCiegos->sum('ventas') }}</div>
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
                  <th style="width:46%;">País</th>
                  <th class="text-right">Ventas</th>
                  <th class="text-right">Share</th>
                  <th class="text-center">Reporte SEO</th>
                </tr>
              </thead>
              <tbody>
                @foreach($countries as $country)
                  <tr>
                    <td>{{ $country['name'] }}</td>
                    <td class="text-right"><strong>{{ $country['ventas'] }}</strong></td>
                    <td class="text-right text-muted">{{ number_format($country['share'], 1) }}%</td>
                    <td class="text-center">
                      @if($country['en_reporte_seo'])
                        <span class="badge badge-secondary" style="font-weight:500;">analizado</span>
                      @else
                        <span class="badge" style="background:#FEF3C7;color:#92400E;font-weight:500;">no analizado</span>
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
              @foreach($puntosCiegos as $country)
                <li style="margin-bottom:4px;">
                  <strong>{{ $country['name'] }}</strong> — {{ $country['ventas'] }}
                  {{ $country['ventas'] == 1 ? 'venta' : 'ventas' }}
                  ({{ number_format($country['share'], 1) }}% del total)
                </li>
              @endforeach
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
        <li><strong>No hay detalle por ciudad.</strong> En <code>customers</code> el campo <code>city</code> está diligenciado
            en 6 de {{ $total }} registros ganados, así que la ciudad no es derivable. El indicativo telefónico da país, no ciudad:
            en Colombia la mayoría son móviles (3xx), que no tienen indicativo regional.</li>
        <li><strong>Una venta es un registro en estado Ganado Maquinas</strong>, no una factura. No representa monto ni unidades.</li>
        <li><strong>Sin dimensión temporal.</strong> Es el acumulado histórico al corte, no ventas del periodo.</li>
        <li><strong>El país es inferido</strong>, no declarado por el cliente. Un cliente con número de otro país queda clasificado ahí.</li>
        <li><strong>Para llegar a ciudad</strong> hace falta enriquecer datos: capturar ciudad en el formulario, o cruzar con
            los destinos de despacho del formulario POE.</li>
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
  var datos = @json($countries);
  var maxVentas = {{ $maxVentas }};

  var mapa = L.map('mapa-ventas', { scrollWheelZoom: false }).setView([2, -70], 3);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 10,
    attribution: '&copy; OpenStreetMap'
  }).addTo(mapa);

  var limites = [];

  datos.forEach(function (pais) {
    // Área proporcional a las ventas: el radio crece con la raíz cuadrada.
    var radio = 70000 + Math.sqrt(pais.ventas / maxVentas) * 330000;
    var color = pais.en_reporte_seo ? '#1D4ED8' : '#B45309';

    L.circle([pais.lat, pais.lng], {
      radius: radio,
      color: color,
      weight: 1.5,
      fillColor: color,
      fillOpacity: 0.32
    })
      .addTo(mapa)
      .bindTooltip(
        '<strong>' + pais.name + '</strong><br>' +
        pais.ventas + (pais.ventas === 1 ? ' venta' : ' ventas') +
        ' · ' + pais.share + '%' +
        '<br><span style="color:#6b7280">' +
        (pais.en_reporte_seo ? 'analizado en el reporte SEO' : 'NO analizado en el reporte SEO') +
        '</span>',
        { sticky: true }
      );

    limites.push([pais.lat, pais.lng]);
  });

  if (limites.length) {
    mapa.fitBounds(limites, { padding: [40, 40], maxZoom: 4 });
  }

  var leyenda = L.control({ position: 'bottomright' });
  leyenda.onAdd = function () {
    var div = L.DomUtil.create('div');
    div.style.cssText = 'background:#fff;padding:8px 10px;border-radius:5px;font-size:12px;box-shadow:0 1px 4px rgba(0,0,0,.25);line-height:1.6;';
    div.innerHTML =
      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#1D4ED8;opacity:.55;"></span> ' +
      'analizado en reporte SEO<br>' +
      '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#B45309;opacity:.55;"></span> ' +
      'punto ciego';
    return div;
  };
  leyenda.addTo(mapa);
})();
</script>
@endsection
