@extends('layout')

@push('styles')
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .dashboard-wrapper {
        background: #f4f6fb;
        border-radius: 18px;
        padding: 1.5rem;
    }

    .time-filter-card {
        background: linear-gradient(120deg, #ffe9e2, #ffe9f2);
        border-radius: 18px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        border: 1px solid rgba(249, 115, 22, .2);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .quick-range-pills {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        align-items: center;
    }

    .quick-range-pills .pill {
        border: none;
        background: transparent;
        padding: .4rem 1rem;
        border-radius: 999px;
        font-weight: 600;
        color: #b45309;
        transition: background .2s ease, color .2s ease, box-shadow .2s ease;
    }

    .quick-range-pills .pill.active {
        background: #fff;
        color: #0f172a;
        box-shadow: 0 6px 10px rgba(249, 115, 22, .25);
    }

    .time-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        align-items: flex-end;
    }

    .time-filter-input {
        flex: 1 1 280px;
    }

    .time-filter-input .input-group {
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .08);
    }

    .time-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .dashboard-header {
        display: flex;
        flex-direction: column;
        gap: .25rem;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .kpi-card {
        border-radius: 16px;
        padding: 1rem 1.25rem;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .05);
        box-shadow: 0 10px 25px rgba(15, 23, 42, .05);
        transition: transform .15s ease, box-shadow .15s ease;
        min-height: 125px;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(15, 23, 42, .08);
    }

    .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .kpi-title {
        font-size: 1rem;
        font-weight: 600;
        color: #475569;
        margin: .25rem 0;
    }

    .kpi-subtitle,
    .kpi-accent {
        font-size: .85rem;
        margin: 0;
    }

    .kpi-subtitle {
        color: #94a3b8;
    }

    .kpi-accent {
        font-weight: 600;
    }

    .user-breakdown-card {
        margin-top: 2rem;
        background: #fff;
        border-radius: 18px;
        padding: 1.5rem;
        border: 1px solid rgba(15, 23, 42, .05);
        box-shadow: 0 15px 30px rgba(15, 23, 42, .08);
    }

    .user-breakdown-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .user-breakdown-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .65rem 0;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
    }

    .user-breakdown-row:last-child {
        border-bottom: none;
    }

    .user-breakdown-label {
        width: 200px;
    }

    .user-breakdown-label strong {
        display: block;
        font-size: .95rem;
        color: #0f172a;
    }

    .user-breakdown-label span {
        font-size: .8rem;
        color: #94a3b8;
    }

    .user-breakdown-bar {
        flex: 1;
        display: flex;
        height: 34px;
        border-radius: 999px;
        overflow: hidden;
        background: #e2e8f0;
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .05);
    }

    .user-breakdown-segment {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .75rem;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(15, 23, 42, .3);
    }

    .user-breakdown-empty {
        text-align: center;
        color: #94a3b8;
        padding: 1rem 0;
    }

    .user-breakdown-chart {
        min-height: 380px;
        margin-bottom: 1.5rem;
    }

    .user-breakdown-chart canvas {
        width: 100% !important;
        height: 100% !important;
    }

    @media (max-width: 768px) {
        .dashboard-header {
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
@php($metrics = $metrics ?? [])
<div class="container py-4">
    <div class="dashboard-wrapper">
        <form id="dashboard-filter" method="GET" class="time-filter-card">
            <div class="quick-range-pills">
                @foreach ($filterOptions as $value => $label)
                    <button type="submit" name="range" value="{{ $value }}"
                        class="pill quick-range-button {{ $selectedRange === $value ? 'active' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="from_date" value="{{ $fromDate }}">
            <input type="hidden" name="to_date" value="{{ $toDate }}">
            <div class="time-filter-row">
                <div class="time-filter-input">
                    <label for="dashboard_range" class="mb-1 text-muted">Seleccionar rango</label>
                    <div class="input-group">
                        <input type="text" id="dashboard_range" class="form-control"
                            placeholder="Seleccionar rango"
                            value="{{ (!empty($fromDate) && !empty($toDate)) ? \Carbon\Carbon::parse($fromDate)->format('d-m-Y').' - '.\Carbon\Carbon::parse($toDate)->format('d-m-Y') : '' }}"
                            autocomplete="off">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>
                </div>
                <div class="time-filter-actions">
                    <button type="submit" class="btn btn-dark rounded-pill px-4">Aplicar</button>
                    <button type="button" class="btn btn-link text-dark" id="dashboard_range_clear">
                        Limpiar
                    </button>
                </div>
            </div>
        </form>

        <div class="dashboard-header mb-4">
            <h2 class="mb-0">Panel de control</h2>
            <p class="text-muted mb-0">Indicadores principales de clientes y oportunidades.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="kpi-grid">
            @forelse ($metrics as $metric)
                <div class="kpi-card">
                    <p class="kpi-value">{{ number_format($metric['value'] ?? 0) }}</p>
                    <p class="kpi-title">{{ $metric['title'] ?? '' }}</p>
                    <p class="kpi-accent" style="color: {{ $metric['accent'] ?? '#2563eb' }}">
                        {{ $metric['subtitle'] ?? '' }}
                    </p>
                </div>
            @empty
                <div class="alert alert-info mb-0">
                    No hay indicadores para mostrar por el momento.
                </div>
            @endforelse
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
