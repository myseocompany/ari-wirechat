@extends('layout')

@section('content')
<h1>{{$model->name}} {{$model->business}} <br> {{$model->document}}</h1>
{{-- start new show --}}
<div class="card-block">
  <form action="/actions/{{$model->id}}/update">
    {{ csrf_field() }}
    <div class="row">
      <div class="col-md-6">
        {{-- Fecha --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Fecha:</strong></span></div> <div class="col-md-6">
              {{$model->created_at}}
            </div></div>
        {{-- Creado --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Creado por:</strong></span></div> <div class="col-md-6">
              {{$model->getCreatorName()}}
            </div></div>
        {{-- Cliente --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Cliente:</strong></span></div> <div class="col-md-6">{{$model->getCustomerName()}}</div></div>
       {{-- Nota --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Nota:</strong></span></div> <div class="col-md-6">
            <textarea name="note" id="note" cols="30" rows="10"  class="form-control" >{{$model->note}}</textarea>
              
            </div></div>
       {{-- Tipo --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Tipo de acción</strong></span></div> <div class="col-md-6">
            <select name="type_id" id="type_id"  class="form-control" >
              @foreach($action_options as $item)
                <option value="{{$item->id}}" @if($item->id==$model->id) selected="selected" @endif>{{$item->name}}</option>
              @endforeach
            </select>
              
            </div></div>
            {{-- Email --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Email</strong></span></div> <div class="col-md-6">{{$model->getEmailSubject()}}</div></div>
            <input type="hidden" id="id" name="id" value="{{$model->id}}">
      </div>

    </div>
    

          <input type="submit" value="Enviar" class="btn btn-primary btn-sm">
  </form>
</div>
<br>

@endsection