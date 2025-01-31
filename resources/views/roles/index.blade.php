@extends('layout')

@section('content')
 @if (session('statustwo'))
          <div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
            {!! html_entity_decode(session('statustwo')) !!}
        </div>
  @endif
<br>
<h1 class="text-center">Roles</h1>
    <div><a href="/roles/create">+Create</a></div>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Role</th>
                  <th>Options</th>
                </tr>
              </thead>
              <tbody>
                @foreach($model as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td><a href="/roles/{{ $item->id }}/show">{{ $item->name }}</a></td>
                  <td>
                    {{-- editar --}}
                    <a href="/roles/{{$item->id }}/edit"><span class="btn btn-sm btn-warning" aria-hidden="true" title="Editar">Editar</span></a>
                    {{-- eliminar --}}
                    <a href="/roles/{{$item->id }}/destroy"><span class="btn btn-sm btn-danger" aria-hidden="false" title="Eliminar">Eliminar</span></a>
                  </td>
                </tr>
        @endforeach
              </tbody>
            </table>
  
@endsection