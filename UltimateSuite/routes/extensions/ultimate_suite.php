<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Extensions\UltimateSuite\Http\Controllers\Api\Client\Servers\VersionManagerController;
use Pterodactyl\Extensions\UltimateSuite\Http\Controllers\Api\Client\Servers\PlayerManagerController;
use Pterodactyl\Extensions\UltimateSuite\Http\Controllers\Api\Client\UserController;

/*
|--------------------------------------------------------------------------
| Ultimate Suite Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/api/client'], function () {
    // User Language
    Route::post('/account/language', [UserController::class, 'updateLanguage']);

    // Server Specific Routes
    Route::group(['prefix' => '/servers/{server}'], function () {
        // Version Manager
        Route::post('/ultimate-suite/version', [VersionManagerController::class, 'updateVersion']);
        
        // Player Manager
        Route::get('/ultimate-suite/players', [PlayerManagerController::class, 'getPlayers']);
        Route::post('/ultimate-suite/players/command', [PlayerManagerController::class, 'executeCommand']);
    });
});
