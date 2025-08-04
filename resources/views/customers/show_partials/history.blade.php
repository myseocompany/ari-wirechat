<h2 class="mt-4">Historial</h2>

<div class="card shadow-sm p-3 mb-4">
    @for($i = 0; $i < count($histories); $i++)
        @php
            $history = $histories[$i];
            $nextOwner = isset($histories[$i+1]) ? $histories[$i+1]->user_id : null;
            $nextOwnerName = isset($histories[$i+1]) && isset($histories[$i+1]->user) ? $histories[$i+1]->user->name : 'Sin asignar';
            $isReassigned = $nextOwner !== null && $nextOwner != $history->user_id;
        @endphp

        <div class="border-bottom py-2">
            {{-- Fecha y hace cuánto --}}
            <div class="mb-1 text-muted small">
                <i class="fa fa-clock-o"></i> 
                {{ \Carbon\Carbon::parse($history->updated_at)->format('d/m/Y H:i') }}
                <span class="text-secondary">
                    ({{ \Carbon\Carbon::parse($history->updated_at)->diffForHumans() }})
                </span>
            </div>

            {{-- Propietario actual --}}
            @if(isset($history->user) && !empty($history->user_id))
                <span>
                    <i class="fa fa-user"></i> Propietario:
                    <span class="badge badge-primary">{{$history->user->name}}</span>
                </span>

                {{-- Detectar cambio de propietario en el siguiente registro --}}
                @if($isReassigned)
                    <span class="badge badge-warning">
                        <i class="fa fa-exchange"></i> Reasignado (antes: {{$nextOwnerName}})
                    </span>
                @endif
            @else
                <span>
                    <i class="fa fa-user-slash"></i> Sin asignar
                </span>
            @endif
            
            {{-- Usuario que hizo el cambio --}}
            @if(isset($history->updated_user))
                <br>
                <span class="text-dark">
                    <i class="fa fa-pencil"></i> Cambiado por: {{$history->updated_user->name}}
                </span>
            @endif
            
            {{-- Estado --}}
            <span>
                <i class="fa fa-flag"></i> Estado:
                <span class="badge" style="background-color: {{ isset($history->status) ? $history->status->color : 'gray' }}">
                    {{ isset($history->status) ? $history->status->name : $history->status_id }}
                </span> 
            </span>
        </div>
    @endfor
</div>
