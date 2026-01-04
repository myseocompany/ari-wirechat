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
</script>
@endpush
