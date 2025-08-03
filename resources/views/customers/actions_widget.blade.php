<div class="card shadow-sm p-3 mb-4">
    @if(isset($actionProgramed))
        <div class="alert alert-info">
            <strong>Acción pendiente:</strong> {{$actionProgramed->note}}
            <input type="hidden" name="ActionProgrameId" value="{{$actionProgramed->id}}">
        </div>
    @endif

    <form action="/customers/{{$model->id}}/action/store" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Nota --}}
        <div class="form-group mb-3">
            <textarea name="note" id="note" rows="4" class="form-control" placeholder="Escribe la nota..." required></textarea>
        </div>

        {{-- Estado y Tipo de Acción --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <select name="status_id" id="status_id" class="form-control">
                    @foreach($statuses_options as $status_option)
                        <option value="{{$status_option->id}}" @if($model->status_id == $status_option->id) selected @endif>
                            {{$status_option->name}}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-center">
                <select name="type_id" id="type_id" class="form-control">
                    @foreach($action_options as $action_option)
                        <option value="{{$action_option->id}}">{{$action_option->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Archivo --}}
        <div class="form-group mb-3">
            <input type="file" class="form-control" id="file" name="file">
            <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">
        </div>

        {{-- Toggle para programar acción --}}
        <div class="form-group mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="toggleDate" onclick="toggleDateInput()">
                <label class="form-check-label" for="toggleDate">Programar acción</label>
            </div>
        </div>

        {{-- Campo de fecha programada (oculto por defecto) --}}
        <div class="form-group mb-4" id="dateInputContainer" style="display:none;">
            <input class="form-control" name="date_programed" type="datetime-local" id="example-datetime-local-input" value="{{$today}}">
        </div>

        {{-- Botón enviar --}}
        <div class="text-center">
            <button type="submit" class="btn btn-primary w-100">Guardar acción</button>
        </div>
    </form>
</div>

<script>
function toggleDateInput() {
    const checkbox = document.getElementById('toggleDate');
    const dateContainer = document.getElementById('dateInputContainer');
    dateContainer.style.display = checkbox.checked ? 'block' : 'none';
}
</script>
 @include('customers.status_table', ["statuses_options"=>$statuses_options])
  @include('customers.actions_table', ["action_options"=>$action_options])