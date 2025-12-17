@extends('layout')

@push('styles')
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    body {
        background-color: #f5f7fb;
    }



    .time-filter-card {
        background: #fff;
        border-radius: 14px;
        padding: .5rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(17, 19, 34, .1);
        display: flex;
        align-items: center;
        gap: .75rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, .05);
        flex-wrap: wrap;
    }

    .quick-range-pills {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        align-items: center;
        flex: 1 1 auto;
    }

    .quick-range-pills .pill {
        background: #fff;
        padding: .4rem 1rem;
        border-radius: 999px;
        font-weight: 500;
        color: #475467;
        transition: background .2s ease, color .2s ease, box-shadow .2s ease;
    }

    .quick-range-pills .pill.active {
        background: #111322;
        color: #fff;
        border-color: #111322;
        box-shadow: 0 12px 24px rgba(17, 19, 34, .25);
    }

    .quick-range-pills .pill:not(.active) {
        border: none;
    }

    .date-picker-pill {
        display: flex;
        align-items: center;
        gap: .4rem;
        border: 1px solid #e4e7ec;
        border-radius: 999px;
        padding: .35rem .9rem;
        background: #fff;
    }

    .date-picker-pill .form-control {
        border: none;
        background: transparent;
        padding: 0;
        width: 150px;
    }

    .dashboard-header {
        display: flex;
        flex-direction: column;
        gap: .25rem;
    }

    .dashboard-header h2 {
        color: #101828;
        font-weight: 700;
    }

    .dashboard-header p {
        color: #667085;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .kpi-card {
        border-radius: 14px;
        padding: 1.1rem 1.2rem;
        background: #fff;
        border: 1px solid #e4e7ec;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
    }

    .kpi-title {
        font-size: .9rem;
        font-weight: 600;
        color: #667085;
        margin: 0 0 .35rem;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        color: #101828;
        margin: 0;
    }

    .user-breakdown-card {
        margin-top: 2rem;
        background: #fff;
        border-radius: 22px;
        padding: 1.75rem;
        border: 1px solid #e4e7ec;
        box-shadow: 0 35px 65px rgba(15, 23, 42, .07);
    }

    .user-breakdown-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .user-breakdown-header h4 {
        font-weight: 700;
        color: #101828;
    }

    .user-breakdown-header p,
    .user-breakdown-header .text-muted {
        color: #667085 !important;
    }

    .user-breakdown-empty {
        text-align: center;
        color: #98a2b3;
        padding: 1rem 0;
    }

    .user-breakdown-chart {
        min-height: 380px;
        flex: 1;
    }

    .user-breakdown-chart canvas {
        width: 100% !important;
        height: 100% !important;
    }

    @media (max-width: 768px) {
        .dashboard-header {
            text-align: center;
        }

        .user-breakdown-content {
            flex-direction: column;
        }

        .today-customers-list {
            width: 100%;
        }
    }

    .user-breakdown-content {
        display: flex;
        gap: 1.5rem;
        align-items: stretch;
        flex-wrap: wrap;
    }

    .today-customers-list {
        flex: 0 0 320px;
        max-width: 360px;
        background: #f8fafc;
        border: 1px solid #e4e7ec;
        border-radius: 18px;
        padding: 1rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .6);
        display: flex;
        flex-direction: column;
    }

    .today-customers-header {
        margin-bottom: 1rem;
    }

    .today-customers-header h5 {
        margin-bottom: .15rem;
        font-weight: 700;
        color: #101828;
    }

    .today-customers-header span {
        font-size: .85rem;
        color: #667085;
    }

    .today-customers-body {
        display: flex;
        flex-direction: column;
        gap: .85rem;
        max-height: 360px;
        overflow-y: auto;
    }

    .today-customer-item {
        background: #fff;
        border-radius: 14px;
        padding: .75rem;
        border: 1px solid #e4e7ec;
        box-shadow: 0 6px 15px rgba(15, 23, 42, .05);
    }

    .today-customer-name {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #101828;
        font-weight: 600;
        font-size: .95rem;
    }

    .today-customer-meta {
        font-size: .8rem;
        color: #667085;
    }
</style>
@endpush

@section('content')
@php($metrics = $metrics ?? [])
@php($todayUserCustomers = $todayUserCustomers ?? collect())
<div class="container py-4">
    <div class="dashboard-wrapper">
        <form id="dashboard-filter" method="GET" class="time-filter-card">
            <input type="hidden" name="from_date" value="{{ $fromDate }}">
            <input type="hidden" name="to_date" value="{{ $toDate }}">
            <div class="quick-range-pills">
                @foreach ($filterOptions as $value => $label)
                    <button type="submit" name="range" value="{{ $value }}"
                        class="pill quick-range-button {{ $selectedRange === $value ? 'active' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
                <div class="date-picker-pill">
                    <i class="fa fa-calendar text-muted"></i>
                    <input type="text" id="dashboard_range" class="form-control"
                        placeholder="Seleccionar rango"
                        value="{{ (!empty($fromDate) && !empty($toDate)) ? \Carbon\Carbon::parse($fromDate)->format('d-m-Y').' - '.\Carbon\Carbon::parse($toDate)->format('d-m-Y') : '' }}"
                        autocomplete="off">
                </div>
                <div class="time-filter-actions ml-auto">
                    <button type="submit" class="btn btn-dark rounded-pill px-4">Aplicar</button>
                    <button type="button" class="btn btn-link text-dark" id="dashboard_range_clear">
                        Limpiar
                    </button>
                </div>
            </div>
        </form>


        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

            <div class="kpi-grid">
            @forelse ($metrics as $metric)
                <div class="kpi-card">
                    <p class="kpi-title">{{ $metric['title'] ?? '' }}</p>
                    <p class="kpi-value">{{ number_format($metric['value'] ?? 0) }}</p>
                </div>
            @empty
                <div class="alert alert-info mb-0">
                    No hay indicadores para mostrar por el momento.
                </div>
            @endforelse
        </div>

        @php($totalBreakdownCustomers = $hasUserBreakdown ? array_sum(array_column($userBreakdown, 'total')) : 0)
        <div class="user-breakdown-card">
            <div class="user-breakdown-header">
                <div>
                    <h4 class="mb-1">Clientes por usuario</h4>
                    <p class="text-muted mb-0">Comparativo de clientes por ejecutivo y etapa.</p>
                </div>
                <div class="text-right">
                    <span class="text-muted small d-block">Total clientes</span>
                    <strong class="h5 mb-0">{{ number_format($totalBreakdownCustomers) }}</strong>
                </div>
            </div>

            <div class="user-breakdown-content">
                <div class="user-breakdown-chart">
                    <canvas id="userBreakdownChart"></canvas>
                </div>
                <div class="today-customers-list">
                    <div class="today-customers-header">
                        <h5>Clientes creados hoy</h5>
                        <span>Asignados a ti</span>
                    </div>
                    <div class="today-customers-body">
                        @forelse ($todayUserCustomers as $customer)
                            <div class="today-customer-item">
                                <div class="today-customer-name">
                                    <span>{{ $customer->name ?? 'Sin nombre' }}</span>
                                    <small class="text-muted">{{ optional($customer->created_at)->format('H:i') }}</small>
                                </div>
                                <div class="today-customer-meta">
                                    {{ $customer->business ?? 'Sin empresa' }}
                                </div>
                                @if (! empty($customer->email))
                                    <div class="today-customer-meta">{{ $customer->email }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">Hoy no tienes clientes nuevos asignados.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            @unless ($hasUserBreakdown)
                <p class="user-breakdown-empty mb-0">
                    No hay clientes con etiquetas asignadas para mostrar.
                </p>
            @endunless
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
    const $form = $('#dashboard-filter');
    if (!$form.length) {
        return;
    }

    const $rangeInput = $('#dashboard_range');
    const $fromField = $form.find('input[name="from_date"]');
    const $toField = $form.find('input[name="to_date"]');
    const $clearBtn = $('#dashboard_range_clear');
    const $quickButtons = $('.quick-range-button');
    const DEFAULT_START = moment().startOf('month');
    const DEFAULT_END = moment().endOf('month');

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

    const startDate = parseOrDefault($fromField.val(), DEFAULT_START);
    const endDate = parseOrDefault($toField.val(), DEFAULT_END);

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
            'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
            'Últimos 10 días': [moment().subtract(9, 'days'), moment()],
            'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
            'Esta semana': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
            'Semana pasada': [moment().subtract(1,'week').startOf('isoWeek'), moment().subtract(1,'week').endOf('isoWeek')],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes anterior': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
            'Máximo': [moment('2015-01-01', 'YYYY-MM-DD'), moment()]
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

    const breakdownData = @json($userBreakdown);
    if (breakdownData.length && window.Chart) {
        const ctx = document.getElementById('userBreakdownChart');
        if (ctx) {
            const slugMap = {};
            breakdownData.forEach(row => {
                row.segments.forEach(segment => {
                    if (!slugMap[segment.slug]) {
                        slugMap[segment.slug] = {
                            label: segment.label,
                            color: segment.color || '#64748b',
                        };
                    }
                });
            });

            const labels = breakdownData.map(row => row.name);
            const datasets = Object.keys(slugMap).map(slug => {
                const info = slugMap[slug];
                return {
                    label: info.label,
                    data: breakdownData.map(row => {
                        const segment = row.segments.find(seg => seg.slug === slug);
                        return segment ? segment.count : 0;
                    }),
                    backgroundColor: info.color,
                    borderWidth: 1,
                    borderColor: 'rgba(255, 255, 255, 0.5)',
                    borderRadius: 8,
                    barThickness: 22,
                };
            });

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets,
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                            grid: {
                                drawBorder: false,
                            },
                        },
                        y: {
                            stacked: true,
                            grid: {
                                display: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    const value = context.parsed.x ?? context.parsed.y ?? 0;
                                    return `${context.dataset.label}: ${value}`;
                                },
                            },
                        },
                    },
                },
            });
        }
    }
})();
</script>
@endpush
