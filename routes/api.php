<?php

use App\Http\Controllers\Api\APIController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\MachineReportController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\WAToolBoxController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\VoipController;
use App\Http\Middleware\CheckApiToken;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(
    function () {
        Route::get('/customers/saveCustomer', [APIController::class, 'saveApi']);
        Route::post('/google-ads/leads', [APIController::class, 'saveFromGoogleAds']);
        // Route::post('/customers/update', [APIController::class, 'saveApi']);
        Route::post('/customers/update', [APIController::class, 'updateFromRD']);
        Route::get('/request-logs', [APIController::class, 'listRequestLogs']);
        Route::post('/request-logs/{id}/resend', [APIController::class, 'resendRequestLog']);

        Route::post('/watoolbox', [WAToolBoxController::class, 'receiveMessage']);
        Route::post('/watoolbox/webhook', [WAToolBoxController::class, 'receiveMessage']);
        Route::post('/watoolbox/testping', function () {
            return response()->json(['pong' => true]);
        });

        Route::post('/campaigns/{campaign_id}/send-to/{customer_id}', [APIController::class, 'sendCampaign']);

        Route::post('/actions/save', [APIController::class, 'saveQuickAction']);
        Route::post('/channels-action', [APIController::class, 'saveChannelsAction']);
        Route::post('/retell-action', [APIController::class, 'handle']);
        Route::post('/quizzes/escalable', [QuizController::class, 'store']);
        Route::get('/quizzes/escalable/result/{slug}', [QuizController::class, 'showResult']);
        Route::post('/calculator', [QuizController::class, 'storeCalculator']);

        Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify']);
        Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'receive']);
        Route::match(['get', 'post'], '/voip/twiml', [VoipController::class, 'twiml'])->name('api.voip.twiml');
        Route::post('/voip/callbacks/status', [VoipController::class, 'statusCallback'])->name('api.voip.callbacks.status');
        Route::post('/voip/callbacks/recording', [VoipController::class, 'recordingCallback'])->name('api.voip.callbacks.recording');

    });

Route::middleware([CheckApiToken::class, 'throttle:60,1'])->group(function () {
    Route::get('/customers', [CustomerApiController::class, 'index']);           // filtros
    Route::get('/customers/{id}', [CustomerApiController::class, 'show']);

    Route::patch('/customers/{id}/status', [CustomerApiController::class, 'updateStatus']);
    Route::post('/customers/{id}/actions', [CustomerApiController::class, 'addAction']);

    Route::post('/customers/bulk/status', [CustomerApiController::class, 'bulkUpdateStatus']);
    Route::post('/customers/bulk/actions', [CustomerApiController::class, 'bulkAddAction']);
});

Route::prefix('v1')->middleware(['api', \App\Http\Middleware\MachineTokenAuth::class])->group(function () {
    Route::post('/machines/report', [MachineReportController::class, 'store']);
});
