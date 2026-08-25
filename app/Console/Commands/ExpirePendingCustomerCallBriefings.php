<?php

namespace App\Console\Commands;

use App\Models\CustomerCallBriefing;
use Illuminate\Console\Command;

class ExpirePendingCustomerCallBriefings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-pending-customer-call-briefings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca como fallidas las preparaciones de llamada pendientes y vencidas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expired = CustomerCallBriefing::query()
            ->where('status', 'pending')
            ->where('updated_at', '<', now()->subMinutes(5))
            ->update([
                'status' => 'failed',
                'error_code' => 'pending_timeout',
                'error_message' => 'La preparación tardó demasiado. Intenta nuevamente.',
                'updated_at' => now(),
            ]);

        $this->info("Preparaciones vencidas: {$expired}");

        return self::SUCCESS;
    }
}
