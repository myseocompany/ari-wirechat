<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WAToolBoxController;
use App\Http\Controllers\Api\APIController;
use App\Http\Controllers\Api\RetellWebhookController;


Route::middleware('api')->group(
    function () {
        Route::get('/customers/saveCustomer', [APIController::class, 'saveApi']);
        Route::post('/customers/update', [APIController::class, 'saveApi']);
        
        Route::post('/watoolbox', [WAToolBoxController::class, 'receiveMessage']);
        Route::post('/watoolbox/webhook', [WAToolBoxController::class, 'receiveMessage']);
        Route::post('/watoolbox/testping', function () {
            return response()->json(['pong' => true]);
        });

        Route::post('/campaigns/{campaign_id}/send-to/{customer_id}', [APIController::class, 'sendCampaign']);

        Route::post('/actions/save', [APIController::class, 'saveQuickAction']);
        Route::post('/channels-action', [APIController::class, 'saveChannelsAction']);
        Route::post('/retell-action', [APIController::class, 'handle']);

        
});




