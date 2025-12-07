 @extends('layout')
<?php function clearWP($str)
{
  $str = trim($str);
  $str = str_replace("+", "", $str);
  return $str;
} ?>


@section('content')
<hr>
@if($model != null)

@if($model->isBanned())
<h1 style="color:red;"> <i class="fa fa-exclamation-circle" style="color:gray; "></i> {{$model->name}} <br> </h1>
@else

@php
  $makerIcon = $model->maker === 1 ? '🥟 ' : ($model->maker === 0 ? '💡 ' : ($model->maker === 2 ? '🍗🥩⚙️ ' : ''));
  $makerLabel = $model->maker === 1 ? 'Hace empanadas' : ($model->maker === 0 ? 'Proyecto' : ($model->maker === 2 ? 'Desmechadora' : null));
@endphp

<h1 class="customer_name"> {!! $makerIcon !!}{{$model->name}}


  <br>
</h1>
<div class="customer_created_at  gray-dark"><small>ID: {{$model->id}}</small></div>
<div class="customer_created_at  gray-dark"><small>{{$model->created_at}}</small></div>
@endif

{{-- Alertas --}}
@if (session('status'))
<div class="alert-primary alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
  {!! html_entity_decode(session('status')) !!}
</div>
@endif
@if (session('statusone'))
<div class="alert-warning alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
  {!! html_entity_decode(session('statusone')) !!}
</div>
@endif
@if (session('statustwo'))
<div class="alert alert-danger alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
  {!! html_entity_decode(session('statustwo')) !!}
</div>
@endif
{{-- fin alertas --}}


<div class="card-block">
  
    <div class="row">
      <!-- Primera columna -->
      <div class="col-md-4">

      

        <div class="card">
          <h5 class="card-title card-header">Detalles</h5>
          <div class="card-body">


            <p class="card-text">
              <div>
<!--
                <form method="POST" action="/customers/start-chat" id="wire_chat">
                  @csrf
                  <input type="hidden" name="customer_id" value="{{ $model->id }}">
                  <input type="hidden" name="mensaje" value="¡Hola, te hablo de parte de maquiempandas! ¿En qué puedo ayudarte?">
                  
                  <button type="submit" class="btn btn-outline-primary btn-sm mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke-width="1.5" 
                    stroke="currentColor" 
                    width="16" height="16" 
                    class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" 
                    d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                  </svg>
                      Iniciar Chat
                  </button>
              </form>

            -->
              
              </div>
            
              <div>
                
                <span class="lavel"><strong>Imagen:</strong></span>
                <a href="{{$model->image_url}}">Link</a>
                
              
              </div>
            <div><span class="lavel"><strong>Nombre:</strong></span> {{$model->name}}</div>
             @if(!empty($model->business))
            <div><span class="lavel"><strong>Empresa:</strong></span> {{$model->business}}</div>
            @endif
            
            <!-- teléfono -->
            @php
                $phone1 = $model->phone ? '+' . ltrim($model->getInternationalPhone($model->phone), '+') : null;
                $phone2 = $model->phone2 ? '+' . ltrim($model->getInternationalPhone($model->phone2), '+') : null;
            @endphp

            @if($phone1)
              <div>
                <span class="label"><strong>Teléfono:</strong></span>
                <span>{{ $phone1 }}</span>
              </div>
            @endif

            @if($phone2 && $phone2 !== $phone1)
              <div>
                <span class="label"><strong>Celular:</strong></span>
                <span>{{ $phone2 }}</span>
              </div>
            @endif
            <!-- fin teléfono -->



            <div><span class="lavel"><strong>Email:</strong></span> {{$model->email}}</div>
            <div><span class="lavel"><strong>País:</strong></span> {{$model->country}}</div>
            <div><span class="lavel"><strong>Estado:</strong></span> @if(isset($model->status)&& !is_null($model->status)&&$model->status!='')
              <span class="badge" style="background-color: @if(isset($model->status) && ($model->status != '')) {{$model->status->color}};@else gray @endif">{{$model->status->name}}</span> @endif
            </div>
            @if($makerLabel)
            <div>
              <span class="lavel"><strong>Tipo de cliente:</strong></span> {!! $makerIcon !!} {{ $makerLabel }}
            </div>
            @endif
            <div><strong>Asignado a:</strong>
              @if(isset($model->user)&& !is_null($model->user)&&$model->user!=''){{$model->user->name}} @else Sin asignar @endif
            </div>
            <div><span class="lavel"><strong>Interes:</strong></span>
              <span style="background-color: #66C366; border-radius: 50%; width: 25px; height: 25px; text-align: center; color: white; align-items: left;">{{$model->scoring_interest}}</span>
            </div>
            <div class="row">
              <div class="col-md-6 lavel"><span class="lavel"><strong>Perfil:</strong></span></div>
              <div class="col-md-6 scoring">
                <div class="scoring-stars">
                  @php
                    $stars = $model->getScoringToNumber();
                  @endphp
                
                  @for ($i = 1; $i <= 4; $i++)
                    @if ($i <= $stars)
                      <span style="color: gold; font-size: 18px;">★</span>
                    @else
                      <span style="color: lightgray; font-size: 18px;">☆</span>
                    @endif
                  @endfor
                </div>
              </div>
            </div>


            <div>
              <span class="lavel"><strong>No empanadas:</strong></span>{{$model->count_empanadas}}
            </div>

            <div>
              <span class="lavel"><strong>Rd Station: </strong><a href="{{$model->rd_public_url}}" target="_blank">Link</a></span>

            </div>

            <div>
              <span class="lavel"><strong>Notas:</strong></span> {{$model->notes}}
            </div>

            <div>
              <span class="lavel"><strong>Campaña:</strong></span> {{$model->campaign_name}}
            </div>

            <div class="mt-3">
              <h3 class="text-sm font-semibold">Etiquetas</h3>
              <div class="mb-2">
                @if($model->tags && $model->tags->count())
                  @foreach($model->tags as $tag)
                    <span class="px-2 py-1 rounded-full text-xs font-semibold mr-2 mb-1 d-inline-block" style="background-color: {{ $tag->color ?? '#e2e8f0' }};">
                      {{ $tag->name }}
                    </span>
                  @endforeach
                @else
                  <span class="text-muted">Sin etiquetas</span>
                @endif
              </div>

              @if(isset($allTags) && $allTags->count())
                <form method="POST" action="{{ route('customers.tags.update', $model) }}" id="customer-tags-form">
                  @csrf
                  <div class="grid grid-cols-2 gap-2" id="tag-options-grid">
                    @foreach($allTags as $tagOption)
                      @php
                        $checked = $model->tags->contains($tagOption->id);
                        $color = $tagOption->color ?: '#edf2f7';
                      @endphp
                      <label class="flex items-center gap-2 px-3 py-2 rounded border cursor-pointer text-sm" style="border-color: {{ $checked ? $color : '#e2e8f0' }}; background-color: {{ $checked ? $color : '#fff' }};">
                        <input
                          type="checkbox"
                          name="tags[]"
                          value="{{ $tagOption->id }}"
                          class="form-checkbox tag-checkbox"
                          data-name="{{ $tagOption->name }}"
                          data-color="{{ $tagOption->color ?: '#e2e8f0' }}"
                          @checked($checked)>
                        <span>{{ $tagOption->name }}</span>
                      </label>
                    @endforeach
                  </div>
                  @error('tags')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                  @enderror
                  @error('tags.*')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                  @enderror
                </form>
                <div id="tags-feedback" class="small text-muted mt-2"></div>
                @push('scripts')
                <script>
                  $(function() {
                    var $form = $('#customer-tags-form');
                    var $feedback = $('#tags-feedback');
                    var $badgesContainer = $form.prev('.mb-2');

                    function renderBadgesFromSelection() {
                      var selected = [];
                      $form.find('.tag-checkbox:checked').each(function() {
                        selected.push({
                          name: $(this).data('name'),
                          color: $(this).data('color') || '#e2e8f0'
                        });
                      });

                      if (!selected.length) {
                        $badgesContainer.html('<span class="text-muted">Sin etiquetas</span>');
                        return;
                      }

                      var html = selected.map(function(tag) {
                        return '<span class="px-2 py-1 rounded-full text-xs font-semibold mr-2 mb-1 d-inline-block" style="background-color: ' + tag.color + ';">' + tag.name + '</span>';
                      }).join('');
                      $badgesContainer.html(html);
                    }

                    function sendTags() {
                      var payload = $form.serializeArray();
                      if (!$form.find('.tag-checkbox:checked').length) {
                        payload.push({ name: 'tags', value: '' });
                      }

                      $feedback.text('Guardando etiquetas...');
                      $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: $.param(payload),
                        headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(resp) {
                          $feedback.text(resp.message || 'Etiquetas actualizadas.');
                          renderBadgesFromSelection();
                        },
                        error: function() {
                          $feedback.text('No se pudieron guardar las etiquetas.');
                        }
                      });
                    }

                    $form.on('change', '.tag-checkbox', sendTags);
                    renderBadgesFromSelection();
                  });
                </script>
                @endpush
              @endif
            </div>
           
            </p>

            <?php $customer = $model; ?>






            @if($actual)
            <div>
              <a href="/customers/{{$model->id}}/edit">
                <span class="btn btn-primary btn-sm" aria-hidden="true">Editar</span>
              </a>
              @if(is_null($model->user_id) || $model->user_id==0)
              <a href="/customers/{{$model->id}}/assignMe">
                <span class="btn btn-primary btn-sm" aria-hidden="true">Asignarme</span>
              </a>
              @endif

              @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 10)

              <a href="/customers/{{ $model->id }}/destroy">
                <span class="btn btn-sm btn-danger" aria-hidden="true" title="Eliminar">Eliminar</span></a>
              @endif
              @endif
              <button class="btn btn-primary btn-sm" id="btnCopiar" size="5"> POA</button>
              <a href="/orders/create/{{ $model->id }}">
                <span class="btn btn-primary btn-sm" aria-hidden="true" title="Eliminar">Cotizar</span>
              </a>
            </div>





          </div>
        </div>
        <br>
        @include('customers.show_partials.orders')
        <div class="card">
          <h5 class="card-title card-header">Dirección</h5>
          <div class="card-body">
            <p class="card text">
            <div><span class="lavel"><strong>Documento:</strong></span> {{$model->document}}</div>
            <div><span class="lavel"><strong>Dirección:</strong></span> {{$model->address}}</div>
            <div><span class="lavel"><strong>Departamento:</strong></span> {{$model->department}}</div>
            <div><span class="lavel"><strong>Ciudad:</strong></span> {{$model->city}}</div>
            <div><span class="lavel"><strong>Usuario actualizador:</strong></span>@if(isset($model->updated_user)){{$model->updated_user->name}}@endif
            </div>
            <div><span class="lavel"><strong>Tamaño de empandas:</strong></span> {{$model->empanadas_size}}</div>
            <div><span class="lavel"><strong>Número de sedes:</strong></span> {{$model->number_venues}}</div>
            <div><span class="lavel"><strong> Última Fecha de actualización:</strong></span> {{$model->updated_at}}</div>
            </p>
          </div>
        </div>
        <div class="card">
          <h5 class="card-title card-header">Detalle Contacto</h5>
          <div class="card-body">
            <p class="card text">
            <div><span class="lavel"><strong>Nombre:</strong></span> {{$model->contact_name}}</div>
            <div><span class="lavel"><strong>email:</strong></span> {{$model->contact_email}}</div>
            <div><span class="lavel"><strong>Telefono:</strong></span> {{$model->contact_phone2}}</div>
            <div><span class="lavel"><strong>Telefono:</strong></span> {{$model->contact_position}}</div>

            </p>
          </div>
        </div>

        <div class="card">
          <h5 class="card-title card-header">Empresa</h5>
          <div class="card-body">
            <p class="card-text">

            <div><span class="lavel"><strong>Empresa:</strong></span>{{$model->business}}</div>

            <div><span class="lavel"><strong>Cargo:</strong></span>{{$model->position}}</div>


            <div><span class="lavel"><strong>Producto Adquirido:</strong></span>{{$model->bought_products}}</div>
            <div><span class="lavel"><strong>Valor Cotizado:</strong></span>{{$model->total_sold}}</div>

            <div><span class="lavel"><strong>Fecha de Compra:</strong></span>{{$model->purchase_date}}</div>

            <div><span class="lavel"><strong>Producto consultado:</strong></span>
              @if(isset($model->product)){{$model->product->name}} @endif</div>



            <div><strong>Fuente:</strong>
              @if(isset($model->source)&& !is_null($model->source)&&$model->source!=''){{$model->source->name}}
              @endif

            </div>
            </p>
          </div>
        </div>

      </div>
      <!-- fin columna 1 -->
      <!-- Segunda columna -->

      <div class="col-md-8">
        @include('customers.partials.actions_form')
        @include('customers.show_partials.actions_widget_wp')


        @include('customers.partials.action_opportunity')
        @include('customers.partials.action_sale_form')
        @include('customers.partials.action_PQR')
        @include('customers.partials.action_spare')


        <!-- inicio acordeon -->


      <div class="card">
        <div class="card-body">


          <p class="card-text">
            <!-- BODYMS -->


            @if($actual)
          
            @include('customers.partials.acordion.emails')
            @include('customers.partials.acordion.polls')
            @include('customers.partials.acordion.files', ['customer' => $model])                                                        
            @include('customers.partials.acordion.poa')
          

              @include('customers.show_partials.history')

              @endif

              @else
              El prospecto no existe

              @endif

              <!-- end BODYMS -->
              </p>



          </div>
        </div>

      </div>
       <!-- fin acordeon -->
      </div>


  </form>
  
  <script>
    document.getElementById("btnCopiar").addEventListener("click", function() {
      var textoCopiar = "https://arichat.co/metadata/{{$model->id}}/create/poe/40";

      var elementoInput = document.createElement("input");
      elementoInput.value = textoCopiar;
      document.body.appendChild(elementoInput);

      elementoInput.select();
      document.execCommand("copy");

      document.body.removeChild(elementoInput);
      alert("¡URL copiada al portapapeles!");
    });
  </script>
</div>
@endsection
