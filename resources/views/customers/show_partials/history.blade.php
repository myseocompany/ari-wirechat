<h2 class="mt-4">Historial</h2>

<div class="card shadow-sm p-3 mb-4">
    @foreach($histories as $history)
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
                <span class="">
                    <i class="fa fa-user"></i> Propietario: <span class="badge badge-primary">{{$history->user->name}}</span>
                </span>
            @else
                <span class="">
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
            <span class="" 
                  >
                <i class="fa fa-flag"></i> Estado: <span class="badge" style="background-color: {{ isset($history->status) ? $history->status->color : 'gray' }}">
{{ isset($history->status) ? $history->status->name : $history->status_id }}
                </span> 
            </span>
        </div>
    @endforeach
</div>
