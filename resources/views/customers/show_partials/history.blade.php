<h2 class="mt-6 text-lg font-semibold text-slate-900">Historial</h2>

<div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    @for($i = 0; $i < count($histories); $i++)
        @php
            $history = $histories[$i];
            $nextOwner = isset($histories[$i+1]) ? $histories[$i+1]->user_id : null;
            $nextOwnerName = isset($histories[$i+1]) && isset($histories[$i+1]->user) ? $histories[$i+1]->user->name : 'Sin asignar';
            $isReassigned = $nextOwner !== null && $nextOwner != $history->user_id;
        @endphp

        <div class="border-b border-slate-200 py-3">
            {{-- Fecha y hace cuánto --}}
            <div class="mb-1 text-xs text-slate-500">
                <i class="fa fa-clock-o"></i>
                {{ \Carbon\Carbon::parse($history->updated_at)->format('d/m/Y H:i') }}
                <span class="text-slate-400">
                    ({{ \Carbon\Carbon::parse($history->updated_at)->diffForHumans() }})
                </span>
            </div>

            {{-- Propietario actual --}}
            @if(isset($history->user) && !empty($history->user_id))
                <span>
                    <i class="fa fa-user"></i> Propietario:
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">{{$history->user->name}}</span>
                </span>

                {{-- Detectar cambio de propietario en el siguiente registro --}}
                @if($isReassigned)
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
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
                <span class="text-slate-700">
                    <i class="fa fa-pencil"></i> Cambiado por: {{$history->updated_user->name}}
                </span>
            @endif
            
            {{-- Estado --}}
            <span>
                <i class="fa fa-flag"></i> Estado:
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background-color: {{ isset($history->status) ? $history->status->color : 'gray' }}">
                    {{ isset($history->status) ? $history->status->name : $history->status_id }}
                </span> 
            </span>
        </div>
    @endfor
</div>
