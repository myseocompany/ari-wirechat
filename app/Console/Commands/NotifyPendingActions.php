<?php

namespace App\Console\Commands;

use App\Mail\ActionReminderMail;
use App\Models\Action;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyPendingActions extends Command
{
    protected $signature = 'actions:notify';

    protected $description = 'Envía notificaciones por email para acciones próximas a vencer.';

    public function handle(): int
    {
        $windowMinutes = (int) app_config('followup_default_minutes', 0);

        if ($windowMinutes <= 0) {
            $this->warn('followup_default_minutes no configurado o en 0; no se enviarán recordatorios.');
            return self::SUCCESS;
        }

        $now = now();
        $windowEnd = $now->copy()->addMinutes($windowMinutes);

        Log::info('actions:notify starting', [
            'window_minutes' => $windowMinutes,
            'window_start' => $now->toDateTimeString(),
            'window_end' => $windowEnd->toDateTimeString(),
            'system_now' => now()->toDateTimeString(),
            'criteria' => 'delivery_date IS NULL, notified_at IS NULL, due_date BETWEEN window_start and window_end',
        ]);

        $sent = 0;
        $skippedNoEmail = 0;
        $total = 0;

        Action::query()
            ->whereNull('delivery_date')
            ->whereNull('notified_at')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$now, $windowEnd])
            ->with(['customer.user'])
            ->orderBy('id')
            ->chunkById(200, function ($actions) use (&$sent, &$skippedNoEmail, &$total) {
                foreach ($actions as $action) {
                    $total++;
                    $user = optional($action->customer)->user;
                    $email = $user->email ?? null;

                    if (! $email) {
                        $skippedNoEmail++;
                        Log::info('actions:notify skip no email', ['action_id' => $action->id, 'customer_id' => $action->customer_id]);
                        continue;
                    }

                    Mail::to($email)->send(new ActionReminderMail($action));

                    $action->forceFill(['notified_at' => now()])->save();
                    $sent++;
                }
            });

        Log::info('actions:notify finished', [
            'window_minutes' => $windowMinutes,
            'sent' => $sent,
            'skipped_no_email' => $skippedNoEmail,
            'total_found' => $total,
            'window_start' => $now->toDateTimeString(),
            'window_end' => $windowEnd->toDateTimeString(),
            'system_now' => now()->toDateTimeString(),
        ]);

        $this->info("actions:notify sent={$sent} skipped_no_email={$skippedNoEmail} total_found={$total} window_start={$now} window_end={$windowEnd} system_now=".now());

        return self::SUCCESS;
    }
}
