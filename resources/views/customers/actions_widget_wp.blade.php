<style>
    #actions_display {
    max-height: 300px; /* Establece la altura máxima que deseas para la sección */
    overflow-y: auto; /* Agrega una barra de desplazamiento vertical cuando sea necesario */
    /* Otros estilos según tus preferencias */
    }
</style>
<div id="actions_display">
<table class="table table-striped">
  <thead>

    <th>Fecha</th>
    <th>Tipo Acción</th>
    <th>Creado por</th>
    
    <th>Descripción</th>
    <th></th>
    
  </thead>
  <tbody>
    @foreach($actions as $action)
    <tr>
      <td>
        <a href="/actions/{{$action->id}}/show">

          {{$action->created_at}}
        </a>
      </td>
      <td>@if(isset($action->type)&& !is_null($action->type)&& $action->type!=''){{$action->type->name}}@endif</td>
      <td>@if(isset($action->creator)&& !is_null($action->creator)&& $action->creator!=''){{$action->creator->name}}@else Automático @endif</td>
     
      <td> 
        @if($action->type_id==2 || $action->type_id==4) {{$action->getEmailSubject()}} <br> {{$action->note}} @else {{$action->note}}@endif</td>
      <td>

       @if((Auth::check()) && (Auth::user()->role_id == 1))
       <a href="/actions/{{$action->id}}/destroy">
        <span class="btn btn-sm btn-danger fa fa-trash-o" aria-hidden="true" title="Eliminar"></span>
      </a>
      @endif
    </td>

  </tr>
  @endforeach

</tbody>
</table>
</div>