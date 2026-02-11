@csrf
@if (($method ?? 'POST') === 'PUT')
  @method('PUT')
@endif

<div class="form-group">
  <label for="serial">Serial físico</label>
  <input
    id="serial"
    type="text"
    class="form-control @error('serial') is-invalid @enderror"
    name="serial"
    maxlength="80"
    required
    value="{{ old('serial', $machine->serial) }}"
    placeholder="PLC-00001234"
  >
  @error('serial')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="form-group">
  <label for="current_customer_id">Customer actual</label>
  <select
    id="current_customer_id"
    name="current_customer_id"
    class="form-control @error('current_customer_id') is-invalid @enderror"
  >
    <option value="">Sin asignar</option>
    @foreach ($customers as $customerId => $customerName)
      <option
        value="{{ $customerId }}"
        @selected((string) old('current_customer_id', $machine->current_customer_id) === (string) $customerId)
      >
        {{ $customerName }} (#{{ $customerId }})
      </option>
    @endforeach
  </select>
  @error('current_customer_id')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
  <small class="form-text text-muted">
    Al cambiar este valor, se actualiza el historial en `machine_customer_histories`.
  </small>
</div>

<div class="d-flex justify-content-between align-items-center">
  <a href="{{ route('admin.machines.index') }}" class="btn btn-outline-secondary">Volver</a>
  <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Guardar' }}</button>
</div>
