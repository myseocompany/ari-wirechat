<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WAToolBoxController;
use App\Http\Controllers\Api\APIController;
use App\Http\Controllers\Api\RetellWebhookController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Middleware\CheckApiToken;


Route::middleware('api')->group(
    function () {
        Route::get('/customers/saveCustomer', [APIController::class, 'saveApi']);
        #Route::post('/customers/update', [APIController::class, 'saveApi']);
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


});


Route::middleware([CheckApiToken::class, 'throttle:60,1'])->group(function () {
    Route::get('/customers', [CustomerApiController::class, 'index']);           // filtros
    Route::get('/customers/{id}', [CustomerApiController::class, 'show']);

    Route::patch('/customers/{id}/status', [CustomerApiController::class, 'updateStatus']);
    Route::post('/customers/{id}/actions', [CustomerApiController::class, 'addAction']);

    Route::post('/customers/bulk/status', [CustomerApiController::class, 'bulkUpdateStatus']);
    Route::post('/customers/bulk/actions', [CustomerApiController::class, 'bulkAddAction']);
});
