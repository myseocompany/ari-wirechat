@extends('layout')

@section('content')
<h1>Nueva cuenta de WhatsApp</h1>

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('whatsapp-accounts.store') }}">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="name">Nombre interno</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Ej: Cuenta principal" value="{{ old('name') }}">
        </div>
        <div class="form-group col-md-6">
          <label for="phone_number">Teléfono de envío</label>
          <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="57XXXXXXXXXX" value="{{ old('phone_number') }}">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="phone_number_id">ID de teléfono (phone_number_id)</label>
          <input type="text" class="form-control" id="phone_number_id" name="phone_number_id" placeholder="Ej: 331909369994463" value="{{ old('phone_number_id') }}" required>
        </div>
        <div class="form-group col-md-6">
          <label for="api_token">Bearer token</label>
          <textarea class="form-control" id="api_token" name="api_token" rows="2" required>{{ old('api_token') }}</textarea>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="business_account_id">WABA ID (business_account_id)</label>
          <input type="text" class="form-control" id="business_account_id" name="business_account_id" placeholder="Ej: 123456789012345" value="{{ old('business_account_id') }}">
        </div>
      </div>
      <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
        <label class="form-check-label" for="is_default">Usar como predeterminada</label>
      </div>
      <div class="d-flex">
        <button type="submit" class="btn btn-primary mr-2">Guardar</button>
        <a href="{{ route('whatsapp-accounts.index') }}" class="btn btn-light">Cancelar</a>
      </div>
    </form>
  </div>
  <div class="card-footer text-muted">
    El endpoint se generará automáticamente con el ID de teléfono.
  </div>
</div>
@endsection
