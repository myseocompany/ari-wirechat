<?php

namespace App\Http\Controllers\Api;

use App\Enums\WAMessageType;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MessageSource;
use App\Models\RequestLog;
use App\Services\LeadAssignmentService;
use App\Services\MessageSourceConversationService;
use Illuminate\Http\File as HttpFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Namu\WireChat\Enums\MessageType;
use Namu\WireChat\Events\MessageCreated;
use Namu\WireChat\Jobs\NotifyParticipants;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message as ModelsMessage;

class WAToolBoxController extends Controller
{
    public function __construct(
        private readonly LeadAssignmentService $leadAssignmentService,
        private readonly MessageSourceConversationService $messageSourceConversationService
    ) {}

    public function receiveMessage(Request $request)
    {
        $this->saveRequestLog($request);

        Log::info('Receiving data at WAToolBoxController receiveMessage:', [$request->all()]);

        $validatedData = $request->validate([
            'id' => 'required|string',
            'type' => 'required|string',
            'user' => 'required|string',
            'phone' => 'required|string',
            'content' => 'required|string',
            'name' => 'string|nullable',
            'name2' => 'string|nullable',
            'image' => 'string|nullable',
            'APIKEY' => 'required|string',
        ]);

        $messageSource = MessageSource::query()
            ->where('APIKEY', $validatedData['APIKEY'])
            ->first();

        if (! $messageSource) {
            return response()->json(['message' => 'Fuente del mensaje no encontrada'], 404);
        }

        if (! $messageSource->isActive()) {
            return response()->json(['message' => 'Fuente del mensaje inactiva'], 422);
        }

        $sender = $this->resolveSender($validatedData, $messageSource);
        $conversation = $this->messageSourceConversationService->resolveOrCreate($messageSource, $sender);
        $this->messageSourceConversationService->syncAssignedAgentParticipant($conversation, $sender);

        $responseDetail = 'Mensaje procesado correctamente.';

        if ($validatedData['type'] === 'chat') {
            $message = $sender->sendMessageTo($conversation, $validatedData['content']);
            if (! $message) {
                return response()->json(['message' => 'No fue posible almacenar el mensaje'], 500);
            }

            $this->notifyParticipants($message);
            $responseDetail = 'Mensaje de chat procesado correctamente.';
        } elseif ($validatedData['type'] === 'image') {
            try {
                $message = $this->storeAttachmentMessage(
                    $validatedData['content'],
                    ['png', 'jpg', 'jpeg', 'mp4', 'heic', 'HEIC'],
                    $sender,
                    $conversation
                );

                $this->notifyParticipants($message);
                $responseDetail = 'Mensaje con imagen procesado correctamente.';
            } catch (\Throwable $exception) {
                Log::error('Error al procesar imagen WAToolBox', ['error' => $exception->getMessage()]);

                return response()->json(['message' => 'Error al procesar la imagen'], 500);
            }
        } elseif ($validatedData['type'] === 'ptt') {
            try {
                $message = $this->storeAttachmentMessage(
                    $validatedData['content'],
                    ['mp3', 'wav', 'ogg'],
                    $sender,
                    $conversation
                );

                $this->notifyParticipants($message);
                $responseDetail = 'Mensaje de audio procesado correctamente.';
            } catch (\Throwable $exception) {
                Log::error('Error al procesar audio WAToolBox', ['error' => $exception->getMessage()]);

                return response()->json(['message' => 'Error al procesar el audio'], 500);
            }
        } else {
            Log::warning('Tipo de mensaje no soportado en WAToolBox', [
                'type' => $validatedData['type'],
                'payload_id' => $validatedData['id'],
            ]);

            return response()->json([
                'message' => 'Tipo de mensaje no soportado',
                'type' => $validatedData['type'],
            ], 422);
        }

        return response()->json([
            'message' => 'Data processed successfully',
            'message_source' => $messageSource,
            'detail' => $responseDetail,
        ], 200);
    }

    private function resolveSender(array $validatedData, MessageSource $messageSource): Customer
    {
        $api = app(APIController::class);
        $settings = is_array($messageSource->settings) ? $messageSource->settings : [];

        $apiRequest = new Request;
        $apiRequest->replace([
            'name' => $validatedData['name'] ?? $validatedData['name2'] ?? $validatedData['phone'],
            'phone' => $validatedData['phone'],
            'email' => null,
            'source_id' => (int) ($settings['source_id'] ?? 76),
            'status_id' => 1,
            'image_url' => $validatedData['image'] ?? null,
            'content' => $validatedData['content'] ?? null,
        ]);

        /** @var Customer|null $sender */
        $sender = $api->getSimilarModel($apiRequest);
        $isNewSender = false;

        if (! $sender) {
            $isNewSender = true;
            /** @var Customer $sender */
            $sender = $api->saveAPICustomer($apiRequest);

            if (! $sender->user_id) {
                $assignedUserId = $this->leadAssignmentService->getAssignableUserId();
                if ($assignedUserId) {
                    $sender->user_id = $assignedUserId;
                    $sender->save();

                    $this->leadAssignmentService->recordAssignment(
                        $assignedUserId,
                        $sender->id,
                        'wa_toolbox',
                        [
                            'source_id' => $sender->source_id,
                        ]
                    );
                }
            }

            $api->storeActionAPI($apiRequest, $sender->id);
        }

        if (! empty($validatedData['image'])) {
            $sender->image_url = $validatedData['image'];
        }

        if ($isNewSender && empty($sender->name) && ! empty($validatedData['name2'])) {
            $sender->name = $validatedData['name2'];
        }

        $sender->save();

        return $sender;
    }

    private function storeAttachmentMessage(
        string $content,
        array $allowedMimeTypes,
        Customer $sender,
        Conversation $conversation
    ): ModelsMessage {
        $tmpFileObject = $this->validateBase64($content, $allowedMimeTypes);
        if (! $tmpFileObject) {
            throw new \RuntimeException('Archivo adjunto inválido');
        }

        $tmpFileObjectPathName = $tmpFileObject->getPathname();

        $file = new UploadedFile(
            $tmpFileObjectPathName,
            $tmpFileObject->getFilename(),
            $tmpFileObject->getMimeType(),
            0,
            true
        );

        $path = $file->store(
            config('wirechat.attachments.storage_folder', 'attachments'),
            config('wirechat.attachments.storage_disk', 'public')
        );

        $message = ModelsMessage::create([
            'conversation_id' => $conversation->id,
            'sendable_type' => $sender->getMorphClass(),
            'sendable_id' => $sender->id,
            'type' => MessageType::ATTACHMENT,
        ]);

        $message->attachment()->create([
            'file_path' => $path,
            'file_name' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'url' => Storage::url($path),
        ]);

        unlink($tmpFileObjectPathName);

        return $message;
    }

    private function notifyParticipants(ModelsMessage $message): void
    {
        broadcast(new MessageCreated($message));
        NotifyParticipants::dispatch($message->conversation, $message);
    }

    private function validateBase64(string $base64data, array $allowedMimeTypes)
    {
        if (str_contains($base64data, ';base64')) {
            [, $base64data] = explode(';', $base64data);
            [, $base64data] = explode(',', $base64data);
        }

        if (base64_decode($base64data, true) === false) {
            return false;
        }

        if (base64_encode(base64_decode($base64data)) !== $base64data) {
            return false;
        }

        $fileBinaryData = base64_decode($base64data);

        $tmpFileName = tempnam(sys_get_temp_dir(), 'medialibrary');
        file_put_contents($tmpFileName, $fileBinaryData);

        $tmpFileObject = new HttpFile($tmpFileName);

        $allowedMimeTypes = Arr::flatten($allowedMimeTypes);

        if (empty($allowedMimeTypes)) {
            return $tmpFileObject;
        }

        $validation = FacadesValidator::make(
            ['file' => $tmpFileObject],
            ['file' => 'mimes:'.implode(',', $allowedMimeTypes)]
        );

        if ($validation->fails()) {
            return false;
        }

        return $tmpFileObject;
    }

    public function saveRequestLog(Request $request): void
    {
        $model = new RequestLog;

        if ($request->isJson()) {
            $requestData = $request->json()->all();
        } else {
            $requestData = $request->all();
        }

        logger($requestData);
        if (isset($requestData['type']) && ($requestData['type'] != WAMessageType::IMAGE->value)) {
            $model->request = json_encode($requestData);
            $model->save();
        }
    }
}
