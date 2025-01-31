@extends('layout')

@section('content')
<h1>Orden {{$model->id}}</h1>

<form class="form-group" action="/products/{{$model->id}}/update" method="POST">
  {{ csrf_field() }}
  <div class="row">
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Nombre:</label>
        <input id="name" name="name" class="col-6 form-control" value="{{$model->name}}">
      </div>
    </div>
    
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Etapa:</label>
         <select id="category_id" name="category_id" class="col-6 form-control" required="required">
          <option>Seleccione una categoría ...</option>
          @foreach($categories as $item)
          <option value="{{$item->id}}" @if($model->category_id==$item->id) selected="selected" @endif>{{$item->name}}</option>
          @endforeach
        </select>
      </div>
    </div>
    
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Tipo:</label>
         
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Matrícula:</label>
        <input id="registration" name="registration" class="col-6 form-control" value="{{$model->registration}}">
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6" >VIS:</label>
        <div class="col-6">
          <div class="row " id="vis_container">
            <span class="col-3">Si</span>
            <input type="radio" id="VIS" name="VIS" class="col-3" value="1" @if($model->VIS==1) checked="checked" @endif">
            <span class="col-3">No</span>
            
            <input type="radio" id="no_VIS" name="VIS" class="col-3" value="0" @if($model->VIS!=1) checked="checked" @endif">
          </div>
        </div>
        
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Área construida:</label>
        <input id="built_area" name="built_area" class="col-6 form-control" value="{{$model->built_area}}">
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Área privada:</label>
        <input id="private_area" name="private_area" class="col-6 form-control" value="{{$model->private_area}}">
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Valor básico:</label>
        <input id="price" name="price" class="col-6 form-control" value="{{$model->price}}">
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Valor prima de altura:</label>
        <input id="height_over_price" name="height_over_price" class="col-6 form-control" value="{{$model->height_over_price}}">
      </div>
    </div>
    <div class="col-md-6 col-sm-12">
      <div class="row">
        <label class="col-6">Estado:</label>
        
        
      </div>
    </div>
  </div>
<div class="nav_var">
  <input type="submit" name="" class="btn btn-primary btn-sm" value="Enviar">
</div>

</form>
@endsection