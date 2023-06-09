<?php

namespace App\Http\Controllers\Client;

use App\Enums\RenewStatusEnum;
use App\Enums\RenewTypeEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientTrafficRenewRequest;
use App\Http\Requests\ClientTrafficShowRequest;
use App\Models\ClientTraffic;
use App\Models\Inbound;
use App\Models\Renew;
use App\Models\UserClientTraffic;
use App\Services\Contracts\XuiEnglishRequestServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RenewClientController extends Controller
{

    public function __construct(protected XuiEnglishRequestServiceInterface $xuiRequestService)
    {
    }

    public function show(int $clientId, ClientTrafficShowRequest $request)
    {

        if (auth()->user()->role === UserRoleEnum::RESELLER->value) {
            $userClientTraffic = UserClientTraffic::where('client_traffic_id', $clientId)
                ->where('user_id', auth()->user()->id);
        } elseif (auth()->user()->role === UserRoleEnum::ADMIN->value) {
            $userClientTraffic = ClientTraffic::where('id', $clientId);
        }
        $userClientTraffic = $userClientTraffic->first();

        if (is_null($userClientTraffic)) {
            return abort('403');
        }

        $clientRow = ClientTraffic::find($clientId);
        return view('clients.renew', ['client' => $clientRow]);
    }

    public function update(int $clientId, ClientTrafficRenewRequest $request)
    {

        $clientRow = ClientTraffic::find($clientId);
        $inboundRow = Inbound::find($clientRow->inbound_id);
        $inboundClients = json_decode($inboundRow->settings, true)['clients'];
        $expireTime = Carbon::tomorrow()->addMonth()->timestamp * 1000;
        $total = $request->traffic * (1024 * 1024 * 1024);
        foreach ($inboundClients as $key => $inboundClient) {
            if ($inboundClient['email'] === $clientRow->email) {
                $inboundClients[$key]['totalGB'] = $total;
                $inboundClients[$key]['expiryTime'] = $expireTime;
            }
        }
        $settings = json_decode($inboundRow->settings);
        $settings->clients = $inboundClients;
        $inboundRow->enable = (bool)$inboundRow->enable;
        $inboundRow->settings = json_encode($settings, JSON_PRETTY_PRINT);
        DB::transaction(function () use ($clientRow, $inboundRow, $clientId, $total, $expireTime, $request) {
            ClientTraffic::where('id', $clientId)->update([
                'total' => $total,
                'down' => 0,
                'up' => 0,
                'expiry_time' => $expireTime,
                'enable' => 1
            ]);
            $userClientTraffic = UserClientTraffic::where('client_traffic_id', $clientId)
                ->first();
            Renew::create([
                'user_id' => $userClientTraffic->user_id,
                'client_id' => $clientId,
                'traffic' => $request->traffic,
                'status' => RenewStatusEnum::RENEW->value,
                'type' => RenewTypeEnum::RENEW->value,
            ]);

        });

        $this->xuiRequestService->updateInbound($clientRow->inbound_id, $inboundRow->toArray());

        return redirect(route('client.get-client-connection', ['clientId' => $clientId]));
    }
}
