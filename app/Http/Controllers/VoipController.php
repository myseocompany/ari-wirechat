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
            'twiml_url' => url('/api/voip/twiml'),
        ]);
    }

    public function twiml(RenderTwimlRequest $request): Response
    {
        $destinationNumber = $request->destinationNumber();
        $actionId = $request->actionId();

        if ($destinationNumber === null) {
            return $this->xmlResponse(
                '<Response><Say voice="alice">No destination number was provided.</Say></Response>'
            );
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
            $recordingCallbackUrl = $this->buildUrlWithQuery(
                route('api.voip.callbacks.recording'),
                [
                    'action_id' => $actionId,
                    'token' => $this->callbackSecret(),
                ]
            );
            $safeRecordingCallbackUrl = htmlspecialchars($recordingCallbackUrl, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $dialAttributes[] = 'record="record-from-answer-dual"';
            $dialAttributes[] = 'recordingStatusCallback="'.$safeRecordingCallbackUrl.'"';
            $dialAttributes[] = 'recordingStatusCallbackMethod="POST"';
        }

        $dialAttributesText = implode(' ', $dialAttributes);
        $twiml = <<<XML
<Response>
    <Dial {$dialAttributesText}>
        <Number>{$safeDestination}</Number>
    </Dial>
</Response>
XML;

        return $this->xmlResponse($twiml);
    }

    public function call(PlaceCallRequest $request): JsonResponse
    {
        $accountSid = (string) config('services.twilio.account_sid');
        $authToken = (string) config('services.twilio.auth_token');
        $callerId = (string) config('services.twilio.caller_id');
        $destinationNumber = $request->destinationNumber();
        $twimlUrl = url('/api/voip/twiml');

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
        $user = $request->user();

        if (! $customer->hasFullAccess($user)) {
            return response()->json([
                'message' => 'No tienes permisos para llamar a este cliente.',
            ], 403);
        }

        $agentPhone = $request->agentPhone();
        if ($agentPhone === null) {
            return response()->json([
                'message' => 'Debes indicar el teléfono del asesor en formato E.164.',
            ], 422);
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
            'note' => $this->buildInitialCallNote($creatorName, $agentPhone, $destinationNumber),
        ]);

        $secret = $this->callbackSecret();
        $twimlUrl = $this->buildUrlWithQuery(route('api.voip.twiml'), [
            'to' => $destinationNumber,
            'action_id' => $action->id,
            'token' => $secret,
        ]);
        $statusCallbackUrl = $this->buildUrlWithQuery(route('api.voip.callbacks.status'), [
            'action_id' => $action->id,
            'token' => $secret,
        ]);

        try {
            $response = Http::asForm()
                ->withBasicAuth($accountSid, $authToken)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Calls.json", [
                    'To' => $agentPhone,
                    'From' => $callerId,
                    'Url' => $twimlUrl,
                    'Method' => 'POST',
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
            'agent_phone' => $agentPhone,
            'to' => $destinationNumber,
            'from' => $callerId,
        ]);
    }

    public function statusCallback(TwilioStatusCallbackRequest $request): Response
    {
        if (! $this->isValidCallback($request->accountSid(), $request->webhookToken())) {
            return response('Forbidden', 403);
        }

        $action = $this->resolveCallAction($request->actionId(), $request->callSid());
        if (! $action) {
            return response('OK', 200);
        }

        $callStatus = $request->callStatus();
        if ($callStatus !== null) {
            $action->note = $this->appendNoteLine($action->note, 'Estado Twilio: '.$callStatus);
            if ($callStatus === 'completed' && $action->delivery_date === null) {
                $action->delivery_date = now();
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
        if (! $this->isValidCallback($request->accountSid(), $request->webhookToken())) {
            return response('Forbidden', 403);
        }

        $action = $this->resolveCallAction($request->actionId(), $request->callSid());
        if (! $action) {
            return response('OK', 200);
        }

        $recordingStatus = $request->recordingStatus();
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

    private function buildInitialCallNote(string $creatorName, string $agentPhone, string $destinationNumber): string
    {
        return "Llamada Twilio iniciada por {$creatorName}. Asesor: {$agentPhone}. Destino: {$destinationNumber}.";
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

    private function resolveCallAction(?int $actionId, ?string $callSid): ?Action
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
            ->first();
    }

    private function isValidCallback(string $accountSid, ?string $token): bool
    {
        $configuredAccountSid = trim((string) config('services.twilio.account_sid'));
        if ($configuredAccountSid === '' || ! hash_equals($configuredAccountSid, $accountSid)) {
            return false;
        }

        $secret = $this->callbackSecret();
        if ($secret === null) {
            return true;
        }

        if ($token === null) {
            return false;
        }

        return hash_equals($secret, $token);
    }

    private function callbackSecret(): ?string
    {
        $secret = trim((string) config('services.twilio.webhook_secret'));

        return $secret === '' ? null : $secret;
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
}
