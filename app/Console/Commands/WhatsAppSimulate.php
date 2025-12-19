<?php

namespace App\Console\Commands;

use App\Services\WhatsAppInboundMessageService;
use Illuminate\Console\Command;

class WhatsAppSimulate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:simulate {jsonFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula un webhook entrante de WhatsApp usando un archivo JSON.';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppInboundMessageService $service): int
    {
        $path = (string) $this->argument('jsonFile');
        $resolvedPath = $this->resolvePath($path);

        if (! is_file($resolvedPath)) {
            $this->error("Archivo no encontrado: {$resolvedPath}");

            return self::FAILURE;
        }

        $contents = file_get_contents($resolvedPath);
        if ($contents === false) {
            $this->error("No se pudo leer el archivo: {$resolvedPath}");

            return self::FAILURE;
        }

        try {
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->error('JSON inválido: '.$exception->getMessage());

            return self::FAILURE;
        }

        $processed = $service->handle($payload);
        $this->info("Mensajes procesados: {$processed}");

        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }
}
