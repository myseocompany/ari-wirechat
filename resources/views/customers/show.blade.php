@extends('layouts.tailwind')
<?php function clearWP($str)
{
  $str = trim($str);
  $str = str_replace("+", "", $str);
  return $str;
} ?>

@section('content')
<div id="customer_show_content">

@if($model != null)

@php
  $makerIcon = $model->maker === 1 ? '🥟 ' : ($model->maker === 0 ? '💡 ' : ($model->maker === 2 ? '🍗🥩⚙️ ' : ''));
  $makerLabel = $model->maker === 1 ? 'Hace empanadas' : ($model->maker === 0 ? 'Proyecto' : ($model->maker === 2 ? 'Desmechadora' : null));
  $circleColor = method_exists($model, 'getStatusColor') ? $model->getStatusColor() : '#DFAAFF';
  $initials = method_exists($model, 'getInitials') ? $model->getInitials() : strtoupper(substr($model->name, 0, 2));
@endphp

{{-- Alertas --}}
@if (session('status'))
<div class="mb-2 flex items-start justify-between gap-3 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800" role="alert">
  <div>{!! html_entity_decode(session('status')) !!}</div>
  <button type="button" class="text-slate-500 hover:text-slate-700" data-dismiss="alert" aria-label="Cerrar">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
@if (session('statusone'))
<div class="mb-2 flex items-start justify-between gap-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
  <div>{!! html_entity_decode(session('statusone')) !!}</div>
  <button type="button" class="text-slate-500 hover:text-slate-700" data-dismiss="alert" aria-label="Cerrar">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
@if (session('statustwo'))
<div class="mb-2 flex items-start justify-between gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
  <div>{!! html_entity_decode(session('statustwo')) !!}</div>
  <button type="button" class="text-slate-500 hover:text-slate-700" data-dismiss="alert" aria-label="Cerrar">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
{{-- fin alertas --}}

<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
      <!-- Primera columna -->
      <div class="lg:col-span-3 space-y-4 rounded-2xl bg-white p-4">
        @if($model)
          <div class="bg-white text-sm text-slate-700">
            <div class="flex flex-col items-center text-center">
              <div class="flex h-16 w-16 items-center justify-center rounded-full text-lg font-semibold text-white" style="background-color: {{ $circleColor }}">
                {{ $initials }}
              </div>
              <div class="mt-3 text-lg font-semibold text-slate-900">{!! $makerIcon !!}{{ $model->name }}</div>
              <div class="text-xs text-slate-500">ID: {{ $model->id }}</div>
              <div class="text-xs text-slate-500">{{ $model->created_at }}</div>
              @if($makerLabel)
                <div class="mt-2 text-xs font-medium text-slate-500">{!! $makerIcon !!} {{ $makerLabel }}</div>
              @endif
            </div>
            <div class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-500">
              @if($model->user)
                <span>{{ $model->user->name }}</span>
                <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-semibold text-white" style="background-color: #6c757d;">
                  {{ method_exists($model->user, 'getInitials') ? $model->user->getInitials() : strtoupper(substr($model->user->name,0,2)) }}
                </div>
              @else
                <span class="text-slate-400">Sin asesor</span>
                <div class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-semibold text-white" style="background-color: #ccc;">
                  <i class="fa fa-user" aria-hidden="true"></i>
                </div>
              @endif
            </div>
            @if($actual)
              <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                <a href="{{ route('orders.create', $model->id) }}">
                  <span class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700" aria-hidden="true">Crear orden</span>
                </a>
                <a href="/customers/{{$model->id}}/edit">
                  <span class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-700" aria-hidden="true">Editar</span>
                </a>
                @if(is_null($model->user_id) || $model->user_id==0)
                <a href="/customers/{{$model->id}}/assignMe">
                  <span class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-700" aria-hidden="true">Asignarme</span>
                </a>
                @endif

                @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 10)
                  <a href="/customers/{{ $model->id }}/destroy">
                    <span class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700" aria-hidden="true" title="Eliminar">Eliminar</span>
                  </a>
                @endif
                <button class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-700" id="btnCopiar" size="5">POA</button>

                <form method="POST" action="{{ route('customers.send-welcome', $model->id) }}">
                  @csrf
                  <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60" @if($welcomeAlreadySent) disabled @endif title="Enviar mensaje de bienvenida drip_01">
                    D01
                  </button>
                </form>
              </div>
            @endif
          </div>
        @endif

      

        <div class="bg-white">
          <div class="text-sm text-slate-700">
            <h2 class="mb-2 text-base font-semibold text-slate-900">Detalles</h2>
<!--
            <form method="POST" action="/customers/start-chat" id="wire_chat">
              @csrf
              <input type="hidden" name="customer_id" value="{{ $model->id }}">
              <input type="hidden" name="mensaje" value="¡Hola, te hablo de parte de maquiempandas! ¿En qué puedo ayudarte?">
              <button type="submit" class="inline-flex items-center rounded-md border border-blue-600 px-3 py-1.5 text-sm font-medium text-blue-600 transition hover:bg-blue-50">
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
            <div class="space-y-3">
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Imagen</div>
                <a href="{{ $model->image_url }}" class="text-sm text-blue-600 hover:underline">Link</a>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Nombre</div>
                <div class="text-sm text-slate-900">{{ $model->name }}</div>
              </div>
              @if(!empty($model->business))
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Empresa</div>
                <div class="text-sm text-slate-900">{{ $model->business }}</div>
              </div>
              @endif
            
            <!-- teléfono -->
            @php
                $phone1 = $model->phone ? '+' . ltrim($model->getInternationalPhone($model->phone), '+') : null;
                $phone2 = $model->phone2 ? '+' . ltrim($model->getInternationalPhone($model->phone2), '+') : null;
            @endphp

            @if($phone1)
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Teléfono</div>
                <div class="inline-flex items-center gap-2 text-sm text-slate-900">
                  <span>{{ $phone1 }}</span>
                  <button type="button" class="inline-flex items-center text-slate-500 transition hover:text-slate-700 copy-phone" data-phone="{{ $phone1 }}" aria-label="Copiar teléfono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                    </svg>
                  </button>
                </div>
              </div>
            @endif

            @if($phone2 && $phone2 !== $phone1)
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Celular</div>
                <div class="inline-flex items-center gap-2 text-sm text-slate-900">
                  <span>{{ $phone2 }}</span>
                  <button type="button" class="inline-flex items-center text-slate-500 transition hover:text-slate-700 copy-phone" data-phone="{{ $phone2 }}" aria-label="Copiar teléfono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                    </svg>
                  </button>
                </div>
              </div>
            @endif
            <!-- fin teléfono -->

            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">Email</div>
              <div class="text-sm text-slate-900">{{ $model->email }}</div>
            </div>
            @if(!empty($model->country))
            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">País</div>
              <div class="text-sm text-slate-900">{{ $model->country }}</div>
            </div>
            @endif
            @if(!empty($model->department))
            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">Departamento</div>
              <div class="text-sm text-slate-900">{{ $model->department }}</div>
            </div>
            @endif
            @if(!empty($model->city))
            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">Ciudad</div>
              <div class="text-sm text-slate-900">{{ $model->city }}</div>
            </div>
            @endif
            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">Estado</div>
              @if(isset($model->status)&& !is_null($model->status)&&$model->status!='')
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold text-white" style="background-color: {{ $model->status->color ?? 'gray' }}">
                  {{ $model->status->name }}
                </span>
              @endif
            </div>
            @if($makerLabel)
            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">Tipo de cliente</div>
              <div class="text-sm text-slate-900">{!! $makerIcon !!} {{ $makerLabel }}</div>
            </div>
            @endif
            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">No empanadas</div>
              <div class="text-sm text-slate-900">{{ $model->count_empanadas }}</div>
            </div>

            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">Rd Station</div>
              <a href="{{ $model->rd_public_url }}" target="_blank" class="text-sm text-blue-600 hover:underline">Link</a>
            </div>

            <div class="space-y-0.5">
              <div class="text-xs text-slate-500">Campaña</div>
              <div class="text-sm text-slate-900">{{ $model->campaign_name }}</div>
            </div>
            @if(!empty($model->ad_name))
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Anuncio</div>
                <div class="text-sm text-slate-900">{{ $model->ad_name }}</div>
              </div>
            @endif
            @if(!empty($model->adset_name))
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Conjunto</div>
                <div class="text-sm text-slate-900">{{ $model->adset_name }}</div>
              </div>
            @endif

            <?php $customer = $model; ?>
            </div>
          </div>
        </div>
        <div class="bg-white">
          <div class="text-sm text-slate-700">
            <h2 class="mb-2 text-base font-semibold text-slate-900">Dirección</h2>
            <div class="space-y-3">
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Documento</div>
                <div class="text-sm text-slate-900">{{ $model->document }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Dirección</div>
                <div class="text-sm text-slate-900">{{ $model->address }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Usuario actualizador</div>
                <div class="text-sm text-slate-900">
                  @if(isset($model->updated_user)){{ $model->updated_user->name }}@endif
                </div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Tamaño de empandas</div>
                <div class="text-sm text-slate-900">{{ $model->empanadas_size }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Número de sedes</div>
                <div class="text-sm text-slate-900">{{ $model->number_venues }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Última Fecha de actualización</div>
                <div class="text-sm text-slate-900">{{ $model->updated_at }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-white">
          <div class="text-sm text-slate-700">
            <h2 class="mb-2 text-base font-semibold text-slate-900">Detalle Contacto</h2>
            <div class="space-y-3">
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Nombre</div>
                <div class="text-sm text-slate-900">{{ $model->contact_name }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Email</div>
                <div class="text-sm text-slate-900">{{ $model->contact_email }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Teléfono</div>
                <div class="text-sm text-slate-900">{{ $model->contact_phone2 }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Cargo</div>
                <div class="text-sm text-slate-900">{{ $model->contact_position }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white">
          <div class="text-sm text-slate-700">
            <h2 class="mb-2 text-base font-semibold text-slate-900">Empresa</h2>
            <div class="space-y-3">
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Empresa</div>
                <div class="text-sm text-slate-900">{{ $model->business }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Cargo</div>
                <div class="text-sm text-slate-900">{{ $model->position }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Producto Adquirido</div>
                <div class="text-sm text-slate-900">{{ $model->bought_products }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Valor Cotizado</div>
                <div class="text-sm text-slate-900">{{ $model->total_sold }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Fecha de Compra</div>
                <div class="text-sm text-slate-900">{{ $model->purchase_date }}</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Producto consultado</div>
                <div class="text-sm text-slate-900">@if(isset($model->product)){{ $model->product->name }}@endif</div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Fuente</div>
                <div class="text-sm text-slate-900">
                  @if(isset($model->source)&& !is_null($model->source)&&$model->source!=''){{ $model->source->name }}@endif
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- fin columna 1 -->
      <!-- Segunda columna -->

      <div class="lg:col-span-6 space-y-4 rounded-2xl bg-slate-100 p-4">
        @include('customers.partials.customer_tabs', ['model' => $model, 'actual' => $actual])
      </div>
      <!-- Tercera columna -->
      <div class="lg:col-span-3 space-y-4 rounded-2xl bg-white p-4">
        <div class="bg-white">
          <div class="text-sm text-slate-700">
            <h2 class="mb-2 text-base font-semibold text-slate-900">Interes y Perfil</h2>
            <div class="space-y-3">
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Interes</div>
                <div class="text-sm text-slate-900">
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-xs font-semibold text-white">{{ $model->scoring_interest }}</span>
                </div>
              </div>
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Perfil</div>
                <div class="flex items-center gap-1">
                  @php
                    $stars = $model->getScoringToNumber();
                  @endphp
                  @for ($i = 1; $i <= 4; $i++)
                    @if ($i <= $stars)
                      <span class="text-lg text-amber-400">★</span>
                    @else
                      <span class="text-lg text-slate-300">☆</span>
                    @endif
                  @endfor
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-white">
          <div class="text-sm text-slate-700">
            <h2 class="mb-2 text-base font-semibold text-slate-900">Notas y etiquetas</h2>
            <div class="space-y-3">
              <div class="space-y-0.5">
                <div class="text-xs text-slate-500">Notas</div>
                <div class="notes-wrapper notes-editor relative mb-1" data-save-url="/customers/{{ $model->id }}/notes">
                  <div
                    class="notes-display min-h-[60px] whitespace-pre-wrap break-words rounded-md bg-slate-50 p-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    contenteditable="true"
                  >{{ $model->notes }}</div>
                  <button type="button" class="notes-edit-btn absolute bottom-2 right-2 inline-flex items-center rounded-md bg-white px-2 py-1 text-xs text-slate-600 shadow-sm transition hover:text-slate-800" data-modal="#notesModalMain" aria-label="Editar notas">
                    ✏️
                  </button>
                  <small class="notes-feedback text-xs text-slate-500"></small>
                </div>
              </div>

              <div class="space-y-1">
                <div class="text-xs text-slate-500">Etiquetas</div>
                @if(isset($allTags) && $allTags->count())
                  @include('customers.partials.tags_selector', [
                    'selectedTags' => $model->tags,
                    'formId' => 'customer-tags-form',
                    'formAction' => route('customers.tags.update', $model),
                    'feedbackSelector' => '#tags-feedback',
                  ])
                  <div id="tags-feedback" class="mt-2 text-xs text-slate-500 tags-feedback"></div>
                  @include('customers.partials.tags_script')
                @endif
              </div>
            </div>
          </div>
        </div>
        <!-- Modal notas -->
        <div class="modal fade" id="notesModalMain" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg mx-auto w-full max-w-3xl" role="document">
            <div class="modal-content rounded-lg bg-white shadow-xl">
              <div class="modal-header flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h5 class="text-base font-semibold text-slate-900">Editar notas</h5>
                <button type="button" class="text-slate-500 transition hover:text-slate-700" data-dismiss="modal" aria-label="Cerrar">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body px-4 py-3">
                <textarea class="notes-textarea w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" rows="8" aria-label="Notas"></textarea>
              </div>
              <div class="modal-footer flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                <button type="button" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50" data-dismiss="modal">Cancelar</button>
                <button type="button" class="notes-save-btn inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-blue-700">Guardar</button>
              </div>
            </div>
          </div>
        </div>

        @include('customers.partials.notes_script')
        @include('customers.show_partials.orders')
      </div>
    </div>
  </div>


  <div class="modal fade" id="metaPayloadModal" tabindex="-1" role="dialog" aria-labelledby="metaPayloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg mx-auto w-full max-w-3xl" role="document">
      <div class="modal-content rounded-lg bg-white shadow-xl">
        <div class="modal-header flex items-center justify-between border-b border-slate-200 px-4 py-3">
          <h5 class="text-base font-semibold text-slate-900" id="metaPayloadModalLabel">Preview payload Meta Ads</h5>
          <button type="button" class="text-slate-500 transition hover:text-slate-700" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body space-y-3 px-4 py-3 text-sm text-slate-700">
          <p class="text-xs text-slate-500">
            Endpoint:
            <span id="metaPayloadEndpoint" class="font-semibold text-slate-700"></span>
          </p>
          <div id="metaPayloadError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>
          <div>
            <p class="mb-1 text-xs text-slate-500">Payload:</p>
            <pre class="max-h-[300px] overflow-auto rounded-md bg-slate-50 p-3 text-xs text-slate-700" id="metaPayloadContent"></pre>
          </div>
          <div>
            <p class="mb-1 text-xs text-slate-500">Respuesta del servidor:</p>
            <pre class="max-h-[200px] overflow-auto rounded-md bg-slate-50 p-3 text-xs text-slate-700" id="metaPayloadResponse"></pre>
          </div>
        </div>
        <div class="modal-footer flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
          <button type="button" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50" data-dismiss="modal">Cerrar</button>
          <button type="button" class="inline-flex items-center rounded-md border border-blue-600 px-3 py-1.5 text-sm font-medium text-blue-600 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-60" id="btnMetaPayloadCopy" disabled>Copiar JSON</button>
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
        errorBox.classList.remove('hidden');
      }

      function performAction(config) {
        if (!config.button) {
          return;
        }

        config.button.addEventListener('click', function() {
          errorBox.classList.add('hidden');
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
@else
  <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">El prospecto no existe</div>
@endif
</div>
@endsection
