
@extends('layout')
<?php  function clearWP($str){
  $str = trim($str);
  $str = str_replace("+", "", $str );
  return $str;
} ?>


@section('content')


<div class="row">

<div class="col-md 12">
  <h1>
 <strong>   {{$model->name}}</strong>
  </h1>
</div>
</div>

<div class="row">

<div class="col-md-6">

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Nombre:</strong></span></div> <div class="col-md-6">{{$model->name}}</div></div>
                
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Teléfono:</strong></span></div> <div class="col-md-6">{{$model->phone}} <a href="https://wa.me/{{ clearWP($model->phone) }}" target="_empty">WhatsApp </a></div></div>
</div>
<div class="col-md-6">
        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Celular:</strong></span></div> <div class="col-md-6">{{$model->phone2}}</div></div>

        <div class="row"><div class="col-md-6"><span class="lavel"><strong>Correo Electrónico:</strong></span></div> <div class="col-md-6">{{$model->email}}</div></div>
</div>

</div>

<br><br><br><br>
<div class="row">
<div class="col-md-2"></div>
<div class="col-md-10">
  <h1>
 <strong>  Crea una encuesta de satisfacción
 </strong>
  </h1>
</div>
</div>

<div class="row">
  <div class="col-md-3"></div>
  <div class="col-md-6">
  
    <form action="/metadata/{{$model->id}}/store"  method="POST" style="margin-left:10px; margin-right:10px;">
          {{ csrf_field() }}	
         
            @foreach($metaData as $item)


            @if($item->type_id == null)
              <br><br>
              <h3>{{$item->value}}</h3>

             @elseif($item->type_id == 1)
              <input type="hidden" id="customer_id" name="customer_id" value="{{$model->id}}">  

              <label> {{$item->value}}</label>
              <input type="number" id="meta_{{$item->id}}" name="meta_{{$item->id}}"  placeholder="{{$item->value}}" class="form-control">
                   
             @elseif($item->type_id == 4)
                <br>
                  <label> {{$item->value}}</label>
                  <textarea name="meta_{{$item->id}}"  id="meta_{{$item->id}}"  rows="5" cols="67" placeholder="{{$item->value}}"></textarea>
                  <input type="submit" value="Enviar" class="btn btn-primary" size="7" >
           @endif
          @endforeach


           
       
    </form>
    </div>
  </div>
@endsection