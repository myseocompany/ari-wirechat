<style>
    #actions_display {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 10px;
    }
    .action-card {
        margin-bottom: 10px;
        padding: 10px 15px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        background: #f8f9fa;
        border-left: 5px solid #0d6efd; /* default fallback color */
    }
    .action-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
    }
    .action-note {
        font-weight: bold;
        margin-bottom: 4px;
    }
    .action-meta {
        font-size: 0.8rem;
        color: #6c757d;
        text-align: right;
    }
    .delete-action {
        color: #dc3545;
        font-size: 0.9rem;
        border: none;
        background: transparent;
    }
</style>

<div id="actions_display">
    @foreach($actions as $action)
        @php
            $color = $action->type->color ?? '#0d6efd';
            $icon = $action->type->icon ?? null;
        @endphp
        <div class="action-card" style="border-left-color: {{ $color }};">
            <div class="action-header">
                <div>
                    <div class="action-note">
                        @if($icon)
                            <i class="fa {{ $icon }}"></i>
                        @endif
                        {!! nl2br(e($action->note)) !!}
                    </div>
                </div>
                <div class="action-meta">
                    {{ \Carbon\Carbon::parse($action->created_at)->format('d M Y H:i') }}<br>
                    {{ $action->creator->name ?? 'Automático' }}
                    @if(Auth::check() && (Auth::user()->role_id == 1 || Auth::user()->role_id == 14))
                        <br>
                        <a href="/actions/{{$action->id}}/destroy" class="delete-action" title="Eliminar">
                            <i class="fa fa-trash-o"></i>
                        </a>
                    @endif
                </div>
            </div>
            @if(in_array($action->type_id, [2, 4]) && method_exists($action, 'getEmailSubject'))
                <div class="small text-muted">
                    <strong>{{ $action->getEmailSubject() }}</strong>
                </div>
            @endif
        </div>
    @endforeach
</div>
