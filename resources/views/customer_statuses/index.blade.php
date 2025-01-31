@extends('layout')

@section('content')
<div class="container mt-4">
<h1 class="text-center">Estados de los Usuarios</h1>
  
  <div class="row">
      <a href="/customer_statuses/create">Crear <i class="fa fa-plus-square"></i></a>
    <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Description</th>
                  <th>Weight</th>
                  <th>Color</th>
                  <th>Options</th>
                </tr>
              </thead>
              <tbody>
                @foreach($customer_statuses as $customer_status)
                <tr>
                  <td>{{ $customer_status->id }}</td>
                  <td><a href="/customer_statuses/{{ $customer_status->id }}">{{ $customer_status->name }}</a></td>
                  <td>{{ $customer_status->description }}</td>
                 <td>{{ $customer_status->weight }}</td>
                 <td><input type="text" class="form-control" readonly="" style="height: 30px;width: 30px; background-color: {{ $customer_status->color }};border: 0px solid transparent;"></td>
                  <td>
                      <a href="/customer_statuses/{{$customer_status->id }}" class="btn btn-sm btn-success my-2 my-sm-0">Ver</a>
                      <a href="/customer_statuses/{{$customer_status->id }}/edit" class="btn btn-sm btn-primary my-2 my-sm-0">Editar</a>
                      <a href="/customer_statuses/{{$customer_status->id }}/destroy" class="btn btn-sm btn-danger my-2 my-sm-0">Eliminar</a>
                  </td>

                </tr>
        @endforeach
              </tbody>
            </table>
          </div>
  </div>
  </div>
  <script type="text/javascript">
  $('tbody').sortable();
</script> 
  
  
@endsection