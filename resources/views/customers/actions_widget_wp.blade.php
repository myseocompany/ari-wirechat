<style>
    #actions_display {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 10px;
    }
    .action-card {
        border-left: 5px solid #0d6efd;
        background: #f8f9fa;
        margin-bottom: 10px;
        padding: 10px 15px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .action-card .badge {
        font-size: 0.75rem;
    }
    .action-card .meta {
        font-size: 0.8rem;
        color: #6c757d;
    }
</style>

<div id="actions_display">
    @foreach($actions as $action)
        <div class="action-card">
            <div class="d-flex justify-content-between">
                <div>
                    <strong>
                        <a href="/actions/{{$action->id}}/show" class="text-decoration-none text-dark">
                            {{ \Carbon\Carbon::parse($action->created_at)->format('Y-m-d H:i') }}
                        </a>
                    </strong>
                    <br>
                    <span class="badge bg-secondary">
                        {{ $action->type->name ?? 'Sin tipo' }}
                    </span>
                    @if($action->type_id == 29)
                        <span class="badge bg-danger">Venta perdida</span>
                    @endif
                </div>
                <div class="text-end">
                    <span class="meta">
                        {{ $action->creator->name ?? 'Automático' }}
                    </span><br>
                    @if(Auth::check() && (Auth::user()->role_id == 1 || Auth::user()->role_id == 14 ) )
                        <a href="/actions/{{$action->id}}/destroy" class="text-danger small">
                            <i class="fa fa-trash-o"></i> Eliminar
                        </a>
                    @endif
                </div>
            </div>
            <div class="mt-2 small">
                @if(in_array($action->type_id, [2, 4]))
                    <strong>{{ $action->getEmailSubject() }}</strong><br>
                @endif
                {!! nl2br(e($action->note)) !!}
            </div>
        </div>
    @endforeach
</div>
