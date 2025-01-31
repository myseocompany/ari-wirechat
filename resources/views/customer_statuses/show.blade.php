@extends('layout')

@section('content')
<h1>{{$customer_statuses->name}}</h1>
<form method="POST" action="/customer_statuses/{{ $customer_statuses->id}}/edit">
  {{ csrf_field() }}
  <div class="form-group">
    <label for="name">Nombre:</label>
      <input type="text" name="name" id="name" class="form-control" placeholder="Nombre" value="{{$customer_statuses->name}}" readonly="">
  </div>  
  <div class="form-group">
    <label for="description">Descripción:</label>
      <input type="text" name="description" id="description" class="form-control" placeholder="Descripción" value="{{$customer_statuses->description}}" readonly="">
  </div> 
    <div class="form-group">
    <label for="weight">Peso:</label>
      <input type="text" name="weight" id="weight" class="form-control" placeholder="weight" value="{{$customer_statuses->weight}}" readonly="">
  </div>
  <div class="form-group">
    <label for="color">Color:</label>
      <input type="text" name="color" id="color" class="form-control" placeholder="color" value="{{$customer_statuses->color}}" readonly="">
      <input type="text" name="colorShow" id="colorShow" class="form-control" readonly="" style="height: 50px;width: 50px; background-color: {{$customer_statuses->color}}; margin-top: 1%;">
  </div>  
  
  <button type="submit" class="btn btn-sm btn-primary my-2 my-sm-0">Editar</button>
</form>
@endsection