@extends('layout')

@section('content')
<br>
<h1 class="text-center">Users</h1>
  <div><a href="/users/create">Create + <i class="fa fa-plus-square"></i></a></div>
  <div>
      <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>User</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th>Options</th>
                </tr>
              </thead>
              <tbody>
                @foreach($users as $user)
                <tr>
                  <td>{{ $user->id }}</td>
                  <td><a href="/users/{{ $user->id }}">{{ $user->name }}</a></td>
                  <td>{{ $user->email }}</td>
                   <td>
                     <?php 
                         if(isset($user->status_id)&&($user->status_id!="")&&(!is_null($user->status))){
                               echo $user->status->name;
                            }
                        ?> 
                  </td>
                  <td><a href="/users/{{$user->id }}/edit" class="btn btn-sm btn-warning my-2 my-sm-0">Editar</a></td>
                </tr>
        @endforeach
              </tbody>
            </table>
          </div>
@endsection