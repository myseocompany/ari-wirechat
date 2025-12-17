@if ($errors->any())
    <div class="alert alert-danger">
        <p class="mb-1"><strong>Por favor corrige los siguientes campos:</strong></p>
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
        class="form-control @error('name') is-invalid @enderror"
        id="name"
        name="name"
        value="{{ old('name', $menu->name) }}"
        required
    >
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="url">URL</label>
    <input
        type="text"
        class="form-control @error('url') is-invalid @enderror"
        id="url"
        name="url"
        value="{{ old('url', $menu->url) }}"
        placeholder="/ruta"
    >
    @error('url')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="parent_id">Menú padre</label>
        <select
            class="form-control @error('parent_id') is-invalid @enderror"
            id="parent_id"
            name="parent_id"
        >
            <option value="">Sin padre</option>
            @foreach ($parentOptions as $parent)
                <option
                    value="{{ $parent->id }}"
                    {{ (string) old('parent_id', $menu->parent_id) === (string) $parent->id ? 'selected' : '' }}
                >
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-3">
        <label for="weight">Orden</label>
        <input
            type="number"
            class="form-control @error('weight') is-invalid @enderror"
            id="weight"
            name="weight"
            value="{{ old('weight', $menu->weight) }}"
            min="0"
            step="1"
        >
        @error('weight')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-3">
        <label for="inner_link">Tipo de enlace</label>
        <select
            class="form-control @error('inner_link') is-invalid @enderror"
            id="inner_link"
            name="inner_link"
            required
        >
            @php
                $innerValue = old('inner_link', $menu->inner_link ? '1' : '0');
            @endphp
            <option value="1" {{ $innerValue === '1' ? 'selected' : '' }}>Interno</option>
            <option value="0" {{ $innerValue === '0' ? 'selected' : '' }}>Externo</option>
        </select>
        @error('inner_link')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <a href="{{ route('menus.index') }}" class="btn btn-link">Cancelar</a>
    <button type="submit" class="btn btn-primary">
        {{ $submitLabel ?? 'Guardar' }}
    </button>
</div>
