<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Inbound;

class InboundController extends Controller
{
    public function __invoke()
    {
        $inbounds = Inbound::with('clientTraffics')->withCount('clientTraffics')->get();
        $convertToGB = config('traffic_client.convert_to_gb');
        $return = [];
        foreach ($inbounds as $inbound) {
            if (!isset($return[$inbound->id])) {
                $return[$inbound->id] = [
                    'expiredTotalTraffic' => 0,
                    'activeTotalTraffic' => 0,
                ];
            }
            $return[$inbound->id]['members'] = $inbound->client_traffics_count;
            $return[$inbound->id]['domain'] = $inbound->remark;
            if ($inbound->client_traffics_count > 0) {
                foreach ($inbound->clientTraffics as $clientTraffic) {
                    if ((microtime(true) * 1000) > $clientTraffic->expiry_time) {
                        $return[$inbound->id]['expiredTotalTraffic'] += ($clientTraffic->total / $convertToGB);
                    } else {
                        $return[$inbound->id]['activeTotalTraffic'] += ($clientTraffic->total / $convertToGB);
                    }
                }
            }
        }

        return view('clients.inbounds', ['inbounds' => $return]);
    }
}
