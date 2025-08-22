@extends('layouts.agile')



@if(isset($phase))
  @section('title', $phase->name)
@else
  @section('title', 'Leads') 
@endif



@section('list')
<div class="col-12">
  Registro <strong>{{ $model->firstItem() }}</strong> a 
  <strong>{{ $model->lastItem() }}</strong> de 
  <strong>{{ $model->total() }}</strong>
</div>
 

 <script type="text/javascript">
   var ratings = [];
 </script>
 <?php $cont=0; ?>

  @foreach($model as $item)
    @include('customers.index_partials.card', ['item' => $item])            
                  
  @endforeach

  <style>
    ul.pagination {
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 15px;
    padding: 10px;
}

  </style>
<div class="row">
  <div class="col-12 d-flex justify-content-center">
      {!! $model->appends(request()->input())->links() !!}
  </div>
</div>


@endsection

@section('filter')
 @include('customers.index_partials.side_filter')
@endsection



<?php 
  function requestToStr($request){
    $str = "?";
    $url = $request->fullUrl();
    $parsedUrl = parse_url($url);
    
    if(isset($parsedUrl['query'] ))
      $str .= $parsedUrl['query']; 

    return $str; 
  }
 ?>




{{-- Alertas --}}
  @if (session('status'))
          <div class="alert alert-primary alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
            {!! html_entity_decode(session('status')) !!}
        </div>
  @endif
    @if (session('statusone'))
          <div class="alert alert-success alert-dismissible" role="alert">
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
@section('content')
  @include('customers.index_partials.groupbar', ['customersGroup' => $customersGroup])


<div>@if(isset($sum_g)) TOTAL {{$sum_g}} @endif </div>

<style>
@media (max-width: 576px) {
    .customer_name {
        display: flex;
        flex-direction: column;
    }
    .customer_description {
        display: flex;
        flex-direction: column;
    }
}

</style>

  @include('customers.index_partials.side_show')
@endsection

@push('scripts')
<script>
$(function() {
  function updateFields(start, end) {
    $('#from_date').val(start.format('YYYY-MM-DD'));
    $('#to_date').val(end.format('YYYY-MM-DD'));
    $('#reportrange span').html(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
  }

  $('#reportrange').daterangepicker({
    opens: 'right',
    locale: {
      format: 'DD-MM-YYYY',
      applyLabel: "Aplicar",
      cancelLabel: "Cancelar",
      daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
      monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
        "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
      ],
      firstDay: 1
    },
    ranges: {
       'Hoy': [moment(), moment()],
       'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
       'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
       'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
       'Este mes': [moment().startOf('month'), moment().endOf('month')],
       'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    }
  }, updateFields);

  // Inicializa con los valores si están definidos
  @if($request->from_date && $request->to_date)
    updateFields(moment("{{ $request->from_date }}"), moment("{{ $request->to_date }}"));
  @endif
});

// Links rápidos
function setRange(days) {
  var end = moment();
  var start = moment().subtract(days - 1, 'days');
  $('#from_date').val(start.format('YYYY-MM-DD'));
  $('#to_date').val(end.format('YYYY-MM-DD'));
  $('#reportrange span').html(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
}

function clearRange() {
  $('#from_date').val('');
  $('#to_date').val('');
  $('#reportrange span').html('Seleccionar rango');
}
</script>
@endpush
