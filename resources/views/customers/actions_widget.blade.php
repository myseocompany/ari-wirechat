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
    <button class="btn btn-link" type="button" data-toggle="tooltip" data-html="true" data-placement="top" title='<h4>Clic para ver todas las acciones</h4>
      <div class="box">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Acción</th>
              <th scope="col"> Entrada</th>
              <th scope="col"> Salida</th>
              <th scope="col">Descripción</th>
            </tr>
          </thead>
          <tbody> 
            <tr>
              <th scope="row">Llamada de salida</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row">Se inicio la llamada pero no contestó</td>
            </tr>
            <tr>
              <th scope="row">Llamada contacto</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row">Se le llamó, contestó, pero está ocupado</td>
            </tr>
            <tr>
              <th scope="row">Llamada efectiva</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row">Explique el producto. Min 5 min</td>
            </tr>
            <tr>
              <th scope="row">Llamada de entrada</th>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Email de salida</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row">x</td>
              
            </tr>
            <tr>
              <th scope="row">Visita web</th>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Leyó email</th>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">No se pudo enviar mail</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Email salida</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Facebook salida</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Instagram salida</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">WhatsApp salida</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Facebook entrada</th>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Instagram entrada</th>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">WhatsApp entrada</th>
              <td class="row-center" scope="row">x</td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Video llamada</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Visitó MQE</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Visita empresa</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Soporte técnico</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Despacho</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Interacción Whatsapp Business</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">No contesta</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Actualización</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Escuela</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Pedido</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">PQR</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
            <tr>
              <th scope="row">Acompañamiento de pago</th>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
              <td class="row-center" scope="row"></td>
            </tr>
          </tbody>
        </table>
      </div>'> <i class="fa fa-question-circle question"></i></button>
    <select name="status_id" id="status_id">
      <option value="">Seleccione un estado</option>
      @foreach($statuses_options as $status_option)
      <option value="{{$status_option->id}}">{{$status_option->name}}</option>
      @endforeach
    </select>

    @include('customers.status_table', ["statuses_options"=>$statuses_options])
    
    <button class="btn btn-link" type="button" data-toggle="tooltip" data-html="true" data-placement="top" title=''>
      <span id="helpButton" style="cursor:pointer; color:blue;">  
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