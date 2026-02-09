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
        $this->updateProgress($transcription, 'start', 'Iniciando transcripción', 5);

        $tempFiles = [];

        try {
            $customerFile = $transcription->customerFile;
            $localPath = null;
            $contentType = null;
            $durationSeconds = null;

            if (! $customerFile) {
                $this->updateProgress($transcription, 'download', 'Descargando audio original', 10);
                [$localPath, $contentType, $originalName] = $this->downloadToTemp($action->url, $timeout);
                $contentType = $this->normalizeContentType($contentType) ?? $this->detectContentType($localPath);
                $tempFiles[] = $localPath;

                $durationSeconds = $this->getDurationSeconds($localPath);
                $this->updateProgress($transcription, 'store', 'Guardando audio en archivos del cliente', 20);
                $customerFile = $this->storeCustomerFile($action, $transcription, $localPath, $contentType, $originalName);
                $transcription->customer_file_id = $customerFile->id;
            } else {
                $this->updateProgress($transcription, 'download', 'Descargando audio almacenado', 10);
                [$localPath, $contentType] = $this->downloadFromCustomerFile($customerFile, $timeout);
                $contentType = $this->normalizeContentType($contentType) ?? $this->detectContentType($localPath);
                $customerFile = $this->ensureCustomerFileExtension($customerFile, $localPath, $contentType);
                $tempFiles[] = $localPath;
                $durationSeconds = $this->getDurationSeconds($localPath);
            }

            if ($durationSeconds !== null) {
                $transcription->duration_seconds = (int) round($durationSeconds);
            }

            $this->updateProgress($transcription, 'prepare', 'Preparando audio para transcripción', 30);
            $chunkPaths = $this->splitIfNeeded($localPath, $tempFiles);
            $texts = [];

            $totalChunks = count($chunkPaths);
            foreach ($chunkPaths as $index => $chunkPath) {
                $percent = 30 + (int) round((($index + 1) / max(1, $totalChunks)) * 60);
                $this->updateProgress(
                    $transcription,
                    'transcribe',
                    'Transcribiendo segmento '.($index + 1).' de '.$totalChunks,
                    min(95, $percent)
                );
                $chunkContentType = $this->detectContentType($chunkPath) ?? $contentType;
                $texts[] = $this->transcribeFile(
                    $chunkPath,
                    basename($chunkPath),
                    $chunkContentType,
                    $apiKey,
                    $baseUrl,
                    $timeout
                );
            }

            $transcription->status = 'done';
            $this->updateProgress($transcription, 'done', 'Transcripción completada', 100);
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

    private function ensureCustomerFileExtension(CustomerFile $file, string $localPath, ?string $contentType): CustomerFile
    {
        $existingExtension = pathinfo($file->url, PATHINFO_EXTENSION);
        if ($existingExtension !== '') {
            return $file;
        }

        $extension = $this->guessExtension($file->url, $contentType);
        if ($extension === '') {
            return $file;
        }

        $disk = Storage::disk('spaces');
        $newName = $file->url.'.'.$extension;
        $newKey = "files/{$file->customer_id}/{$newName}";
        $stream = fopen($localPath, 'r');

        $disk->put($newKey, $stream, [
            'visibility' => 'public',
            'ContentType' => $contentType ?: 'application/octet-stream',
        ]);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $oldKey = "files/{$file->customer_id}/{$file->url}";
        if ($disk->exists($oldKey)) {
            $disk->delete($oldKey);
        }

        $file->url = $newName;
        $file->filename = $newName;
        $file->size = filesize($localPath) ?: $file->size;
        $file->mime_type = $contentType ?: $file->mime_type;
        $file->save();

        return $file;
    }

    private function transcribeFile(string $path, string $filename, ?string $contentType, string $apiKey, string $baseUrl, int $timeout): string
    {
        $stream = fopen($path, 'r');
        $response = Http::timeout($timeout)
            ->withToken($apiKey)
            ->attach('file', $stream, $filename, $contentType ? ['Content-Type' => $contentType] : [])
            ->post($baseUrl.'/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'es',
                'response_format' => 'json',
            ]);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if ($response->failed()) {
            $body = $response->json() ?: $response->body();
            $bodyPreview = is_string($body) ? $body : json_encode($body);
            $bodyPreview = $bodyPreview ? mb_substr($bodyPreview, 0, 600) : '';

            \Log::warning('whisper_transcription_failed', [
                'status' => $response->status(),
                'body' => $body,
            ]);

            throw new \RuntimeException('Error de transcripción: http_'.$response->status().' '.$bodyPreview);
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
        if ($extension === '') {
            $extension = $this->guessExtension('', $this->detectContentType($path)) ?: 'mp3';
        }
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

        $contentType = $this->normalizeContentType($contentType);
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

    private function normalizeContentType(?string $contentType): ?string
    {
        if (! $contentType) {
            return null;
        }

        $normalized = trim(strtolower(explode(';', $contentType)[0]));

        return $normalized === '' ? null : $normalized;
    }

    private function detectContentType(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (! $finfo) {
            return null;
        }

        $mime = finfo_file($finfo, $path) ?: null;
        finfo_close($finfo);

        return $mime ? $this->normalizeContentType($mime) : null;
    }

    private function markError(ActionTranscription $transcription, string $message): void
    {
        $transcription->status = 'error';
        $transcription->error_message = $message;
        $transcription->progress_step = 'error';
        $transcription->progress_message = 'Error durante la transcripción';
        $transcription->progress_percent = null;
        $transcription->save();
    }

    private function updateProgress(ActionTranscription $transcription, string $step, string $message, ?int $percent = null): void
    {
        $transcription->progress_step = $step;
        $transcription->progress_message = $message;
        $transcription->progress_percent = $percent;
        $transcription->save();
    }
}
