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
    }
    .logs-search {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
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
        <div id="selected-counter" class="text-muted small mb-2 mb-md-0">0 seleccionados</div>
        <form method="get" class="logs-search">
            <div style="flex:1;">
                <input type="text" name="q" value="{{ $search }}" placeholder="Buscar en payload, email o teléfono...">
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
                        <th style="width:40px;">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th style="width:70px;">ID</th>
                        <th>Primeros datos del request</th>
                        <th>Email</th>
                        <th style="width:170px;">Creado</th>
                        <th style="width:120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <input type="checkbox" class="log-checkbox" value="{{ $log->id }}">
                            </td>
                            <td class="text-muted">
                                <a href="{{ route('request_logs.show', $log->id) }}">#{{ $log->id }}</a>
                            </td>
                            <td>
                                @if(!empty($log->payload_preview))
                                    <ul class="list-unstyled mb-0 small">
                                        @foreach($log->payload_preview as $key => $value)
                                            <li><strong>{{ $key }}:</strong> {{ \Illuminate\Support\Str::limit($value, 80) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="muted small">Sin datos</span>
                                @endif
                            </td>
                            <td>
                                @if($log->display_email)
                                    <span class="pill">{{ $log->display_email }}</span>
                                @else
                                    <span class="muted small">No informado</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $log->created_at }}</td>
                            <td>
                                <form method="POST" action="{{ route('request_logs.resend', $log->id) }}">
                                    @csrf
                                    <button class="btn btn-outline-primary btn-sm" type="submit">Reenviar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Sin registros</td>
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

@push('scripts')
<script>
    const counterEl = document.getElementById('selected-counter');
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.log-checkbox');

    function updateCounter() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked).length;
        counterEl.textContent = `${selected} seleccionado${selected === 1 ? '' : 's'}`;
    }

    selectAll?.addEventListener('change', (e) => {
        checkboxes.forEach(cb => cb.checked = e.target.checked);
        updateCounter();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCounter);
    });
</script>
@endpush
