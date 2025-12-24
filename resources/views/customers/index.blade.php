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

@push('styles')
  <x-design.styles />
  <style>
    ul.pagination {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.75rem;
    }
    ul.pagination .page-item a,
    ul.pagination .page-item span {
      border-radius: 9999px;
      padding: 0.4rem 0.85rem;
      border: 1px solid #e2e8f0;
      background: #ffffff;
      color: #1c2640;
      font-size: 0.85rem;
      font-weight: 600;
    }
    ul.pagination .page-item.active span {
      background: #ff5c5c;
      border-color: #ff5c5c;
      color: #ffffff;
    }
    .time-filter-card {
      background: #fff;
      border-radius: 14px;
      padding: .5rem;
      border: 1px solid rgba(17, 19, 34, .1);
      display: flex;
      align-items: center;
      gap: .75rem;
      box-shadow: 0 20px 45px rgba(15, 23, 42, .05);
      flex-wrap: wrap;
    }
    .quick-range-pills {
      display: flex;
      flex-wrap: wrap;
      gap: .4rem;
      align-items: center;
      flex: 1 1 auto;
    }
    .quick-range-pills .pill {
      background: #fff;
      padding: .4rem 1rem;
      border-radius: 999px;
      font-weight: 500;
      color: #475467;
      transition: background .2s ease, color .2s ease, box-shadow .2s ease;
    }
    .quick-range-pills .pill.active {
      background: #111322;
      color: #fff;
      border-color: #111322;
      box-shadow: 0 12px 24px rgba(17, 19, 34, .25);
    }
    .quick-range-pills .pill:not(.active) {
      border: none;
    }
    .date-picker-pill {
      display: flex;
      align-items: center;
      gap: .4rem;
      border: 1px solid #e4e7ec;
      border-radius: 999px;
      padding: .35rem .9rem;
      background: #fff;
    }
    .date-picker-pill .form-control {
      border: none;
      background: transparent;
      padding: 0;
      width: 150px;
    }
  </style>
@endpush

@section('content')
  <div class="ds-body flex flex-col gap-6">
    @if (session('status'))
      <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4 text-sm text-[color:var(--ds-navy)] shadow-sm">
        {!! html_entity_decode(session('status')) !!}
      </div>
    @endif
    @if (session('statusone'))
      <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-mint)] p-4 text-sm text-[color:var(--ds-navy)] shadow-sm">
        {!! html_entity_decode(session('statusone')) !!}
      </div>
    @endif
    @if (session('statustwo'))
      <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-blush)] p-4 text-sm text-[color:var(--ds-coral)] shadow-sm">
        {!! html_entity_decode(session('statustwo')) !!}
      </div>
    @endif

    <div class="time-filter-card">
      <div class="quick-range-pills">
        <button type="button" class="pill quick-range-button" data-range="today">Hoy</button>
        <button type="button" class="pill quick-range-button" data-range="yesterday">Ayer</button>
        <button type="button" class="pill quick-range-button" data-range="weekly">Semana</button>
        <button type="button" class="pill quick-range-button" data-range="monthly">Mes</button>
        <button type="button" class="pill quick-range-button" data-range="last30">Últimos 30</button>
        <button type="button" class="pill quick-range-button" data-range="last90">Últimos 90</button>
        <button type="button" class="pill quick-range-button" data-range="all">Todo</button>
        <div class="date-picker-pill">
          <i class="fa fa-calendar text-muted"></i>
          <input type="text" id="customers_range" class="form-control" placeholder="Seleccionar rango" autocomplete="off">
        </div>
        <div class="time-filter-actions ml-auto">
          <button type="button" class="btn btn-dark rounded-pill px-4" id="customers_range_apply">Aplicar</button>
          <button type="button" class="btn btn-link text-dark" id="customers_range_clear">Limpiar</button>
        </div>
      </div>
    </div>

    <x-design.section class="ds-shell border-slate-200">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-col gap-2">
          <x-design.eyebrow>{{ isset($phase) ? $phase->name : 'Leads' }}</x-design.eyebrow>
          <p class="text-sm text-slate-700">
            Registro <span class="font-semibold text-[color:var(--ds-ink)]">{{ $model->firstItem() }}</span> a
            <span class="font-semibold text-[color:var(--ds-ink)]">{{ $model->lastItem() }}</span> de
            <span class="font-semibold text-[color:var(--ds-ink)]">{{ $model->total() }}</span>
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <x-design.badge tone="cloud">Total {{ $model->total() }}</x-design.badge>
          <x-design.badge tone="outline">Activos {{ $model->count() }}</x-design.badge>
        </div>
      </div>
    </x-design.section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
      <div class="flex flex-col gap-4">
        <script type="text/javascript">
          var ratings = [];
        </script>
        <?php $cont=0; ?>

        <div class="flex flex-col gap-4">
          @foreach($model as $item)
            @include('customers.index_partials.card', ['item' => $item])
          @endforeach

          @if($model->count() === 0)
            <div class="rounded-2xl border border-slate-200 bg-[color:var(--ds-cloud)] p-4 text-sm text-[color:var(--ds-navy)] shadow-sm">
              No se encontraron prospectos con esos filtros.
            </div>
          @endif
        </div>
      </div>

      <aside class="lg:sticky lg:top-24">
        <div class="flex flex-col gap-4">
          @include('customers.index_partials.groupbar', [
            'customersGroup' => $customersGroup,
            'parent_statuses' => $parent_statuses,
            'statusGroups' => $statusGroups,
          ])

          @if(isset($sum_g))
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
              Total estados: <span class="font-semibold text-[color:var(--ds-ink)]">{{ $sum_g }}</span>
            </div>
          @endif
        </div>

        <div class="mt-4">
          <x-design.section class="border-slate-200">
            @include('customers.index_partials.side_filter')
          </x-design.section>
        </div>
      </aside>
    </div>

    <div class="flex justify-center">
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

  window.changeParentStatus = function (id) {
    var $parentInput = $('#parent_status_id');
    if (! $parentInput.length) {
      return;
    }
    $parentInput.val(id);
    $('#status_id').val('');
    $('#filter_form').submit();
  };

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
  const $topInput  = $('#customers_range');
  const $applyBtn  = $('#customers_range_apply');
  const $clearBtn  = $('#customers_range_clear');
  const $quickButtons = $('.quick-range-button');

  if (! $filterForm.length || ! $search.length) {
    return;
  }

  function setActiveQuickButton(value) {
    $quickButtons.removeClass('active');
    if (value) {
      $quickButtons.filter('[data-range="' + value + '"]').addClass('active');
    }
  }

  // ======== DATE RANGE PICKER ========
  function updateInput($input, start, end, updateWidget = true) {
    if (! $input.length) {
      return;
    }
    $input.val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
    if (updateWidget && $input.data('daterangepicker')) {
      $input.data('daterangepicker').setStartDate(start);
      $input.data('daterangepicker').setEndDate(end);
    }
  }

  function setHidden(start, end, updateWidget = true) {
    // Backend: YYYY-MM-DD
    $from.val(start.format('YYYY-MM-DD'));
    $to.val(end.format('YYYY-MM-DD'));
    updateInput($inputDR, start, end, updateWidget);
    updateInput($topInput, start, end, updateWidget);
  }

  function getInitialStart(){
    const fd = $from.val();
    return fd ? moment(fd, 'YYYY-MM-DD') : moment().subtract(89,'days'); // default 90 días
  }
  function getInitialEnd(){
    const td = $to.val();
    return td ? moment(td, 'YYYY-MM-DD') : moment();
  }

  function initDateRangePicker($input, parentEl) {
    if (! $input.length) {
      return;
    }
    $input.daterangepicker({
      startDate: getInitialStart(),
      endDate: getInitialEnd(),
      maxDate: moment(),
      parentEl: parentEl,
      opens: 'right',
      autoUpdateInput: false,
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
        'Este año': [moment().startOf('year'), moment().endOf('year')],
        'Mes anterior': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
        'Máximo': [ORIGEN_MAXIMO, moment()]
      }
    }, function(start, end){
      setHidden(start, end, false);
    })
    .on('apply.daterangepicker', function(ev, picker){
      setHidden(picker.startDate, picker.endDate, false);
    })
    .on('cancel.daterangepicker', function(){
      clearRange();
    });
  }

  function bindManualInput($input) {
    if (! $input.length) {
      return;
    }
    $input.on('blur keydown', function(e){
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
  }

  initDateRangePicker($inputDR, '.filter-overlay__panel');
  initDateRangePicker($topInput);
  bindManualInput($inputDR);
  bindManualInput($topInput);

  if ($from.val() && $to.val()) {
    setHidden(moment($from.val(),'YYYY-MM-DD'), moment($to.val(),'YYYY-MM-DD'));
  }

  // ======== Enlaces rápidos (externos) ========
  window.setRange = function(days) {
    const end = moment();
    const start = moment().subtract(days-1,'days'); // inclusivo
    setHidden(start, end);
    return false;
  };
  window.setYesterday = function() {
    const day = moment().subtract(1,'day');
    setHidden(day, day);
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
    $topInput.val('');
    setActiveQuickButton(null);
    return false;
  };

  if ($applyBtn.length) {
    $applyBtn.on('click', function () {
      $filterForm.trigger('submit');
    });
  }

  if ($clearBtn.length) {
    $clearBtn.on('click', function () {
      clearRange();
    });
  }

  if ($quickButtons.length) {
    $quickButtons.on('click', function () {
      const range = $(this).data('range');
      if (range === 'today') {
        setRange(1);
      } else if (range === 'yesterday') {
        setYesterday();
      } else if (range === 'weekly') {
        setThisWeek();
      } else if (range === 'monthly') {
        setCurrentMonth();
      } else if (range === 'last30') {
        setRange(30);
      } else if (range === 'last90') {
        setRange(90);
      } else if (range === 'all') {
        setMaximo();
      }
      setActiveQuickButton(range);
    });
  }

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
@endpush
