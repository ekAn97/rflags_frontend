<?php

use App\Http\Controllers\RedFlagsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// About page
Route::get('/about', function () {
    return view('about');
});

Route::get('/api/logs', [RedFlagsController::class, 'getLogs']);
