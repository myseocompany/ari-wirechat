<?php

namespace App\Http\Controllers;

use App\Http\Requests\Voip\GenerateTokenRequest;
use App\Http\Requests\Voip\PlaceCallRequest;
use App\Http\Requests\Voip\PlaceCustomerCallRequest;
use App\Http\Requests\Voip\RenderTwimlRequest;
use App\Http\Requests\Voip\TwilioRecordingCallbackRequest;
use App\Http\Requests\Voip\TwilioStatusCallbackRequest;
use App\Models\Action;
use App\Models\Customer;
use App\Services\TwilioAccessTokenFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

class VoipController extends Controller
{
    public function index(): View
    {
        return view('voip.index');
    }

    public function token(GenerateTokenRequest $request, TwilioAccessTokenFactory $factory): JsonResponse
    {
        if (! $this->isTwilioEnabled()) {
            return response()->json([
                'message' => 'Twilio está deshabilitado en la configuración.',
            ], 503);
        }

        $identity = $request->resolvedIdentity();

        try {
            $token = $factory->makeVoiceToken($identity);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('Error generating Twilio token.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo generar el token de llamada.',
            ], 500);
        }

        return response()->json([
            'token' => $token,
            'identity' => $identity,
            'twiml_url' => $this->publicUrl(url('/api/voip/twiml')),
        ]);
    }

    public function twiml(RenderTwimlRequest $request): Response
    {
        if (! $this->isTwilioEnabled()) {
            return $this->xmlResponse(
                '<Response><Say voice="alice">Twilio está deshabilitado en este entorno.</Say></Response>'
            );
        }

        $destinationNumber = $request->explicitDestinationNumber();
        $actionId = $request->actionId();

        if ($destinationNumber === null) {
            return $this->directTwimlResponse();
        }

        $callerId = (string) config('services.twilio.caller_id');
        if ($callerId === '') {
            return $this->xmlResponse(
                '<Response><Say voice="alice">Twilio caller ID is missing.</Say></Response>'
            );
        }

        $safeCallerId = htmlspecialchars($callerId, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $safeDestination = htmlspecialchars($destinationNumber, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $dialAttributes = [
            'callerId="'.$safeCallerId.'"',
        ];

        if ($actionId !== null) {
            $recordingCallbackUrl = $this->publicUrl($this->buildUrlWithQuery(
                route('api.voip.callbacks.recording'),
                [
                    'action_id' => $actionId,
                    'token' => $this->callbackSecret(),
                ]
            ));
            $safeRecordingCallbackUrl = htmlspecialchars($recordingCallbackUrl, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $statusCallbackUrl = $this->publicUrl($this->buildUrlWithQuery(
                route('api.voip.callbacks.status'),
                [
                    'action_id' => $actionId,
                    'token' => $this->callbackSecret(),
                ]
            ));
            $safeStatusCallbackUrl = htmlspecialchars($statusCallbackUrl, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $dialActionUrl = $this->publicUrl($this->buildUrlWithQuery(
                route('api.voip.callbacks.status'),
                [
                    'action_id' => $actionId,
                    'token' => $this->callbackSecret(),
                    'source' => 'dial_action',
                ]
            ));
            $safeDialActionUrl = htmlspecialchars($dialActionUrl, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $dialAttributes[] = 'record="record-from-answer-dual"';
            $dialAttributes[] = 'recordingStatusCallback="'.$safeRecordingCallbackUrl.'"';
            $dialAttributes[] = 'recordingStatusCallbackMethod="POST"';
            $dialAttributes[] = 'action="'.$safeDialActionUrl.'"';
            $dialAttributes[] = 'method="POST"';

            $numberAttributes = [
                'statusCallback="'.$safeStatusCallbackUrl.'"',
                'statusCallbackMethod="POST"',
                'statusCallbackEvent="initiated ringing answered completed"',
            ];
        } else {
            $numberAttributes = [];
        }

        $dialAttributesText = implode(' ', $dialAttributes);
        $numberAttributesText = implode(' ', $numberAttributes);
        $numberAttributesPrefix = $numberAttributesText === '' ? '' : ' '.$numberAttributesText;
        $twiml = <<<XML
<Response>
    <Dial {$dialAttributesText}>
        <Number{$numberAttributesPrefix}>{$safeDestination}</Number>
    </Dial>
</Response>
XML;

        return $this->xmlResponse($twiml);
    }

    public function call(PlaceCallRequest $request): JsonResponse
    {
        if (! $this->isTwilioEnabled()) {
            return response()->json([
                'message' => 'Twilio está deshabilitado en la configuración.',
            ], 503);
        }

        $accountSid = (string) config('services.twilio.account_sid');
        $authToken = (string) config('services.twilio.auth_token');
        $callerId = (string) config('services.twilio.caller_id');
        $destinationNumber = $request->destinationNumber();
        $twimlUrl = $this->publicUrl(url('/api/voip/twiml'));

        if ($accountSid === '' || $authToken === '' || $callerId === '') {
            return response()->json([
                'message' => 'La configuración para llamada saliente (Account SID/Auth Token/Caller ID) está incompleta.',
            ], 422);
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($accountSid, $authToken)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Calls.json", [
                    'To' => $destinationNumber,
                    'From' => $callerId,
                    'Url' => $twimlUrl,
                    'Method' => 'POST',
                ]);
        } catch (\Throwable $exception) {
            Log::error('Twilio outbound call request failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No fue posible contactar la API de Twilio.',
            ], 500);
        }

        $payload = $response->json();
        if (! $response->successful()) {
            $errorMessage = (string) ($payload['message'] ?? 'Twilio rechazó la llamada.');

            return response()->json([
                'message' => $errorMessage,
                'twilio_code' => $payload['code'] ?? null,
                'twilio_status' => $response->status(),
            ], 422);
        }

        return response()->json([
            'message' => 'Llamada enviada a Twilio.',
            'call_sid' => $payload['sid'] ?? null,
            'status' => $payload['status'] ?? null,
            'to' => $destinationNumber,
            'from' => $callerId,
        ]);
    }

    public function callCustomer(Customer $customer, PlaceCustomerCallRequest $request): JsonResponse
    {
        if (! $this->isTwilioEnabled()) {
            return response()->json([
                'message' => 'Twilio está deshabilitado en la configuración.',
            ], 503);
        }

        $user = $request->user();

        if (! $customer->hasFullAccess($user)) {
            return response()->json([
                'message' => 'No tienes permisos para llamar a este cliente.',
            ], 403);
        }

        $destinationNumber = $request->destinationNumber();
        if ($destinationNumber === null) {
            $bestPhone = $customer->getBestPhoneCandidate();
            $destinationNumber = $bestPhone ? $customer->getInternationalPhone($bestPhone) : null;
        }

        if (! $this->isValidE164Phone($destinationNumber)) {
            return response()->json([
                'message' => 'El cliente no tiene un teléfono válido para llamada.',
            ], 422);
        }

        $accountSid = (string) config('services.twilio.account_sid');
        $authToken = (string) config('services.twilio.auth_token');
        $callerId = (string) config('services.twilio.caller_id');
        if ($accountSid === '' || $authToken === '' || $callerId === '') {
            return response()->json([
                'message' => 'La configuración de Twilio está incompleta.',
            ], 422);
        }

        $creatorName = trim((string) ($user?->name ?? 'Asesor'));
        $action = Action::create([
            'customer_id' => $customer->id,
            'type_id' => 21,
            'creator_user_id' => (int) ($user?->id ?? 0),
            'note' => $this->buildInitialCallNote($creatorName, $callerId, $destinationNumber),
        ]);

        if ($request->isClientPreparation()) {
            return response()->json([
                'message' => 'Acción de llamada preparada.',
                'action_id' => $action->id,
                'to' => $destinationNumber,
                'from' => $callerId,
            ]);
        }

        $secret = $this->callbackSecret();
        $twimlUrl = $this->publicUrl(route('api.voip.twiml'));
        $statusCallbackUrl = $this->publicUrl($this->buildUrlWithQuery(route('api.voip.callbacks.status'), [
            'action_id' => $action->id,
            'token' => $secret,
        ]));
        $recordingCallbackUrl = $this->publicUrl($this->buildUrlWithQuery(route('api.voip.callbacks.recording'), [
            'action_id' => $action->id,
            'token' => $secret,
        ]));

        try {
            $response = Http::asForm()
                ->withBasicAuth($accountSid, $authToken)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Calls.json", [
                    'To' => $destinationNumber,
                    'From' => $callerId,
                    'Url' => $twimlUrl,
                    'Method' => 'POST',
                    'Record' => 'true',
                    'RecordingStatusCallback' => $recordingCallbackUrl,
                    'RecordingStatusCallbackMethod' => 'POST',
                    'StatusCallback' => $statusCallbackUrl,
                    'StatusCallbackMethod' => 'POST',
                    'StatusCallbackEvent' => 'initiated ringing answered completed',
                ]);
        } catch (\Throwable $exception) {
            Log::error('Twilio CRM outbound call request failed.', [
                'message' => $exception->getMessage(),
                'customer_id' => $customer->id,
                'action_id' => $action->id,
            ]);

            $action->note = $this->appendNoteLine($action->note, 'Error Twilio: No fue posible contactar la API.');
            $action->save();

            return response()->json([
                'message' => 'No fue posible contactar la API de Twilio.',
                'action_id' => $action->id,
            ], 500);
        }

        $payload = $response->json();
        if (! $response->successful()) {
            $errorMessage = (string) ($payload['message'] ?? 'Twilio rechazó la llamada.');
            $action->note = $this->appendNoteLine($action->note, 'Error Twilio: '.$errorMessage);
            $action->save();

            return response()->json([
                'message' => $errorMessage,
                'twilio_code' => $payload['code'] ?? null,
                'twilio_status' => $response->status(),
                'action_id' => $action->id,
            ], 422);
        }

        $callSid = trim((string) ($payload['sid'] ?? ''));
        if ($callSid !== '') {
            $action->note = $this->appendNoteLine($action->note, $this->callSidTag($callSid));
            $action->save();
        }

        return response()->json([
            'message' => 'Llamada enviada a Twilio.',
            'call_sid' => $callSid !== '' ? $callSid : null,
            'status' => $payload['status'] ?? null,
            'action_id' => $action->id,
            'to' => $destinationNumber,
            'from' => $callerId,
        ]);
    }

    public function statusCallback(TwilioStatusCallbackRequest $request): Response
    {
        if (! $this->isTwilioEnabled()) {
            Log::info('Twilio status callback ignored because Twilio is disabled.', [
                'request_url' => $request->fullUrl(),
                'action_id' => $request->actionId(),
                'call_sid' => $request->callSid(),
            ]);

            return response('OK', 200);
        }

        $this->logTwilioCallback('Twilio status callback received.', $request);

        if (! $this->isValidCallback($request->accountSid(), $request->webhookToken())) {
            Log::warning('Twilio status callback rejected.', [
                'account_sid' => $request->accountSid(),
                'action_id' => $request->actionId(),
                'call_sid' => $request->callSid(),
                'source' => (string) $request->input('source', ''),
            ]);

            return response('Forbidden', 403);
        }

        $action = $this->resolveCallAction(
            $request->actionId(),
            $request->callSid(),
            $request->destinationNumber()
        );
        if (! $action) {
            Log::warning('Twilio status callback action not found.', [
                'action_id' => $request->actionId(),
                'call_sid' => $request->callSid(),
                'destination' => $request->destinationNumber(),
            ]);

            return response('OK', 200);
        }

        $callStatus = $request->callStatus();
        $callSid = $request->callSid();
        if ($callSid !== null) {
            $action->note = $this->appendNoteLine($action->note, $this->callSidTag($callSid));
        }

        if ($callStatus !== null) {
            $action->note = $this->appendNoteLine($action->note, 'Estado Twilio: '.$callStatus);
            if ($callStatus === 'completed' && $action->delivery_date === null) {
                $action->delivery_date = now();
            }

            if ($callStatus === 'completed' && $action->url === null && $callSid !== null) {
                $recordingUrl = $this->fetchRecordingUrlByCallSid($callSid);
                if ($recordingUrl !== null) {
                    $action->url = $recordingUrl;
                    $action->note = $this->appendNoteLine($action->note, 'Grabación Twilio: completed');
                }
            }
        }

        $duration = $request->callDuration();
        if ($duration !== null && $duration >= 0) {
            $action->creation_seconds = $duration;
        }

        if ($action->isDirty()) {
            $action->save();
        }

        return response('OK', 200);
    }

    public function recordingCallback(TwilioRecordingCallbackRequest $request): Response
    {
        if (! $this->isTwilioEnabled()) {
            Log::info('Twilio recording callback ignored because Twilio is disabled.', [
                'request_url' => $request->fullUrl(),
                'action_id' => $request->actionId(),
                'call_sid' => $request->callSid(),
            ]);

            return response('OK', 200);
        }

        $this->logTwilioCallback('Twilio recording callback received.', $request);

        if (! $this->isValidCallback($request->accountSid(), $request->webhookToken())) {
            Log::warning('Twilio recording callback rejected.', [
                'account_sid' => $request->accountSid(),
                'action_id' => $request->actionId(),
                'call_sid' => $request->callSid(),
            ]);

            return response('Forbidden', 403);
        }

        $action = $this->resolveCallAction(
            $request->actionId(),
            $request->callSid(),
            $request->destinationNumber()
        );
        if (! $action) {
            Log::warning('Twilio recording callback action not found.', [
                'action_id' => $request->actionId(),
                'call_sid' => $request->callSid(),
                'destination' => $request->destinationNumber(),
                'recording_status' => $request->recordingStatus(),
            ]);

            return response('OK', 200);
        }

        $recordingStatus = $request->recordingStatus();
        $callSid = $request->callSid();
        if ($callSid !== null) {
            $action->note = $this->appendNoteLine($action->note, $this->callSidTag($callSid));
        }

        if ($recordingStatus !== null) {
            $action->note = $this->appendNoteLine($action->note, 'Grabación Twilio: '.$recordingStatus);
        }

        $recordingUrl = $request->recordingUrl();
        if ($recordingStatus === 'completed' && $recordingUrl !== null) {
            $action->url = $this->ensureRecordingAudioUrl($recordingUrl);
            if ($action->delivery_date === null) {
                $action->delivery_date = now();
            }
        }

        if ($action->isDirty()) {
            $action->save();
        }

        return response('OK', 200);
    }

    protected function xmlResponse(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }

    private function buildInitialCallNote(string $creatorName, string $callerId, string $destinationNumber): string
    {
        return "Llamada Twilio iniciada por {$creatorName}. Línea: {$callerId}. Destino: {$destinationNumber}.";
    }

    private function directTwimlResponse(): Response
    {
        $twimlFilePath = base_path('twilio.xml');
        if (is_file($twimlFilePath)) {
            $twiml = trim((string) file_get_contents($twimlFilePath));
            if ($twiml !== '' && str_contains($twiml, '<Response')) {
                return $this->xmlResponse($twiml);
            }
        }

        return $this->xmlResponse(
            '<Response><Say voice="alice">Llamada iniciada desde AriChat.</Say></Response>'
        );
    }

    private function appendNoteLine(?string $note, string $line): string
    {
        $base = trim((string) $note);
        if ($base === '') {
            return $line;
        }

        if (str_contains($base, $line)) {
            return $base;
        }

        return $base."\n".$line;
    }

    private function callSidTag(string $callSid): string
    {
        return '[twilio_call_sid:'.$callSid.']';
    }

    private function resolveCallAction(?int $actionId, ?string $callSid, ?string $destinationNumber = null): ?Action
    {
        if ($actionId !== null) {
            $action = Action::query()
                ->whereKey($actionId)
                ->where('type_id', 21)
                ->first();
            if ($action) {
                return $action;
            }
        }

        if ($callSid === null) {
            return null;
        }

        return Action::query()
            ->where('type_id', 21)
            ->where('note', 'like', '%'.$this->callSidTag($callSid).'%')
            ->latest('id')
            ->first()
            ?? $this->resolveCallActionByDestination($destinationNumber);
    }

    private function resolveCallActionByDestination(?string $destinationNumber): ?Action
    {
        if ($destinationNumber === null || $destinationNumber === '') {
            return null;
        }

        return Action::query()
            ->where('type_id', 21)
            ->whereNull('delivery_date')
            ->where('created_at', '>=', now()->subHours(12))
            ->where('note', 'like', '%Destino: '.$destinationNumber.'%')
            ->latest('id')
            ->first();
    }

    private function isValidCallback(?string $accountSid, ?string $token): bool
    {
        $secret = $this->callbackSecret();
        if ($secret !== null) {
            if ($token === null) {
                return false;
            }

            return hash_equals($secret, $token);
        }

        $configuredAccountSid = trim((string) config('services.twilio.account_sid'));
        if ($configuredAccountSid === '') {
            return true;
        }

        if ($accountSid === null || $accountSid === '') {
            return false;
        }

        return hash_equals($configuredAccountSid, $accountSid);
    }

    private function callbackSecret(): ?string
    {
        $secret = trim((string) config('services.twilio.webhook_secret'));

        return $secret === '' ? null : $secret;
    }

    private function isTwilioEnabled(): bool
    {
        return app_feature_enabled('twilio_enabled', (bool) config('services.twilio.enabled', true));
    }

    private function isValidE164Phone(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        return (bool) preg_match('/^\+?[1-9]\d{6,14}$/', $value);
    }

    private function buildUrlWithQuery(string $url, array $query): string
    {
        $cleanQuery = array_filter($query, static function ($value) {
            return $value !== null && $value !== '';
        });

        if ($cleanQuery === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query($cleanQuery);
    }

    private function ensureRecordingAudioUrl(string $recordingUrl): string
    {
        if (preg_match('/\.(mp3|wav)(\?.*)?$/i', $recordingUrl) === 1) {
            return $recordingUrl;
        }

        return $recordingUrl.'.mp3';
    }

    private function fetchRecordingUrlByCallSid(string $callSid): ?string
    {
        $accountSid = trim((string) config('services.twilio.account_sid'));
        $authToken = trim((string) config('services.twilio.auth_token'));
        if ($accountSid === '' || $authToken === '') {
            return null;
        }

        try {
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->get("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Recordings.json", [
                    'CallSid' => $callSid,
                    'PageSize' => 1,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Twilio recordings lookup failed.', [
                'message' => $exception->getMessage(),
                'call_sid' => $callSid,
            ]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $recording = $response->json('recordings.0');
        if (! is_array($recording)) {
            return null;
        }

        $recordingUrl = trim((string) ($recording['media_url'] ?? ''));
        if ($recordingUrl === '') {
            $uri = trim((string) ($recording['uri'] ?? ''));
            if ($uri !== '') {
                $recordingUrl = 'https://api.twilio.com'.$uri;
            }
        }
        if ($recordingUrl === '') {
            return null;
        }

        if (str_ends_with($recordingUrl, '.json')) {
            $recordingUrl = substr($recordingUrl, 0, -5);
        }

        return $this->ensureRecordingAudioUrl($recordingUrl);
    }

    private function publicUrl(string $url): string
    {
        if (str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, 'http://')) {
            return 'https://'.substr($url, 7);
        }

        return $url;
    }

    private function logTwilioCallback(string $message, \Illuminate\Http\Request $request): void
    {
        Log::info($message, [
            'request_url' => $request->fullUrl(),
            'action_id' => $request->input('action_id'),
            'has_token' => $request->filled('token'),
            'account_sid' => $request->input('AccountSid'),
            'call_sid' => $request->input('CallSid'),
            'dial_call_sid' => $request->input('DialCallSid'),
            'to' => $request->input('To'),
            'called' => $request->input('Called'),
            'call_status' => $request->input('CallStatus'),
            'dial_call_status' => $request->input('DialCallStatus'),
            'call_duration' => $request->input('CallDuration'),
            'dial_call_duration' => $request->input('DialCallDuration'),
            'recording_sid' => $request->input('RecordingSid'),
            'recording_status' => $request->input('RecordingStatus'),
            'recording_url' => $this->shortenLogValue((string) $request->input('RecordingUrl', '')),
            'source' => $request->input('source'),
        ]);
    }

    private function shortenLogValue(string $value, int $maxLength = 180): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (mb_strlen($trimmed) <= $maxLength) {
            return $trimmed;
        }

        return mb_substr($trimmed, 0, $maxLength).'...';
    }
}
