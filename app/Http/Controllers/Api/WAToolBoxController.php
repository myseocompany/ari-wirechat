<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Customer;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

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
    public $imageBase64 = "";

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

    
    Log::info("API". $validatedData['APIKEY']);
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
    $coder = Customer::firstOrCreate(
        ['phone' => $validatedData['phone']],
        [
            'name' => $validatedData['name'] ?? $validatedData['name2'],

            ///'team_id' => $teamId,
        ]
    );

    $nicolas = User::findByPhone($reciver_phone);

    if (!$nicolas) {
        Log::warning('No se encontró un usuario con el teléfono: ' . $reciver_phone);
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    Log::info('Usuario identificado: ' . $nicolas->name);
    
    

    Log::info('Telefono Nicolas '.$reciver_phone);
    logger(["content"=>$validatedData['content']]);
    $message = $coder->sendMessageTo($nicolas, /*$validatedData['content']*/ "demo");

    $conversation = $message->conversation;
    Log::info('Telefono enviado '.$message);
    broadcast(new MessageCreated($message));
    NotifyParticipants::dispatch($message->conversation,$message);
    
    $imageData = base64_decode($validatedData['image']);
    logger(["image64"=>$imageData]);

    if (preg_match('/^data:image\/(\w+);base64,/', $imageData)) {
    
        try {
            // Decodificar la imagen Base64 y guardarla
            
            
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
            
            
            logger('testing '.$conversation);
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
