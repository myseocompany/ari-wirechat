@php
  $tabsId = 'customer-tabs-'.($model->id ?? '0');
  $customer = $model;
  $actionsId = $tabsId.'-actions';
  $filesId = $tabsId.'-files';
  $calculatorId = $tabsId.'-calculator';
  $poaId = $tabsId.'-poa';
  $historyId = $tabsId.'-history';
@endphp

@if($actual)
  <div class="customer-tabs space-y-4">
    <div class="flex items-center justify-between">
      <div class="customer-section-controls inline-flex flex-wrap items-center gap-2 rounded-full bg-slate-100 p-1">
        <button class="tab-button inline-flex items-center rounded-full px-4 py-1.5 text-sm font-medium text-slate-600 transition focus:outline-none focus:ring-2 focus:ring-blue-200 is-active" type="button" data-section-target="customer-actions">
          Acciones
        </button>
        <button class="tab-button inline-flex items-center rounded-full px-4 py-1.5 text-sm font-medium text-slate-600 transition focus:outline-none focus:ring-2 focus:ring-blue-200" type="button" data-section-target="customer-files">
        Archivos
      </button>
        <button class="tab-button inline-flex items-center rounded-full px-4 py-1.5 text-sm font-medium text-slate-600 transition focus:outline-none focus:ring-2 focus:ring-blue-200" type="button" data-section-target="customer-calculator">
        Calculadora
      </button>
        <button class="tab-button inline-flex items-center rounded-full px-4 py-1.5 text-sm font-medium text-slate-600 transition focus:outline-none focus:ring-2 focus:ring-blue-200" type="button" data-section-target="customer-poa">
        POA
      </button>
      </div>
    </div>

    <div id="customer-actions" class="customer-section-pane is-active space-y-4">
      @include('customers.partials.actions_form')
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        @include('customers.show_partials.actions_widget_wp')
      </div>
      @include('customers.show_partials.history')
    </div>
    <div id="customer-files" class="customer-section-pane space-y-4">
      @include('customers.partials.acordion.files', ['customer' => $model])
    </div>
    <div id="customer-calculator" class="customer-section-pane space-y-4">
      @include('customers.partials.acordion.calculator')
    </div>
    <div id="customer-poa" class="customer-section-pane space-y-4">
      @include('customers.partials.acordion.poa')
    </div>
    <script>
      (function () {
        const container = document.currentScript?.closest('.customer-tabs');
        if (!container) {
          return;
        }
        const buttons = Array.from(container.querySelectorAll('[data-section-target]'));
        const panes = Array.from(container.querySelectorAll('.customer-section-pane'));
        if (buttons.length === 0 || panes.length === 0) {
          return;
        }
        const activeClasses = ['bg-white', 'text-slate-900', 'shadow'];
        const inactiveClasses = ['text-slate-500', 'hover:text-slate-700'];
        const setActive = function (targetId) {
          panes.forEach(function (pane) {
            pane.classList.toggle('is-active', pane.id === targetId);
          });
          buttons.forEach(function (button) {
            const isActive = button.getAttribute('data-section-target') === targetId;
            button.classList.toggle('is-active', isActive);
            activeClasses.forEach(function (className) {
              button.classList.toggle(className, isActive);
            });
            inactiveClasses.forEach(function (className) {
              button.classList.toggle(className, !isActive);
            });
          });
        };
        buttons.forEach(function (button) {
          button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-section-target');
            if (targetId) {
              setActive(targetId);
            }
          });
        });
        setActive(buttons[0].getAttribute('data-section-target'));
      })();
    </script>
  </div>
@else
  <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">El prospecto no existe</div>
@endif
