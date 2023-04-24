<?php

namespace App\Http\Controllers\Client;

use App\Enums\ClientTrafficEnableEnum;
use App\Enums\RenewStatusEnum;
use App\Enums\RenewTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CreateNewClientTrafficRequest;
use App\Http\Requests\Client\StoreNewClientTrafficRequest;
use App\Models\ClientTraffic;
use App\Models\Inbound;
use App\Models\Renew;
use App\Models\User;
use App\Models\UserClientTraffic;
use App\Services\Contracts\XuiEnglishRequestServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewClientController extends Controller
{

    public function __construct(protected XuiEnglishRequestServiceInterface $xuiEnglishRequestService)
    {
    }

    public function create(CreateNewClientTrafficRequest $request)
    {
        $inbounds = Inbound::all();
        $users = User::all();
        return view('clients.create_client', ['inbounds' => $inbounds, 'users' => $users]);
    }

    public function store(StoreNewClientTrafficRequest $request)
    {
        DB::transaction(function () use ($request) {
            $expireTime = Carbon::tomorrow()->addMonth()->timestamp * 1000;
            $clientTraffic = ClientTraffic::create([
                'inbound_id' => $request->inbound,
                'enable' => ClientTrafficEnableEnum::ENABLE->value,
                'email' => $request->username,
                'up' => 0,
                'down' => 0,
                'expiry_time' => $expireTime,
                'total' => $request->total * config('traffic_client.convert_to_gb'),
            ]);

            $inboundRow = Inbound::find($request->inbound);
            $userSetting = [
                'id' => Str::uuid(),
                'alterId' => 0,
                'email' => $request->username,
                'limitIp' => 0,
                'totalGB' => $request->total * config('traffic_client.convert_to_gb'),
                'expiryTime' => $expireTime
            ];
            $inboundSettings = json_decode($inboundRow->settings, true);
            $clients = [];
            foreach ($inboundSettings['clients'] as $client) {
                $clients[] = $client;
            }
            $clients[] = $userSetting;
            $settings = [
                'clients' => $clients,
                'disableInsecureEncryption' => $inboundSettings['disableInsecureEncryption']
            ];
            $inboundRow->settings = json_encode($settings, JSON_PRETTY_PRINT);
            UserClientTraffic::create([
                'user_id' => $request->user,
                'client_traffic_id' => $clientTraffic->id
            ]);
            $inboundRow->enable = (bool)$inboundRow->enable;
            $this->xuiEnglishRequestService->updateInbound($request->inbound, $inboundRow->toArray());
            Renew::create([
                'type' => RenewTypeEnum::CREATE->value,
                'user_id' => $request->user,
                'client_id' => $clientTraffic->id,
                'traffic' => $request->total * config('traffic_client.convert_to_gb'),
                'status' => RenewStatusEnum::RENEW->value,
            ]);
        });
        return redirect(route('dashboard'));
    }
}
