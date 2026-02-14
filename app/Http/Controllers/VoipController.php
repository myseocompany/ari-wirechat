<?php

namespace App\Http\Controllers;

use App\Http\Requests\Voip\GenerateTokenRequest;
use App\Http\Requests\Voip\PlaceCallRequest;
use App\Http\Requests\Voip\RenderTwimlRequest;
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

        $twiml = <<<XML
<Response>
    <Dial callerId="{$safeCallerId}">
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

    protected function xmlResponse(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }
}
