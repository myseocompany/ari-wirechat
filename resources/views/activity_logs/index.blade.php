@extends('layout')

@push('styles')
<style>
    .logs-card {
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 25px -15px rgba(17, 24, 39, 0.3);
        border-radius: 16px;
    }
    .logs-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px 0 18px;
        flex-wrap: wrap;
    }
    .logs-search {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        flex-wrap: wrap;
    }
    .logs-search input {
        border-radius: 12px;
        border: 1px solid #d1d5db;
        padding: 10px 14px;
        width: 100%;
    }
    .logs-table thead th {
        font-size: 13px;
        text-transform: none;
        color: #4b5563;
        border-top: none;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }
    .logs-table tbody td {
        vertical-align: middle;
        border-color: #f3f4f6;
    }
    .logs-table tbody tr:hover {
        background: #f9fafb;
    }
    .pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 12px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 12px;
        font-weight: 600;
    }
    .muted {
        color: #9ca3af;
    }
</style>
@endpush

@section('content')
<div class="mt-4">
    <div class="logs-header">
        <div class="text-muted small mb-2 mb-md-0">Actividad de usuarios</div>
        <form method="get" class="logs-search">
            <div style="flex:2; min-width: 220px;">
                <input type="text" name="q" value="{{ $search }}" placeholder="Buscar en acción, sujeto o meta...">
            </div>
            <div style="flex:1; min-width: 160px;">
                <input type="text" name="action" value="{{ $action }}" placeholder="Acción exacta">
            </div>
            <div style="flex:1; min-width: 140px;">
                <input type="number" name="user_id" value="{{ $userId }}" placeholder="User ID">
            </div>
            <select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()" style="max-width: 110px;">
                @foreach([10,25,50,100] as $size)
                    <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }} / pág.</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
        </form>
    </div>

    <div class="logs-card mt-2">
        <div class="table-responsive">
            <table class="table logs-table mb-0">
                <thead>
                    <tr>
                        <th style="width:80px;">ID</th>
                        <th>Acción</th>
                        <th>Usuario</th>
                        <th>Sujeto</th>
                        <th>IP</th>
                        <th style="width:170px;">Creado</th>
                        <th style="width:120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-muted">
                                <a href="{{ route('activity_logs.show', $log->id) }}">#{{ $log->id }}</a>
                            </td>
                            <td>
                                <span class="pill">{{ $log->action }}</span>
                            </td>
                            <td>
                                @if($log->user)
                                    {{ $log->user->name }} <span class="muted">(#{{ $log->user->id }})</span>
                                @else
                                    <span class="muted">Sistema</span>
                                @endif
                            </td>
                            <td>
                                @if($log->subject_type && $log->subject_id)
                                    <span class="muted">{{ \Illuminate\Support\Str::afterLast($log->subject_type, '\\') }}</span>
                                    #{{ $log->subject_id }}
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="muted">{{ $log->ip ?? '-' }}</span>
                            </td>
                            <td class="text-muted">{{ $log->created_at }}</td>
                            <td>
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('activity_logs.show', $log->id) }}">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex align-items-center justify-content-between px-3 py-2">
            <div class="small text-muted">
                @if($logs->total())
                    Mostrando {{ $logs->firstItem() }} - {{ $logs->lastItem() }} de {{ $logs->total() }}
                @else
                    Sin resultados
                @endif
            </div>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
