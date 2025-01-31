<h2>Historial</h2>
<div class="table-responsive">
  <ul class="list-group">
    <?php $now = \Carbon\Carbon::now();?>
    @foreach($histories as $history)
      <li class="list-group-item">
        <a href="/customers/history/{{$history->id}}/show">{{$history->updated_at}} </a>
        @if(isset($history->updated_user)) Modificado por <strong>{{$history->updated_user->name}}.</strong> @endif
        Estado<strong>
          @if (isset($history->status) && ($history->status != ''))
            {{$history->status->name}}
          @else
            {{$history->status_id}}
          @endif
        </strong>,
        @if(isset($history->user) && ($history->user_id != '') && !is_null($history->user))
          asignado a <strong>{{$history->user->name}}</strong>
        @else
          sin asignar
        @endif

        <span class="badge" style="background-color: @if(isset($history->status) && ($history->status != '')) {{$history->status->color}} @else gray @endif">
          {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $history->updated_at)->diffForHumans() }}
        </span>
      </li>
    @endforeach
    <li class="list-group-item">
      {{$customer->updated_at}}
      @if(isset($customer->updated_user) and $customer->updated_user_id != null) Modificado por <strong>{{$customer->updated_user->name}}</strong>. @endif

      <strong>@if(isset($customer->status) && !is_null($customer->status) && $customer->status != '') Estado {{$customer->status->name}} @endif</strong>
      @if(isset($customer->user) && ($customer->user_id != '') && !is_null($customer->user))
        asignado a <strong>{{$customer->user->name}}</strong>
      @else
        sin asignar
      @endif
      <span class="badge" style="background-color: @if(isset($customer->status) && !is_null($customer->status) && $customer->status != '') {{$customer->status->color}} @else gray @endif;">
        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $customer->updated_at)->diffForHumans() }}
      </span>
    </li>
  </ul>
</div>
