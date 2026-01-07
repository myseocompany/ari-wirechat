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
    <a href="{{ route('activity_logs.index') }}" class="btn btn-link mb-3">← Volver</a>

    <div class="payload-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="meta-label">ID</div>
                <h4 class="mb-0">#{{ $log->id }}</h4>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="meta-label">Acción</div>
                <div>{{ $log->action }}</div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Usuario</div>
                <div>
                    @if($log->user)
                        {{ $log->user->name }} (#{{ $log->user->id }})
                    @else
                        Sistema
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Sujeto</div>
                <div>
                    @if($log->subject_type && $log->subject_id)
                        {{ \Illuminate\Support\Str::afterLast($log->subject_type, '\\') }} #{{ $log->subject_id }}
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="meta-label">IP</div>
                <div>{{ $log->ip ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">User Agent</div>
                <div class="small text-muted">{{ $log->user_agent ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="meta-label">Creado</div>
                <div>{{ $log->created_at }}</div>
            </div>
        </div>

        <div class="mb-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Meta (JSON)</h6>
        </div>
        <pre class="json-view" aria-label="payload json">{{ $payloadPretty }}</pre>
    </div>
</div>
@endsection
