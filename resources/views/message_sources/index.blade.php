@extends('layout')

@section('content')
<h1 class="mb-3">Líneas de WhatsApp (WAToolBox)</h1>

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

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="text-muted small">Gestiona APIKEY, estado, source_id y webhook por cada línea.</div>
  <a href="{{ route('message-sources.create') }}" class="btn btn-primary">Nueva línea</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead class="thead-light">
        <tr>
          <th>ID</th>
          <th>Tipo</th>
          @if($hasPhoneNumberColumn)
            <th>Teléfono</th>
          @endif
          <th>API KEY</th>
          <th>Source ID</th>
          <th>Webhook</th>
          <th>Estado</th>
          <th>Default</th>
          <th class="text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($messageSources as $messageSource)
          @php
            $settings = is_array($messageSource->settings) ? $messageSource->settings : [];
            $apiKeyPreview = strlen((string) $messageSource->APIKEY) > 12
              ? substr((string) $messageSource->APIKEY, 0, 8) . '...'
              : $messageSource->APIKEY;
            $isActive = (bool) data_get($settings, 'active', true);
          @endphp
          <tr>
            <td>{{ $messageSource->id }}</td>
            <td>{{ $messageSource->type }}</td>
            @if($hasPhoneNumberColumn)
              <td>{{ $messageSource->phone_number ?: '—' }}</td>
            @endif
            <td><code>{{ $apiKeyPreview }}</code></td>
            <td>{{ data_get($settings, 'source_id', '—') }}</td>
            <td style="max-width: 260px; word-break: break-word;">{{ data_get($settings, 'webhook_url', '—') }}</td>
            <td>
              <span class="badge {{ $isActive ? 'badge-success' : 'badge-secondary' }}">
                {{ $isActive ? 'Activa' : 'Inactiva' }}
              </span>
            </td>
            <td>
              @if($messageSource->is_default)
                <span class="badge badge-primary">Sí</span>
              @else
                <span class="text-muted">No</span>
              @endif
            </td>
            <td class="text-right">
              <a href="{{ route('message-sources.edit', $messageSource) }}" class="btn btn-outline-primary btn-sm">Editar</a>
              <form method="POST" action="{{ route('message-sources.destroy', $messageSource) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta línea?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ $hasPhoneNumberColumn ? 9 : 8 }}" class="text-center text-muted py-4">
              No hay líneas configuradas.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($messageSources->hasPages())
    <div class="p-3">
      {{ $messageSources->links() }}
    </div>
  @endif
</div>
@endsection
