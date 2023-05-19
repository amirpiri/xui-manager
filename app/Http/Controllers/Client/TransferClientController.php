<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientTraffic;
use App\Models\Inbound;
use App\Services\Contracts\ClientTrafficServiceInterface;
use Illuminate\Http\Request;

class TransferClientController extends Controller
{
    public function show(int $clientId)
    {
        $clientRow = ClientTraffic::where('id', $clientId)->first();
        $inboundRow = Inbound::where('id', $clientRow->inbound_id)->first();

        foreach (json_decode($inboundRow->settings, true)['clients'] as $client) {
            if ($clientRow->email === $client['email']) {
                $uuid = $client['id'];
            }
        }


        $targetInbounds = Inbound::where('id', '<>', $clientRow->inbound_id)->get();
        return view('clients.transfer', ['clientId' => $clientId, 'inbounds' => $targetInbounds, 'uuid' => $uuid]);
    }

    public function store(Request $request, ClientTrafficServiceInterface $clientTrafficService)
    {
        $clientTrafficService->transferUser($request->uuid, $request->inbound);
        return redirect()->route('client.list');
    }
}
