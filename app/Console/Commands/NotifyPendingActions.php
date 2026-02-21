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
    private const WHATSAPP_REMINDER_TYPE_60 = '60';

    private const WHATSAPP_REMINDER_TYPE_10 = '10';

    private const WHATSAPP_REMINDER_TYPE_MORNING = 'morning';

    protected $signature = 'actions:notify';

    protected $description = 'Envía notificaciones por email para acciones próximas a vencer.';

    public function handle(): int
    {
        $windowMinutes = (int) app_config('followup_default_minutes', 0);
        $meetingTemplate60 = trim((string) app_config('whatsapp_meeting_reminder_60_template', ''));
        $meetingTemplate10 = trim((string) app_config('whatsapp_meeting_reminder_10_template', ''));
        $meetingTemplateMorning = trim((string) app_config('whatsapp_meeting_reminder_morning_template', ''));
        $meetingWindow60 = (int) app_config('whatsapp_meeting_reminder_60_minutes', 60);
        $meetingWindow10 = (int) app_config('whatsapp_meeting_reminder_10_minutes', 10);
        $meetingMorningTime = trim((string) app_config('whatsapp_meeting_reminder_morning_time', '08:00'));

        if ($windowMinutes <= 0) {
            $this->warn('followup_default_minutes no configurado o en 0; no se enviarán recordatorios.');

            return self::SUCCESS;
        }

        $now = now();
        $windowEnd = $now->copy()->addMinutes($windowMinutes);

        $sent = 0;
        $skippedNoEmail = 0;
        $total = 0;

        if ($meetingWindow60 > 0 && $meetingTemplate60 !== '') {
            $this->sendMeetingReminders(
                $meetingTemplate60,
                $meetingWindow60,
                self::WHATSAPP_REMINDER_TYPE_60
            );
        }

        if ($meetingWindow10 > 0 && $meetingTemplate10 !== '') {
            $this->sendMeetingReminders(
                $meetingTemplate10,
                $meetingWindow10,
                self::WHATSAPP_REMINDER_TYPE_10
            );
        }

        if ($meetingTemplateMorning !== '' && $meetingMorningTime !== '') {
            $this->sendMeetingMorningReminders($meetingTemplateMorning, $meetingMorningTime);
        }

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

        $this->info("actions:notify sent={$sent} skipped_no_email={$skippedNoEmail} total_found={$total} window_start={$now} window_end={$windowEnd} system_now=".now());

        return self::SUCCESS;
    }

    private function sendMeetingReminders(string $template, int $minutes, string $reminderType): void
    {
        $now = now();
        $windowEnd = $now->copy()->addMinutes($minutes);

        Action::query()
            ->whereNull('delivery_date')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$now, $windowEnd])
            ->where('type_id', 9)
            ->with('customer')
            ->orderBy('id')
            ->chunkById(200, function ($actions) use ($template, $reminderType) {
                foreach ($actions as $action) {
                    if ($this->hasMeetingReminderBeenSent($action->id, $reminderType)) {
                        continue;
                    }

                    $this->sendMeetingReminderWhatsAppWithTemplate($action, $template, $reminderType);
                }
            });
    }

    private function sendMeetingMorningReminders(string $template, string $time): void
    {
        $now = now();
        $today = $now->toDateString();
        $morningStart = $now->copy()->setTimeFromTimeString($time);
        $morningEnd = $morningStart->copy()->addMinute();

        if (! $now->between($morningStart, $morningEnd)) {
            return;
        }

        Action::query()
            ->whereNull('delivery_date')
            ->whereNotNull('due_date')
            ->whereDate('due_date', $today)
            ->where('type_id', 9)
            ->with('customer')
            ->orderBy('id')
            ->chunkById(200, function ($actions) use ($template) {
                foreach ($actions as $action) {
                    if ($this->hasMeetingReminderBeenSent($action->id, self::WHATSAPP_REMINDER_TYPE_MORNING)) {
                        continue;
                    }

                    $this->sendMeetingReminderWhatsAppWithTemplate($action, $template, self::WHATSAPP_REMINDER_TYPE_MORNING);
                }
            });
    }

    private function sendMeetingReminderWhatsAppWithTemplate(Action $action, string $template, string $reminderType): bool
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
            $template,
            $components,
            null,
            null,
            [
                'reminder_type' => $reminderType,
                'action_id' => $action->id,
                'due_date' => $action->due_date?->toDateTimeString(),
            ]
        );

        return true;
    }

    private function hasMeetingReminderBeenSent(int $actionId, string $reminderType): bool
    {
        return Action::query()
            ->where('type_id', 16)
            ->where('object_id', $actionId)
            ->where(function ($query) use ($reminderType) {
                $query->where('reminder_type', $reminderType)
                    ->orWhere(function ($legacyQuery) use ($reminderType) {
                        $legacyQuery->whereNull('reminder_type')
                            ->where('note', 'like', '%"reminder_type":"'.$reminderType.'"%');
                    });
            })
            ->exists();
    }
}
