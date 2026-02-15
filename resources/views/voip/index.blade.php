@extends('layouts.guest')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-1 py-1">
        <div class="w-full max-w-[320px] rounded-2xl border border-slate-200 bg-white p-3 shadow-lg shadow-slate-300/30">
            <div class="space-y-1">
                <label for="voip-to" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono</label>
                <div class="flex items-center gap-1.5">
                    <input
                        id="voip-to"
                        type="text"
                        inputmode="tel"
                        autocomplete="off"
                        class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                        placeholder="+573001234567"
                        required
                    >
                    <button
                        id="voip-clear"
                        type="button"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-slate-50 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                        aria-label="Borrar teléfono"
                        title="Borrar"
                    >
                        C
                    </button>
                </div>
                <p id="voip-status" class="text-center text-[11px] font-medium text-slate-600">Listo para llamar</p>
            </div>

            <div class="mt-2 grid grid-cols-3 gap-1">
                <button type="button" data-keypad="1" class="voip-key">1</button>
                <button type="button" data-keypad="2" class="voip-key"><span>2</span><small>ABC</small></button>
                <button type="button" data-keypad="3" class="voip-key"><span>3</span><small>DEF</small></button>
                <button type="button" data-keypad="4" class="voip-key"><span>4</span><small>GHI</small></button>
                <button type="button" data-keypad="5" class="voip-key"><span>5</span><small>JKL</small></button>
                <button type="button" data-keypad="6" class="voip-key"><span>6</span><small>MNO</small></button>
                <button type="button" data-keypad="7" class="voip-key"><span>7</span><small>PQRS</small></button>
                <button type="button" data-keypad="8" class="voip-key"><span>8</span><small>TUV</small></button>
                <button type="button" data-keypad="9" class="voip-key"><span>9</span><small>WXYZ</small></button>
                <button type="button" data-keypad="+" class="voip-key">+</button>
                <button type="button" data-keypad="0" class="voip-key">0</button>
                <button type="button" data-keypad="backspace" class="voip-key">⌫</button>
            </div>

            <button
                id="voip-action"
                type="button"
                class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-emerald-500 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300"
            >
                Iniciar llamada
            </button>
        </div>
    </div>

    <script>
        (function () {
            const tokenUrl = '{{ route('voip.token') }}';
            const destinationInput = document.getElementById('voip-to');
            const clearButton = document.getElementById('voip-clear');
            const statusText = document.getElementById('voip-status');
            const actionButton = document.getElementById('voip-action');
            const keypadButtons = document.querySelectorAll('[data-keypad]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const urlParams = new URLSearchParams(window.location.search);
            const prefilledDestination = (urlParams.get('to') || '').trim();
            const shouldAutoCall = urlParams.get('autocall') === '1';
            const prepareUrl = (urlParams.get('prepare_url') || '').trim();

            let device = null;
            let activeCall = null;
            let sdkLoadPromise = null;
            let isPlacingCall = false;
            let callPhase = 'idle';
            let preparedActionId = null;
            let preparedDestination = null;

            function setStatus(message, tone) {
                statusText.textContent = message;

                statusText.className = 'text-center text-xs font-medium';
                if (tone === 'success') {
                    statusText.classList.add('text-emerald-600');
                    return;
                }
                if (tone === 'danger') {
                    statusText.classList.add('text-rose-600');
                    return;
                }
                if (tone === 'warning') {
                    statusText.classList.add('text-amber-600');
                    return;
                }
                if (tone === 'info') {
                    statusText.classList.add('text-sky-600');
                    return;
                }

                statusText.classList.add('text-slate-600');
            }

            function setCallPhase(phase) {
                callPhase = phase;

                actionButton.classList.remove('bg-emerald-500', 'hover:bg-emerald-600', 'focus:ring-emerald-300');
                actionButton.classList.remove('bg-rose-500', 'hover:bg-rose-600', 'focus:ring-rose-300');

                if (phase === 'idle') {
                    actionButton.textContent = 'Iniciar llamada';
                    actionButton.classList.add('bg-emerald-500', 'hover:bg-emerald-600', 'focus:ring-emerald-300');
                    return;
                }

                actionButton.textContent = 'Colgar';
                actionButton.classList.add('bg-rose-500', 'hover:bg-rose-600', 'focus:ring-rose-300');
            }

            function setActionEnabled(enabled) {
                actionButton.disabled = !enabled;
                actionButton.classList.toggle('opacity-60', !enabled);
                actionButton.classList.toggle('cursor-not-allowed', !enabled);
            }

            function getValidatedDestination() {
                const destination = destinationInput.value.trim();
                if (!/^\+?[1-9]\d{6,14}$/.test(destination)) {
                    setStatus('Número inválido. Usa formato E.164.', 'warning');
                    return null;
                }

                return destination;
            }

            function appendKey(key) {
                if (key === 'backspace') {
                    destinationInput.value = destinationInput.value.slice(0, -1);
                    destinationInput.focus();
                    return;
                }

                if (key === '+') {
                    const currentValue = destinationInput.value.trim();
                    if (currentValue.startsWith('+')) {
                        destinationInput.focus();
                        return;
                    }

                    destinationInput.value = currentValue === '' ? '+' : '+' + currentValue.replace(/^\+/, '');
                    destinationInput.focus();
                    return;
                }

                destinationInput.value += key;
                destinationInput.focus();
            }

            function resetPreparedActionIfNumberChanged() {
                const currentDestination = destinationInput.value.trim();
                if (preparedDestination !== currentDestination) {
                    preparedActionId = null;
                    preparedDestination = null;
                }
            }

            async function prepareCallAction(destination) {
                if (!prepareUrl) {
                    return null;
                }

                if (preparedActionId !== null && preparedDestination === destination) {
                    return preparedActionId;
                }

                const response = await fetch(prepareUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        to: destination,
                        client: true,
                    }),
                });

                let payload = {};
                try {
                    payload = await response.json();
                } catch (error) {
                    payload = {};
                }

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo preparar la acción de llamada.');
                }

                const actionId = Number(payload.action_id ?? 0);
                if (!Number.isFinite(actionId) || actionId <= 0) {
                    throw new Error('No se recibió action_id válido para registrar la llamada.');
                }

                preparedActionId = actionId;
                preparedDestination = destination;

                return actionId;
            }

            function attachCallEvents(call) {
                call.on('accept', function () {
                    setStatus('Llamada conectada', 'success');
                    setCallPhase('in_call');
                    setActionEnabled(true);
                });

                call.on('disconnect', function () {
                    activeCall = null;
                    setStatus('Llamada finalizada', 'secondary');
                    setCallPhase('idle');
                    setActionEnabled(true);
                });

                call.on('cancel', function () {
                    activeCall = null;
                    setStatus('Llamada cancelada', 'secondary');
                    setCallPhase('idle');
                    setActionEnabled(true);
                });

                call.on('reject', function () {
                    activeCall = null;
                    setStatus('Llamada rechazada', 'warning');
                    setCallPhase('idle');
                    setActionEnabled(true);
                });

                call.on('error', function (error) {
                    activeCall = null;
                    setStatus(error?.message || 'Error en la llamada', 'danger');
                    setCallPhase('idle');
                    setActionEnabled(true);
                });
            }

            function attachDeviceEvents(deviceInstance) {
                deviceInstance.on('registered', function () {
                    if (callPhase === 'idle') {
                        setStatus('Softphone conectado', 'success');
                    }
                });

                deviceInstance.on('error', function (error) {
                    setStatus(error?.message || 'Error de Twilio Device', 'danger');
                    if (!activeCall) {
                        setCallPhase('idle');
                        setActionEnabled(true);
                    }
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
                    '/vendor/twilio/twilio.min.js',
                    'https://cdn.jsdelivr.net/npm/@twilio/voice-sdk@2.12.3/dist/twilio.min.js',
                    'https://unpkg.com/@twilio/voice-sdk@2.12.3/dist/twilio.min.js',
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

                    throw lastError || new Error('No fue posible cargar Twilio Voice SDK desde los orígenes configurados.');
                })();

                await sdkLoadPromise;
            }

            async function connectDevice() {
                if (device) {
                    return device;
                }

                await ensureTwilioSdk();

                setStatus('Conectando softphone...', 'info');

                const response = await fetch(tokenUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({}),
                });

                const payload = await response.json();

                if (!response.ok || !payload.token) {
                    throw new Error(payload.message || 'No se pudo generar token.');
                }

                device = new Twilio.Device(payload.token, {
                    codecPreferences: ['opus', 'pcmu'],
                    enableRingingState: true,
                });

                attachDeviceEvents(device);
                await device.register();

                return device;
            }

            async function startWebCall() {
                if (isPlacingCall || activeCall) {
                    return;
                }

                const destination = getValidatedDestination();
                if (!destination) {
                    return;
                }

                isPlacingCall = true;
                setCallPhase('dialing');
                setActionEnabled(false);
                setStatus('Preparando llamada...', 'info');

                try {
                    const voipDevice = await connectDevice();
                    const actionId = await prepareCallAction(destination);
                    const params = {
                        to: destination,
                        To: destination,
                    };

                    if (actionId !== null) {
                        params.action_id = String(actionId);
                    }

                    activeCall = await voipDevice.connect({ params: params });
                    attachCallEvents(activeCall);
                    setStatus('Timbrando...', 'info');
                    setActionEnabled(true);
                } catch (error) {
                    activeCall = null;
                    setStatus(error?.message || 'No se pudo iniciar la llamada.', 'danger');
                    setCallPhase('idle');
                    setActionEnabled(true);
                } finally {
                    isPlacingCall = false;
                }
            }

            function endWebCall() {
                if (activeCall) {
                    activeCall.disconnect();
                }
                if (device) {
                    device.disconnectAll();
                }

                activeCall = null;
                setStatus('Llamada finalizada', 'secondary');
                setCallPhase('idle');
                setActionEnabled(true);
            }

            keypadButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    appendKey(button.getAttribute('data-keypad'));
                    resetPreparedActionIfNumberChanged();
                });
            });

            destinationInput.addEventListener('input', function () {
                resetPreparedActionIfNumberChanged();
            });

            clearButton.addEventListener('click', function () {
                destinationInput.value = '';
                preparedActionId = null;
                preparedDestination = null;
                destinationInput.focus();
            });

            actionButton.addEventListener('click', function () {
                if (callPhase === 'idle') {
                    void startWebCall();
                    return;
                }

                endWebCall();
            });

            if (prefilledDestination !== '') {
                destinationInput.value = prefilledDestination;
                setStatus('Destino cargado desde CRM.', 'info');
            }

            if (shouldAutoCall && prefilledDestination !== '') {
                setTimeout(function () {
                    void startWebCall();
                }, 150);
            }
        })();
    </script>

    <style>
        .voip-key {
            display: inline-flex;
            min-height: 44px;
            width: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1px;
            border-radius: 0.6rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 1.1rem;
            font-weight: 600;
            color: #0f172a;
            transition: background-color 160ms ease, border-color 160ms ease;
        }

        .voip-key:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }

        .voip-key small {
            font-size: 0.55rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            color: #94a3b8;
            text-transform: uppercase;
            line-height: 1;
        }
    </style>
@endsection
