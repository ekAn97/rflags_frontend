<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedFlagsController;

Route::get('/', fn() => redirect('/incidents'));

Route::prefix('api')->group(function () {
    Route::get('/raw-logs/recent',        [RedFlagsController::class, 'rawLogsRecent']);
    Route::get('/raw-logs/stats',         [RedFlagsController::class, 'rawLogsStats']);
    Route::get('/incidents',              [RedFlagsController::class, 'incidents']);
    Route::get('/incidents/{id}',         [RedFlagsController::class, 'incident']);
    Route::get('/statistics',             [RedFlagsController::class, 'statistics']);
    Route::get('/search/ip/{ip}',         [RedFlagsController::class, 'searchByIp']);
    Route::get('/recent',                 [RedFlagsController::class, 'recent']);
    Route::post('/analyze/templates',     [RedFlagsController::class, 'analyzeTemplates']);
});

// Main dashboard view
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
