@php
    $fromDateValue = '';
    $fromTimeValue = '';
    $toDateValue = '';
    $toTimeValue = '';

    if ($request->filled('from_date')) {
        $fromDate = \Carbon\Carbon::parse($request->from_date);
        $fromDateValue = $fromDate->format('Y-m-d');
        if ($request->filled('from_time')) {
            $fromTimeValue = $request->from_time;
        } elseif (strlen($request->from_date ?? '') > 10) {
            $fromTimeValue = $fromDate->format('H:i');
        }
    }

    if ($request->filled('to_date')) {
        $toDate = \Carbon\Carbon::parse($request->to_date);
        $toDateValue = $toDate->format('Y-m-d');
        if ($request->filled('to_time')) {
            $toTimeValue = $request->to_time;
        } elseif (strlen($request->to_date ?? '') > 10) {
            $toTimeValue = $toDate->format('H:i');
        }
    }
@endphp

<form action="/actions/" method="GET" id="filter_form" class="flex flex-col gap-4">
    @include('actions.dashboard')

    <input type="hidden" name="range_type" id="range_type" value="{{ $request->range_type }}">

    <!-- Selector de tiempo -->
    <div>
        <label for="filter" class="mb-1 text-sm font-medium text-gray-700">Rango rápido</label>
        <select name="filter" id="filter" onchange="update()"
            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Seleccione tiempo</option>
            <option value="0" @if ($request->filter == "0") selected @endif>Hoy</option>
            <option value="-1" @if ($request->filter == "-1") selected @endif>Ayer</option>
            <option value="thisweek" @if ($request->filter == "thisweek") selected @endif>Esta semana</option>
            <option value="lastweek" @if ($request->filter == "lastweek") selected @endif>Semana pasada</option>
            <option value="lastmonth" @if ($request->filter == "lastmonth") selected @endif>Mes pasado</option>
            <option value="currentmonth" @if ($request->filter == "currentmonth") selected @endif>Este mes</option>
            <option value="-7" @if ($request->filter == "-7") selected @endif>Últimos 7 días</option>
            <option value="-30" @if ($request->filter == "-30") selected @endif>Últimos 30 días</option>
        </select>
    </div>

    <!-- Fechas -->
    <div>
        <label for="from_date" class="mb-1 text-sm font-medium text-gray-700">Desde</label>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <input type="date" id="from_date" name="from_date" value="{{ $fromDateValue }}"
                class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
            <input type="time" id="from_time" name="from_time" value="{{ $fromTimeValue }}" step="60"
                class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
    </div>

    <div>
        <label for="to_date" class="mb-1 text-sm font-medium text-gray-700">Hasta</label>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <input type="date" id="to_date" name="to_date" value="{{ $toDateValue }}"
                class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
            <input type="time" id="to_time" name="to_time" value="{{ $toTimeValue }}" step="60"
                class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
    </div>

    <!-- Toggle pendientes -->
    <div class="flex items-center gap-2" x-data="{ pending: {{ request()->input('pending', 'true') === 'true' ? 'true' : 'false' }} }">
        <label for="pending-toggle" class="text-sm font-medium text-gray-700">Pendientes</label>
        <button type="button" id="pending-toggle" @click="pending = !pending"
            :class="pending ? 'bg-blue-600' : 'bg-gray-300'"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition">
            <span :class="pending ? 'translate-x-6' : 'translate-x-1'"
                class="inline-block h-4 w-4 transform rounded-full bg-white transition"></span>
        </button>
        <input type="hidden" name="pending" :value="pending ? 'true' : 'false'">
    </div>

    <!-- Solo audios -->
    <div class="flex items-center gap-2">
        <input type="checkbox" id="has_audio" name="has_audio" value="1" @checked($request->boolean('has_audio'))
            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        <label for="has_audio" class="text-sm font-medium text-gray-700">Solo audios</label>
    </div>

    <!-- Tipo acción -->
    <div>
        <select name="type_id" id="type_id"
            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Tipo acción...</option>
            @foreach($action_options as $item)
                <option value="{{ $item->id }}" @if ($request->type_id == $item->id) selected @endif>{{ $item->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Usuario -->
    @if(Auth::check() && (Auth::user()->role_id == 1 || Auth::user()->role_id == 10))
        <div>
            <select name="user_id" id="user_id"
                class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos los usuarios</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @if ($request->user_id == $user->id) selected @endif>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <!-- Estado cliente -->
    <div>
        <select name="status_id" id="status_id"
            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">Estado cliente...</option>
            @foreach($statuses_options as $statusOption)
                <option value="{{ $statusOption->id }}" @if ($request->status_id == $statusOption->id) selected @endif>{{ $statusOption->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Búsqueda -->
    <div>
        <input type="text" placeholder="Busca o escribe" id="action_search" name="action_search" value="{{ $request->get('action_search') }}"
            class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>

    <!-- Botón -->
    <div>
        <button type="submit" class="w-full rounded-md bg-blue-600 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Filtrar
        </button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const filterSelect = document.getElementById('filter');
                const rangeTypeInput = document.getElementById('range_type');
                const fromDate = document.getElementById('from_date');
                const fromTime = document.getElementById('from_time');
                const toDate = document.getElementById('to_date');
                const toTime = document.getElementById('to_time');

                if (!filterSelect || !fromTime || !toTime) {
                    return;
                }

                const setManualRangeMode = function () {
                    filterSelect.value = '';
                    if (rangeTypeInput) {
                        rangeTypeInput.value = 'all';
                    }
                };
                window.setManualRangeMode = setManualRangeMode;

                [fromDate, fromTime, toDate, toTime].forEach(function (field) {
                    if (!field) {
                        return;
                    }

                    field.addEventListener('change', setManualRangeMode);
                });

                const originalUpdate = window.update;

                window.update = function () {
                    if (typeof originalUpdate === 'function') {
                        originalUpdate.apply(this, arguments);
                    }

                    if (rangeTypeInput) {
                        rangeTypeInput.value = 'all';
                    }

                    if (!filterSelect.value) {
                        fromTime.value = '';
                        toTime.value = '';
                        return;
                    }

                    fromTime.value = '00:00';
                    toTime.value = '23:59';
                };

                if (filterSelect.value) {
                    fromTime.value = fromTime.value || '00:00';
                    toTime.value = toTime.value || '23:59';
                }
            });
        </script>
    @endpush
@endonce
