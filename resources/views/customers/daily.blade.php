@extends('layout')

@section('title', 'Clientes')

<?php function clearWP($str)
{
  $str = trim($str);
  $str = str_replace("+", "", $str);
  return $str;
} ?>
<!-- MAQUIEMPANADAS -->@section('content')
<h1>Clientes</h1>
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
function toggleDateInput(id) {
  const container = document.getElementById('dateInputContainer_' + id);
  const checkbox = document.getElementById('toggleDate_' + id);
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

<div><a style="color: #4178be;" href="customers/create">Crear
    <i class="fa fa-plus" aria-hidden="true"></i>
  </a> | <a href="/leads/excel{{ requestToStr($request) }}">Excel</a>
  | <a href="/import/">Importar</a>
</div>
<br>
{{-- obteber datos del tiempo --}}


<div>
  @include('customers.filter_daily')
</div>



<div>
  <?php $cont_group = 0; ?>
  @if($customersGroup->count()!=0)


  @foreach($customersGroup as $item)
  <?php if ($item->count > 0) {
    $cont_group++;
  } ?>
  @endforeach
  <ul class="groupbar bb_hbox">

    @foreach($customersGroup as $item)
    @if($item->count != 0)
    <li class="groupBarGroup" style="background-color: {{$item->color}}; width: <?php
                                                                                if ($cont_group != 0) {
                                                                                  echo 100 / $cont_group;
                                                                                }
                                                                                ?>%">
      <h3>{{$item->count}}</h3>

      <div><a href="#" onclick="changeStatus({{$item->id}})">{{$item->name}}</a></div>
    </li>
    @endif
    @endforeach
  </ul>
  @else
  Sin Estados
  @endif
</div>

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
<div class="">
  @if (count($model) > 0)
  <table class="table table-striped">
    <thead class="thead-light">
      <tr>
        <th>Cliente</th>
        @if (Auth::user()->role_id !== 2)
          <th>Asesor</th>
        @endif
        <th>Gestion</th>
        <th>Última acción</th>
        <th>Próxima acción</th>
        @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 10)
          <th></th>
        @endif
      </tr>
    </thead>

    <?php $lastStatus = -1 ?>
    <tbody>
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

      <tr>
        <td class="d-flex align-items-center">
          <div class="customer-circle small-circle mr-2" style="background-color: {{ $item->getStatusColor() ?? '#DFAAFF' }}">
            {{ $item->getInitials() }}
          </div>
          <div>
            <div><a href="/customers/{{ $item->id }}/show">{{$item->name}}</a></div>

            @if(isset($item->scoring_interest) && ($item->scoring_interest>0))
            <span style="background-color: #ccc; border-radius: 50%; width: 25px; height: 25px; text-align: center; color: white; align-items: left; font-size: 12px; padding: 2px;">{{$customer->scoring_interest}}</span>
            @endif
            <div class="col-md-12 scoring">
              <div class="stars-outer">
                <div class="stars-inner" style="width: {{ ($item->getScoringToNumber()/4)*100 }}%"></div>
              </div>
            </div>
            @if(isset($item->email))<div>{{$item->email}}</div>@endif
            @if(isset($item->phone))<div><a @if(isset($customer->phone)) href="https://wa.me/{{ clearWP($customer->getPhone()) }}" @else href="" @endif target="_empty">{{$customer->phone}}</a></div>@endif
            @if(isset($item->phone2))<div><a @if(isset($customer->phone2)) href="https://wa.me/{{ clearWP($customer->getPhone()) }}" @else href="" @endif target="_empty">{{$customer->phone2}}</a></div>@endif



          </div>
        </td>

        @if (Auth::user()->role_id !== 2)
        <td>
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
            <select name="user_id" class="custom-select" id="user_id_{{$item->id}}" onchange="updateUser({{$item->id}});">
              <option value="">Usuario...</option>
              <option value="null">Sin asignar</option>
              @foreach($users as $user)
              <option value="{{$user->id}}" @if ($item->user_id == $user->id) selected="selected" @endif>
                <?php echo substr($user->name, 0, 10); ?>

              </option>
              @endforeach
            </select>
          @else
            <div class="form-control-plaintext">
              {{ optional($item->user)->name ?? 'Sin asignar' }}
            </div>
          @endif
          <div id="customer_status_{{$item->id}}"></div>
        </td>
        @endif

        <td>
          @if(isset($item->status_id)&&($item->status_id!="")&&(!is_null($item->status)))
            <span class="customer_status" style="background-color: {{$item->status->color}}">{{$item->status->name}}</span>
          @endif
          <div class="mt-2">
            @if(isset($allTags) && $allTags->count())
              <form
                method="POST"
                action="{{ route('customers.tags.update', $item) }}"
                class="customer-tags-form"
                data-tags-feedback="#tags-feedback-{{$item->id}}">
                @csrf
                <div class="d-flex flex-wrap" style="gap: 6px;">
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
                      <span class="tag-swatch" style="border-color: {{ $checked ? $color : '#e2e8f0' }}; background-color: {{ $checked ? $color : 'transparent' }};"></span>
                      <span>{{ $tagOption->name }}</span>
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
        </td>
        <td>
          @php
            $lastAction = $item->actions()
              ->where(function($q) {
                  $q->whereNull('due_date')->orWhereNotNull('delivery_date');
              })
              ->orderBy('created_at', 'desc')
              ->first();
          @endphp
          @if($lastAction)
            <div class="text-muted small mb-1">{{ $lastAction->created_at }}</div>
            <div><strong>{{ optional($lastAction->type)->name }}:</strong> {{ $lastAction->getDescription() }}</div>
          @endif
          <button class="btn btn-sm btn-outline-primary mt-2" data-toggle="modal" data-target="#addActionModal-{{$item->id}}">
            Seguimiento
          </button>
        </td>
        <td>
          @php $nextAction = $item->actions()->whereNull('delivery_date')->orderBy('due_date')->first(); @endphp
          @if($nextAction && $nextAction->due_date)
            <div class="text-muted small">{{ $nextAction->due_date }}</div>
            <div><strong>{{ optional($nextAction->type)->name }}:</strong> {{ $nextAction->getDescription() }}</div>
          @else
            <span class="text-muted">Sin próxima acción</span>
          @endif
        </td>
        @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 10)
        <td>
          {{-- Delete --}}
          <a href="customers/{{ $item->id }}/destroy"><span class="btn btn-sm btn-danger fa fa-trash-o" aria-hidden="true" title="Eliminar"></span></a>
        </td>
        @endif


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

  @endif
  {{-- {{$model->links()}} --}}
  {{ $model->appends(request()->input())->links() }}
  <div>
    {{-- Registro {{ $model->currentPage()*$model->perPage() - ( $model->perPage() - 1 ) }} a {{ $model->currentPage()*$model->perPage()}} de {{ $model->total()}} --}}

  </div>
</div>
@endsection
