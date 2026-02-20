@php
  $settings = is_array($messageSource->settings ?? null) ? $messageSource->settings : [];
  $active = (int) old('active', data_get($settings, 'active', true));
@endphp

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $action }}">
      @csrf
      @isset($method)
        @method($method)
      @endisset

      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="type">Tipo</label>
          <input type="text" class="form-control" id="type" name="type" value="{{ old('type', $messageSource->type ?? 'watoolbox') }}" required>
        </div>
        @if($hasPhoneNumberColumn)
          <div class="form-group col-md-6">
            <label for="phone_number">Número de teléfono</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $messageSource->phone_number) }}" placeholder="57XXXXXXXXXX">
          </div>
        @endif
      </div>

      <div class="form-group">
        <label for="APIKEY">API KEY</label>
        <input type="text" class="form-control" id="APIKEY" name="APIKEY" value="{{ old('APIKEY', $messageSource->APIKEY) }}" required>
      </div>

      <div class="form-row">
        <div class="form-group col-md-8">
          <label for="webhook_url">Webhook URL (settings.webhook_url)</label>
          <input type="text" class="form-control" id="webhook_url" name="webhook_url" value="{{ old('webhook_url', data_get($settings, 'webhook_url')) }}" placeholder="https://...">
        </div>
        <div class="form-group col-md-4">
          <label for="source_id">Source ID (settings.source_id)</label>
          <input type="number" min="1" class="form-control" id="source_id" name="source_id" value="{{ old('source_id', data_get($settings, 'source_id')) }}">
        </div>
      </div>

      <div class="form-check mb-2">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" class="form-check-input" id="active" name="active" value="1" @checked($active === 1)>
        <label class="form-check-label" for="active">Línea activa</label>
      </div>
      <div class="form-check mb-3">
        <input type="hidden" name="is_default" value="0">
        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" @checked((int) old('is_default', $messageSource->is_default) === 1)>
        <label class="form-check-label" for="is_default">Usar como línea predeterminada</label>
      </div>

      <div class="d-flex">
        <button type="submit" class="btn btn-primary mr-2">{{ $submitLabel }}</button>
        <a href="{{ route('message-sources.index') }}" class="btn btn-light">Cancelar</a>
      </div>
    </form>
  </div>
</div>
