@extends('layout')

@push('styles')
<style>
    .card-box {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 12px 28px -18px rgba(17, 24, 39, 0.35);
    }
    pre.json-view {
        background: #0b1221;
        color: #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        overflow: auto;
        font-size: 13px;
        line-height: 1.5;
    }
</style>
@endpush

@section('content')
<div class="mt-4">
    <a href="{{ route('request_logs.index') }}" class="btn btn-link mb-3">← Volver</a>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Reenvío de log #{{ $result['log']->id }}</h4>
        <span class="badge {{ $result['status'] >= 200 && $result['status'] < 300 ? 'badge-success' : 'badge-danger' }}">
            Status: {{ $result['status'] }}
        </span>
    </div>

    <div class="card-box p-3 mb-3">
        <div class="row">
            <div class="col-md-4">
                <div class="text-muted small">Destino</div>
                <div>{{ $result['forwarded_to'] }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Creado</div>
                <div>{{ $result['log']->created_at }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Actualizado</div>
                <div>{{ $result['log']->updated_at }}</div>
            </div>
        </div>
    </div>

    <div class="card-box p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Respuesta del servidor</h6>
        </div>
        <pre class="json-view">{{ $serverPretty }}</pre>
    </div>

    <div class="card-box p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Payload reenviado</h6>
        </div>
        <pre class="json-view">{{ $payloadPretty }}</pre>
    </div>
</div>
@endsection
