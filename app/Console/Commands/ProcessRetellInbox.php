<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessRetellInbox extends Command
{
    protected $signature = 'retell:process {--limit=100}';
    protected $description = 'Procesa eventos Retell pendientes';

    public function handle()
    {
        $limit = (int)$this->option('limit');

        \DB::table('retell_inbox')
            ->whereNull('processed_at')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function ($row) {
                try {
                    $data = json_decode($row->payload, true) ?? [];
                    $call = $data['call'] ?? $data;

                    // === AQUÍ pega tu lógica actual de parseo, match de cliente
                    //     y creación de Action (la que hoy está en el webhook) ===
                    // Recomendaciones:
                    // - Normaliza teléfono (ver nota abajo).
                    // - No hagas LIKE "%1234567890%" sin índice.
                    // - Guarda action->retell_call_id = $row->call_id para idempotencia.

                    // Ejemplo mínimo:
                    $this->crearActionDesdeRetell($call, $data);

                    \DB::table('retell_inbox')
                        ->where('id', $row->id)
                        ->update(['processed_at' => now(), 'error' => null, 'updated_at' => now()]);
                } catch (\Throwable $e) {
                    \Log::error('Retell process error: '.$e->getMessage());
                    \DB::table('retell_inbox')
                        ->where('id', $row->id)
                        ->update(['error' => substr($e->getMessage(),0,190), 'updated_at' => now()]);
                }
            });

        return 0;
    }

    private function crearActionDesdeRetell(array $call, array $data)
    {
        // Tu lógica de crear Action… (idéntica a la que ya tienes)
        // Sugerencia: añade $action->retell_call_id = $call['call_id'] ?? null;
    }
}
