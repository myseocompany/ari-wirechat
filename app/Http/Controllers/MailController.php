<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\DemoEmail;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
    public function send()
    {
        $details = [
            'title' => 'Mail from Laravel',
            'body' => 'This is for testing email using smtp',
            'from' => 'nicolas@myseocompany.co',
            
        ];

        $demoEmail = new DemoEmail($details);

        //dd($demoEmail);

        // Comprueba que los detalles no sean null y que los índices existan
        if ($details && array_key_exists('title', $details) && array_key_exists('body', $details)) {
            try {
                Mail::to('nicolas@myseocompany.co')->send($demoEmail);
                echo('Your mail has been sent successfully!');
            } catch (\Exception $e) {
                echo('Could not send email: ' . $e->getMessage());
            }
        } else {
            echo('Details array is null or missing indices');
        }

        
        //return back()->with('message_sent','Your mail has been sent successfully!');
    }

    function sendEmail($to, $subject, $message, $headers) {
        if (mail($to, $subject, $message, $headers)) {
            echo 'Email sent successfully to ' . $to;
        } else {
            echo 'Failed to send email';
        }
    }
    
public function sendPHP(){
    $to = 'nicolas@myseocompany.co';
    $subject = 'Mail from PHP';
    $message = 'This is a test email using PHP mail() function.';
    $headers = 'From: nicolas@myseocompany.co';
    
    $this->sendEmail($to, $subject, $message, $headers);
}

}
