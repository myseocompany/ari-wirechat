<?php

namespace App\Models;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DemoEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    

    public function build()
{
    return $this->subject('Mail from Laravel')
                ->view('emails.demo')
                ->with('details', $this->details);  // asegúrate de que estás pasando los detalles aquí
}

}
