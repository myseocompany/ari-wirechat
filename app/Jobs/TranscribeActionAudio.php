<?php

namespace App\Jobs;

use App\Models\Action;
use App\Models\ActionTranscription;
use App\Models\CustomerFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

class TranscribeActionAudio implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $actionId, public int $transcriptionId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transcription = ActionTranscription::find($this->transcriptionId);
        if (! $transcription) {
            return;
        }

        $action = Action::find($this->actionId);
        if (! $action) {
            $this->markError($transcription, 'Acción no encontrada.');

            return;
        }

        if (! $action->isCall()) {
            $this->markError($transcription, 'La acción no es una llamada con audio.');

            return;
        }

        $apiKey = (string) config('openai.api_key');
        $baseUrl = rtrim((string) config('openai.base_url', 'https://api.openai.com/v1'), '/');
        $timeout = (int) config('openai.timeout', 30);

        if ($apiKey === '') {
            $this->markError($transcription, 'missing_openai_configuration');

            return;
        }

        $transcription->status = 'processing';
        $transcription->error_message = null;
        $transcription->save();

        $tempFiles = [];

        try {
            $customerFile = $transcription->customerFile;
            $localPath = null;
            $contentType = null;
            $durationSeconds = null;

            if (! $customerFile) {
                [$localPath, $contentType, $originalName] = $this->downloadToTemp($action->url, $timeout);
                $tempFiles[] = $localPath;

                $durationSeconds = $this->getDurationSeconds($localPath);
                $customerFile = $this->storeCustomerFile($action, $transcription, $localPath, $contentType, $originalName);
                $transcription->customer_file_id = $customerFile->id;
            } else {
                [$localPath, $contentType] = $this->downloadFromCustomerFile($customerFile, $timeout);
                $tempFiles[] = $localPath;
                $durationSeconds = $this->getDurationSeconds($localPath);
            }

            if ($durationSeconds !== null) {
                $transcription->duration_seconds = (int) round($durationSeconds);
            }

            $chunkPaths = $this->splitIfNeeded($localPath, $tempFiles);
            $texts = [];

            foreach ($chunkPaths as $chunkPath) {
                $texts[] = $this->transcribeFile(
                    $chunkPath,
                    basename($chunkPath),
                    $apiKey,
                    $baseUrl,
                    $timeout
                );
            }

            $transcription->status = 'done';
            $transcription->transcript_text = trim(implode("\n\n", $texts));
            $transcription->error_message = null;
            $transcription->save();
        } catch (Throwable $exception) {
            $this->markError($transcription, $exception->getMessage());
        } finally {
            foreach ($tempFiles as $tempFile) {
                if ($tempFile && is_file($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }
    }

    private function downloadToTemp(string $url, int $timeout): array
    {
        $tempPath = $this->makeTempPath($url);

        $response = Http::timeout($timeout)
            ->sink($tempPath)
            ->get($url);

        if ($response->failed()) {
            throw new \RuntimeException('No se pudo descargar el audio.');
        }

        $contentType = (string) $response->header('Content-Type');
        $originalName = basename(parse_url($url, PHP_URL_PATH) ?? '') ?: 'audio';

        return [$tempPath, $contentType, $originalName];
    }

    private function downloadFromCustomerFile(CustomerFile $file, int $timeout): array
    {
        $url = $file->public_url;
        if (! $url) {
            throw new \RuntimeException('No se pudo generar la URL del archivo.');
        }

        $tempPath = $this->makeTempPath($file->url);
        $response = Http::timeout($timeout)
            ->sink($tempPath)
            ->get($url);

        if ($response->failed()) {
            throw new \RuntimeException('No se pudo descargar el archivo almacenado.');
        }

        return [$tempPath, (string) $response->header('Content-Type')];
    }

    private function storeCustomerFile(Action $action, ActionTranscription $transcription, string $localPath, ?string $contentType, string $originalName): CustomerFile
    {
        $extension = $this->guessExtension($originalName, $contentType);
        $storedName = (string) Str::uuid();
        if ($extension !== '') {
            $storedName .= '.'.$extension;
        }

        $disk = Storage::disk('spaces');
        $key = "files/{$action->customer_id}/{$storedName}";
        $stream = fopen($localPath, 'r');

        $disk->put($key, $stream, [
            'visibility' => 'public',
            'ContentType' => $contentType ?: 'application/octet-stream',
        ]);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return CustomerFile::create([
            'customer_id' => $action->customer_id,
            'action_id' => $action->id,
            'url' => $storedName,
            'name' => $originalName !== '' ? $originalName : 'Llamada '.$action->id,
            'creator_user_id' => $transcription->requested_by,
            'uuid' => (string) Str::uuid(),
            'filename' => $storedName,
            'size' => filesize($localPath) ?: null,
            'mime_type' => $contentType,
        ]);
    }

    private function transcribeFile(string $path, string $filename, string $apiKey, string $baseUrl, int $timeout): string
    {
        $stream = fopen($path, 'r');
        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->attach('file', $stream, $filename)
            ->post($baseUrl.'/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'es',
                'response_format' => 'json',
            ]);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if ($response->failed()) {
            throw new \RuntimeException('Error de transcripción: http_'.$response->status());
        }

        $text = (string) data_get($response->json(), 'text', '');
        if ($text === '') {
            throw new \RuntimeException('Transcripción vacía.');
        }

        return $text;
    }

    private function splitIfNeeded(string $path, array &$tempFiles): array
    {
        $maxBytes = 25 * 1024 * 1024;
        $size = filesize($path) ?: 0;

        if ($size <= $maxBytes) {
            return [$path];
        }

        $duration = $this->getDurationSeconds($path);
        if (! $duration || $duration <= 0) {
            throw new \RuntimeException('No se pudo leer la duración del audio para dividirlo.');
        }

        $bytesPerSecond = $size / $duration;
        if ($bytesPerSecond <= 0) {
            throw new \RuntimeException('No se pudo calcular el bitrate del audio.');
        }

        $segmentSeconds = (int) max(1, floor(($maxBytes * 0.9) / $bytesPerSecond));
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $extension = $extension !== '' ? $extension : 'mp3';
        $outputPattern = $this->makeTempDir().'/segment-%03d.'.$extension;

        $process = new Process([
            'ffmpeg',
            '-i',
            $path,
            '-f',
            'segment',
            '-segment_time',
            (string) $segmentSeconds,
            '-c',
            'copy',
            $outputPattern,
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('No se pudo dividir el audio.');
        }

        $segments = glob(str_replace('%03d', '*', $outputPattern)) ?: [];
        if ($segments === []) {
            throw new \RuntimeException('No se generaron segmentos de audio.');
        }

        foreach ($segments as $segment) {
            $tempFiles[] = $segment;
        }

        return $segments;
    }

    private function getDurationSeconds(string $path): ?float
    {
        $process = new Process([
            'ffprobe',
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $path,
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            return null;
        }

        return (float) $output;
    }

    private function makeTempPath(string $seed): string
    {
        $extension = strtolower(pathinfo($seed, PATHINFO_EXTENSION));
        $filename = (string) Str::uuid();
        if ($extension !== '') {
            $filename .= '.'.$extension;
        }

        return $this->makeTempDir().'/'.$filename;
    }

    private function makeTempDir(): string
    {
        $dir = storage_path('app/tmp/transcriptions');
        File::ensureDirectoryExists($dir);

        return $dir;
    }

    private function guessExtension(string $name, ?string $contentType): string
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($extension !== '') {
            return $extension;
        }

        $contentType = $contentType ? trim(strtolower(explode(';', $contentType)[0])) : null;
        $map = [
            'audio/mpeg' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/x-m4a' => 'm4a',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
            'audio/webm' => 'webm',
        ];

        return $contentType && isset($map[$contentType]) ? $map[$contentType] : '';
    }

    private function markError(ActionTranscription $transcription, string $message): void
    {
        $transcription->status = 'error';
        $transcription->error_message = $message;
        $transcription->save();
    }
}
