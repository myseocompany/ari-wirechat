<h2 class="mt-4">Acciones</h2>

@php
    $record = isset($customer) ? $customer : $model;
@endphp

<div class="card shadow-sm p-3 mb-4">
    <form action="/customers/{{$customer->id}}/action/store" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Acción programada previa --}}
        @if(isset($actionProgramed))
            <div class="alert alert-info">
                <strong>Acción pendiente:</strong> {{$actionProgramed->note}}
                <input type="hidden" name="ActionProgrameId" value="{{$actionProgramed->id}}">
            </div>
        @endif

        {{-- Nota --}}
        <div class="form-group mb-3">
            <textarea name="note" id="note" rows="4" class="form-control" placeholder="Escribe la nota..." required></textarea>
        </div>

        {{-- Estado y Tipo de Acción --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="status_id" class="form-label">Estado</label>
                <select name="status_id" id="status_id" class="form-control">
                    <option value="">Seleccione un estado</option>
                    @foreach($statuses_options as $status_option)
                        <option value="{{ $status_option->id }}" 
                            {{ $record->status_id == $status_option->id ? 'selected' : '' }}>
                            {{ $status_option->name }}
                        </option>
                    @endforeach
                </select>

                @include('customers.status_table', ["statuses_options"=>$statuses_options])
                <button class="btn btn-link p-0" type="button" data-toggle="tooltip" data-html="true" data-placement="top">
                    <i class="fa fa-question-circle text-primary" id="helpButtonStatus"></i>
                </button>
            </div>

            <div class="col-md-6">
                <label for="type_id" class="form-label">Tipo de acción</label>
                <select name="type_id" id="type_id" class="form-control" required>
                    @foreach($action_options as $action_option)
                        <option value="{{$action_option->id}}">{{$action_option->name}}</option>
                    @endforeach
                </select>
                @include('customers.actions_table', ["action_options"=>$action_options])
                <button class="btn btn-link p-0" type="button" data-toggle="tooltip" data-html="true" data-placement="top">
                    <i class="fa fa-question-circle text-primary" id="helpButtonAction"></i>
                </button>
            </div>
        </div>

        {{-- Archivo (opcional) --}}
        <div class="form-group mb-3">
            <input type="file" class="form-control" id="file" name="file">
        </div>

        {{-- Toggle programación --}}
        <div class="form-group mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="toggleDate" onclick="toggleDateInput()">
                <label class="form-check-label" for="toggleDate">Programar acción</label>
            </div>
        </div>

        {{-- Campo fecha (inicialmente oculto) --}}
        <div class="form-group mb-4" id="dateInputContainer" style="display: none;">
            <label for="example-datetime-local-input" class="form-label">Fecha y hora</label>
            <input class="form-control" name="date_programed" type="datetime-local" id="example-datetime-local-input">
        </div>

        {{-- Botón submit --}}
        <div class="text-center">
            <button type="submit" class="btn btn-primary w-100">Guardar acción</button>
        </div>

        <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
    </form>
</div>

<script>
function toggleDateInput() {
    const checkbox = document.getElementById('toggleDate');
    const dateInput = document.getElementById('date_programed');
    const dateContainer = document.getElementById('dateInputContainer');
    
    if (checkbox.checked) {
        dateContainer.style.display = 'block';
        dateInput.disabled = false;
    } else {
        dateContainer.style.display = 'none';
        dateInput.disabled = true;
    }
}

</script>
