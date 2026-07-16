@extends('layouts.tailwind')

@push('styles')
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
  body {
    background-color: #f5f7fb;
  }

  .wirechat-filter-card {
    background: #fff;
    border: 1px solid rgba(17, 19, 34, .1);
    border-radius: 14px;
    box-shadow: 0 20px 45px rgba(15, 23, 42, .05);
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: .75rem;
  }

  .quick-range-pills {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
  }

  .quick-range-pills .pill {
    background: #fff;
    border: 0;
    border-radius: 999px;
    color: #475467;
    font-weight: 500;
    padding: .4rem 1rem;
    transition: background .2s ease, color .2s ease, box-shadow .2s ease;
  }

  .quick-range-pills .pill.active {
    background: #111322;
    box-shadow: 0 12px 24px rgba(17, 19, 34, .25);
    color: #fff;
  }

  .date-picker-pill {
    align-items: center;
    background: #fff;
    border: 1px solid #e4e7ec;
    border-radius: 999px;
    display: flex;
    gap: .4rem;
    padding: .35rem .9rem;
  }

  .date-picker-pill .form-control {
    background: transparent;
    border: 0;
    padding: 0;
    width: 150px;
  }

  .wirechat-kpi-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }

  .wirechat-card {
    background: #fff;
    border: 1px solid #e4e7ec;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
    padding: 1.1rem 1.2rem;
  }

  .wirechat-kpi-title {
    color: #667085;
    font-size: .9rem;
    font-weight: 600;
    margin: 0 0 .35rem;
  }

  .wirechat-kpi-value {
    color: #101828;
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
  }

  .wirechat-chart {
    height: 340px;
  }

  .wirechat-message-body {
    max-width: 520px;
    white-space: normal;
  }
</style>
@endpush

@section('content')
<div class="container py-4">
  <div class="mb-4">
    <h1 class="text-2xl font-semibold text-slate-900">Mensajes entrantes de Wirechat</h1>
    <p class="mt-1 text-sm text-slate-500">Conteo y detalle de mensajes enviados por clientes.</p>
  </div>

  <form id="wirechat-filter" method="GET" class="wirechat-filter-card" data-loading-overlay data-loading-message="Cargando...">
    <input type="hidden" name="from_date" value="{{ $fromDate }}">
    <input type="hidden" name="to_date" value="{{ $toDate }}">

    <div class="quick-range-pills">
      @foreach ($filterOptions as $value => $label)
        <button type="submit" name="range" value="{{ $value }}" class="pill quick-range-button {{ $selectedRange === $value ? 'active' : '' }}">
          {{ $label }}
        </button>
      @endforeach

      <div class="date-picker-pill">
        <i class="fa fa-calendar text-muted"></i>
        <input
          type="text"
          id="wirechat_range"
          class="form-control"
          placeholder="Seleccionar rango"
          value="{{ (! empty($fromDate) && ! empty($toDate)) ? \Carbon\Carbon::parse($fromDate)->format('d-m-Y').' - '.\Carbon\Carbon::parse($toDate)->format('d-m-Y') : '' }}"
          autocomplete="off"
        >
      </div>

      <div class="ml-auto d-flex align-items-center gap-2">
        <button type="submit" class="btn btn-dark rounded-pill px-4">Aplicar</button>
        <button type="button" class="btn btn-link text-dark" id="wirechat_range_clear">Limpiar</button>
      </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
      <div>
        <label for="customer_search" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cliente</label>
        <input class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="customer_search" name="customer_search" value="{{ $request->customer_search }}" placeholder="Nombre, empresa o telefono">
      </div>
      <div>
        <label for="message_search" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Mensaje</label>
        <input class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none" type="text" id="message_search" name="message_search" value="{{ $request->message_search }}" placeholder="Texto del mensaje">
      </div>
    </div>
  </form>

  <div class="wirechat-kpi-grid">
    <div class="wirechat-card">
      <p class="wirechat-kpi-title">Mensajes entrantes</p>
      <p class="wirechat-kpi-value">{{ number_format($totalMessages) }}</p>
    </div>
    <div class="wirechat-card">
      <p class="wirechat-kpi-title">Clientes con mensajes</p>
      <p class="wirechat-kpi-value">{{ number_format($totalCustomers) }}</p>
    </div>
    <div class="wirechat-card">
      <p class="wirechat-kpi-title">Conversaciones</p>
      <p class="wirechat-kpi-value">{{ number_format($totalConversations) }}</p>
    </div>
  </div>

  <div class="wirechat-card mt-4">
    <div class="mb-3 flex items-center justify-between gap-3">
      <div>
        <h2 class="mb-0 text-lg font-semibold text-slate-900">Mensajes por dia</h2>
        <p class="mb-0 text-sm text-slate-500">Distribucion diaria del rango seleccionado.</p>
      </div>
    </div>
    <div class="wirechat-chart">
      <canvas id="wirechatDailyChart"></canvas>
    </div>
  </div>

  <div class="wirechat-card mt-4">
    <div class="mb-3 flex items-center justify-between gap-3">
      <div>
        <h2 class="mb-0 text-lg font-semibold text-slate-900">Mensajes recientes</h2>
        <p class="mb-0 text-sm text-slate-500">Ultimos mensajes entrantes que coinciden con el filtro.</p>
      </div>
      <span class="badge badge-dark">Total: {{ number_format($recentMessages->total()) }}</span>
    </div>

    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Mensaje</th>
            <th>Asignado</th>
            <th>Conversacion</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recentMessages as $message)
            @php($messageBody = trim((string) $message->body) !== '' ? $message->body : '['.($message->type ?? 'mensaje').']')
            @php($phone = $message->phone ?? $message->phone2 ?? $message->contact_phone2)
            <tr>
              <td class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($message->created_at)->format('Y-m-d H:i') }}</td>
              <td>
                <a href="{{ url('/customers/'.$message->customer_id.'/show') }}" class="font-semibold text-slate-900 text-decoration-none">
                  {{ $message->customer_name ?? 'Sin nombre' }}
                </a>
                <div class="text-xs text-slate-500">{{ $message->business ?? 'Sin empresa' }}</div>
                @if ($phone)
                  <div class="text-xs text-slate-500">{{ $phone }}</div>
                @endif
              </td>
              <td class="wirechat-message-body text-sm text-slate-700">{{ $messageBody }}</td>
              <td class="text-sm text-slate-600">{{ $message->user_name ?? 'Sin asignar' }}</td>
              <td class="text-sm text-slate-600">{{ $message->conversation_id ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-4 text-center text-sm text-slate-500">No hay mensajes para mostrar.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">
      {{ $recentMessages->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  const $form = $('#wirechat-filter');
  if (!$form.length) {
    return;
  }

  const $rangeInput = $('#wirechat_range');
  const $fromField = $form.find('input[name="from_date"]');
  const $toField = $form.find('input[name="to_date"]');
  const $clearBtn = $('#wirechat_range_clear');
  const $quickButtons = $('.quick-range-button');
  const defaultStart = moment().startOf('month');
  const defaultEnd = moment().endOf('month');

  function parseOrDefault(value, fallback) {
    return value ? moment(value, 'YYYY-MM-DD') : fallback.clone();
  }

  function setHidden(start, end, syncPicker = true) {
    $fromField.val(start.format('YYYY-MM-DD'));
    $toField.val(end.format('YYYY-MM-DD'));
    $rangeInput.val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));

    if (syncPicker && $rangeInput.data('daterangepicker')) {
      $rangeInput.data('daterangepicker').setStartDate(start);
      $rangeInput.data('daterangepicker').setEndDate(end);
    }
  }

  function clearRange() {
    $fromField.val('');
    $toField.val('');
    $rangeInput.val('');
  }

  const startDate = parseOrDefault($fromField.val(), defaultStart);
  const endDate = parseOrDefault($toField.val(), defaultEnd);

  $rangeInput.daterangepicker({
    startDate,
    endDate,
    autoUpdateInput: false,
    opens: 'center',
    locale: {
      format: 'DD-MM-YYYY',
      applyLabel: 'Aplicar',
      cancelLabel: 'Cancelar',
      daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
      monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
      firstDay: 1
    },
    ranges: {
      'Hoy': [moment(), moment()],
      'Ayer': [moment().subtract(1, 'day'), moment().subtract(1, 'day')],
      'Ultimos 7 dias': [moment().subtract(6, 'days'), moment()],
      'Ultimos 30 dias': [moment().subtract(29, 'days'), moment()],
      'Ultimos 60 dias': [moment().subtract(59, 'days'), moment()],
      'Ultimos 90 dias': [moment().subtract(89, 'days'), moment()],
      'Esta semana': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
      'Semana pasada': [moment().subtract(1,'week').startOf('isoWeek'), moment().subtract(1,'week').endOf('isoWeek')],
      'Este mes': [moment().startOf('month'), moment().endOf('month')],
      'Mes anterior': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
      'Maximo': [moment('2015-01-01', 'YYYY-MM-DD'), moment()]
    }
  }, function(start, end) {
    setHidden(start, end, false);
  }).on('apply.daterangepicker', function(ev, picker) {
    setHidden(picker.startDate, picker.endDate, false);
  }).on('cancel.daterangepicker', function() {
    clearRange();
  });

  if ($fromField.val() && $toField.val()) {
    setHidden(startDate, endDate, false);
  }

  $clearBtn.on('click', function(e) {
    e.preventDefault();
    clearRange();
  });

  $quickButtons.on('click', function() {
    clearRange();
  });

  const dailyMessages = @json($dailyMessages);
  const chartCanvas = document.getElementById('wirechatDailyChart');
  if (chartCanvas && window.Chart) {
    new Chart(chartCanvas, {
      type: 'bar',
      data: {
        labels: dailyMessages.map(row => row.message_date),
        datasets: [{
          label: 'Mensajes',
          data: dailyMessages.map(row => Number(row.total || 0)),
          backgroundColor: '#111322',
          borderRadius: 8,
          barThickness: 22
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  }
})();
</script>
@endpush
