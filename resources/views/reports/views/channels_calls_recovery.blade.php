@extends('layout')

@section('content')
<h1 class="mb-3">Recuperación de llamadas Channels</h1>

@if (session('status'))
  <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if ($searchError)
  <div class="alert alert-danger">
    Error consultando Channels: {{ $searchError }}
  </div>
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

@if (! $channelsConfigured)
  <div class="alert alert-warning">
    Configura <code>CHANNELS_API_TOKEN</code> y <code>CHANNELS_ACCOUNT</code> para habilitar la búsqueda y recuperación.
  </div>
@endif

<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('reports.channels_calls_recovery') }}">
      <div class="form-row">
        <div class="form-group col-md-2">
          <label for="from_date">Desde</label>
          <input id="from_date" name="from_date" type="date" class="form-control" value="{{ $filters['from_date'] }}">
        </div>
        <div class="form-group col-md-2">
          <label for="to_date">Hasta</label>
          <input id="to_date" name="to_date" type="date" class="form-control" value="{{ $filters['to_date'] }}">
        </div>
        <div class="form-group col-md-3">
          <label for="call_id">Call ID</label>
          <input id="call_id" name="call_id" type="text" class="form-control" value="{{ $filters['call_id'] }}" placeholder="Opcional">
        </div>
        <div class="form-group col-md-2">
          <label for="agent_id">Agent ID / Username</label>
          <input id="agent_id" name="agent_id" type="text" class="form-control" value="{{ $filters['agent_id'] }}" placeholder="ID o correo/username">
        </div>
        <div class="form-group col-md-3">
          <label for="msisdn">MSISDN / Teléfono</label>
          <input id="msisdn" name="msisdn" type="text" class="form-control" value="{{ $filters['msisdn'] }}" placeholder="Opcional">
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="form-check mb-2">
          <input id="only_missing" name="only_missing" type="checkbox" value="1" class="form-check-input" @checked($filters['only_missing'])>
          <label class="form-check-label" for="only_missing">Mostrar solo faltantes</label>
        </div>
        <button type="submit" class="btn btn-primary mb-2">Buscar (últimos 30 días por defecto)</button>
      </div>
    </form>
  </div>
</div>

@if ($searched)
  <div class="row mb-3">
    <div class="col-md-3 mb-2">
      <div class="card">
        <div class="card-body py-3">
          <div class="text-muted small">Llamadas remotas</div>
          <div class="h4 mb-0">{{ $summary['remote_total'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="card">
        <div class="card-body py-3">
          <div class="text-muted small">Ya existen localmente</div>
          <div class="h4 mb-0 text-success">{{ $summary['existing_local'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="card">
        <div class="card-body py-3">
          <div class="text-muted small">Faltantes</div>
          <div class="h4 mb-0 text-warning">{{ $summary['missing_local'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="card">
        <div class="card-body py-3">
          <div class="text-muted small">Mostradas</div>
          <div class="h4 mb-0">{{ $summary['displayed'] }}</div>
        </div>
      </div>
    </div>
  </div>

  @if (count($calls) > 0)
    <form method="POST" action="{{ route('reports.channels_calls_recovery.queue') }}">
      @csrf

      <input type="hidden" name="from_date" value="{{ $filters['from_date'] }}">
      <input type="hidden" name="to_date" value="{{ $filters['to_date'] }}">
      <input type="hidden" name="call_id" value="{{ $filters['call_id'] }}">
      <input type="hidden" name="agent_id" value="{{ $filters['agent_id'] }}">
      <input type="hidden" name="msisdn" value="{{ $filters['msisdn'] }}">
      <input type="hidden" name="only_missing" value="{{ $filters['only_missing'] ? '1' : '0' }}">

      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div class="small text-muted">Selecciona llamadas faltantes para encolarlas.</div>
        <div>
          <button type="button" class="btn btn-outline-secondary btn-sm mr-1" onclick="toggleChannelsMissing(true)">Seleccionar faltantes</button>
          <button type="button" class="btn btn-outline-secondary btn-sm mr-2" onclick="toggleChannelsMissing(false)">Limpiar</button>
          <button type="submit" class="btn btn-success btn-sm">Encolar seleccionadas</button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-striped table-hover">
          <thead class="thead-light">
            <tr>
              <th style="width: 70px;">Sel</th>
              <th>Fecha</th>
              <th>Call ID</th>
              <th>MSISDN</th>
              <th>Agent</th>
              <th>Grabación</th>
              <th>Local</th>
              <th>URL grabación</th>
              <th>Debug llamada</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($calls as $index => $call)
              @php
                $isMissing = (bool) ($call['is_missing'] ?? false);
              @endphp
              <tr class="{{ $isMissing ? 'table-warning' : '' }}">
                <td>
                  @if ($isMissing)
                    <input type="checkbox" name="selected_indexes[]" value="{{ $index }}" class="js-channels-selector">
                  @endif

                  <input type="hidden" name="calls[{{ $index }}][call_id]" value="{{ $call['call_id'] }}">
                  <input type="hidden" name="calls[{{ $index }}][call_created_at]" value="{{ $call['call_created_at'] }}">
                  <input type="hidden" name="calls[{{ $index }}][msisdn]" value="{{ $call['msisdn'] }}">
                  <input type="hidden" name="calls[{{ $index }}][agent_id]" value="{{ $call['agent_id'] }}">
                  <input type="hidden" name="calls[{{ $index }}][agent_username]" value="{{ $call['agent_username'] }}">
                  <input type="hidden" name="calls[{{ $index }}][agent_name]" value="{{ $call['agent_name'] }}">
                  <input type="hidden" name="calls[{{ $index }}][agent_surname]" value="{{ $call['agent_surname'] }}">
                  <input type="hidden" name="calls[{{ $index }}][agent_msisdn]" value="{{ $call['agent_msisdn'] }}">
                  <input type="hidden" name="calls[{{ $index }}][recording_exists]" value="{{ $call['recording_exists'] ? '1' : '0' }}">
                  <input type="hidden" name="calls[{{ $index }}][recording_url]" value="{{ $call['recording_url'] }}">
                </td>
                <td>{{ $call['call_created_at'] ?: '—' }}</td>
                <td><code>{{ $call['call_id'] }}</code></td>
                <td>{{ $call['msisdn'] ?: '—' }}</td>
                <td>
                  <div>{{ $call['agent_id'] ?: '—' }}</div>
                  @if (!empty($call['agent_username']))
                    <div class="small text-muted">{{ $call['agent_username'] }}</div>
                  @endif
                </td>
                <td>
                  @if ($call['recording_exists'])
                    <span class="badge badge-success">Sí</span>
                  @else
                    <span class="badge badge-secondary">No</span>
                  @endif
                </td>
                <td>
                  @if ($call['local_exists'])
                    <span class="badge badge-success">Existe</span>
                    <div class="small text-muted">{{ $call['local_sources'] ?: 'detectado' }}</div>
                  @elseif ($isMissing)
                    <span class="badge badge-warning">Faltante</span>
                  @else
                    <span class="badge badge-secondary">Sin audio</span>
                  @endif
                </td>
                <td style="max-width: 360px; word-break: break-word;">
                  @if (!empty($call['recording_url']))
                    <a href="{{ $call['recording_url'] }}" target="_blank" rel="noopener noreferrer">
                      {{ \Illuminate\Support\Str::limit($call['recording_url'], 90) }}
                    </a>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td style="min-width: 280px;">
                  <details>
                    <summary class="small" style="cursor: pointer;">Ver payload</summary>
                    <div class="small mt-2">
                      @if (!empty($call['agent_debug_fields']))
                        <div class="mb-2"><strong>Campos posibles de agente/usuario:</strong></div>
                        @foreach ($call['agent_debug_fields'] as $fieldPath => $fieldValue)
                          <div><code>{{ $fieldPath }}</code>: <strong>{{ $fieldValue }}</strong></div>
                        @endforeach
                      @else
                        <div class="text-muted mb-2">No se detectaron claves tipo agent/user/owner/seller.</div>
                      @endif
                      <pre class="mb-0 p-2 bg-light border rounded" style="max-height: 180px; overflow: auto; white-space: pre-wrap; word-break: break-word;">{{ $call['raw_json'] }}</pre>
                    </div>
                  </details>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </form>
  @else
    <div class="alert alert-info">No se encontraron llamadas con esos filtros.</div>
  @endif
@endif

<h2 class="mt-4 mb-3">Recuperaciones recientes</h2>
<div class="table-responsive">
  <table class="table table-sm table-striped table-hover">
    <thead class="thead-light">
      <tr>
        <th>Fecha</th>
        <th>Call ID</th>
        <th>Estado</th>
        <th>Archivo local</th>
        <th>Tamaño</th>
        <th>Error</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($recoveries as $recovery)
        @php
          $statusClass = match ($recovery->status) {
            \App\Models\ChannelsCallRecovery::STATUS_RECOVERED => 'badge-success',
            \App\Models\ChannelsCallRecovery::STATUS_PROCESSING => 'badge-primary',
            \App\Models\ChannelsCallRecovery::STATUS_QUEUED => 'badge-info',
            \App\Models\ChannelsCallRecovery::STATUS_NO_RECORDING => 'badge-secondary',
            default => 'badge-danger',
          };
        @endphp
        <tr>
          <td>{{ optional($recovery->updated_at)->format('Y-m-d H:i:s') }}</td>
          <td><code>{{ $recovery->call_id }}</code></td>
          <td><span class="badge {{ $statusClass }}">{{ $recovery->status }}</span></td>
          <td style="max-width: 280px; word-break: break-word;">{{ $recovery->local_file_path ?: '—' }}</td>
          <td>{{ $recovery->local_file_size ? number_format((int) $recovery->local_file_size) : '—' }}</td>
          <td style="max-width: 320px; word-break: break-word;">{{ $recovery->error ?: '—' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center text-muted">Aún no hay recuperaciones en cola.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<script>
  function toggleChannelsMissing(state) {
    const checkboxes = document.querySelectorAll('.js-channels-selector');
    checkboxes.forEach((checkbox) => {
      checkbox.checked = state;
    });
  }
</script>
@endsection
