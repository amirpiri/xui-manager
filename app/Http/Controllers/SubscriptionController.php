<?php

namespace App\Http\Controllers;

use App\Http\TelegramWebhooks\Services\DnsService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function generateSubscriptionLink(string $uuid)
    {
        $extract_uuid_pattern = "/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/";
        $string_to_match = $uuid;
        preg_match_all($extract_uuid_pattern, $string_to_match, $matches);
        $uuid = $matches[0] ?? [];
        if (count($uuid) == 0) {
            return 'Wrong ID!';
        }

        $dnsService = new DnsService(config('cloudflare.dns_zone_id'));
        $dnsRecords = $dnsService->getZoneRecords();

        if ($dnsRecords['success'] === true) {
            $links = '';
            if (count($dnsRecords['result'] ?? []) > 0) {
                foreach ($dnsRecords['result'] as $dnsRecord) {
                    if (!empty($dnsRecord['comment'])) {
                        $links .= generateConfigLink($uuid[0], $dnsRecord['name'], $dnsRecord['comment']) . PHP_EOL;
                    }
                }
            }
        }
        return $links;
    }

    public function generateSubscriptionLinkBase64(string $uuid)
    {
        return base64_encode($this->generateSubscriptionLink($uuid));
    }
}
