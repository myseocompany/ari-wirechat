@extends('layout')

@section('content')

<h1>Acciones</h1>
@if($model instanceof \Illuminate\Pagination\LengthAwarePaginator )
Registro <strong>{{ $model->currentPage() * $model->perPage() - ( $model->perPage() - 1 ) }}</strong> a <strong>{{ min($model->currentPage() * $model->perPage(), $model->total()) }}</strong> de <strong>{{$model->total()}}</strong>
@endif
<form action="/actions/" method="GET" id="filter_form">
  <div class="row">
    <div class="col"><select name="filter" class="custom-select" id="filter" onchange="update()">
        <option value="">Seleccione tiempo</option>
        <option value="0" @if ($request->filter == "0") selected="selected" @endif>hoy</option>
        <option value="-1" @if ($request->filter == "-1") selected="selected" @endif>ayer</option>
        <option value="thisweek" @if ($request->filter == "thisweek") selected="selected" @endif>esta semana</option>
        
        <option value="lastweek" @if ($request->filter == "lastweek") selected="selected" @endif>semana pasada</option>
        <option value="lastmonth" @if ($request->filter == "lastmonth") selected="selected" @endif>mes pasado</option>
        <option value="currentmonth" @if ($request->filter == "currentmonth") selected="selected" @endif>este mes</option>
        <option value="-7" @if ($request->filter == "-7") selected="selected" @endif>ultimos 7 dias</option>
        <option value="-30" @if ($request->filter == "-30") selected="selected" @endif>ultimos 30 dias</option>
        
      </select></div>
    <div class="col"><input class="input-date" type="date" id="from_date" name="from_date" onchange="cleanFilter()" value="{{$request->from_date}}">
    <br>  
    <input class="input-date" type="date" id="to_date" name="to_date" onchange="cleanFilter()" value="{{$request->to_date}}">
</div>

<div class="col">
 
{{-- Combo de estados --}}
<select name="type_id" class="slectpicker custom-select" id="type_id" onchange="submit();">
        <option value="">Tipo acción...</option>
        @foreach($action_options as $item)
          <option value="{{$item->id}}" @if ($request->type_id == $item->id) selected="selected" @endif>
             {{ $item->name }}
            
          </option>
        @endforeach
      </select>
      </div>

<div class="col">

      <!--  
*
*    Combo de usuarios
*
-->
      <select name="creator_user_id" class="custom-select" id="creator_user_id" onchange="submit();">
        <option value="">Usuario creador</option>
        @foreach($users as $user)
          <option value="{{$user->id}}" @if ($request->creator_user_id == $user->id) selected="selected" @endif>
             <?php echo substr($user->name, 0, 10); ?>
            
          </option>
        @endforeach
      </select>
      </div>

<div class="col">

      <!--  
*
*    Combo de usuarios
*
-->
<select name="user_id" class="custom-select" id="user_id" onchange="submit();">
        <option value="">Dueño del cliente</option>
        @foreach($users as $user)
          <option value="{{$user->id}}" @if ($request->user_id == $user->id) selected="selected" @endif>
             <?php echo substr($user->name, 0, 10); ?>
            
          </option>
        @endforeach
      </select>
      </div>

<div class="col">

      <input type="submit" class="btn btn-sm btn-primary my-2 my-sm-0" value="Filtrar" >
</div>

  </div>
       
     
    </form>
<table class="table table-striped">
  <tbody>
  @foreach ($model as $item)
    <tr>
      
      
      <td>
        <div>@if(isset($item->customer->status))
        <span class="badge" style="background-color: {{$item->customer->status->color}} ">
        {{$item->customer->status->name}} 
         </span> 
        @endif</div>
{{$item->created_at}} -   {{$item->getTypeName()}} 
        <a href="/customers/{{$item->customer_id}}/show"><h4> {{$item->getCustomerName()}}</h4></a>
        <div class="action_note">{{$item->note}}</div>
        <div class="action_created"></div>
        <div class="row">
          
            @if(isset($item->customer))
            Asesor:
            @if(isset($item->customer->user))
            <div class="col">{{$item->customer->user->name}}</div>
            @endif
            
            @if(isset($item->creator))
            <div class="col">Creador: {{$item->creator->name}}</div>
            @endif
          
            <div class="col">{{$item->customer->phone}}</div>
          <div class="col">{{$item->customer->email}}</div>

          
            @if(isset($item->customer->project))
            
            <div class="col">{{$item->customer->project->name}}</div>
            @endif
            @if(isset($item->customer->source))
            
            <div class="col">{{$item->customer->source->name}}</div>
            @endif
          @endif
          
        </div>
        
          </td>
    </tr>
  @endforeach
  </tbody>
</table>
@if($model instanceof \Illuminate\Pagination\LengthAwarePaginator )

{{ $model->appends(request()->input())->links() }}
@endif
@endsection