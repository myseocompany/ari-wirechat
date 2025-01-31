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
</style>




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
  @include('customers.filter')
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

    <?php $lastStatus = -1 ?>
    <tbody>
      <?php $count = 1; ?>
      @foreach($model as $item)

      <tr>
        <td>
          {{$count}}
        </td>
        <td>
          <div><a href="/customers/{{ $item->id }}/show">{{$item->name}}</a>

            @if(isset($item->scoring_interest) && ($item->scoring_interest>0))
            <span style="background-color: #ccc; border-radius: 50%; width: 25px; height: 25px; text-align: center; color: white; align-items: left; font-size: 12px; padding: 2px;">{{$customer->scoring_interest}}</span>
            @endif
            <div class="col-md-12 scoring">
              <div class="stars-outer">
                <div class="stars-inner"></div>
                <script type="text/javascript">
                  starTotal = 4;
                  starPercentage = ({
                        {
                          $item - > getScoringToNumber()
                        }
                      }
                      / starTotal) * 100;
                      starPercentageRounded = (Math.round(starPercentage / 10) * 10) + '%'; console.log(starPercentageRounded); $('.stars-inner').width(starPercentageRounded);
                </script>
              </div>
            </div>
            @if(isset($item->email))<div>{{$item->email}}</div>@endif
            @if(isset($item->phone))<div><a @if(isset($customer->phone)) href="https://wa.me/{{ clearWP($customer->getPhone()) }}" @else href="" @endif target="_empty">{{$customer->phone}}</a></div>@endif
            @if(isset($item->phone2))<div><a @if(isset($customer->phone2)) href="https://wa.me/{{ clearWP($customer->getPhone()) }}" @else href="" @endif target="_empty">{{$customer->phone2}}</a></div>@endif


        </td>

        <td>
          <div class="customer_dates"> {{$item->created_at}} </div>
        <td>
          <!--  
*
*    Combo de usuarios
*
-->
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
          <div id="customer_status_{{$item->id}}"></div>







        <td>


          @if(isset($item->status_id)&&($item->status_id!="")&&(!is_null($item->status)))
          <span class="customer_status" style="background-color: {{$item->status->color}}">{{$item->status->name}}</span>
          @endif

        </td>
        <td>@if(($item->getLastUserAction()!=null)){{$item->getLastUserAction()->created_at}}@endif</td>
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