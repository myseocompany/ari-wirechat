<?php

namespace App\Listeners;

use App\Models\Customer;
use App\Models\User;
use App\Services\WAToolboxService;
use GuzzleHttp\Psr7\Message;
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
        logger(['event'=> $event ]);
        // $event->message['APIKEY']
        if ($message->sendable_type === 'App\\Models\\Customer')
            $message  = ModelsMessage::find( $event->message->id );
        $response = null;
        $user = $message->sendable; // get User
        $customer = $message->conversation->participants()
            ->where(function ($query) use ($user) {
                $query->where('participantable_id', '<>', $user->id)
                     ->orWhere('participantable_type', '<>', $user->getMorphClass());
            })->first();
        $customer = $customer->participantable;
        logger($customer);
        // $phone_number = Customer::find( $event->message['body'] );
        $phone_number = $customer->phone;

        $this->defaultMessageSource = $user?->getUserDefaultMessageSource();
        logger($this->defaultMessageSource->settings);
        if ($this->defaultMessageSource) {
            logger('reacched');
            $this->waToolboxService = new WAToolboxService($this->defaultMessageSource);
            logger('reacched 2');

        
                Log::info('WAToolnbox MessageControler', [$this->defaultMessageSource->settings]);
                
                $message = $event->message['body'];
                

                $response = $this->waToolboxService->sendMessageToWhatsApp([
                    'phone_number' => $phone_number,
                    'message' => $message,
                ]);

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
