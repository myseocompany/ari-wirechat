@extends('layout')

@section('title', 'Clientes')

<?php function clearWP($str)
{
  $str = trim($str);
  $str = str_replace("+", "", $str);
  return $str;
} ?>
<!-- MAQUIEMPANADAS -->@section('content')
{{-- Encabezado principal movido a bloque contextual --}}
<style>
  a:hover {
    color: #4178be;
  }
  .customer-circle {
    border-radius: 50%;
    font-size: 1rem;
    text-align: center;
    color: #fff;
    font-weight: bold;
    line-height: 40px;
    width: 40px;
    height: 40px;
  }
  .tag-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 0;
    border-radius: 6px;
    border: none;
    background: transparent;
    cursor: pointer;
  }
  .tag-swatch {
    width: 32px;
    height: 18px;
    border-radius: 4px;
    clip-path: polygon(0 0, 82% 0, 100% 50%, 82% 100%, 0 100%);
    border: 1px solid #e2e8f0;
    font-size: 10px;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
  }
  .tag-checkbox {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    width: 0;
    height: 0;
  }
</style>

<script>
function toggleDateInput(id, options = {}) {
  const containerId = options.containerId || ('dateInputContainer_' + id);
  const checkboxId = options.checkboxId || ('toggleDate_' + id);
  const container = document.getElementById(containerId);
  const checkbox = document.getElementById(checkboxId);
  if (container && checkbox) {
    container.style.display = checkbox.checked ? 'block' : 'none';
  }
}
</script>




<?php
function requestToStr($request)
{
  $str = "?";
  $url = $request->fullUrl();
  $parsedUrl = parse_url($url);

  if (isset($parsedUrl['query']))
    $str .= $parsedUrl['query'];

  return $str;
}
?>

{{-- obteber datos del tiempo --}}
@php
  $totalCount = $model->total();
  $todayLabel = \Carbon\Carbon::now()->format('d M Y');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="mb-1">Seguimientos</h1>
    <div class="text-muted small">Actualiza estados, agenda acciones o etiquetas. Hoy: {{ $todayLabel }}.</div>
    <div class="font-weight-bold">{{ $totalCount }} clientes</div>
  </div>
  <div class="d-flex align-items-center gap-2">
    <a href="/customers/create" class="btn btn-primary btn-sm mr-2">Crear</a>
    <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="collapse" data-target="#daily-filter" aria-expanded="false">Filtros</button>
  </div>
</div>

<div id="daily-filter" class="collapse mb-3">
  @include('customers.filter_daily')
</div>

@php
  $sort = $request->get('sort', 'next_action');
  $baseParams = $request->except('page');
  $sortUrl = function($key) use ($request, $baseParams) {
    $params = array_merge($baseParams, ['sort' => $key, 'page' => null]);
    return $request->url().'?'.http_build_query(array_filter($params, fn($v) => $v !== null && $v !== ''));
  };
@endphp

<div class="d-flex flex-wrap align-items-center mb-3 justify-content-end" style="gap: 8px;">
  <span class="text-muted small mr-2">Ordenar por:</span>
  <a href="{{ $sortUrl('next_action') }}" class="btn btn-sm {{ $sort === 'next_action' ? 'btn-primary' : 'btn-outline-secondary' }}">Próx. acción</a>
  <a href="{{ $sortUrl('last_action') }}" class="btn btn-sm {{ $sort === 'last_action' ? 'btn-primary' : 'btn-outline-secondary' }}">Última acción</a>
  <a href="{{ $sortUrl('advisor') }}" class="btn btn-sm {{ $sort === 'advisor' ? 'btn-primary' : 'btn-outline-secondary' }}">Asesor</a>
  <a href="{{ $sortUrl('recent') }}" class="btn btn-sm {{ $sort === 'recent' ? 'btn-primary' : 'btn-outline-secondary' }}">Reciente</a>
</div>

@php
  $mqlTag = isset($allTags) ? $allTags->firstWhere('name', 'MQL') : null;
@endphp

@if(isset($searchResults) && $request->filled('search'))
  <div class="bg-white shadow rounded-lg mb-3">
    <div class="px-4 py-3 border-b">
      <h4 class="m-0 text-sm font-semibold">Resultados de búsqueda</h4>
    </div>
    @if($searchResults->count())
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Contacto</th>
              <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Acción</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            @foreach($searchResults as $sCustomer)
              @php
                $hasMql = $mqlTag ? $sCustomer->tags->contains($mqlTag->id) : false;
              @endphp
              <tr>
                <td class="px-4 py-2 text-sm">
                  <div class="font-semibold">
                    <a href="{{ route('customers.show', $sCustomer) }}" class="hover:underline">{{ $sCustomer->name }}</a>
                  </div>
                  <div class="text-xs text-gray-500">ID: {{ $sCustomer->id }}</div>
                </td>
                <td class="px-4 py-2 text-sm text-gray-700">
                  @if($sCustomer->email)<div>{{ $sCustomer->email }}</div>@endif
                  @if($sCustomer->phone)<div>{{ $sCustomer->phone }}</div>@endif
                </td>
                <td class="px-4 py-2 text-sm">
                  @if($mqlTag)
                    @if($hasMql)
                      <span class="inline-block px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">Ya es MQL</span>
                    @else
                      <form method="POST" action="{{ route('customers.tags.add_mql', $sCustomer) }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url('/reports/views/daily_customers_followup') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Agregar como MQL</button>
                      </form>
                    @endif
                  @else
                    <span class="text-muted text-xs">Etiqueta MQL no configurada.</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="p-3 text-sm text-gray-600">No hay coincidencias.</div>
    @endif
  </div>
@endif

<div>
  <div class="alert alert-primary alert-dismissible fade show" role="alert" style="display:none" id="notication_area">
    <span id="notication_area_text">Demo</span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
</div>

{{-- Alertas --}}
@if (session('status'))
<div class="alert alert-primary alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
  {!! html_entity_decode(session('status')) !!}
</div>
@endif
@if (session('statusone'))
<div class="alert alert-warning alert-dismissible" role="alert">
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

{{-- tabla resumen --}}
<br>
Registro <strong>{{ $model->currentPage()*$model->perPage() - ( $model->perPage() - 1 ) }}</strong> a <strong>{{ $model->getActualRows}}</strong> de <strong>{{$model->total()}}</strong>
<br>
{{-- <div>{{$model->total()}} Registro(s)</div> --}}
<br>
<div class="bg-white shadow rounded-lg overflow-hidden">
  @if (count($model) > 0)
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</th>
          @if (Auth::user()->role_id !== 2)
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Asesor</th>
          @endif
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Última Acción</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Acción pendiente</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Días sin seg.</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Acciones</th>
        </tr>
      </thead>

      <?php $lastStatus = -1 ?>
      <tbody class="divide-y divide-gray-200">
        <?php $count = 1; ?>
        @foreach($model as $item)

      {{-- Modal seguimiento --}}
      <div class="modal fade" id="addActionModal-{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="addActionModalLabel-{{$item->id}}" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addActionModalLabel-{{$item->id}}">Agregar seguimiento</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="/customers/{{$item->id}}/action/store" method="POST">
              @csrf
              <input type="hidden" name="customer_id" value="{{$item->id}}">
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6">
                    <label for="status_id_{{$item->id}}" class="form-label">Estado</label>
                    <select name="status_id" id="status_id_{{$item->id}}" class="form-control">
                      <option value="">Seleccione un estado</option>
                      @foreach($statuses_options as $statusOption)
                        <option value="{{$statusOption->id}}" @if($item->status_id == $statusOption->id) selected @endif>
                          {{$statusOption->name}}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="type_id_{{$item->id}}" class="form-label">Tipo de acción</label>
                    <select name="type_id" id="type_id_{{$item->id}}" class="form-control" required>
                      @foreach($action_options as $actionType)
                        <option value="{{$actionType->id}}">{{$actionType->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="form-group mt-3">
                  <label for="note_{{$item->id}}">Comentario</label>
                  <textarea name="note" id="note_{{$item->id}}" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggleDate_{{$item->id}}" onclick="toggleDateInput({{$item->id}})">
                    <label class="form-check-label" for="toggleDate_{{$item->id}}">Programar acción</label>
                  </div>
                </div>
                <div class="form-group" id="dateInputContainer_{{$item->id}}" style="display:none;">
                  <label for="date_programed_{{$item->id}}">Fecha próxima acción</label>
                  <input type="datetime-local" name="date_programed" id="date_programed_{{$item->id}}" class="form-control">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Modal: completar acción pendiente --}}
      @php
        $nextActionModal = $item->actions()
          ->whereNull('delivery_date')
          ->reorder()
          ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
          ->orderBy('due_date')
          ->orderByDesc('created_at')
          ->first();
      @endphp
      @if($nextActionModal)
      <div class="modal fade" id="completeActionModal-{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="completeActionModalLabel-{{$item->id}}" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="completeActionModalLabel-{{$item->id}}">Marcar acción como hecha</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="/customers/action/pending" method="POST">
              @csrf
              <input type="hidden" name="action_id" value="{{ $nextActionModal->id }}">
              <div class="modal-body">
                @if($nextActionModal->note)
                  <div class="mb-2 text-muted small">Acción pendiente: {{ $nextActionModal->note }}</div>
                @endif
                <div class="form-group">
                  <label for="complete_note_{{$item->id}}">Comentario</label>
                  <textarea name="note" id="complete_note_{{$item->id}}" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="complete_type_{{$item->id}}">Tipo de acción</label>
                    <select name="type_id" id="complete_type_{{$item->id}}" class="form-control" required>
                      @foreach($action_options as $actionType)
                        <option value="{{$actionType->id}}" @if($nextActionModal->type_id == $actionType->id) selected @endif>{{$actionType->name}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="complete_status_{{$item->id}}">Estado del cliente</label>
                    <select name="status_id" id="complete_status_{{$item->id}}" class="form-control" required>
                      @foreach($statuses_options as $statusOption)
                        <option value="{{$statusOption->id}}" @if($item->status_id == $statusOption->id) selected @endif>{{$statusOption->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group mb-2">
                  <div class="form-check form-switch">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="toggleCompleteDate_{{$item->id}}"
                      onclick="toggleDateInput({{$item->id}}, {containerId: 'completeDateContainer_{{$item->id}}', checkboxId: 'toggleCompleteDate_{{$item->id}}'})">
                    <label class="form-check-label" for="toggleCompleteDate_{{$item->id}}">Programar próxima acción</label>
                  </div>
                </div>
                <div class="form-group" id="completeDateContainer_{{$item->id}}" style="display:none;">
                  <label for="complete_due_{{$item->id}}">Próxima fecha</label>
                  <input type="datetime-local" name="new_due_date" id="complete_due_{{$item->id}}" class="form-control">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Marcar como hecha</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      @endif

      <tr class="bg-white hover:bg-gray-50">
        <td class="px-0 py-4 align-top" style="border-left: 4px solid {{ $item->getStatusColor() ?? '#DFAAFF' }};">
          <div class="px-4">
            <div class="d-flex align-items-center">
              <div class="customer-circle small-circle mr-2" style="background-color: {{ $item->getStatusColor() ?? '#DFAAFF' }}">
                {{ $item->getInitials() }}
              </div>
              <div class="position-relative w-100">
                <div class="font-semibold text-gray-900">
                  <a href="/customers/{{ $item->id }}/show" class="hover:underline">{{$item->name}}</a>
                </div>
                <div class="small text-muted mt-1">
                  @if(isset($item->email))<div class="text-gray-600">{{$item->email}}</div>@endif
                  @if(!empty($item->phone))
                    <div class="d-flex align-items-center gap-2">
                      <a class="text-indigo-600 hover:underline" @if(isset($customer->phone)) href="https://wa.me/{{ clearWP($customer->getPhone()) }}" @else href="" @endif target="_empty">{{$customer->phone}}</a>
                      @if(!empty($customer->phone))
                      <button type="button" class="btn btn-link p-0 text-gray-600 copy-phone" data-phone="{{$customer->phone}}" aria-label="Copiar teléfono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                      </button>
                      @endif
                    </div>
                  @endif
                  @if(!empty($item->phone2))
                    <div class="d-flex align-items-center gap-2">
                      <a class="text-indigo-600 hover:underline" @if(isset($customer->phone2)) href="https://wa.me/{{ clearWP($customer->getPhone()) }}" @else href="" @endif target="_empty">{{$customer->phone2}}</a>
                      @if(!empty($customer->phone2))
                      <button type="button" class="btn btn-link p-0 text-gray-600 copy-phone" data-phone="{{$customer->phone2}}" aria-label="Copiar teléfono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                      </button>
                      @endif
                    </div>
                @endif
                {{-- Estado --}}
                @if(isset($item->status_id)&&($item->status_id!="")&&(!is_null($item->status)))
                  <div class="mt-2 d-inline-flex align-items-center text-xs font-semibold" style="color: #6b7280;">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background-color: {{$item->status->color}};margin-right:6px;"></span>
                    {{$item->status->name}}
                  </div>
                @endif
                {{-- Etiquetas --}}
                @if(isset($allTags) && $allTags->count())
                  <form
                    method="POST"
                    action="{{ route('customers.tags.update', $item) }}"
                    class="customer-tags-form mt-2"
                    data-tags-feedback="#tags-feedback-{{$item->id}}">
                    @csrf
                    <div class="flex flex-wrap gap-2">
                      @foreach($allTags as $tagOption)
                        @php
                          $checked = $item->tags->contains($tagOption->id);
                          $color = $tagOption->color ?: '#edf2f7';
                        @endphp
                        <label class="tag-label text-sm">
                          <input
                            type="checkbox"
                            name="tags[]"
                            value="{{ $tagOption->id }}"
                            class="form-checkbox tag-checkbox mr-2"
                            data-name="{{ $tagOption->name }}"
                            data-color="{{ $tagOption->color ?: '#e2e8f0' }}"
                            @checked($checked)>
                          <span class="tag-swatch" style="border-color: {{ $checked ? $color : '#e2e8f0' }}; background-color: {{ $checked ? $color : 'transparent' }}; color: {{ $checked ? '#fff' : '#000' }};">
                            {{ $tagOption->name }}
                          </span>
                        </label>
                      @endforeach
                    </div>
                  </form>
                  <div class="tags-feedback small text-muted mt-1" id="tags-feedback-{{$item->id}}"></div>
                  @once
                    @include('customers.partials.tags_script')
                  @endonce
                @endif
              </div>
            </div>
            </div>
          </div>
        </td>

        @if (Auth::user()->role_id !== 2)
        <td class="px-4 py-4 align-top">
          <!--  
*
*    Combo de usuarios
*
-->
          @if ($canAssignCustomers)
            <script>
              function updateUser(cid) {
                console.log(cid);
                var uid = $("#user_id_" + cid).val();
                var parameters = {
                  customer_id: cid,
                  user_id: uid
                };
                console.log(parameters);
                $.ajax({
                  data: parameters,
                  url: '/customers/ajax/update_user',
                  type: 'get',
                  beforeSend: function() {},
                  success: function(response) {
                    console.log(response);
                    $("#notication_area").css('display', 'block');
                    $("#notication_area_text").html("Se actualizó el cliente " + response);
                  }
                });
              }
            </script>
            <select name="user_id" class="custom-select w-full border-gray-300 rounded-md text-sm" id="user_id_{{$item->id}}" onchange="updateUser({{$item->id}});">
              <option value="">Usuario...</option>
              <option value="null">Sin asignar</option>
              @foreach($users as $user)
              <option value="{{$user->id}}" @if ($item->user_id == $user->id) selected="selected" @endif>
                <?php echo substr($user->name, 0, 10); ?>

              </option>
              @endforeach
            </select>
          @else
            <div class="text-sm text-gray-700">
              {{ optional($item->user)->name ?? 'Sin asignar' }}
            </div>
          @endif
          <div id="customer_status_{{$item->id}}"></div>
        </td>
        @endif
        <td class="px-4 py-4 align-top space-y-2">
          @php
            $lastAction = $item->actions()
              ->where(function($q) {
                  $q->whereNull('due_date')->orWhereNotNull('delivery_date');
              })
              ->orderBy('created_at', 'desc')
              ->first();
            $nextAction = $item->actions()
              ->whereNull('delivery_date')
              ->reorder()
              ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
              ->orderBy('due_date')
              ->orderByDesc('created_at')
              ->first();
            $daysWithoutFollowup = null;
            if ($lastAction) {
              $daysWithoutFollowup = \Carbon\Carbon::parse($lastAction->created_at)->diffInDays(\Carbon\Carbon::now());
            }
            $badgeText = null;
            $badgeClass = 'badge-secondary';
            if ($nextAction && $nextAction->due_date) {
              $due = \Carbon\Carbon::parse($nextAction->due_date);
              if ($due->isPast() && !$due->isToday()) {
                $badgeText = 'Vencido';
                $badgeClass = 'badge-danger';
              } elseif ($due->isToday()) {
                $badgeText = 'Hoy';
                $badgeClass = 'badge-danger';
              } elseif ($due->isTomorrow()) {
                $badgeText = 'Mañana';
                $badgeClass = 'badge-warning';
              }
            }
          @endphp
          @if($lastAction)
            <div class="text-muted small mb-1">{{ $lastAction->created_at }}</div>
            <div>{{ $lastAction->getDescription() }}</div>
          @endif
        </td>
        <td class="px-4 py-4 align-top space-y-2">
          @if($nextAction)
            <div class="text-muted small d-flex align-items-center" style="gap:6px;">
              <span>{{ $nextAction->due_date ?? 'Sin fecha' }}</span>
              @if($badgeText && $nextAction->due_date)
                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
              @endif
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
              <span>{{ $nextAction->getDescription() }}</span>
              <input type="checkbox"
                data-toggle="modal"
                data-target="#completeActionModal-{{$item->id}}"
                class="w-6 h-6 rounded-full border-2 border-blue-500 text-blue-600 focus:ring-2 focus:ring-blue-400 checked:bg-blue-600 checked:border-transparent"
                onclick="this.checked=false"
                aria-label="Marcar como hecha">
            </div>
          @else
            <span class="text-muted">Sin acción pendiente</span>
          @endif
        </td>
        <td class="px-4 py-4 align-top space-y-2">
          @if(!is_null($daysWithoutFollowup))
            <span class="font-semibold">{{ (int) $daysWithoutFollowup }}</span>
          @else
            <span class="text-muted">-</span>
          @endif
        </td>
        <td class="px-4 py-4 align-top text-right">
          <div class="d-inline-flex align-items-center" style="gap: 8px;">
            <button class="d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 1px solid #cbd5e0; color: #6b7280; font-size: 18px; line-height: 1;"
              data-toggle="modal" data-target="#addActionModal-{{$item->id}}" aria-label="Agregar seguimiento">
              +
            </button>
            @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 10)
              <a href="customers/{{ $item->id }}/destroy" class="btn btn-sm btn-danger" title="Eliminar">
                <span class="fa fa-trash-o" aria-hidden="true"></span>
              </a>
            @endif
          </div>
        </td>


        <!--
                  <td>
                  <a href="customers/{{ $item->id }}/show"><span class="btn btn-sm btn-success fa fa-eye fa-3" aria-hidden="true" title="Consultar"></span></a>
                    <a href="customers/{{ $item->id }}/edit"><span class="btn btn-sm btn-warning fa fa-pencil-square-o" aria-hidden="true" title="Editar"></span></a>
                    {{-- Delete --}}
                    <a href="customers/{{ $item->id }}/destroy"><span class="btn btn-sm btn-danger fa fa-trash-o" aria-hidden="true" title="Eliminar"></span></a>
                  </td>
                  </td>
                -->
      </tr>
      <?php $count++;
      $lastStatus = $item->status_id;
      ?>
      @endforeach
      <?php $count--; ?>

    </tbody>
    <?php

    if (isset($item->points)) {
      $total_tools += $item->points;
    }
    $count++;
    ?>
    </table>
  </div>
  @else
    <div class="p-4 text-sm text-gray-600">Sin registros.</div>
  @endif
</div>
{{-- {{$model->links()}} --}}
{{ $model->appends(request()->input())->links() }}
<div>
  {{-- Registro {{ $model->currentPage()*$model->perPage() - ( $model->perPage() - 1 ) }} a {{ $model->currentPage()*$model->perPage()}} de {{ $model->total()}} --}}

</div>
<script>
  (function() {
    function showCopyNotice(message) {
      var $note = $('#notication_area');
      var $text = $('#notication_area_text');
      if (!$note.length) return;
      $text.text(message || 'Copiado');
      $note.show();
      setTimeout(function() { $note.fadeOut(); }, 2000);
    }

    $(document).on('click', '.copy-phone', function() {
      var phone = $(this).data('phone');
      if (!phone) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(phone.toString())
          .then(function() { showCopyNotice('Teléfono copiado'); })
          .catch(function() { showCopyNotice('No se pudo copiar'); });
      } else {
        var $tmp = $('<textarea>').val(phone.toString()).appendTo('body');
        $tmp[0].select();
        try { document.execCommand('copy'); showCopyNotice('Teléfono copiado'); }
        catch (e) { showCopyNotice('No se pudo copiar'); }
        $tmp.remove();
      }
    });
  })();
</script>
@endsection
