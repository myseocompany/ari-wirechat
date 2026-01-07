<?php

namespace App\Console\Commands;

use App\Models\Action;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
                    $whatsAppNotified = false;

                    if ((int) $action->type_id === 9) {
                        $whatsAppNotified = $this->sendMeetingReminderWhatsApp($action);
                    }

                    if (! $email) {
                        $skippedNoEmail++;
                        Log::info('actions:notify skip no email', ['action_id' => $action->id, 'customer_id' => $action->customer_id]);
                        if ($whatsAppNotified) {
                            $action->forceFill(['notified_at' => now()])->save();
                        }

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

    private function sendMeetingReminderWhatsApp(Action $action): bool
    {
        $customer = $action->customer;
        if (! $customer) {
            Log::info('actions:notify skip whatsapp missing customer', ['action_id' => $action->id]);

            return false;
        }

        $url = trim((string) $action->url);
        if ($url === '') {
            Log::info('actions:notify skip whatsapp missing url', ['action_id' => $action->id, 'customer_id' => $action->customer_id]);

            return false;
        }

        $phone = $customer->getPhone();
        if ($phone === '') {
            Log::info('actions:notify skip whatsapp missing phone', ['action_id' => $action->id, 'customer_id' => $action->customer_id]);

            return false;
        }

        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $url],
                ],
            ],
        ];

        app(WhatsAppService::class)->sendTemplateToCustomer(
            $customer,
            (string) config('whatsapp.meeting_reminder_template', '2026_feria_falta_1hora'),
            $components
        );

        return true;
    }
}
