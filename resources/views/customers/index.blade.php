@extends('layouts.tailwind')



@if(isset($phase))
  @section('title', $phase->name)
@else
  @section('title', 'Leads') 
@endif



@include('customers.partials.notes_script')



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




@section('content')
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

  <div class="d-flex flex-wrap align-items-center justify-content-between col-12">
    <div>
      Registro <strong>{{ $model->firstItem() }}</strong> a 
      <strong>{{ $model->lastItem() }}</strong> de 
      <strong>{{ $model->total() }}</strong>
    </div>
    <button class="btn btn-primary btn-sm mt-2 mt-sm-0" type="button" data-filter-open>
      Filtros
    </button>
  </div>

  @include('customers.index_partials.groupbar', ['customersGroup' => $customersGroup])

  <div>@if(isset($sum_g)) TOTAL {{$sum_g}} @endif </div>

  <script type="text/javascript">
    var ratings = [];
  </script>
  <?php $cont=0; ?>

  @foreach($model as $item)
    @include('customers.index_partials.card', ['item' => $item])            
  @endforeach

  @if($model->count() === 0)
    <div class="alert alert-info mt-3">
      No se encontraron prospectos con esos filtros.
    </div>
  @endif

  <style>
    ul.pagination {
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 15px;
      padding: 10px;
    }
  </style>

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
  <div class="row">
    <div class="col-12 d-flex justify-content-center">
      {!! $model->appends(request()->input())->links() !!}
    </div>
  </div>

  <div id="customer_overlay" class="customer-overlay" aria-hidden="true">
    <div class="customer-overlay__backdrop" data-customer-overlay-close></div>
    <div class="customer-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="customer_overlay_title">
      <div class="customer-overlay__header">
        <h2 id="customer_overlay_title">Cliente</h2>
        <button class="customer-overlay__close" type="button" data-customer-overlay-close aria-label="Cerrar detalle">&times;</button>
      </div>
      <div class="customer-overlay__body" id="customer_overlay_body"></div>
    </div>
  </div>

  @hasSection('filter')
    <div id="filter_overlay" class="filter-overlay" aria-hidden="true">
      <div class="filter-overlay__backdrop" data-filter-close></div>
      <div class="filter-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="filter_overlay_title">
        <div class="filter-overlay__header">
          <h2 id="filter_overlay_title">Filtros</h2>
          <button class="filter-overlay__close" type="button" data-filter-close aria-label="Cerrar filtros">&times;</button>
        </div>
        <div class="filter-overlay__body">
          @yield('filter')
        </div>
      </div>
    </div>
  @endif
@endsection

@section('filter')
  @include('customers.index_partials.side_filter')
@endsection

@push('scripts')
<script>
  // Navegación: al hacer click en la tarjeta, cargar detalle via AJAX (excepto click en asesor)
  $(document).on('click', '.customer-card', function(e) {
    const isAdvisor = $(e.target).closest('.advisor-link').length > 0;
    const isOverlayLink = $(e.target).closest('.customer-overlay-link').length > 0;
    if (isAdvisor || isOverlayLink) {
      return;
    }
    e.preventDefault();
    e.stopPropagation();
    const url = $(this).data('url');
    if (url) {
      window.location.href = url;
    }
  });

  (function () {
    const overlay = document.getElementById('customer_overlay');
    const overlayBody = document.getElementById('customer_overlay_body');
    if (!overlay || !overlayBody) {
      return;
    }
    const openOverlay = function () {
      overlay.setAttribute('aria-hidden', 'false');
      document.body.classList.add('customer-overlay-open');
    };
    const closeOverlay = function () {
      overlay.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('customer-overlay-open');
      overlayBody.innerHTML = '';
    };
    overlay.addEventListener('click', function (event) {
      if (event.target && event.target.hasAttribute('data-customer-overlay-close')) {
        closeOverlay();
      }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeOverlay();
      }
    });
    $(document).on('click', '.customer-overlay-link', function (event) {
      event.preventDefault();
      event.stopPropagation();
      const url = $(this).data('url') || $(this).attr('href');
      if (!url) {
        return;
      }
      openOverlay();
      overlayBody.innerHTML = '<div class="text-center py-5">Cargando...</div>';
      $.get(url, function (resp) {
        const $html = $('<div>').html(resp);
        const newContent = $html.find('#customer_show_content').html();
        if (newContent) {
          overlayBody.innerHTML = newContent;
          if (window.initCustomerTags) {
            window.initCustomerTags($('#customer_overlay_body'));
          }
          if (window.initNotesEditors) {
            window.initNotesEditors($('#customer_overlay_body'));
          }
        } else {
          window.location.href = url;
        }
      }).fail(function () {
        window.location.href = url;
      });
    });
  })();

  (function() {
    // Notas: inicialización unificada se maneja en customers.partials.notes_script
    // ======== CONFIG ========
  const ORIGEN_MAXIMO = moment('1900-01-01', 'YYYY-MM-DD'); // cambia si prefieres 1970-01-01
  const $filterForm = $('#filter_form');
  const $search     = $('#search');
  const $from      = $('#from_date');
  const $to        = $('#to_date');
  const $inputDR   = $('#reportrange_input');

  if (! $filterForm.length || ! $search.length) {
    return;
  }

  // ======== DATE RANGE PICKER ========
  function setHidden(start, end, updateWidget = true) {
    // Backend: YYYY-MM-DD
    $from.val(start.format('YYYY-MM-DD'));
    $to.val(end.format('YYYY-MM-DD'));
    // Visible: DD-MM-YYYY
    $inputDR.val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
    if (updateWidget && $inputDR.data('daterangepicker')) {
      $inputDR.data('daterangepicker').setStartDate(start);
      $inputDR.data('daterangepicker').setEndDate(end);
    }
  }

  function getInitialStart(){
    const fd = $from.val();
    return fd ? moment(fd, 'YYYY-MM-DD') : moment().subtract(89,'days'); // default 90 días
  }
  function getInitialEnd(){
    const td = $to.val();
    return td ? moment(td, 'YYYY-MM-DD') : moment();
  }

  // Inicializa el DRP sobre el INPUT editable
  if ($inputDR.length) {
    $inputDR.daterangepicker({
      startDate: getInitialStart(),
      endDate:   getInitialEnd(),
      maxDate: moment(),
      parentEl: '.filter-overlay__panel',
      opens: 'right',
      autoUpdateInput: false, // nosotros seteamos el input
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
        'Últimos 7 días': [moment().subtract(6,'days'), moment()],   // incluye hoy
        'Últimos 10 días': [moment().subtract(9,'days'), moment()],
        'Últimos 30 días': [moment().subtract(29,'days'), moment()],
        'Esta semana': [moment().startOf('isoWeek'), moment().endOf('isoWeek')],
        'Semana pasada': [moment().subtract(1,'week').startOf('isoWeek'), moment().subtract(1,'week').endOf('isoWeek')],
        'Este mes': [moment().startOf('month'), moment().endOf('month')],
        'Este año': [moment().startOf('year'), moment().endOf('year')],
        'Mes anterior': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
        'Máximo': [ORIGEN_MAXIMO, moment()]
      }
    }, function(start, end){
      setHidden(start, end, false);
      // Opcional: enviar al aplicar
      // $advForm.trigger('submit');
    })
    .on('apply.daterangepicker', function(ev, picker){
      setHidden(picker.startDate, picker.endDate, false);
      // $advForm.trigger('submit');
    })
    .on('cancel.daterangepicker', function(){
      clearRange();
    });

    // Si venía con rango del request, puebla el input visible
    if ($from.val() && $to.val()) {
      setHidden(moment($from.val(),'YYYY-MM-DD'), moment($to.val(),'YYYY-MM-DD'));
    }
  }

  // Soporta edición manual: "DD-MM-YYYY - DD-MM-YYYY"
  $inputDR.on('blur keydown', function(e){
    if (e.type==='blur' || e.key==='Enter') {
      const val = $(this).val().trim();
      const parts = val.split(' - ');
      if (parts.length === 2) {
        const s = moment(parts[0], 'DD-MM-YYYY', true);
        const en = moment(parts[1], 'DD-MM-YYYY', true);
        if (s.isValid() && en.isValid() && !en.isBefore(s)) {
          setHidden(s, en);
        }
      }
      if (e.key==='Enter') e.preventDefault();
    }
  });

  // ======== Enlaces rápidos (externos) ========
  window.setRange = function(days) {
    const end = moment();
    const start = moment().subtract(days-1,'days'); // inclusivo
    setHidden(start, end);
    return false;
  };
  window.setThisWeek = function() {
    setHidden(moment().startOf('isoWeek'), moment().endOf('isoWeek'));
    return false;
  };
  window.setLastWeek = function() {
    setHidden(moment().subtract(1,'week').startOf('isoWeek'), moment().subtract(1,'week').endOf('isoWeek'));
    return false;
  };
  window.setCurrentMonth = function() {
    setHidden(moment().startOf('month'), moment().endOf('month'));
    return false;
  };
  window.setLastMonth = function() {
    setHidden(moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month'));
    return false;
  };
  window.setMaximo = function() {
    setHidden(ORIGEN_MAXIMO, moment());
    return false;
  };
  window.clearRange = function() {
    $from.val('');
    $to.val('');
    $inputDR.val('');
    return false;
  };

  function copyPhoneToClipboard(phone, onSuccess, onError) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(phone).then(onSuccess).catch(onError);
      return;
    }
    const $tmp = $('<textarea>').val(phone).appendTo('body');
    $tmp.select();
    try {
      document.execCommand('copy');
      onSuccess();
    } catch (err) {
      onError(err);
    }
    $tmp.remove();
  }

  $(document).on('click', '.copy-phone', function (event) {
    event.preventDefault();
    event.stopPropagation();
    const phone = $(this).data('phone');
    if (!phone) {
      return;
    }
    copyPhoneToClipboard(phone.toString(), function () {
      alert('Teléfono copiado');
    }, function () {
      alert('No se pudo copiar el teléfono');
    });
  });
})();
</script>
@hasSection('filter')
  <script>
    (function () {
      const overlay = document.getElementById('filter_overlay');
      if (!overlay) {
        return;
      }
      const openOverlay = function () {
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('filter-overlay-open');
      };
      const closeOverlay = function () {
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('filter-overlay-open');
      };
      document.addEventListener('click', function (event) {
        if (event.target.closest('[data-filter-open]')) {
          event.preventDefault();
          openOverlay();
          return;
        }
        if (event.target.closest('[data-filter-close]')) {
          event.preventDefault();
          closeOverlay();
        }
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          closeOverlay();
        }
      });
    })();
  </script>
@endif
@endpush
