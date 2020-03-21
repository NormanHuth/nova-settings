<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::namespace('\NormanHuth\NovaValuestore\Http\Controllers')->group(function () {
    Route::prefix('nova-vendor/nova-valuestore')->group(function () {
        Route::get('/settings', 'SettingsController@get');
        Route::post('/settings', 'SettingsController@save');
    });

    Route::delete('/nova-api/nova-valuestore/settings/field/{fieldName}', 'SettingsController@deleteImage');
});
