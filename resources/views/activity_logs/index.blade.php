@extends('layout')

@push('styles')
<style>
    .logs-card {
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 25px -15px rgba(17, 24, 39, 0.3);
        border-radius: 16px;
    }
    .logs-header {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        padding: 4px 0 0 0;
    }
    .logs-filter-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .logs-search {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        flex-wrap: wrap;
    }
    .logs-search input {
        border-radius: 12px;
        border: 1px solid #d1d5db;
        padding: 10px 14px;
        width: 100%;
    }
    .time-filter-card {
        background: #fff;
        border-radius: 14px;
        padding: 0.5rem;
        border: 1px solid rgba(17, 19, 34, 0.1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.05);
        flex-wrap: wrap;
    }
    .quick-range-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        align-items: center;
        width: 100%;
    }
    .quick-range-pills .range-pill {
        background: #fff;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        font-weight: 500;
        color: #475467;
        transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        border: none;
    }
    .quick-range-pills .range-pill.active {
        background: #111322;
        color: #fff;
        box-shadow: 0 12px 24px rgba(17, 19, 34, 0.25);
    }
    .date-picker-pill {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        border: 1px solid #e4e7ec;
        border-radius: 999px;
        padding: 0.35rem 0.9rem;
        background: #fff;
    }
    .date-picker-pill .form-control {
        border: none;
        background: transparent;
        padding: 0;
        width: 170px;
        height: auto;
    }
    .time-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .logs-table thead th {
        font-size: 13px;
        text-transform: none;
        color: #4b5563;
        border-top: none;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }
    .logs-table tbody td {
        vertical-align: middle;
        border-color: #f3f4f6;
    }
    .logs-table tbody tr:hover {
        background: #f9fafb;
    }
    .action-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 12px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 12px;
        font-weight: 600;
    }
    .muted {
        color: #9ca3af;
    }
    .monitor-shell {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .monitor-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px;
        border-bottom: 1px solid #eef2f7;
    }
    .monitor-header h5 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }
    .monitor-header p {
        margin: 0;
        color: #6b7280;
        font-size: 13px;
    }
    .monitor-range {
        font-size: 12px;
        font-weight: 600;
        color: #0f766e;
        background: #ecfeff;
        padding: 6px 10px;
        border-radius: 10px;
        border: 1px solid #99f6e4;
        white-space: nowrap;
    }
    .pulse-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(245px, 1fr));
        gap: 12px;
        padding: 16px 18px 18px 18px;
    }
    .pulse-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        overflow: hidden;
    }
    .pulse-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
    }
    .pulse-card-head strong {
        font-size: 17px;
        color: #111827;
    }
    .pulse-total {
        font-size: 13px;
        color: #14b8a6;
        font-weight: 700;
        white-space: nowrap;
    }
    .pulse-card-body {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .pulse-action {
        font-size: 17px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }
    .pulse-meta {
        font-size: 13px;
        color: #6b7280;
    }
    .status-dot {
        width: 13px;
        height: 13px;
        border-radius: 999px;
        display: inline-block;
        margin-right: 8px;
        border: 2px solid transparent;
    }
    .status-online {
        background: #0284c7;
        border-color: rgba(2, 132, 199, 0.2);
    }
    .status-range {
        background: #94a3b8;
        border-color: rgba(148, 163, 184, 0.2);
    }
    .monitor-chart-card {
        padding: 14px 16px;
    }
    .monitor-chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }
    .monitor-chart-header h6 {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #111827;
    }
    .monitor-chart-header span {
        font-size: 12px;
        color: #6b7280;
    }
    .monitor-chart-body {
        height: 360px;
        position: relative;
    }
    .monitor-empty {
        border: 1px dashed #d1d5db;
        border-radius: 12px;
        background: #f9fafb;
        color: #6b7280;
        text-align: center;
        padding: 36px 16px;
        font-size: 14px;
    }
    @media (max-width: 991px) {
        .monitor-chart-body {
            height: 300px;
        }
    }
    @media (max-width: 767px) {
        .monitor-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .monitor-range {
            white-space: normal;
        }
        .monitor-chart-header h6 {
            font-size: 20px;
        }
        .time-filter-actions {
            margin-left: 0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="mt-4">
    <div class="logs-header">
        <div class="text-muted small">Actividad de usuarios</div>
        <form method="get" id="activity-logs-filter" class="logs-filter-form">
            <input type="hidden" name="from_date" value="{{ $fromDate }}">
            <input type="hidden" name="to_date" value="{{ $toDate }}">
            <input type="hidden" name="range" id="activity_logs_range_type" value="{{ $selectedRange }}">

            <div class="time-filter-card">
                <div class="quick-range-pills">
                    @foreach($filterOptions as $value => $label)
                        <button type="submit" name="range" value="{{ $value }}" class="range-pill quick-range-button {{ $selectedRange === $value ? 'active' : '' }}">
                            {{ $label }}
                        </button>
                    @endforeach

                    <div class="date-picker-pill">
                        <i class="fa fa-calendar text-muted"></i>
                        <input
                            type="text"
                            id="activity_logs_range"
                            class="form-control"
                            placeholder="Seleccionar rango"
                            value="{{ (!empty($fromDate) && !empty($toDate)) ? \Carbon\Carbon::parse($fromDate)->format('d-m-Y').' - '.\Carbon\Carbon::parse($toDate)->format('d-m-Y') : '' }}"
                            autocomplete="off"
                        >
                    </div>

                    <div class="time-filter-actions ml-auto">
                        <button type="submit" class="btn btn-dark rounded-pill px-4">Aplicar</button>
                        <button type="button" class="btn btn-link text-dark" id="activity_logs_range_clear">Limpiar</button>
                    </div>
                </div>
            </div>

            <div class="logs-search">
                <div style="flex:2; min-width: 220px;">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Buscar en acción, sujeto o meta...">
                </div>
                <div style="flex:1; min-width: 180px;">
                    <select name="action" class="form-control form-control-sm">
                        <option value="">Todas las acciones</option>
                        @foreach($actions as $actionOption)
                            <option value="{{ $actionOption }}" @selected($actionOption === $action)>{{ $actionOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1; min-width: 220px;">
                    <select name="user_id" class="form-control form-control-sm">
                        <option value="">Todos los usuarios</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) $user->id === (string) $userId)>
                                {{ $user->name }} (#{{ $user->id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()" style="max-width: 110px;">
                    @foreach([10,25,50,100] as $size)
                        <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }} / pág.</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="monitor-shell mt-2">
        <div class="logs-card">
            <div class="monitor-header">
                <div>
                    <h5>Pulse de usuarios</h5>
                    <p>Usuarios activos en este momento o con actividad dentro del rango filtrado.</p>
                </div>
                <div class="monitor-range">{{ $dashboardRangeLabel }}</div>
            </div>

            <div class="pulse-grid">
                @forelse($activityCards as $card)
                    <div class="pulse-card">
                        <div class="pulse-card-head">
                            <div class="d-flex align-items-center">
                                <span class="status-dot status-{{ $card['status'] }}"></span>
                                <strong>{{ $card['name'] }}</strong>
                            </div>
                            <span class="pulse-total">{{ number_format($card['total_logs']) }} logs</span>
                        </div>
                        <div class="pulse-card-body">
                            <div class="pulse-action">{{ $card['last_action'] }}</div>
                            <div class="pulse-meta">{{ $card['status_label'] }} · {{ $card['last_activity_at_human'] }}</div>
                            <div class="pulse-meta">Tiempo activo: {{ $card['active_time_label'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="monitor-empty">
                        No hay actividad de usuarios para el rango seleccionado.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <div class="logs-card monitor-chart-card h-100">
                    <div class="monitor-chart-header">
                        <h6>Eventos por día (apilado por usuario)</h6>
                        <span>{{ $dashboardRangeLabel }}</span>
                    </div>
                    <div class="monitor-chart-body">
                        @if(count($eventsByDayChart['labels']) > 0 && count($eventsByDayChart['datasets']) > 0)
                            <canvas id="eventsByUserChart"></canvas>
                        @else
                            <div class="monitor-empty">No hay eventos para construir la gráfica en este rango.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="logs-card monitor-chart-card h-100">
                    <div class="monitor-chart-header">
                        <h6>Top usuarios</h6>
                        <span>Acciones totales + minutos activos</span>
                    </div>
                    <div class="monitor-chart-body">
                        @if(count($topUsersChart['labels']) > 0)
                            <canvas id="topUsersActivityChart"></canvas>
                        @else
                            <div class="monitor-empty">No hay usuarios con actividad para este período.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="logs-card mt-3">
        <div class="table-responsive">
            <table class="table logs-table mb-0">
                <thead>
                    <tr>
                        <th style="width:80px;">ID</th>
                        <th>Acción</th>
                        <th>Usuario</th>
                        <th>Sujeto</th>
                        <th>IP</th>
                        <th style="width:170px;">Creado</th>
                        <th style="width:120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-muted">
                                <a href="{{ route('activity_logs.show', $log->id) }}">#{{ $log->id }}</a>
                            </td>
                            <td>
                                <span class="action-pill">{{ $log->action }}</span>
                            </td>
                            <td>
                                @if($log->user)
                                    {{ $log->user->name }} <span class="muted">(#{{ $log->user->id }})</span>
                                @else
                                    <span class="muted">Sistema</span>
                                @endif
                            </td>
                            <td>
                                @if($log->subject_type && $log->subject_id)
                                    <span class="muted">{{ \Illuminate\Support\Str::afterLast($log->subject_type, '\\') }}</span>
                                    @if($log->subject_type === \App\Models\Customer::class)
                                        <a href="{{ route('customers.show', $log->subject_id) }}">#{{ $log->subject_id }}</a>
                                    @else
                                        #{{ $log->subject_id }}
                                    @endif
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="muted">{{ $log->ip ?? '-' }}</span>
                            </td>
                            <td class="text-muted">{{ $log->created_at }}</td>
                            <td>
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('activity_logs.show', $log->id) }}">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between px-3 py-2">
            <div class="small text-muted">
                @if($logs->total())
                    Mostrando {{ $logs->firstItem() }} - {{ $logs->lastItem() }} de {{ $logs->total() }}
                @else
                    Sin resultados
                @endif
            </div>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const $filterForm = $('#activity-logs-filter');
    if ($filterForm.length) {
        const $rangeInput = $('#activity_logs_range');
        const $fromField = $filterForm.find('input[name="from_date"]');
        const $toField = $filterForm.find('input[name="to_date"]');
        const $rangeField = $('#activity_logs_range_type');
        const $clearButton = $('#activity_logs_range_clear');
        const $quickButtons = $filterForm.find('.quick-range-button');
        const hasDateRangePicker = typeof $rangeInput.daterangepicker === 'function' && typeof window.moment !== 'undefined';

        function setCustomRange(startDate, endDate, syncPicker = true) {
            $fromField.val(startDate.format('YYYY-MM-DD'));
            $toField.val(endDate.format('YYYY-MM-DD'));
            $rangeField.val('custom');
            $rangeInput.val(startDate.format('DD-MM-YYYY') + ' - ' + endDate.format('DD-MM-YYYY'));

            if (syncPicker && $rangeInput.data('daterangepicker')) {
                $rangeInput.data('daterangepicker').setStartDate(startDate);
                $rangeInput.data('daterangepicker').setEndDate(endDate);
            }
        }

        function clearCustomRange(setAllRange = false) {
            $fromField.val('');
            $toField.val('');
            $rangeInput.val('');

            if (setAllRange) {
                $rangeField.val('all');
            }
        }

        if (hasDateRangePicker) {
            const defaultStart = moment().startOf('month');
            const defaultEnd = moment().endOf('month');
            const inputStart = $fromField.val() ? moment($fromField.val(), 'YYYY-MM-DD') : defaultStart;
            const inputEnd = $toField.val() ? moment($toField.val(), 'YYYY-MM-DD') : defaultEnd;

            $rangeInput.daterangepicker({
                startDate: inputStart,
                endDate: inputEnd,
                autoUpdateInput: false,
                opens: 'center',
                locale: {
                    format: 'DD-MM-YYYY',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    firstDay: 1,
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'day'), moment().subtract(1, 'day')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Últimos 60 días': [moment().subtract(59, 'days'), moment()],
                    'Últimos 90 días': [moment().subtract(89, 'days'), moment()],
                    'Esta semana': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
                    'Semana pasada': [moment().subtract(1, 'week').startOf('isoWeek'), moment().subtract(1, 'week').endOf('isoWeek')],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Máximo': [moment('2015-01-01', 'YYYY-MM-DD'), moment()],
                },
            }, function (startDate, endDate) {
                setCustomRange(startDate, endDate, false);
            }).on('apply.daterangepicker', function (event, picker) {
                setCustomRange(picker.startDate, picker.endDate, false);
            }).on('cancel.daterangepicker', function () {
                clearCustomRange(true);
            });

            if ($fromField.val() && $toField.val()) {
                setCustomRange(inputStart, inputEnd, false);
            }
        }

        $quickButtons.on('click', function () {
            const selectedQuickRange = String($(this).val() || 'today');
            $rangeField.val(selectedQuickRange);
            clearCustomRange(false);
        });

        $clearButton.on('click', function (event) {
            event.preventDefault();
            clearCustomRange(true);
        });
    }

    if (!window.Chart) {
        return;
    }

    const eventsByDayChartData = @json($eventsByDayChart);
    const topUsersChartData = @json($topUsersChart);
    const colorPalette = [
        { border: 'rgb(20, 184, 166)', fill: 'rgba(20, 184, 166, 0.22)' },
        { border: 'rgb(59, 130, 246)', fill: 'rgba(59, 130, 246, 0.22)' },
        { border: 'rgb(236, 72, 153)', fill: 'rgba(236, 72, 153, 0.22)' },
        { border: 'rgb(245, 158, 11)', fill: 'rgba(245, 158, 11, 0.22)' },
        { border: 'rgb(139, 92, 246)', fill: 'rgba(139, 92, 246, 0.22)' },
        { border: 'rgb(14, 165, 233)', fill: 'rgba(14, 165, 233, 0.22)' },
        { border: 'rgb(107, 114, 128)', fill: 'rgba(107, 114, 128, 0.18)' },
    ];

    const eventsCanvas = document.getElementById('eventsByUserChart');
    if (
        eventsCanvas &&
        Array.isArray(eventsByDayChartData.labels) &&
        eventsByDayChartData.labels.length > 0 &&
        Array.isArray(eventsByDayChartData.datasets) &&
        eventsByDayChartData.datasets.length > 0
    ) {
        const datasets = eventsByDayChartData.datasets.map((dataset, index) => {
            const color = colorPalette[index % colorPalette.length];

            return {
                label: dataset.label,
                data: Array.isArray(dataset.data) ? dataset.data : [],
                borderColor: color.border,
                backgroundColor: color.fill,
                fill: true,
                tension: 0.32,
                pointRadius: 2,
                pointHoverRadius: 5,
                borderWidth: 2,
                stack: 'events',
            };
        });

        new Chart(eventsCanvas, {
            type: 'line',
            data: {
                labels: eventsByDayChartData.labels,
                datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
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
                                const value = context.parsed.y ?? 0;
                                return `${context.dataset.label}: ${value} eventos`;
                            },
                        },
                    },
                },
            },
        });
    }

    const topUsersCanvas = document.getElementById('topUsersActivityChart');
    if (
        topUsersCanvas &&
        Array.isArray(topUsersChartData.labels) &&
        topUsersChartData.labels.length > 0
    ) {
        const activeTimeLabels = Array.isArray(topUsersChartData.active_time_labels)
            ? topUsersChartData.active_time_labels
            : [];

        new Chart(topUsersCanvas, {
            type: 'bar',
            data: {
                labels: topUsersChartData.labels,
                datasets: [
                    {
                        label: 'Acciones totales',
                        data: Array.isArray(topUsersChartData.actions) ? topUsersChartData.actions : [],
                        backgroundColor: 'rgba(20, 184, 166, 0.86)',
                        borderRadius: 8,
                        barThickness: 18,
                        xAxisID: 'x',
                    },
                    {
                        label: 'Minutos activos',
                        data: Array.isArray(topUsersChartData.active_minutes) ? topUsersChartData.active_minutes : [],
                        backgroundColor: 'rgba(59, 130, 246, 0.82)',
                        borderRadius: 8,
                        barThickness: 18,
                        xAxisID: 'x1',
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        position: 'bottom',
                        title: {
                            display: true,
                            text: 'Acciones',
                        },
                        ticks: {
                            precision: 0,
                        },
                    },
                    x1: {
                        beginAtZero: true,
                        position: 'top',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Minutos activos',
                        },
                        ticks: {
                            precision: 0,
                        },
                    },
                    y: {
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
                                if (context.dataset.label === 'Minutos activos') {
                                    const label = activeTimeLabels[context.dataIndex] || `${context.parsed.x}m`;
                                    return `${context.dataset.label}: ${label}`;
                                }

                                return `${context.dataset.label}: ${context.parsed.x}`;
                            },
                        },
                    },
                },
            },
        });
    }
})();
</script>
@endpush
