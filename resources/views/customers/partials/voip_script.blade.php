@once
  @push('scripts')
    <script>
      (function () {
        if (window.customerVoipBindingsReady) {
          return;
        }
        window.customerVoipBindingsReady = true;

        var e164Pattern = /^\+?[1-9]\d{6,14}$/;
        var voipPopupName = 'ari_voip_softphone';
        var voipPopupFeatures = 'popup=yes,width=360,height=640,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes';

        function normalizePhone(value) {
          return (value || '').trim();
        }

        function isValidPhone(value) {
          return e164Pattern.test(value);
        }

        function openVoipPopup(url) {
          var existingPopup = window.customerVoipPopupRef;
          if (existingPopup && !existingPopup.closed) {
            existingPopup.location.href = url;
            existingPopup.focus();
            return true;
          }

          var popup = window.open(url, voipPopupName, voipPopupFeatures);
          if (!popup) {
            return false;
          }

          window.customerVoipPopupRef = popup;
          popup.focus();

          return true;
        }

        document.addEventListener('click', function (event) {
          var button = event.target.closest('[data-start-customer-call]');
          if (!button) {
            return;
          }

          event.preventDefault();

          var voipPageUrl = button.getAttribute('data-voip-url') || '/voip';
          var prepareUrl = button.getAttribute('data-call-url') || '';
          var targetPhone = normalizePhone(button.getAttribute('data-target-phone'));

          if (!isValidPhone(targetPhone)) {
            window.alert('No se encontró un teléfono válido para iniciar la llamada.');
            return;
          }

          button.disabled = true;
          button.classList.add('opacity-60', 'cursor-not-allowed');

          try {
            var separator = voipPageUrl.indexOf('?') >= 0 ? '&' : '?';
            var destination = voipPageUrl + separator + 'to=' + encodeURIComponent(targetPhone) + '&autocall=1';
            if (prepareUrl) {
              destination += '&prepare_url=' + encodeURIComponent(prepareUrl);
            }
            if (!openVoipPopup(destination)) {
              throw new Error('El navegador bloqueó la ventana emergente. Habilita popups para este sitio.');
            }
          } catch (error) {
            window.alert(error.message || 'No fue posible abrir el softphone.');
          } finally {
            button.disabled = false;
            button.classList.remove('opacity-60', 'cursor-not-allowed');
          }
        });
      })();
    </script>
  @endpush
@endonce
