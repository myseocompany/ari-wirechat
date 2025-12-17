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
  $circleColor = method_exists($model, 'getStatusColor') ? $model->getStatusColor() : '#DFAAFF';
  $initials = method_exists($model, 'getInitials') ? $model->getInitials() : strtoupper(substr($model->name, 0, 2));
@endphp

<div class="d-flex align-items-center mb-2">
  <div class="customer-circle small-circle mr-2" style="background-color: {{ $circleColor }}">
    {{ $initials }}
  </div>
  <div class="flex-grow-1">
    <h1 class="customer_name mb-1"> {!! $makerIcon !!}{{$model->name}}</h1>
    <div class="customer_created_at gray-dark"><small>ID: {{$model->id}}</small></div>
    <div class="customer_created_at gray-dark"><small>{{$model->created_at}}</small></div>
  </div>
  <div class="d-flex align-items-center ml-auto">
    @if($model->user)
      <small class="mr-2">{{ $model->user->name }}</small>
      <div class="customer-circle assessor-circle" style="background-color: #6c757d;">
        {{ method_exists($model->user, 'getInitials') ? $model->user->getInitials() : strtoupper(substr($model->user->name,0,2)) }}
      </div>
    @else
      <small class="text-muted mr-2">Sin asesor</small>
      <div class="customer-circle assessor-circle" style="background-color: #ccc;">
        <i class="fa fa-user" aria-hidden="true"></i>
      </div>
    @endif
  </div>
</div>
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

<style>
  .customer-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #fff;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
  }
  .small-circle { width: 40px; height: 40px; }
  .assessor-circle {
    width: 28px;
    height: 28px;
    line-height: 28px;
    font-size: 0.75rem;
    border-radius: 50%;
    color: #fff;
    font-weight: bold;
    text-align: center;
  }
</style>

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
                <span class="d-inline-flex align-items-center" style="gap:8px;">
                  <span>{{ $phone1 }}</span>
                  <button type="button" class="btn btn-link p-0 text-gray-600 copy-phone" data-phone="{{ $phone1 }}" aria-label="Copiar teléfono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                    </svg>
                  </button>
                </span>
              </div>
            @endif

            @if($phone2 && $phone2 !== $phone1)
              <div>
                <span class="label"><strong>Celular:</strong></span>
                <span class="d-inline-flex align-items-center" style="gap:8px;">
                  <span>{{ $phone2 }}</span>
                  <button type="button" class="btn btn-link p-0 text-gray-600 copy-phone" data-phone="{{ $phone2 }}" aria-label="Copiar teléfono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                    </svg>
                  </button>
                </span>
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

            <div class="mt-2">
              <span class="lavel"><strong>Notas:</strong></span>
              <div class="notes-wrapper position-relative notes-editor" data-save-url="/customers/{{$model->id}}/notes">
                <div
                  class="notes-display border rounded p-2"
                  contenteditable="true"
                >{{ $model->notes }}</div>
                <button type="button" class="btn btn-light btn-sm notes-edit-btn" data-modal="#notesModalMain" aria-label="Editar notas">
                  ✏️
                </button>
                <small class="notes-feedback text-muted"></small>
              </div>
            </div>
            <style>
              .notes-wrapper {
                margin-bottom: 6px;
              }
              .notes-display {
                min-height: 60px;
                white-space: pre-wrap;
                word-break: break-word;
                display: block;
              }
              .notes-display:focus {
                outline: none;
              }
              .notes-display.notes-display--active {
                box-shadow: 0 0 0 0.1rem rgba(0, 0, 0, 0.05);
              }
              .notes-edit-btn {
                position: absolute;
                right: 8px;
                bottom: 8px;
                padding: 4px 8px;
                font-size: 14px;
              }
            </style>

            <!-- Modal notas -->
            <div class="modal fade" id="notesModalMain" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar notas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <textarea class="form-control notes-textarea" rows="8" aria-label="Notas"></textarea>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary notes-save-btn">Guardar</button>
                  </div>
                </div>
              </div>
            </div>

            @include('customers.partials.notes_script')

            <div>
              <span class="lavel"><strong>Campaña:</strong></span> {{$model->campaign_name}}
            </div>

            <div class="mt-3">
              <h3 class="text-sm font-semibold">Etiquetas</h3>
              @if(isset($allTags) && $allTags->count())
                @include('customers.partials.tags_selector', [
                  'selectedTags' => $model->tags,
                  'formId' => 'customer-tags-form',
                  'formAction' => route('customers.tags.update', $model),
                  'feedbackSelector' => '#tags-feedback',
                ])
                <div id="tags-feedback" class="small text-muted mt-2 tags-feedback"></div>
                @include('customers.partials.tags_script')
              @endif
            </div>
           
            </p>

            <?php $customer = $model; ?>






            @if($actual)
            <div class="d-flex align-items-center" style="gap:8px; flex-wrap:wrap;">
              <a href="{{ route('orders.create', $model->id) }}">
                <span class="btn btn-success btn-sm" aria-hidden="true">Crear orden</span>
              </a>
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

              <form method="POST" action="{{ route('customers.send-welcome', $model->id) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" @if($welcomeAlreadySent) disabled @endif title="Enviar mensaje de bienvenida drip_01">
                  <small>D01</small>
                </button>
              </form>
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


        <!-- inicio acordeon -->


      <div class="card">
        <div class="card-body">


          <p class="card-text">
            <!-- BODYMS -->


            @if($actual)
          
            @include('customers.partials.acordion.emails')
            @include('customers.partials.acordion.files', ['customer' => $model])                                                        
            @include('customers.partials.acordion.polls')
            @include('customers.partials.acordion.quiz_escalable')
            @include('customers.partials.acordion.calculator')
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
  
  <div class="modal fade" id="metaPayloadModal" tabindex="-1" role="dialog" aria-labelledby="metaPayloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="metaPayloadModalLabel">Preview payload Meta Ads</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-2">
            Endpoint:
            <span id="metaPayloadEndpoint" class="font-weight-bold"></span>
          </p>
          <div id="metaPayloadError" class="alert alert-danger d-none"></div>
          <div class="mb-3">
            <p class="small text-muted mb-1">Payload:</p>
            <pre class="bg-light p-3 rounded small text-break" id="metaPayloadContent" style="max-height: 300px; overflow: auto;"></pre>
          </div>
          <div>
            <p class="small text-muted mb-1">Respuesta del servidor:</p>
            <pre class="bg-light p-3 rounded small text-break" id="metaPayloadResponse" style="max-height: 200px; overflow: auto;"></pre>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-outline-primary" id="btnMetaPayloadCopy" disabled>Copiar JSON</button>
        </div>
      </div>
    </div>
  </div>

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

    (function() {
      function copyToClipboard(text, onSuccess, onError) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(onSuccess).catch(onError);
          return;
        }
        var $tmp = document.createElement('textarea');
        $tmp.value = text;
        document.body.appendChild($tmp);
        $tmp.select();
        try {
          document.execCommand('copy');
          onSuccess();
        } catch (e) {
          onError();
        }
        document.body.removeChild($tmp);
      }

      document.addEventListener('click', function (event) {
        var trigger = event.target.closest('.copy-phone');
        if (!trigger) return;
        var phone = trigger.getAttribute('data-phone');
        if (!phone) return;
        copyToClipboard(phone.toString(), function () {
          alert('Teléfono copiado');
        }, function () {
          alert('No se pudo copiar el teléfono');
        });
      });
    })();

    (function() {
      var $modal = $('#metaPayloadModal');
      var content = document.getElementById('metaPayloadContent');
      var responseBox = document.getElementById('metaPayloadResponse');
      var endpoint = document.getElementById('metaPayloadEndpoint');
      var errorBox = document.getElementById('metaPayloadError');
      var copyBtn = document.getElementById('btnMetaPayloadCopy');
      var csrfToken = '';
      var csrfMeta = document.querySelector('meta[name="csrf-token"]');
      if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content') || '';
      }

      function stringify(value) {
        if (value === null || typeof value === 'undefined') {
          return '';
        }
        if (typeof value === 'string') {
          return value;
        }
        try {
          return JSON.stringify(value, null, 2);
        } catch (e) {
          return String(value);
        }
      }

      function showPayload(data, expectsResponse) {
        endpoint.textContent = data.endpoint || 'N/D';
        content.textContent = stringify(data.payload || {});
        responseBox.textContent = expectsResponse
          ? stringify(data.server_response ?? 'Sin respuesta')
          : 'Solo vista previa. No se envió al API.';
        copyBtn.disabled = !content.textContent;
      }

      function handleError(message) {
        content.textContent = '';
        responseBox.textContent = '';
        copyBtn.disabled = true;
        errorBox.textContent = message || 'No se pudo procesar la solicitud';
        errorBox.classList.remove('d-none');
      }

      function performAction(config) {
        if (!config.button) {
          return;
        }

        config.button.addEventListener('click', function() {
          errorBox.classList.add('d-none');
          errorBox.textContent = '';
          content.textContent = 'Procesando...';
          responseBox.textContent = config.expectsResponse ? 'Esperando respuesta del servidor...' : '';
          endpoint.textContent = '';
          copyBtn.disabled = true;
          $modal.modal('show');

          var options = {
            method: config.method,
            headers: { 'Accept': 'application/json' }
          };

          if (config.method !== 'GET') {
            options.headers['Content-Type'] = 'application/json';
            if (csrfToken) {
              options.headers['X-CSRF-TOKEN'] = csrfToken;
            }
            options.body = JSON.stringify({});
          }

          fetch(config.url, options)
            .then(function(response) {
              if (!response.ok) {
                return response.json()
                  .catch(function() { return { message: 'Error ' + response.status }; })
                  .then(function(json) {
                    var message = json && json.message ? json.message : ('Error ' + response.status);
                    throw new Error(message);
                  });
              }
              return response.json();
            })
            .then(function(data) {
              if (!data || data.ok !== true) {
                throw new Error(data && data.message ? data.message : 'Respuesta inválida');
              }
              showPayload(data, config.expectsResponse);
            })
            .catch(function(error) {
              handleError(error.message);
            });
        });
      }

    })();
  </script>
</div>
@endsection
