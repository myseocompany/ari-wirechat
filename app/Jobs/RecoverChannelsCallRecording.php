<?php

namespace App\Jobs;

use App\Models\Action;
use App\Models\ChannelsCallRecovery;
use App\Models\Customer;
use App\Models\User;
use App\Services\ChannelsApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RecoverChannelsCallRecording implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 180, 600];

    public function __construct(public int $recoveryId) {}

    public function handle(ChannelsApiService $channelsApiService): void
    {
        $recovery = ChannelsCallRecovery::query()->find($this->recoveryId);
        if (! $recovery) {
            return;
        }

        $recovery->forceFill([
            'status' => ChannelsCallRecovery::STATUS_PROCESSING,
            'processed_at' => now(),
            'error' => null,
        ])->save();

        try {
            if (! $channelsApiService->isConfigured()) {
                throw new \RuntimeException('No hay credenciales de Channels configuradas en el servidor.');
            }

            $recordingUrl = trim((string) ($recovery->recording_url ?? ''));
            $recordingPayload = null;
            $errorCode = null;

            if ($recordingUrl === '') {
                $recordingInfo = $channelsApiService->fetchRecordingForCall($recovery->call_id);
                $recordingUrl = trim((string) ($recordingInfo['recording_url'] ?? ''));
                $errorCode = $recordingInfo['error_code'] ?? null;
                $recordingPayload = $recordingInfo['raw'] ?? null;
            }

            if ($recordingUrl === '') {
                $recovery->forceFill([
                    'status' => $errorCode === 'RECORDING_NOT_FOUND'
                        ? ChannelsCallRecovery::STATUS_NO_RECORDING
                        : ChannelsCallRecovery::STATUS_FAILED,
                    'error' => $errorCode ?? 'No fue posible obtener la URL de grabación.',
                    'payload' => is_array($recordingPayload) ? $recordingPayload : $recovery->payload,
                ])->save();

                return;
            }

            $download = $channelsApiService->downloadRecordingContent($recordingUrl);
            $relativePath = $this->buildStoragePath($recovery->call_id, $recordingUrl, (string) $download['content_type']);
            Storage::disk('local')->put($relativePath, $download['body']);

            $this->createActionIfNeeded($recovery, $recordingUrl);

            $recovery->forceFill([
                'recording_url' => $recordingUrl,
                'status' => ChannelsCallRecovery::STATUS_RECOVERED,
                'recovered_at' => now(),
                'local_file_path' => $relativePath,
                'local_file_size' => (int) ($download['content_length'] ?? strlen((string) $download['body'])),
                'error' => null,
                'payload' => is_array($recordingPayload) ? $recordingPayload : $recovery->payload,
            ])->save();
        } catch (Throwable $exception) {
            Log::error('Channels recovery job failed.', [
                'recovery_id' => $recovery->id,
                'call_id' => $recovery->call_id,
                'error' => $exception->getMessage(),
            ]);

            $recovery->forceFill([
                'status' => ChannelsCallRecovery::STATUS_FAILED,
                'error' => mb_substr($exception->getMessage(), 0, 1900),
            ])->save();
        }
    }

    private function createActionIfNeeded(ChannelsCallRecovery $recovery, string $recordingUrl): void
    {
        $customer = $this->findCustomerByMsisdn($recovery->msisdn);
        if (! $customer) {
            return;
        }

        $exists = Action::query()
            ->where('customer_id', $customer->id)
            ->where(function ($query) use ($recordingUrl, $recovery): void {
                $query->where('url', $recordingUrl)
                    ->orWhere('note', 'like', '%'.$recovery->call_id.'%');
            })
            ->exists();

        if ($exists) {
            return;
        }

        $creatorUserId = $this->resolveCreatorUserId($recovery);

        Action::query()->create([
            'customer_id' => $customer->id,
            'type_id' => 21,
            'creator_user_id' => $creatorUserId ?: 1,
            'delivery_date' => $recovery->call_created_at ?? now(),
            'url' => $recordingUrl,
            'note' => 'Llamada de Channels (backfill) call_id: '.$recovery->call_id,
        ]);
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

    private function buildStoragePath(string $callId, string $recordingUrl, string $contentType): string
    {
        $safeCallId = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $callId);
        if (! is_string($safeCallId) || $safeCallId === '') {
            $safeCallId = 'unknown_call';
        }

        $extension = $this->resolveExtension($recordingUrl, $contentType);

        return sprintf(
            'channels-recordings/%s/%s.%s',
            now()->format('Y/m/d'),
            $safeCallId,
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

    private function resolveCreatorUserId(ChannelsCallRecovery $recovery): ?int
    {
        if (is_numeric($recovery->agent_id)) {
            $fromChannelsId = User::getIdFromChannelsId((int) $recovery->agent_id);
            if ($fromChannelsId !== null) {
                return $fromChannelsId;
            }
        }

        $payload = is_array($recovery->payload) ? $recovery->payload : [];
        $candidateUsername = strtolower(trim((string) (
            data_get($payload, 'agent_username')
            ?? data_get($payload, 'agentUsername')
            ?? data_get($payload, 'agent.email')
            ?? ''
        )));

        if ($candidateUsername !== '') {
            $user = User::query()
                ->whereRaw('LOWER(channels_email) = ?', [$candidateUsername])
                ->first();

            if ($user) {
                return (int) $user->id;
            }
        }

        return null;
    }
}
