@extends('layout')

@section('content')
<form action="/orders/{{$model->id}}/show" method="POST">
    <label class="">Fecha:</label>
    <input id="date" name="date" class="col-6" value="{{$model->date}}"><br>

    <label class="">Descripción:</label>
    <input id="description" name="description" class="col-6" value="{{$model->description}}"><br>

    <label class="">No:</label>
    <input id="internal_id" name="internal_id" class="col-6" value="{{$model->internal_id}}"><br>

    <label class="">Valor:</label>
    <input id="credit" name="credit" class="col-6" value="{{$model->credit}}">

    <div><input type="submit" name="" class="btn btn-primary" style="display: block; margin: 0 auto;"></div>
</form>
@endsection