@if($actions->count()>0)
<h2>Acciones</h2>

  <table class="table table-striped">
  <tbody>
    @foreach($actions as $action)
    <tr>
      <td>
        <div>
      @if(isset($action->type)&& !is_null($action->type)&& $action->type!='')
      @if($action->type->id == 27)
        <i class="fa fa-money"></i>
      @elseif($action->type->id == 28)
         <i class="fa fa-bell"></i>
      @elseif($action->type->id == 29)
        <i class="fa fa-exclamation-triangle"></i>
      @endif {{$action->type->name}}@endif
    </div>
    @if(isset($action->type)&& !is_null($action->type)&& $action->type!='')
      @if($action->type->id == 27)
        <div>
          Valor: ${{$action->sale_amount}}
        </div>
      @endif
    @endif
    
    <div>
      
        <a href="/actions/{{$action->id}}/show">
        {{$action->created_at}}
        </a>
      
    </div>
    <div>  
      @if(isset($action->creator)&& !is_null($action->creator)&& $action->creator!=''){{$action->creator->name}}@else Automático @endif
    </div>
    <div>  
      @if($action->type_id==2 || $action->type_id==4) {{$action->getEmailSubject()}} <br> {{$action->note}} @else {{$action->note}}@endif
      

       @if((Auth::check()) && (Auth::user()->role_id == 1))
       <a href="/actions/{{$action->id}}/destroy">
        <span class="btn btn-sm btn-danger fa fa-trash-o" aria-hidden="true" title="Eliminar"></span>
      </a>
      @endif
    

  </div>
      </td>
    </tr>
  @endforeach

</tbody>
</table>

@endif