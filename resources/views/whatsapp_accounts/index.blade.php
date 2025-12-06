@extends('layout')

@section('content')
<h1>Cuentas de WhatsApp</h1>

@if (session('status'))
  <div class="alert alert-success">{{ session('status') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
  <div class="card mb-2" style="min-width: 320px;">
    <div class="card-body">
      <h6 class="card-title">Enviar plantilla de prueba</h6>
      <form method="POST" action="{{ route('whatsapp-accounts.send-test') }}" class="form-inline">
        @csrf
        <div class="form-group mr-2 mb-2">
          <label class="sr-only" for="test_phone">Teléfono</label>
          <input type="text" class="form-control" id="test_phone" name="test_phone" placeholder="57XXXXXXXXXX" required>
        </div>
        <button type="submit" class="btn btn-outline-primary mb-2">Enviar hello_world</button>
      </form>
      <small class="text-muted">Usa la cuenta predeterminada, plantilla hello_world (en_US).</small>
    </div>
  </div>
  <a href="{{ route('whatsapp-accounts.create') }}" class="btn btn-primary mb-2">Agregar cuenta</a>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="card-title">Cuentas configuradas</h5>
    @forelse($accounts as $account)
      <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
              <div>
                <h6 class="mb-1">{{ $account->name ?? 'Sin nombre' }}</h6>
                <div class="text-muted small">Teléfono: {{ $account->phone_number ?? 'No asignado' }}</div>
              <div class="text-muted small">ID Teléfono: {{ $account->phone_number_id }}</div>
              <div class="text-muted small">WABA ID: {{ $account->business_account_id ?? 'No asignado' }}</div>
              <div class="text-muted small" style="word-break: break-all;">Endpoint: {{ $account->api_url }}</div>
              </div>
              <div class="text-right">
                @if($account->is_default)
                  <span class="badge badge-success">Predeterminada</span>
                @else
                <form method="POST" action="{{ route('whatsapp-accounts.default', $account) }}">
                  @csrf
                  <button type="submit" class="btn btn-outline-secondary btn-sm">Marcar predeterminada</button>
                </form>
              @endif
            </div>
          </div>
          <div class="mt-3 d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" type="button" data-toggle="collapse" data-target="#edit-{{ $account->id }}" aria-expanded="false" aria-controls="edit-{{ $account->id }}">
              Editar
            </button>
            <form method="POST" action="{{ route('whatsapp-accounts.sync-templates', $account) }}" class="mr-2">
              @csrf
              <button type="submit" class="btn btn-outline-success btn-sm">Sincronizar plantillas</button>
            </form>
            <form method="POST" action="{{ route('whatsapp-accounts.destroy', $account) }}" onsubmit="return confirm('¿Eliminar esta cuenta?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
            </form>
          </div>
          <div class="collapse mt-3" id="edit-{{ $account->id }}">
            <div class="card card-body">
              <form method="POST" action="{{ route('whatsapp-accounts.update', $account) }}">
                @csrf
                @method('PUT')
                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label>Nombre interno</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $account->name) }}">
                  </div>
                  <div class="form-group col-md-4">
                    <label>Teléfono de envío</label>
                    <input type="text" class="form-control" name="phone_number" value="{{ old('phone_number', $account->phone_number) }}">
                  </div>
                  <div class="form-group col-md-4">
                    <label>ID de teléfono (phone_number_id)</label>
                    <input type="text" class="form-control" name="phone_number_id" value="{{ old('phone_number_id', $account->phone_number_id) }}" required>
                  </div>
                  <div class="form-group col-md-4">
                    <label>WABA ID (business_account_id)</label>
                    <input type="text" class="form-control" name="business_account_id" value="{{ old('business_account_id', $account->business_account_id) }}">
                  </div>
                </div>
                <div class="form-group">
                  <label>Bearer token (dejar en blanco para no cambiar)</label>
                  <textarea class="form-control" name="api_token" rows="2" placeholder="Solo escribe si quieres actualizar">{{ old('api_token') }}</textarea>
                </div>
                <div class="form-check mb-3">
                  <input type="checkbox" class="form-check-input" id="is_default_{{ $account->id }}" name="is_default" value="1" @checked(old('is_default', $account->is_default))>
                  <label class="form-check-label" for="is_default_{{ $account->id }}">Usar como predeterminada</label>
                </div>
                <div class="d-flex">
                  <button type="submit" class="btn btn-primary mr-2">Guardar cambios</button>
                  <button type="button" class="btn btn-light" data-toggle="collapse" data-target="#edit-{{ $account->id }}">Cancelar</button>
                </div>
              </form>
            </div>
            </div>
          <div class="mt-3">
            <strong>Plantillas sincronizadas:</strong>
            @if($account->templates->isEmpty())
              <div class="text-muted small">Sin plantillas. Usa “Sincronizar plantillas”.</div>
            @else
              <ul class="mb-0 small">
                @foreach($account->templates as $tpl)
                  <li>{{ $tpl->name }} ({{ $tpl->language ?? 'sin idioma' }}) - {{ $tpl->status ?? 'sin estado' }} {{ $tpl->category ? ' / '.$tpl->category : '' }}</li>
                @endforeach
              </ul>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="text-muted">No hay cuentas configuradas.</div>
    @endforelse
  </div>
</div>
@endsection
