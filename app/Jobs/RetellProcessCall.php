<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Action;
use App\Models\Customer;

class RetellProcessCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = [10, 30, 120];

    public function __construct(public array $data) {}

    public function handle(): void
    {
        // 1) Normaliza payload
        $data = isset($this->data[0]) ? $this->data[0] : $this->data;
        if (isset($data['body']) && is_array($data['body'])) $data = $data['body'];
        $call   = $data['call'] ?? $data;

        $event        = $data['event'] ?? ($data['type'] ?? null);
        $callId       = $call['call_id']           ?? null;
        $agentName    = $call['agent_name']        ?? null;
        $status       = $call['call_status']       ?? null;
        $direction    = $call['direction']         ?? null; // inbound|outbound
        $fromNumber   = $call['from_number']       ?? null;
        $toNumber     = $call['to_number']         ?? null;
        $durationMs   = $call['duration_ms']       ?? null;
        $recordingUrl = $call['recording_url']     ?? null;
        $publicLogUrl = $call['public_log_url']    ?? null;

        $analysis     = $call['call_analysis']     ?? [];
        $summary      = $analysis['call_summary']  ?? null;
        $sentiment    = $analysis['user_sentiment']?? null;
        $successful   = $analysis['call_successful'] ?? null;
        $transcript   = $call['transcript']        ?? null;

        if (! $callId) {
            Log::warning('Retell job without call_id, skipping');
            return;
        }

        Log::info('Retell job start', [
            'event' => $event, 'call_id' => $callId, 'status' => $status,
            'direction' => $direction, 'from' => $fromNumber, 'to' => $toNumber
        ]);

        // 2) Buscar cliente por teléfono (usa columnas *_last9 indexadas)
        $customer = $this->findCustomerByPhone($toNumber, $fromNumber);
        if (! $customer) {
            Log::info('Retell: no customer match by phone', ['from' => $fromNumber, 'to' => $toNumber]);
            return;
        }

        // 3) Construir nota
        $pieces = [];
        if ($agentName)   { $pieces[] = "Agente: {$agentName}"; }
        if ($direction)   { $pieces[] = "Dirección: {$direction}"; }
        if ($status)      { $pieces[] = "Estado: {$status}"; }
        if ($durationMs)  { $pieces[] = "Duración: ~".round($durationMs/1000)."s"; }
        if ($sentiment)   { $pieces[] = "Sentimiento: {$sentiment}"; }
        if (is_bool($successful)) { $pieces[] = "Exitosa: ".($successful ? 'sí' : 'no'); }
        if ($summary)     { $pieces[] = "Resumen: {$summary}"; }

        $note = "Llamada (Retell) — ".implode(' | ', $pieces);
        if ($transcript) {
            $safeTranscript = mb_substr($transcript, 0, 2000);
            $note .= "\n\nTranscripción:\n".$safeTranscript.(mb_strlen($transcript) > 2000 ? " …[truncated]" : "");
        }

        // 4) Crear/asegurar Acción (idempotente por retell_call_id único)
        $url = $recordingUrl ?: $publicLogUrl;

        Action::firstOrCreate(
            ['retell_call_id' => $callId],
            [
                'customer_id'     => $customer->id,
                'type_id'         => 104,
                'creator_user_id' => 1,
                'url'             => $url,
                'note'            => $note,
            ]
        );

        // 5) (Opcional) mover estado del cliente si fue exitosa
        // if ($successful && in_array($customer->status_id, [1,36,28])) {
        //     $cHistory = new \App\Models\CustomerHistory;
        //     $cHistory->saveFromModel($customer);
        //     $customer->status_id = 28;
        //     $customer->save();
        // }

        Log::info('Retell job done', ['call_id' => $callId, 'customer_id' => $customer->id]);
    }

    /**
     * Intenta encontrar cliente usando las columnas generadas e indexadas *_last9
     * Prioriza callee en outbound (to), luego caller (from).
     */
    private function findCustomerByPhone(?string $toNumber, ?string $fromNumber): ?Customer
    {
        $candidates = array_values(array_filter([$toNumber, $fromNumber]));
        foreach ($candidates as $num) {
            $digits = preg_replace('/\D+/', '', (string)$num);
            if (! $digits) continue;

            $last9 = substr($digits, -9);
            if (strlen($last9) < 7) continue; // evita matches absurdos

            $match = Customer::query()
                ->where('phone_last9', $last9)
                ->orWhere('phone2_last9', $last9)
                ->orWhere('contact_phone2_last9', $last9)
                ->first();

            if ($match) return $match;
        }
        return null;
    }
}
