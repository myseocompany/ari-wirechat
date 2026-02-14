@extends('layout')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3">Llamadas VoIP</h1>

                    <p class="text-muted mb-3">
                        Marcador rápido con Twilio Voice. Configura tu TwiML App con esta URL:
                        <code id="twiml-url">{{ url('/api/voip/twiml') }}</code>
                    </p>

                    <div class="form-group">
                        <label for="voip-identity">Identidad (opcional)</label>
                        <input id="voip-identity" type="text" class="form-control" placeholder="agent_ventas_01">
                    </div>

                    <div class="form-group">
                        <label for="voip-to">Número destino</label>
                        <input id="voip-to" type="text" class="form-control" placeholder="+573001234567" required>
                        <small class="form-text text-muted">Formato E.164. Ejemplo: +573001234567.</small>
                    </div>

                    <div class="d-flex flex-wrap align-items-center" style="gap: 12px;">
                        <button id="voip-connect" type="button" class="btn btn-outline-primary">
                            Conectar softphone
                        </button>
                        <button id="voip-call" type="button" class="btn btn-success" disabled>
                            Llamar
                        </button>
                        <button id="voip-hangup" type="button" class="btn btn-danger" disabled>
                            Colgar
                        </button>
                        <span id="voip-status" class="badge badge-secondary">Sin conectar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @parent
    <script>
        (function () {
            const tokenUrl = '{{ route('voip.token') }}';
            const statusBadge = document.getElementById('voip-status');
            const twimlUrl = document.getElementById('twiml-url');
            const connectButton = document.getElementById('voip-connect');
            const callButton = document.getElementById('voip-call');
            const hangupButton = document.getElementById('voip-hangup');
            const identityInput = document.getElementById('voip-identity');
            const destinationInput = document.getElementById('voip-to');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            let device = null;
            let activeCall = null;
            let sdkLoadPromise = null;

            const statusClassMap = {
                primary: 'badge-primary',
                secondary: 'badge-secondary',
                success: 'badge-success',
                danger: 'badge-danger',
                warning: 'badge-warning',
                info: 'badge-info',
            };

            function setStatus(message, variant) {
                statusBadge.textContent = message;
                statusBadge.className = 'badge ' + (statusClassMap[variant] || statusClassMap.secondary);
            }

            function setButtons(connected, inCall) {
                callButton.disabled = !connected || inCall;
                hangupButton.disabled = !inCall;
            }

            function attachCallEvents(call) {
                call.on('accept', function () {
                    setStatus('Llamada conectada', 'success');
                    setButtons(true, true);
                });

                call.on('disconnect', function () {
                    activeCall = null;
                    setStatus('Llamada finalizada', 'secondary');
                    setButtons(true, false);
                });

                call.on('cancel', function () {
                    activeCall = null;
                    setStatus('Llamada cancelada', 'secondary');
                    setButtons(true, false);
                });

                call.on('reject', function () {
                    activeCall = null;
                    setStatus('Llamada rechazada', 'warning');
                    setButtons(true, false);
                });

                call.on('error', function (error) {
                    activeCall = null;
                    setStatus(error?.message || 'Error en la llamada', 'danger');
                    setButtons(true, false);
                });
            }

            function attachDeviceEvents(deviceInstance) {
                deviceInstance.on('registered', function () {
                    setStatus('Softphone listo', 'success');
                    setButtons(true, false);
                });

                deviceInstance.on('error', function (error) {
                    setStatus(error?.message || 'Error de Twilio Device', 'danger');
                });
            }

            function injectScript(sourceUrl) {
                return new Promise(function (resolve, reject) {
                    const script = document.createElement('script');
                    script.src = sourceUrl;
                    script.async = true;
                    script.onload = function () {
                        resolve(sourceUrl);
                    };
                    script.onerror = function () {
                        reject(new Error('No se pudo cargar: ' + sourceUrl));
                    };
                    document.head.appendChild(script);
                });
            }

            async function ensureTwilioSdk() {
                if (window.Twilio && window.Twilio.Device) {
                    return;
                }

                if (sdkLoadPromise) {
                    await sdkLoadPromise;
                    return;
                }

                const sdkCandidates = [
                    'https://media.twiliocdn.com/sdk/js/voice/releases/2.12.3/twilio.min.js',
                    'https://sdk.twilio.com/js/voice/releases/2.12.3/twilio.min.js',
                    'https://media.twiliocdn.com/sdk/js/voice/releases/2.9.0/twilio.min.js',
                    'https://sdk.twilio.com/js/voice/releases/2.9.0/twilio.min.js',
                ];

                sdkLoadPromise = (async function () {
                    let lastError = null;

                    for (const candidate of sdkCandidates) {
                        try {
                            await injectScript(candidate);
                            if (window.Twilio && window.Twilio.Device) {
                                return;
                            }
                        } catch (error) {
                            lastError = error;
                        }
                    }

                    throw lastError || new Error('No fue posible cargar Twilio Voice SDK.');
                })();

                await sdkLoadPromise;
            }

            async function connectDevice() {
                if (device) {
                    setStatus('Softphone listo', 'success');
                    setButtons(true, false);
                    return device;
                }

                await ensureTwilioSdk();

                setStatus('Generando token...', 'info');

                const identity = identityInput.value.trim();
                const response = await fetch(tokenUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        identity: identity === '' ? null : identity,
                    }),
                });

                const payload = await response.json();

                if (!response.ok || !payload.token) {
                    throw new Error(payload.message || 'No se pudo generar token.');
                }

                twimlUrl.textContent = payload.twiml_url || twimlUrl.textContent;
                identityInput.value = payload.identity || identityInput.value;

                device = new Twilio.Device(payload.token, {
                    codecPreferences: ['opus', 'pcmu'],
                    enableRingingState: true,
                });

                attachDeviceEvents(device);
                await device.register();

                return device;
            }

            connectButton.addEventListener('click', async function () {
                connectButton.disabled = true;
                try {
                    await connectDevice();
                } catch (error) {
                    setStatus(error?.message || 'No se pudo conectar el softphone.', 'danger');
                } finally {
                    connectButton.disabled = false;
                }
            });

            callButton.addEventListener('click', async function () {
                const destination = destinationInput.value.trim();
                if (!/^\+?[1-9]\d{6,14}$/.test(destination)) {
                    setStatus('Número inválido. Usa formato E.164.', 'warning');
                    return;
                }

                callButton.disabled = true;
                try {
                    const voipDevice = await connectDevice();
                    setStatus('Marcando...', 'info');
                    activeCall = await voipDevice.connect({
                        params: {
                            To: destination,
                        },
                    });
                    attachCallEvents(activeCall);
                    setButtons(true, true);
                } catch (error) {
                    setStatus(error?.message || 'No se pudo iniciar la llamada.', 'danger');
                    setButtons(true, false);
                } finally {
                    if (!activeCall) {
                        callButton.disabled = false;
                    }
                }
            });

            hangupButton.addEventListener('click', function () {
                if (activeCall) {
                    activeCall.disconnect();
                }
                if (device) {
                    device.disconnectAll();
                }
            });
        })();
    </script>
@endsection
