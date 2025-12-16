@extends('layout')

@push('styles')
<style>
    .payload-card {
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
    .meta-label {
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
</style>
@endpush

@section('content')
<div class="mt-4">
    <a href="{{ route('request_logs.index') }}" class="btn btn-link mb-3">← Volver</a>

    <div class="payload-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="meta-label">ID</div>
                <h4 class="mb-0">#{{ $log->id }}</h4>
            </div>
            <form method="POST" action="{{ route('request_logs.resend', $log->id) }}">
                @csrf
                <button class="btn btn-primary btn-sm" type="submit">Reenviar</button>
            </form>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="meta-label">Email</div>
                <div>{{ $displayEmail ?? 'No informado' }}</div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Creado</div>
                <div>{{ $log->created_at }}</div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Actualizado</div>
                <div>{{ $log->updated_at }}</div>
            </div>
        </div>

        <div class="mb-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Payload (JSON)</h6>
            @if(!$payloadPretty)
                <span class="text-danger small">JSON inválido, mostrando texto crudo.</span>
            @endif
        </div>
        <pre class="json-view" aria-label="payload json">{{ $payloadPretty ?? $payloadRaw }}</pre>
    </div>
</div>
@endsection
