
<h2>Acciones</h2>
<div>
  <form action="/customers/{{$customer->id}}/action/store" method="POST">
    {{ csrf_field() }}
    <div>
      @if(isset($actionProgramed))
      <input type="hidden" name="ActionProgrameId" value="{{$actionProgramed->id}}">
      <h3>Acción pendiente: <strong>{{$actionProgramed->note}}</strong></h3>
      @endif
      <textarea name="note" id="note" cols="30" rows="5" required="required"></textarea>
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
    <button class="btn btn-link" type="button" data-toggle="tooltip" data-html="true" data-placement="top" title='<h4>Clic para ver todos los estados</h4>
      <div class="box">
        <table class="table">
          <thead class="thead-dark">
            <tr>
              <th scope="col">Estado</th>
              <th scope="col">Descripción</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th scope="row">Nuevo</th>
              <td scope="row">Recien entra a la base de datos y no se ha hecho un intento de contactarlo. Solo se le envió un correo automático</td>
            </tr>
            <tr>
              <th scope="row">Seguimiento</th>
              <td scope="row">Se llamó pero estaba ocupado, se envió WP pero no contestó. Se llamó pero no contestó</td>
            </tr>
            <tr>
              <th scope="row">Contactado</th>
              <td scope="row">Leyó correo de bienvenida - se debe llamar</td>
            </tr>
            <tr>
              <th scope="row">Prospecto</th>
              <td scope="row">Se le envió la información por whatsapp</td>
            </tr>
            <tr>
              <th scope="row">Opotunidad</th>
              <td scope="row">Contestó la llamada. Se conoce el número de empanadas que hace. Esta interesado. Hizo un nuevo intento de contacto y se le actualizó la fecha de creación</td>
            </tr>
            <tr>
              <th scope="row">Prospecto</th>
              <td scope="row">Demuestra interés a futuro y ya fabrica empanadas.</td>
            </tr>
            <tr>
              <th scope="row">Negociación</th>
              <td scope="row">Muy interesado se le envía cotización, no toma una decisión aún</td>
            </tr>
            <tr>
              <th scope="row">VIP</th>
              <td scope="row">Demuestra el nivel más alto de interés, pendiente a confirmar pago</td>
            </tr>
            <tr>
              <th scope="row">Proyecto</th>
              <td scope="row">Interesado, esta formando empresa no necesita la máquina por ahora</td>
            </tr>
            <tr>
              <th scope="row">Ganado</th>
              <td scope="row">Ya compró máquina de empanadas</td>
            </tr>
            <tr>
              <th scope="row">Ganado otros</th>
              <td scope="row">Ya compró máquina pero no de empanadas</td>
            </tr>
            <tr>
              <th scope="row">Por Facturar</th>
              <td scope="row">Ganado sin generar factura</td>
            </tr>
            <tr>
              <th scope="row">Perdido</th>
              <td scope="row">Pide explícitamente no contactarlo (habeas data)</td>
            </tr>
            <tr>
              <th scope="row">No contesta</th>
              <td scope="row">No contesta en 3 medios, 3 horarios, 3 fechas</td>
            </tr>
            <tr>
              <th scope="row">Escuela</th>
              <td scope="row">Se encuentra interesado o asistió a escuela</td>
            </tr>
          </tbody>
        </table></div>'><i class="fa fa-question-circle question"></i></button>

      </div>
      <div style="margin-bottom:1rem;">
        <label for="example-datetime-local-input" class="col-form-label">Fecha y hora</label>
        <div>
         <input class="form-control" name="date_programed" type="datetime-local" value="{{$today}}" id="example-datetime-local-input">
       </div>
     </div>
     <div>
      <input class="btn btn-primary btn-sm" type="submit" value="Enviar acción">
      <input type="hidden" id="customer_id" name="customer_id" value="{{$customer->id}}">
    </div>
  </form>
</div>