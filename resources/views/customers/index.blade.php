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


@endsection

@section('list_footer')
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
  // Navegación: al hacer click en la tarjeta, cargar detalle via AJAX (excepto click en asesor)
  $(document).on('click', '.customer-card', function(e) {
    const isAdvisor = $(e.target).closest('.advisor-link').length > 0;
    if (isAdvisor) {
      return;
  }
  e.preventDefault();
  e.stopPropagation();
    const url = $(this).data('url');
    if (url) {
      const $side = $('#side_content');
      $side.addClass('loading');
      $.get(url, function(resp) {
        const $html = $('<div>').html(resp);
        const newContent = $html.find('#side_content').html();
        if (newContent) {
          $side.html(newContent);
          if (window.initCustomerTags) {
            window.initCustomerTags($('#side_content'));
          }
        if (window.initCustomerNotes) {
          window.initCustomerNotes($('#side_content'));
        }
        if (window.initCustomerNotesModalSide) {
          window.initCustomerNotesModalSide($('#side_content'));
        }
      } else {
        window.location.href = url;
      }
      }).fail(function() {
        window.location.href = url;
    }).always(function() {
      $side.removeClass('loading');
    });
  }
  });

  (function() {
    // Notas inline (contenteditable + auto-guardado)
    window.initCustomerNotes = function(scope) {
      var $scope = scope ? $(scope) : $(document);
      $scope.find('.notes-editable').each(function() {
        var $notes = $(this);
        if ($notes.data('notes-bound')) return;
        $notes.data('notes-bound', true);

        var saveUrl = $notes.data('save-url');
        var $feedback = $scope.find('#customer-notes-side-feedback');
        var lastValue = ($notes.text() || '').trim();

        function setActive(active) {
          $notes.toggleClass('notes-editable--active', active);
        }

        $notes.on('focus', function() {
          setActive(true);
        });

        $notes.on('blur', function() {
          setActive(false);
          var current = ($notes.text() || '').trim();
          if (current === lastValue) return;
          if (!saveUrl) return;

          if ($feedback.length) {
            $feedback.text('Guardando...');
          }
          $.ajax({
            url: saveUrl,
            method: 'POST',
            data: {
              notes: current,
              _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(resp) {
              lastValue = resp && typeof resp.notes !== 'undefined' ? resp.notes : current;
              $notes.text(lastValue);
              if ($feedback.length) {
                $feedback.text('Notas guardadas');
              }
            },
            error: function() {
              if ($feedback.length) {
                $feedback.text('No se pudo guardar las notas');
              }
            }
          });
        });
      });
    };
    window.initCustomerNotesModalSide = function(scope) {
      var $scope = scope ? $(scope) : $(document);
      var $display = $scope.find('#customer-notes-side');
      if (!$display.length) return;
      var $feedback = $scope.find('#customer-notes-side-feedback');
      var saveUrl = $display.data('save-url');
      var $modal = $scope.find('#notesModalSide');
      var $textarea = $scope.find('#customer-notes-side-textarea');
      var $btn = $scope.find('#customer-notes-side-edit');
      var $save = $scope.find('#customer-notes-side-save');

      // Evitar doble binding
      if ($btn.data('notes-bound')) return;
      $btn.data('notes-bound', true);

      $btn.on('click', function() {
        console.log('Notas lado abrir modal');
        $textarea.val($display.text().replace(/\u00A0/g, ' ').replace(/\s+$/,''));
        $modal.modal('show');
      });

      $save.on('click', function() {
        var current = ($textarea.val() || '').trim();
        console.log('Notas lado guardar');
        if ($feedback.length) $feedback.text('Guardando...');
        $.ajax({
          url: saveUrl,
          method: 'POST',
          data: {
            notes: current,
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function(resp) {
            var newText = resp && typeof resp.notes !== 'undefined' ? resp.notes : current;
            $display.html(newText.replace(/\n/g, '<br>'));
            if ($feedback.length) $feedback.text('Notas guardadas');
            $modal.modal('hide');
          },
          error: function() {
            if ($feedback.length) $feedback.text('No se pudo guardar las notas');
          }
        });
      });
    };
    $(function() {
      if (window.initCustomerNotes) {
        window.initCustomerNotes($('#side_content'));
      }
      if (window.initCustomerNotesModalSide) {
        window.initCustomerNotesModalSide($('#side_content'));
      }
    });

    // ======== CONFIG ========
  const ORIGEN_MAXIMO = moment('1900-01-01', 'YYYY-MM-DD'); // cambia si prefieres 1970-01-01
  const $quickForm = $('#mini_filter_form');
  const $advForm   = $('#filter_form');
  const $qSearch   = $('#search');       // input del buscador rápido
  const $aSearch   = $('#search_adv');   // input del filtro avanzado
  const $from      = $('#from_date');
  const $to        = $('#to_date');
  const $inputDR   = $('#reportrange_input');

  // ======== SYNC BUSCADORES ========
  // 1) Espejar tipeo entre rápido y avanzado
  $qSearch.on('input', function(){ $aSearch.val(this.value); });
  $aSearch.on('input', function(){ $qSearch.val(this.value); });

  // 2) Cuando envían el buscador rápido, en realidad enviamos el form avanzado
  $quickForm.on('submit', function(e){
    e.preventDefault();
    // Garantiza que el avanzado lleve el mismo texto
    $aSearch.val($qSearch.val());
    $advForm.trigger('submit');
  });

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
})();
</script>
@endpush
