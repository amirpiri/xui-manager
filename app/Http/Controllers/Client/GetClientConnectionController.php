<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientTraffic;
use App\Models\Inbound;
use App\Services\GenerateConnection;
use Illuminate\Contracts\View\View;

class GetClientConnectionController extends Controller
{
    /**
     * @param int $id
     * @return View
     */
    public function __invoke(int $id): View
    {
        $clientTraffic = ClientTraffic::find($id);
        $inboundRow = Inbound::find($clientTraffic->inbound_id);
        $uuid = '';
        if (!is_null($inboundRow)) {
            foreach (json_decode($inboundRow->settings)->clients as $client) {
                if ($client->email === $clientTraffic->email) {
                    $uuid = $client->id;
                }
            }
        }
        return view('clients.client_connection', ['connection' => (new GenerateConnection($uuid))->execute()]);
    }
}
