@csrf
@php $isEdit = isset($tag) && $tag->exists; @endphp

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="form-group">
  <label for="name">Nombre</label>
  <input
    type="text"
    name="name"
    id="name"
    class="form-control"
    value="{{ old('name', $tag->name ?? '') }}"
    required>
</div>

<div class="form-group">
  <label for="color">Color</label>
  <input
    type="text"
    name="color"
    id="color"
    class="form-control"
    placeholder="#FF0000"
    value="{{ old('color', $tag->color ?? '') }}">
</div>

<div class="form-group">
  <label for="description">Descripción</label>
  <input
    type="text"
    name="description"
    id="description"
    class="form-control"
    value="{{ old('description', $tag->description ?? '') }}">
</div>

<div class="d-flex align-items-center">
  <button type="submit" class="btn btn-primary mr-2">
    {{ $isEdit ? 'Guardar cambios' : 'Crear etiqueta' }}
  </button>
  <a href="{{ route('tags.index') }}" class="btn btn-secondary">Cancelar</a>
</div>
