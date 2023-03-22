<?php

namespace App\Http\TelegramWebhooks\Services;

use Http;

class DnsService
{
    public function __construct(private readonly string $zonId) {}

    public function getZoneRecords()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . config('cloudflare.api_key'),
        ])->get("https://api.cloudflare.com/client/v4/zones/{$this->zonId}/dns_records");
        return $response->json();
    }
}
