<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\WAToolBoxController;
use App\Http\Controllers\APIController;

Route::middleware('api')->group(
    function () {
        Route::get('/customers/saveCustomer', [APIController::class, 'saveApi']);
        Route::post('/watoolbox', [WAToolBoxController::class, 'receiveMessage']);
});


