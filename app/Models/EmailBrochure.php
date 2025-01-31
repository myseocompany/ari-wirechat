<?php

namespace App\Models;


use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailBrochure extends Email implements ShouldQueue
{

	public function build()
    {
        return $this->view('emails.html.order.confirmed')->with(['url' => URL::to('order/show/'.$this->order->id)]);
    }
}