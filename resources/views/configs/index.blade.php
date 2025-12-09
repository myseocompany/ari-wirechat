@extends('layout')

@section('title', 'Configs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="mb-1">Configuraciones</h1>
    <div class="text-muted small">Valores guardados en la tabla configs.</div>
  </div>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
  @if($configs->count())
    <div class="table-responsive">
      <table class="table mb-0">
        <thead class="thead-light">
          <tr>
            <th>Key</th>
            <th>Valor</th>
            <th>Tipo</th>
            <th>Actualizado</th>
          </tr>
        </thead>
        <tbody>
          @foreach($configs as $config)
            <tr>
              <td class="font-weight-semibold">{{ $config->key }}</td>
              <td>
                @php
                  $display = $config->type === 'json'
                    ? json_encode(json_decode($config->value, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    : $config->value;
                @endphp
                <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ $display }}</pre>
              </td>
              <td><span class="badge badge-secondary">{{ $config->type }}</span></td>
              <td class="text-muted small">{{ optional($config->updated_at)->format('Y-m-d H:i') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="p-3">
      {{ $configs->links() }}
    </div>
  @else
    <div class="p-4 text-muted">No hay configuraciones.</div>
  @endif
</div>
@endsection
