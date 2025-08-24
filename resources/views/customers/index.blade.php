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
(function() {
  // Origen para el rango "Máximo" (ajústalo si prefieres 1970-01-01 o 1000-01-01)
  const ORIGEN_MAXIMO = moment('1900-01-01', 'YYYY-MM-DD');

  // --- Helpers ---
  function setHidden(start, end) {
    // Backend: YYYY-MM-DD
    $('#from_date').val(start.format('YYYY-MM-DD'));
    $('#to_date').val(end.format('YYYY-MM-DD'));
    // Visible: DD-MM-YYYY
    $('#reportrange_input').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
  }
  function updateFields(start, end){ setHidden(start, end); }

  function getInitialStart(){
    const fd = $('#from_date').val();
    // Si no hay filtros → simula tus “últimos 90 días”
    return fd ? moment(fd, 'YYYY-MM-DD') : moment().subtract(89,'days');
  }
  function getInitialEnd(){
    const td = $('#to_date').val();
    return td ? moment(td, 'YYYY-MM-DD') : moment();
  }

  // --- Inicializa DateRangePicker sobre el INPUT ---
  $('#reportrange_input').daterangepicker({
    startDate: getInitialStart(),
    endDate:   getInitialEnd(),
    maxDate: moment(),
    alwaysShowCalendars: true,
    opens: 'right',
    autoUpdateInput: false, // nosotros llenamos el input
    locale: {
      format: 'DD-MM-YYYY',
      applyLabel: "Aplicar",
      cancelLabel: "Cancelar",
      daysOfWeek: ["Do","Lu","Ma","Mi","Ju","Vi","Sa"],
      monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
      firstDay: 1
    },
    ranges: {
      'Hoy': [moment(), moment()],
      'Ayer': [moment().subtract(1,'day'), moment().subtract(1,'day')],
      'Últimos 7 días': [moment().subtract(6,'days'), moment()],
      'Últimos 10 días': [moment().subtract(9,'days'), moment()],
      'Últimos 30 días': [moment().subtract(29,'days'), moment()],
      'Esta semana': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
      'Semana pasada': [moment().subtract(1,'week').startOf('isoWeek'), moment().subtract(1,'week').endOf('isoWeek')],
      'Este mes': [moment().startOf('month'), moment().endOf('month')],
      'Mes anterior': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
      'Máximo': [ORIGEN_MAXIMO, moment()]
    }
  }, updateFields)
  .on('apply.daterangepicker', function(ev, picker){
    updateFields(picker.startDate, picker.endDate);
    // Si quieres aplicar al instante:
    // document.getElementById('filter_form').submit();
  })
  .on('cancel.daterangepicker', function(){
    clearRange();
  });

  // Si la vista llegó con valores en request, puebla el input
  if ($('#from_date').val() && $('#to_date').val()) {
    setHidden(moment($('#from_date').val(),'YYYY-MM-DD'), moment($('#to_date').val(),'YYYY-MM-DD'));
  }

  // --- Edición manual del input (DD-MM-YYYY - DD-MM-YYYY) ---
  $('#reportrange_input').on('blur keydown', function(e){
    if (e.type==='blur' || e.key==='Enter') {
      const raw = $(this).val().trim();
      const parts = raw.split('-').map(s=>s.trim());
      // Esperado: "DD-MM-YYYY - DD-MM-YYYY" → tras split simple por "-", reconstruimos
      // Mejor: dividir por " - "
      const bySep = raw.split(' - ');
      if (bySep.length === 2) {
        const s = moment(bySep[0], 'DD-MM-YYYY', true);
        const e2 = moment(bySep[1], 'DD-MM-YYYY', true);
        if (s.isValid() && e2.isValid() && !e2.isBefore(s)) {
          setHidden(s, e2);
        }
      }
      if (e.key==='Enter') e.preventDefault();
    }
  });

  // --- Enlaces rápidos externos (botones) ---
  window.setRange = function(days) {
    const end = moment();
    const start = moment().subtract(days-1,'days'); // incluye hoy
    setHidden(start, end);
    return false;
  };
  window.setThisWeek = function(){
    setHidden(moment().startOf('isoWeek'), moment().endOf('isoWeek'));
    return false;
  };
  window.setLastWeek = function(){
    setHidden(moment().subtract(1,'week').startOf('isoWeek'), moment().subtract(1,'week').endOf('isoWeek'));
    return false;
  };
  window.setCurrentMonth = function(){
    setHidden(moment().startOf('month'), moment().endOf('month'));
    return false;
  };
  window.setLastMonth = function(){
    setHidden(moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month'));
    return false;
  };
  window.setMaximo = function(){
    setHidden(ORIGEN_MAXIMO, moment());
    return false;
  };
  window.clearRange = function(){
    $('#from_date').val('');
    $('#to_date').val('');
    $('#reportrange_input').val('');
    return false;
  };
})();
</script>
@endpush
