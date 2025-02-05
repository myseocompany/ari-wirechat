<?php

namespace App\Listeners;

use App\Models\Customer;
use App\Models\User;
use App\Services\WAToolboxService;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message as ModelsMessage;

class WAToolboxListener
{
    public $waToolboxService;
    public $defaultMessageSource;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $sendable_type = $event->message->sendable_type;
       //logger(['event'=> $event ]);

        if ($sendable_type === 'App\\Models\\User'){
            $message  = ModelsMessage::find( $event->message->id );
            $response = null;
            $user = $message->sendable; // get User
            $customer = $message->conversation->participants()
                ->where(function ($query) use ($user) {
                    $query->where('participantable_id', '<>', $user->id)
                        ->orWhere('participantable_type', '<>', $user->getMorphClass());
                })->first();
            $customer = $customer->participantable;
            //logger(['customer' => $customer]);
            // $phone_number = Customer::find( $event->message['body'] );
            $phone_number = $customer->phone;

        $this->defaultMessageSource = $user?->getDefaultMessageSource();
        logger($this->defaultMessageSource );
        
        if ($this->defaultMessageSource) {
            //logger('reacched');
            $this->waToolboxService = new WAToolboxService($this->defaultMessageSource);
            //logger('reacched 2');
                Log::info('WAToolnbox MessageControler', [$this->defaultMessageSource->settings]);
                logger(['message'=>$message]);
                $wa_message = $event->message['body'];
                $payload = [
                    'phone_number' => $phone_number,
                    'message' => $wa_message,
                ];
                if(isset($message->attachment->file_path)){

                    /* $payload['message'] = config('app.url') .$message->attachment->url;
                    $response = $this->waToolboxService->sendMessageToWhatsApp($payload); */

                    
                    
                    $payload['media_url'] = $message->attachment->url;
                    logger(['medial_url'=>$payload['media_url']]);
                    $response = $this->waToolboxService->sendMessageToWhatsApp($payload);
                    
                }else{
                    $response = $this->waToolboxService->sendMessageToWhatsApp($payload);
                }

                
                
            $webhookUrl = $this->defaultMessageSource->settings['webhook_url'];
            logger ($webhookUrl);
            logger ($response);
        } else {
            logger( "No hay un message_source predeterminado.");
        }
        }

        

        ///return response()->json($response);
        
        
    }
}
