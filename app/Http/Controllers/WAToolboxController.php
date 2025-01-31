<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Lead;
use App\Models\Message;
use App\Events\MessageReceived;
use App\Services\MessageService;
use App\Models\MessageSource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\File as HttpFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Namu\WireChat\Enums\MessageType;
use Namu\WireChat\Events\MessageCreated;
use Namu\WireChat\Jobs\NotifyParticipants;
use Namu\WireChat\Models\Message as ModelsMessage;

class WAToolBoxController extends Controller{
    public $imageBase64 = "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";

    public function receiveMessage(Request $request){
    Log::info('Receiving data at WAToolBoxController receiveMessage:', [$request->all()]);

    // Validar los datos del request
    $validatedData = $request->validate([
        'id' => 'required|string',
        'type' => 'required|string',
        'user' => 'required|string',
        'phone' => 'required|string',
        'content' => 'required|string',
        'name' => 'required|string',
        'name2' => 'string|nullable',
        'image' => 'string|nullable',
        'APIKEY' => 'required|string'
    ]);

    

    // Identificar el Message Source
    $messageSource = MessageSource::where('APIKEY', $validatedData['APIKEY'])->first();

    
    if (!$messageSource) {
        Log::warning('Message source no encontrado para APIKEY: ' . $validatedData['APIKEY']);
        return response()->json(['message' => 'Fuente del mensaje no encontrada'], 404);
    }
    $reciver_phone = $messageSource->settings['phone_number']; // 57300...

    Log::info('Telefono recibido '.$reciver_phone);
    Log::info('Request phone '. $validatedData['phone']);

    // Obtener el team_id desde la fuente
   // $teamId = $messageSource->team_id;

    // Buscar o crear el Lead
    $coder = Lead::firstOrCreate(
        ['phone' => $validatedData['phone']],
        [
            'name' => $validatedData['name'] ?? $validatedData['name2'],

            ///'team_id' => $teamId,
        ]
    );

    $nicolas = User::where('phone', $reciver_phone)->first();

    Log::info('Telefono Nicolas '.$nicolas->phone);
    
    $message = $coder->sendMessageTo($nicolas, $validatedData['content']);

    $conversation = $message->conversation;
    Log::info('Telefono enviado '.$message);

    broadcast(new MessageCreated($message))->toOthers();

    NotifyParticipants::dispatch($message->conversation,$message);

    if (true) {
        try {
            // Decodificar la imagen Base64 y guardarla
            
            //$imageData = base64_decode($validatedData['image']);
         //   $imageData = base64_decode( $this->imageBase64 );

            
          //  $attachment = tempnam(sys_get_temp_dir(), 'img_'); // Crear un archivo temporal
            ///file_put_contents($attachment, $imageData);
             // Create and associate the attachment with the message
            $tmpFileObject= $this->validateBase64($this->imageBase64,['png,jpg']);
             
            $tmpFileObjectPathName = $tmpFileObject->getPathname();

            $file = new UploadedFile(
                $tmpFileObjectPathName,
                $tmpFileObject->getFilename(),
                $tmpFileObject->getMimeType(),
                0,
                true
            );

        
                     //save photo to disk
           $path = $file->store(config('wirechat.attachments.storage_folder', 'attachments'), config('wirechat.attachments.storage_disk', 'public'));

        //   /  $fileName = $imageData->store('photos', 'public');
        //     return Storage::url($fileName);
            
            
            logger('testing'.$conversation);
            $message = ModelsMessage::create([
                'conversation_id' => $conversation->id,
                'sendable_type' => $coder->getMorphClass(), // Polymorphic sender type
                'sendable_id' => $coder->id, // Polymorphic sender ID
                'type' => MessageType::ATTACHMENT,
                // 'body' => $this->body, // Add body if required
            ]);
            $message->attachment()->create([
                'file_path' => $path,
                'file_name' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'url' => Storage::url($path),
            ]);
            unlink($tmpFileObjectPathName); // delete temp file
            broadcast(new MessageCreated($message))->toOthers();

        NotifyParticipants::dispatch($message->conversation,$message);


        } catch (\Exception $e) {
            logger('error '.$e);
        } 
    }    
    return response()->json([
        'message' => 'Data processed successfully',
        'message_source' => $messageSource,
        'message' => $message,
    ], 200);


    // Si el lead existe pero no tiene nombre, actualizarlo
    /*
    if (is_null($coder->name)) {
        $coder->name = $validatedData['name2'];
        $coder->save();
    }


    // Almacenar la imagen si está presente
    $imageUrl = null;
    if (!empty($validatedData['image'])) {
        try {
            // Decodificar la imagen Base64 y guardarla
            $imageData = base64_decode($validatedData['image']);
            $tempFile = tempnam(sys_get_temp_dir(), 'img_'); // Crear un archivo temporal
            file_put_contents($tempFile, $imageData);
            $messageService = new MessageService();
            // Usar el servicio MessageService para guardar la imagen
            $imageUrl = $messageService->saveImage(new \Illuminate\Http\UploadedFile(
                $tempFile,
                'image.png'
            ));

            Log::info('Imagen almacenada con éxito: ' . $imageUrl);
        } catch (\Exception $e) {
            Log::error('Error al guardar la imagen: ' . $e->getMessage());
        }
    }

    // Crear el mensaje asociado al Lead
    $type_id = $this->determineMessageType($validatedData['type']);
    $message = $lead->messages()->create([
        'lead_id' => $lead->id,
        'type_id' => $type_id,
        'content' => $validatedData['content'],
        'message_source_id' => $messageSource->id, // Asocia la fuente del mensaje
        'message_type_id' => 1,
        'user_id' => 1, // Ajusta según corresponda el usuario relacionado
        'is_outgoing' => false,
        'media_url' => $imageUrl,
    ]);

    Log::info('Mensaje creado:', [
        'team_id' => $teamId,
        'message_source_id' => $messageSource->id,
        'lead_id' => $lead->id,
        'message_id' => $message->id,
    ]);
    /*/
    // Emitir el evento MessageReceived
    //MessageReceived::dispatch($validatedData['content'], $validatedData['phone']);

}
private function validateBase64(string $base64data, array $allowedMimeTypes)
{
    // strip out data URI scheme information (see RFC 2397)
    if (str_contains($base64data, ';base64')) {
        list(, $base64data) = explode(';', $base64data);
        list(, $base64data) = explode(',', $base64data);
    }

    // strict mode filters for non-base64 alphabet characters
    if (base64_decode($base64data, true) === false) {
        return false;
    }

    // decoding and then re-encoding should not change the data
    if (base64_encode(base64_decode($base64data)) !== $base64data) {
        return false;
    }

    $fileBinaryData = base64_decode($base64data);

    // temporarily store the decoded data on the filesystem to be able to use it later on
    $tmpFileName = tempnam(sys_get_temp_dir(), 'medialibrary');
    file_put_contents($tmpFileName, $fileBinaryData);

    $tmpFileObject = new HttpFile($tmpFileName);

    // guard against invalid mime types
    $allowedMimeTypes = Arr::flatten($allowedMimeTypes);

    // if there are no allowed mime types, then any type should be ok
    if (empty($allowedMimeTypes)) {
        return $tmpFileObject;
    }

    // Check the mime types
    $validation = FacadesValidator::make(
        ['file' => $tmpFileObject],
        ['file' => 'mimes:' . implode(',', $allowedMimeTypes)]
    );

    if($validation->fails()) {
        return false;
    }

    return $tmpFileObject;
}






    public function test(){
        // Emitir el evento DataReceived
        $lead = Lead::find(1);
        $message = Message::find(1);
        Log::info('test', ["action"=>"action test"]);
        $e = event(new MessageReceived($lead, $message));

        return $e;
    }

    private function determineMessageType($type)
    {
        // Asigna un tipo de acción según el tipo recibido en WAToolbox
        // Ejemplo simple: chat, ptt, image
        $type_id = "";
        switch ($type) {
            case "text":
                $type_id = 1;
                break;
            case "image":
                $type_id = 2;
                break;
            case "audio":
                $type_id = 3;
                break;
        }

        return $type_id;
    }
}
