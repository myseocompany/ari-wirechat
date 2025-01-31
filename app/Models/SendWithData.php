<?php 
namespace App\Models;
//trujillo
use Illuminate\Mail\Mailable;


class SendWithData extends Mailable
{
    public $emailcontent;

    public function __construct($emailcontent)
    {
        $this->emailcontent = $emailcontent;
    }

    public function build()
    {

        return $this->from($this->emailcontent['from_email'], $this->emailcontent['from_name'])
            ->view($this->emailcontent['view'])
            ->subject($this->emailcontent['subject'])
            ->with( $this->emailcontent );
    }
} 

?>