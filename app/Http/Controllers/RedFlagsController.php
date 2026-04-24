<?php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class RedFlagsController extends Controller
{
    private function apiBase(): string
    {
        return rtrim(env('API_BASE_URL', 'http://db-api:8000'), '/');
    }

    private function api(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(15)->baseUrl($this->apiBase());
    }

    // GET /raw-logs/recent
    public function rawLogsRecent(Request $request)
    {
        $response = $this->api()->get('/raw-logs/recent', $request->query());
        return response()->json($response->json(), $response->status());
    }

    // GET /raw-logs/stats
    public function rawLogsStats()
    {
        $response = $this->api()->get('/raw-logs/stats');
        return response()->json($response->json(), $response->status());
    }

    // GET /incidents
    public function incidents(Request $request)
    {
        $response = $this->api()->get('/incidents', $request->query());
        return response()->json($response->json(), $response->status());
    }

    // GET /incidents/{incident_id}
    public function incident($id)
    {
        $response = $this->api()->get("/incidents/{$id}");
        return response()->json($response->json(), $response->status());
    }

    // GET /statistics
    public function statistics()
    {
        $response = $this->api()->get('/statistics');
        return response()->json($response->json(), $response->status());
    }

    // GET /search/ip/{ip_address}
    public function searchByIp($ip)
    {
        $response = $this->api()->get("/search/ip/{$ip}");
        return response()->json($response->json(), $response->status());
    }

    // GET /recent
    public function recent()
    {
        $response = $this->api()->get('/recent');
        return response()->json($response->json(), $response->status());
    }

    // POST /analyze/templates
    public function analyzeTemplates(Request $request)
    {
        $response = $this->api()->post('/analyze/templates', $request->all());
        return response()->json($response->json(), $response->status());
    }
}
