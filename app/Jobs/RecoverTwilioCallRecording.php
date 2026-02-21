<?php

namespace App\Jobs;

use App\Models\Action;
use App\Models\Customer;
use App\Models\TwilioCallRecovery;
use App\Services\TwilioRecoveryApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RecoverTwilioCallRecording implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 180, 600];

    public function __construct(public int $recoveryId) {}

    public function handle(TwilioRecoveryApiService $twilioRecoveryApiService): void
    {
        $recovery = TwilioCallRecovery::query()->find($this->recoveryId);
        if (! $recovery) {
            return;
        }

        $recovery->forceFill([
            'status' => TwilioCallRecovery::STATUS_PROCESSING,
            'processed_at' => now(),
            'error' => null,
        ])->save();

        try {
            if (! $twilioRecoveryApiService->isConfigured()) {
                throw new \RuntimeException('No hay credenciales de Twilio configuradas en el servidor.');
            }

            $recordingUrl = trim((string) ($recovery->recording_url ?? ''));
            $recordingPayload = null;
            $errorCode = null;

            if ($recordingUrl === '') {
                $recordingInfo = $twilioRecoveryApiService->fetchRecordingForCall($recovery->call_sid);
                $recordingUrl = trim((string) ($recordingInfo['recording_url'] ?? ''));
                $errorCode = $recordingInfo['error_code'] ?? null;
                $recordingPayload = $recordingInfo['raw'] ?? null;

                if (! empty($recordingInfo['recording_sid'])) {
                    $recovery->recording_sid = (string) $recordingInfo['recording_sid'];
                }

                if (is_numeric($recordingInfo['duration_seconds'] ?? null) && $recovery->duration_seconds === null) {
                    $recovery->duration_seconds = (int) $recordingInfo['duration_seconds'];
                }
            }

            if ($recordingUrl === '') {
                $recovery->forceFill([
                    'status' => $errorCode === 'RECORDING_NOT_FOUND'
                        ? TwilioCallRecovery::STATUS_NO_RECORDING
                        : TwilioCallRecovery::STATUS_FAILED,
                    'error' => $errorCode ?? 'No fue posible obtener la URL de grabación.',
                    'payload' => is_array($recordingPayload) ? $recordingPayload : $recovery->payload,
                ])->save();

                return;
            }

            $download = $twilioRecoveryApiService->downloadRecordingContent($recordingUrl);
            $relativePath = $this->buildStoragePath($recovery->call_sid, $recordingUrl, (string) $download['content_type']);
            Storage::disk('local')->put($relativePath, $download['body']);

            $this->createOrUpdateActionIfNeeded($recovery, $recordingUrl);

            $recovery->forceFill([
                'recording_url' => $recordingUrl,
                'recording_exists' => true,
                'status' => TwilioCallRecovery::STATUS_RECOVERED,
                'recovered_at' => now(),
                'local_file_path' => $relativePath,
                'local_file_size' => (int) ($download['content_length'] ?? strlen((string) $download['body'])),
                'error' => null,
                'payload' => is_array($recordingPayload) ? $recordingPayload : $recovery->payload,
            ])->save();
        } catch (Throwable $exception) {
            Log::error('Twilio recovery job failed.', [
                'recovery_id' => $recovery->id,
                'call_sid' => $recovery->call_sid,
                'error' => $exception->getMessage(),
            ]);

            $recovery->forceFill([
                'status' => TwilioCallRecovery::STATUS_FAILED,
                'error' => mb_substr($exception->getMessage(), 0, 1900),
            ])->save();
        }
    }

    private function createOrUpdateActionIfNeeded(TwilioCallRecovery $recovery, string $recordingUrl): void
    {
        $callSidTag = $this->callSidTag($recovery->call_sid);
        $durationSuffix = $recovery->duration_seconds !== null ? ' duration: '.$recovery->duration_seconds.'s' : '';
        $customer = $this->findCustomerForRecovery($recovery);

        if (! $customer) {
            Log::info('Twilio recovery without matching customer.', [
                'call_sid' => $recovery->call_sid,
                'contact_msisdn' => $recovery->contact_msisdn,
                'from_number' => $recovery->from_number,
                'to_number' => $recovery->to_number,
            ]);

            return;
        }

        $existingAction = Action::query()
            ->where('type_id', 21)
            ->where('customer_id', $customer->id)
            ->where(function ($query) use ($recordingUrl, $callSidTag): void {
                $query->where('note', 'like', '%'.$callSidTag.'%')
                    ->orWhere('url', $recordingUrl);
            })
            ->latest('id')
            ->first();

        if ($existingAction !== null) {
            if ($existingAction->delivery_date === null && $recovery->call_created_at !== null) {
                $existingAction->delivery_date = $recovery->call_created_at;
            }

            if ($existingAction->creation_seconds === null && $recovery->duration_seconds !== null) {
                $existingAction->creation_seconds = (int) $recovery->duration_seconds;
            }

            if ($existingAction->url === null || trim((string) $existingAction->url) === '') {
                $existingAction->url = $recordingUrl;
            }

            $existingAction->note = $this->appendNoteLine((string) $existingAction->note, $callSidTag);
            $existingAction->note = $this->appendNoteLine((string) $existingAction->note, 'Grabación Twilio: recovered');

            if ($durationSuffix !== '') {
                $existingAction->note = $this->appendNoteLine((string) $existingAction->note, trim($durationSuffix));
            }

            if ($existingAction->isDirty()) {
                $existingAction->save();
            }

            return;
        }

        $note = 'Llamada de Twilio (backfill)';
        $note = $this->appendNoteLine($note, $callSidTag);
        $note = $this->appendNoteLine($note, 'Grabación Twilio: recovered');
        if ($durationSuffix !== '') {
            $note = $this->appendNoteLine($note, trim($durationSuffix));
        }

        Action::query()->create([
            'customer_id' => $customer->id,
            'type_id' => 21,
            'creator_user_id' => 1,
            'delivery_date' => $recovery->call_created_at ?? now(),
            'creation_seconds' => $recovery->duration_seconds,
            'url' => $recordingUrl,
            'note' => $note,
        ]);
    }

    private function findCustomerForRecovery(TwilioCallRecovery $recovery): ?Customer
    {
        $candidates = [
            $recovery->contact_msisdn,
            $recovery->to_number,
            $recovery->from_number,
        ];

        foreach ($candidates as $candidate) {
            $customer = $this->findCustomerByMsisdn($candidate);
            if ($customer) {
                return $customer;
            }
        }

        return null;
    }

    private function findCustomerByMsisdn(?string $msisdn): ?Customer
    {
        if ($msisdn === null || trim($msisdn) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $msisdn);
        if (! is_string($digits) || $digits === '') {
            return null;
        }

        $customer = Customer::findByPhoneInternational($digits);
        if ($customer) {
            return $customer;
        }

        $digits10 = substr($digits, -10);
        if ($digits10 === '') {
            return null;
        }

        return Customer::query()
            ->whereRaw("REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'(',')') LIKE ?", ["%{$digits10}%"])
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone2,' ',''),'-',''),'(',')') LIKE ?", ["%{$digits10}%"])
            ->first();
    }

    private function buildStoragePath(string $callSid, string $recordingUrl, string $contentType): string
    {
        $safeCallSid = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $callSid);
        if (! is_string($safeCallSid) || $safeCallSid === '') {
            $safeCallSid = 'unknown_call';
        }

        $extension = $this->resolveExtension($recordingUrl, $contentType);

        return sprintf(
            'twilio-recordings/%s/%s.%s',
            now()->format('Y/m/d'),
            $safeCallSid,
            $extension
        );
    }

    private function resolveExtension(string $recordingUrl, string $contentType): string
    {
        $path = (string) parse_url($recordingUrl, PHP_URL_PATH);
        $extensionFromUrl = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($extensionFromUrl, ['mp3', 'wav', 'm4a', 'ogg', 'oga', 'webm'], true)) {
            return $extensionFromUrl;
        }

        $contentType = strtolower(trim($contentType));

        return match (true) {
            str_contains($contentType, 'audio/wav') => 'wav',
            str_contains($contentType, 'audio/mp4') => 'm4a',
            str_contains($contentType, 'audio/ogg') => 'ogg',
            str_contains($contentType, 'audio/webm') => 'webm',
            default => 'mp3',
        };
    }

    private function callSidTag(string $callSid): string
    {
        return '[twilio_call_sid:'.$callSid.']';
    }

    private function appendNoteLine(string $note, string $line): string
    {
        $base = trim($note);
        if ($base === '') {
            return $line;
        }

        if (str_contains($base, $line)) {
            return $base;
        }

        return $base."\n".$line;
    }
}
