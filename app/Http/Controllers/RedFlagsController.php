<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class RedFlagsController extends Controller
{
    // public function getLogs()
    // {
    //     $response = Http::withOptions([

    //         'verify' => false, // αν είναι self-signed certificate
    //     ])->get('https://192.168.6.123:7376/');


    //     $logs = $response->json() ?? [];

    //     return response()->json(['logs' => $logs]);
    // }

}
