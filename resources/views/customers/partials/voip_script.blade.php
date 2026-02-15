@once
  @push('scripts')
    <script>
      (function () {
        if (window.customerVoipBindingsReady) {
          return;
        }
        window.customerVoipBindingsReady = true;

        var e164Pattern = /^\+?[1-9]\d{6,14}$/;
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function normalizePhone(value) {
          return (value || '').trim();
        }

        function isValidPhone(value) {
          return e164Pattern.test(value);
        }

        function requestAgentPhone() {
          var cachedPhone = normalizePhone(window.localStorage.getItem('crm_agent_phone'));
          if (isValidPhone(cachedPhone)) {
            return cachedPhone;
          }

          var typedPhone = window.prompt('Ingresa tu teléfono de asesor en formato E.164 (ej: +573001234567):');
          if (typedPhone === null) {
            return null;
          }

          typedPhone = normalizePhone(typedPhone);
          if (!isValidPhone(typedPhone)) {
            window.alert('El teléfono del asesor debe estar en formato E.164.');
            return null;
          }

          window.localStorage.setItem('crm_agent_phone', typedPhone);

          return typedPhone;
        }

        async function parseJsonResponse(response) {
          try {
            return await response.json();
          } catch (error) {
            return {};
          }
        }

        document.addEventListener('click', async function (event) {
          var button = event.target.closest('[data-start-customer-call]');
          if (!button) {
            return;
          }

          event.preventDefault();

          var callUrl = button.getAttribute('data-call-url');
          var targetPhone = normalizePhone(button.getAttribute('data-target-phone'));
          var customerName = button.getAttribute('data-customer-name') || 'cliente';

          if (!callUrl || !isValidPhone(targetPhone)) {
            window.alert('No se encontró un teléfono válido para iniciar la llamada.');
            return;
          }

          var agentPhone = requestAgentPhone();
          if (!agentPhone) {
            return;
          }

          var previousText = button.textContent;
          button.disabled = true;
          button.textContent = 'Llamando...';

          try {
            var response = await fetch(callUrl, {
              method: 'POST',
              headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
              },
              body: JSON.stringify({
                to: targetPhone,
                agent_phone: agentPhone,
              }),
            });

            var payload = await parseJsonResponse(response);

            if (!response.ok) {
              throw new Error(payload.message || 'Twilio rechazó la llamada.');
            }

            var sidText = payload.call_sid ? ' SID: ' + payload.call_sid : '';
            window.alert('Llamada enviada para ' + customerName + '.' + sidText);
          } catch (error) {
            window.alert(error.message || 'No fue posible iniciar la llamada.');
          } finally {
            button.disabled = false;
            button.textContent = previousText;
          }
        });
      })();
    </script>
  @endpush
@endonce
