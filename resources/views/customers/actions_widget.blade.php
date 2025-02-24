<h2>Acciones</h2>
<div>
  <form action="/customers/{{$model->id}}/action/store" method="POST" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div>
      @if(isset($actionProgramed))
      <input type="hidden" name="ActionProgrameId" value="{{$actionProgramed->id}}">
      <h3>Acción pendiente: <strong>{{$actionProgramed->note}}</strong></h3>
      @endif
      <textarea name="note" id="note" style="width:100%" rows="5" required="required"></textarea>
    </div>
    <div>
     <select name="type_id" id="type_id" required>
      <option value="">Seleccione una acción</option>
      @foreach($action_options as $action_option)
      <option value="{{$action_option->id}}">{{$action_option->name}}</option>
      @endforeach
    </select>
    @include('customers.actions_table', ["action_options"=>$action_options])
    
    <button class="btn btn-link" type="button" data-toggle="tooltip" data-html="true" data-placement="top">
      <span id="helpButtonAction" style="cursor:pointer; color:blue;">  
        <i class="fa fa-question-circle question"></i>
      </span>
    </button>

    <select name="status_id" id="status_id">
      <option value="">Seleccione un estado</option>
      @foreach($statuses_options as $status_option)
      <option value="{{$status_option->id}}">{{$status_option->name}}</option>
      @endforeach
    </select>

    @include('customers.status_table', ["statuses_options"=>$statuses_options])
    
    <button class="btn btn-link" type="button" data-toggle="tooltip" data-html="true" data-placement="top">
      <span id="helpButtonStatus" style="cursor:pointer; color:blue;">  
        <i class="fa fa-question-circle question"></i>
      </span>
    </button>
    
     <!-- Subir archivos-->

     <div class="form-group">       
              <div class="col"><input type="file" class="form-control" id="file" name="file" placeholder="email" ></div>
              <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}"> 

      </div>

      <div style="margin-bottom:1rem;">
        <label for="example-datetime-local-input" class="col-form-label">Fecha y hora</label>
        <div>
         <input class="form-control" name="date_programed" type="datetime-local" value="{{$today}}" id="example-datetime-local-input">
       </div>
     </div>
     <div>


      <input class="btn btn-primary btn-sm" type="submit" value="Enviar acción">
      <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">
    </div>
  </form>
</div>