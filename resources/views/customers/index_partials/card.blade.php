@php
  $lastAction = $item->getLastUserAction();
  $diasSinContacto = $lastAction ? \Carbon\Carbon::parse($lastAction->created_at)->diffInDays(now()) : null;
@endphp

<div class="col-12">
  <div class="customers customer_row row">
    <div class="initials col-sm-1 col-2">
      <div class="customer-circle" style="background-color: {{ $item->getStatusColor() }}">
        {{ $item->getInitials() }}
      </div>
    </div>

    <div class="customer_name col-sm-8 col-10">
      <div>
        <a href="{{ request()->fullUrlWithQuery(['customer_id' => $item->id]) }}">
          {!! $item->maker === 1 ? '🥟' : ($item->maker === 0 ? '💡' : ($item->maker === 2 ? '🍗🥩⚙️' : '')) !!}
          &nbsp;{{ Str::limit($item->name ?? 'Sin nombre', 21) }}
        </a>
      </div>

      <div class="scoring_customer">
        @if($item->country && strlen($item->country) === 2)
          <img src="/img/flags/{{ strtolower($item->country) }}.svg" height="10">
        @else
          {{ $item->country }}
        @endif

        @if($item->scoring_interest > 0)
          <span class="badge bg-secondary">{{ $item->scoring_interest }}</span>
        @endif

        <div class="stars-outer">
          <div class="stars-inner" id="star{{ $loop->index }}"></div>
          <script>ratings.push({{ $item->getScoringToNumber() }});</script>
        </div>
      </div>

      <a href="/customers/{{ $item->id }}/edit">
        <img src="/img/editar.png" id="edit_icon_{{ $item->id }}" style="display: none; width: 17px;">
      </a>

      <div class="customer_description">
        <div>
          <a href="/customers/{{ $item->id }}/show">
            {{ $item->getBestPhoneCandidate()
              ? $item->getInternationalPhone($item->getBestPhoneCandidate())
              : 'Sin teléfono válido' }}
          </a>
        </div>

        <div>
          Registrado: {{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}
        </div>

        @if($lastAction)
          <div>
            {!! $item->getLastContactLabel() !!}
          </div>
        @endif
      </div>
    </div>

    <div class="customer_created_at col-sm-3 d-none d-sm-block">
      <a href="/customers/{{ $item->id }}/show">
        {{ $item->user->name ?? 'sin asesor' }}
      </a>
    </div>
  </div>
</div>


<!--modal campañas -->
  <div id="modalCampañas_{{$item->id}}" class="modal fade" role="dialog">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
               <h4 class="modal-title">Campañas</h4> 
              </div>
              <div class="modal-body">
                      <div class="col-md-12" id="div_campaign_select_{{$item->id}}">
                        
                      <select name="message_id_{{$item->id}}" id="message_id_{{$item->id}}" onchange="nav(this.value,{{$item->id}})" class="form-control">
                          <option value="">Seleccione un mensaje</option>
                        
                            @if(isset($messages))
                              @foreach($messages as $message)
                         
                                <option value="{{$message->text}}">{{substr($message->text,0,40)}}</option>
                              @endforeach
                            @endif
                        </select>
                      </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
    </div>