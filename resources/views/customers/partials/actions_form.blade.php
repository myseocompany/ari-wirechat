<h2 class="mt-6 text-lg font-semibold text-slate-900">Acciones</h2>

@php
    $record = isset($customer) ? $customer : $model;
@endphp

<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <form action="/customers/{{$customer->id}}/action/store" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Acción programada previa --}}
        @if(isset($actionProgramed))
            <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800">
                <strong>Acción pendiente:</strong> {{$actionProgramed->note}}
                <input type="hidden" name="ActionProgrameId" value="{{$actionProgramed->id}}">
            </div>
        @endif

        {{-- Nota --}}
        <div class="mt-4">
            <textarea name="note" id="note" rows="4" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" placeholder="Escribe la nota..." required></textarea>
        </div>

        {{-- Estado y Tipo de Acción --}}
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="status_id" class="block text-sm font-medium text-slate-700">Estado</label>
                <select name="status_id" id="status_id" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="">Seleccione un estado</option>
                    @foreach($statuses_options as $status_option)
                        <option value="{{ $status_option->id }}" 
                            {{ $record->status_id == $status_option->id ? 'selected' : '' }}>
                            {{ $status_option->name }}
                        </option>
                    @endforeach
                </select>

                @include('customers.status_table', ["statuses_options"=>$statuses_options])
                <button class="mt-2 inline-flex items-center text-blue-600 transition hover:text-blue-700" type="button" data-toggle="tooltip" data-html="true" data-placement="top">
                    <i class="fa fa-question-circle" id="helpButtonStatus"></i>
                </button>
            </div>

            <div>
                <label for="type_id" class="block text-sm font-medium text-slate-700">Tipo de acción</label>
                <select name="type_id" id="type_id" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" required>
                    @foreach($action_options as $action_option)
                        <option value="{{$action_option->id}}">{{$action_option->name}}</option>
                    @endforeach
                </select>
                @include('customers.actions_table', ["action_options"=>$action_options])
                <button class="mt-2 inline-flex items-center text-blue-600 transition hover:text-blue-700" type="button" data-toggle="tooltip" data-html="true" data-placement="top">
                    <i class="fa fa-question-circle" id="helpButtonAction"></i>
                </button>
            </div>
        </div>

        {{-- Archivo o URL (opcional) --}}
        <div class="mt-4 space-y-2">
            <input type="file" class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200" id="file" name="file">
            <input type="url" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" id="url" name="url" placeholder="O pega aquí la URL del archivo">
        </div>

        {{-- Toggle programación --}}
        <div class="mt-4">
            <div class="flex items-center gap-2">
                <input class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" type="checkbox" id="toggleDate" data-date-toggle onclick="toggleDateInput(this)">
                <label class="text-sm text-slate-700" for="toggleDate">Programar acción</label>
            </div>
        </div>

        {{-- Campo fecha (inicialmente oculto) --}}
        <div class="mt-4 space-y-2" id="dateInputContainer" data-date-container style="display: none;">
            <label for="example-datetime-local-input" class="block text-sm font-medium text-slate-700">Fecha y hora</label>
            <input class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" name="date_programed" type="datetime-local" id="date_programed" disabled>
        </div>

        <div class="mt-4 flex items-center justify-between rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600" data-action-timer>
            <span class="font-medium text-slate-700">Tiempo creando acción</span>
            <span class="font-semibold tabular-nums text-slate-900" data-action-timer-display>00:00</span>
            <input type="hidden" name="creation_seconds" value="0" data-action-timer-seconds>
        </div>

        {{-- Botón submit --}}
        <div class="mt-4">
            <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Guardar acción</button>
        </div>

        <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
    </form>
</div>

<script>
if (!window.toggleDateInput) {
    window.toggleDateInput = function(element) {
        if (!element) {
            return;
        }
        console.log('Programar acción: click en checkbox', element);
        const form = element.closest('form');
        if (!form) {
            return;
        }
        const dateContainer = form.querySelector('[data-date-container]');
        const dateInput = form.querySelector('input[name="date_programed"]');
        if (!dateContainer || !dateInput) {
            return;
        }
        if (element.checked) {
            dateContainer.style.display = 'block';
            dateInput.disabled = false;
            return;
        }
        dateContainer.style.display = 'none';
        dateInput.disabled = true;
    };
}

if (!window.initActionDateToggle) {
    window.initActionDateToggle = function(scope) {
        const root = scope instanceof Element ? scope : document;
        root.querySelectorAll('[data-date-toggle]').forEach(function(checkbox) {
            window.toggleDateInput(checkbox);
        });
    };
}

if (!window.initActionCreationTimer) {
    window.initActionCreationTimer = function(scope) {
        const root = scope instanceof Element ? scope : document;
        root.querySelectorAll('form').forEach(function(form) {
            const timer = form.querySelector('[data-action-timer]');
            const display = form.querySelector('[data-action-timer-display]');
            const secondsField = form.querySelector('[data-action-timer-seconds]');
            if (!timer || !display || !secondsField || timer.dataset.initialized === 'true') {
                return;
            }

            timer.dataset.initialized = 'true';
            const startedAt = Date.now();
            const updateDisplay = function() {
                const elapsedSeconds = Math.max(0, Math.floor((Date.now() - startedAt) / 1000));
                const minutes = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0');
                const seconds = String(elapsedSeconds % 60).padStart(2, '0');
                display.textContent = `${minutes}:${seconds}`;
                secondsField.value = String(elapsedSeconds);
            };

            updateDisplay();
            const intervalId = window.setInterval(updateDisplay, 1000);
            form.addEventListener('submit', function() {
                updateDisplay();
                window.clearInterval(intervalId);
            }, { once: true });
        });
    };
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.initActionDateToggle) {
        window.initActionDateToggle();
    }
    if (window.initActionCreationTimer) {
        window.initActionCreationTimer();
    }
});
</script>
