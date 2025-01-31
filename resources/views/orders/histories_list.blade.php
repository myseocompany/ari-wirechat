<h2>Historial</h2>
<div class="table-responsive">

  <ul class="list-group">

   <?php $now = \Carbon\Carbon::now();?>                 
   @foreach($model->histories as $history)
   <!-- Asumiendo que $orderHistories es la variable que contiene el historial de la orden -->
   <li class="list-group-item">
	Cambio de estado a 
	@if (isset($history->status) && ($history->status != ''))
    	
		<span class="badge" style="background-color: @if(isset($history->status) && ($history->status_id != '')) {{$history->status->color}};@else gray @endif">
		{{$history->status->name}},
      </span>	
	@endif 
	el: {{$history->updated_at}} 
     por @if(isset($history->updatedUser) && ($history->updatedUser != '') && !is_null($history->updatedUser)) <strong>{{$history->updatedUser->name}}</strong>  @else <strong>Desconocido</strong> @endif
    {{$history->user_ip}} - {{$history->unique_machine}}
     <span class="badge" style="background-color: @if(isset($history->status) && ($history->status_id != '')) {{$history->status->color}};@else gray @endif">
	 @php
          $end = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $history->updated_at);
          echo $end->diffForHumans($now);
        @endphp
      </span>
	</li>
    @endforeach
    <li class="list-group-item">
	 Cambio de estado a 
	@if (isset($model->status) && ($model->status != ''))
	<span class="badge" style="background-color: @if(isset($history->status) && ($history->status_id != '')) {{$history->status->color}};@else gray @endif">
	{{$model->status->name}},
      </span>, @endif 
	el: {{$model->updated_at}} 
     por @if(isset($model->updatedUser) && ($model->updatedUser != '') && !is_null($model->updatedUser)) <strong>{{$model->updatedUser->name}}</strong> @else <strong>Desconocido</strong> @endif


	</li>
    </ul>
    
  </div>