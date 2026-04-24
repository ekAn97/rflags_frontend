<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class RedFlagsController extends Controller
{
    // public function getLogs()
    // {
    //     $response = Http::withOptions([
    //         'verify' => env('API_BASE_URL', false),
    //     ])->get(env('LOG_SERVER_URL') . '/');

    //     $logs = $response->json() ?? [];

    //     return response()->json(['logs' => $logs]);
    // }
}
