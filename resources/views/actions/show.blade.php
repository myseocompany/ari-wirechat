@extends('layout')

@section('content')
<h1>{{$model->name}} {{$model->business}} <br> {{$model->document}}</h1>
{{-- start new show --}}
<div class="card-block">
  <form action="/actions/{{$model->id}}/edit">
    {{ csrf_field() }}
    <div class="row">
      <div class="col-md-6">
        {{-- Fecha --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Fecha:</strong></span></div> <div class="col-md-6">{{$model->created_at}}</div></div>
        {{-- Creado --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Creado por:</strong></span></div> <div class="col-md-6">{{$model->getCreatorName()}}</div></div>
        {{-- Cliente --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Cliente:</strong></span></div> <div class="col-md-6">{{$model->getCustomerName()}}</div></div>
       {{-- Nota --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Nota:</strong></span></div> <div class="col-md-6">{{$model->note}}</div></div>
       {{-- Tipo --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Tipo de acción</strong></span></div> <div class="col-md-6">{{$model->type->name}}</div></div>
            {{-- Email --}}
            <div class="row"><div class="col-md-6"><span class="lavel"><strong>Email</strong></span></div> <div class="col-md-6">{{$model->getEmailSubject()}}</div></div>
      </div>

    </div>
    
    <a href="/actions/{{ $model->id }}/edit">
            <span class="btn btn-primary btn-sm" aria-hidden="true">Editar</span>
          </a>
  </form>
</div>
<br>

    

  

  



@endsection