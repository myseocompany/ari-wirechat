@extends('layout')

@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Etiquetas</h1>
    <a href="{{ route('tags.create') }}" class="btn btn-primary btn-sm">Nueva etiqueta</a>
  </div>

  @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('status') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Color</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($tags as $tag)
          <tr>
            <td>{{ $tag->name }}</td>
            <td>
              @if($tag->color)
                <span class="d-inline-flex align-items-center">
                  <span class="rounded-circle mr-2" style="display:inline-block;width:16px;height:16px;background-color: {{ $tag->color }};"></span>
                  <span>{{ $tag->color }}</span>
                </span>
              @else
                -
              @endif
            </td>
            <td>{{ $tag->description ?? '-' }}</td>
            <td>
              <a href="{{ route('tags.edit', $tag) }}" class="btn btn-sm btn-outline-primary">Editar</a>
              <form action="{{ route('tags.destroy', $tag) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar etiqueta?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-muted">No hay etiquetas registradas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $tags->links() }}
  </div>
</div>
@endsection
