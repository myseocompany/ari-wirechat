<?php

namespace App\Mail;

use App\Models\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Action $action;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function build()
    {
        return $this->subject('Tienes una acción próxima a vencer')
            ->view('emails.action_reminder', [
                'action' => $this->action,
            ]);
    }
}
