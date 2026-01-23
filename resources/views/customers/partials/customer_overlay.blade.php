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

@include('customers.partials.tags_script')
@include('customers.partials.notes_script')

@push('scripts')
<script>
  (function () {
    if (!window.toggleDateInput) {
      window.toggleDateInput = function (element) {
        if (!element) {
          return;
        }
        console.log('Programar acción: click en checkbox', element);
        const form = element.closest('form');
        if (!form) {
          return;
        }
        const dateContainer = form.querySelector('[data-date-container]');
        const dateInput = form.querySelector('input[name="date_programed"]');
        if (!dateContainer || !dateInput) {
          return;
        }
        if (element.checked) {
          dateContainer.style.display = 'block';
          dateInput.disabled = false;
          return;
        }
        dateContainer.style.display = 'none';
        dateInput.disabled = true;
      };
    }

    if (!window.initActionDateToggle) {
      window.initActionDateToggle = function (scope) {
        const $scope = scope ? $(scope) : $(document);
        $scope.find('[data-date-toggle]').each(function () {
          window.toggleDateInput(this);
        });
      };
    }

    if (!window.initCustomerTabs) {
      window.initCustomerTabs = function (scope) {
        const $scope = scope ? $(scope) : $(document);
        $scope.find('.customer-tabs').each(function () {
          const container = this;
          if (container.dataset.tabsReady === '1') {
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
          container.dataset.tabsReady = '1';
        });
      };
    }

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
      if ($(event.target).closest('[data-customer-overlay-ignore]').length > 0) {
        return;
      }
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
          if (window.initCustomerTabs) {
            window.initCustomerTabs($('#customer_overlay_body'));
          }
          if (window.initNotesEditors) {
            window.initNotesEditors($('#customer_overlay_body'));
          }
          if (window.initActionDateToggle) {
            window.initActionDateToggle($('#customer_overlay_body'));
          }
        } else {
          window.location.href = url;
        }
      }).fail(function () {
        window.location.href = url;
      });
    });

    const copyToClipboard = function (text) {
      if (!text) {
        return Promise.reject();
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text);
      }
      return new Promise(function (resolve, reject) {
        const $tmp = document.createElement('textarea');
        $tmp.value = text;
        $tmp.setAttribute('readonly', '');
        $tmp.style.position = 'absolute';
        $tmp.style.left = '-9999px';
        document.body.appendChild($tmp);
        $tmp.select();
        try {
          document.execCommand('copy');
          resolve();
        } catch (error) {
          reject(error);
        }
        document.body.removeChild($tmp);
      });
    };

    document.addEventListener('click', function (event) {
      const phoneTrigger = event.target.closest('.copy-phone');
      if (phoneTrigger && overlay.contains(phoneTrigger)) {
        const phone = phoneTrigger.getAttribute('data-phone') || '';
        copyToClipboard(phone.toString()).then(function () {
          console.log('Telefono copiado al portapapeles.');
        }).catch(function () {
          console.log('No se pudo copiar el telefono.');
        });
        return;
      }

      const textTrigger = event.target.closest('[data-copy-text]');
      if (!textTrigger || !overlay.contains(textTrigger)) {
        return;
      }
      const text = textTrigger.getAttribute('data-copy-text') || '';
      copyToClipboard(text.toString()).then(function () {
        console.log('POA copiado al portapapeles.');
      }).catch(function () {
        console.log('No se pudo copiar el POA.');
      });
    }, true);
  })();
</script>
@endpush
