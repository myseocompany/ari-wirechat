<?php

namespace App\Console\Commands;

use App\Mail\ActionReminderMail;
use App\Models\Action;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Resend\Laravel\Facades\Resend;

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
                    $customer = $action->customer;
                    $user = optional($customer)->user;
                    $email = $user->email ?? null;

                    if (! $email) {
                        $skippedNoEmail++;
                        Log::info('actions:notify skip no email', ['action_id' => $action->id, 'customer_id' => $action->customer_id]);
                        continue;
                    }

                    $resend = new Resend(env('RESEND_KEY'));

                    $customerName = $customer?->name ?: 'Cliente sin nombre';
                    $customerId = $customer?->id ?: $action->customer_id;
                    $customerUrl = route('customers.show', $customerId);
                    $note = trim((string) $action->note);
                    $description = $note !== '' ? $note : 'Acción pendiente sin descripción.';
                    $subject = 'Tarea pendiente - '.Str::limit($customerName, 50);
                    $body = <<<TXT
Tienes una acción pendiente del cliente {$customerName} (ID {$customerId}).

Detalle: {$description}
Ver cliente: {$customerUrl}
TXT;

                    $resend->emails()->send([
                        'from' => 'Maquiempanadas <marketing@maquiempanadas.com>',
                        'to' => $email,
                        'subject' => $subject,
                        'text' => $body,
                    ]);


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
