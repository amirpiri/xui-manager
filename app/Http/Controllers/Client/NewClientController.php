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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewClientController extends Controller
{
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
                'totalGB' => 0,
                'expiryTime' => $expireTime
            ];
            $inboundSettings = json_decode($inboundRow->settings, true);
            $settings = [
                'clients' => array_merge($inboundSettings['clients'], $userSetting),
                'disableInsecureEncryption' => $inboundSettings['disableInsecureEncryption']
            ];
            $inboundRow->settings = $settings;
            Inbound::where('id', $request->inbound)->update($inboundRow->toArray());
            UserClientTraffic::create([
                'user_id' => $request->user,
                'client_traffic_id' => $clientTraffic->id
            ]);
            Renew::create([
                'type' => RenewTypeEnum::CREATE->value,
                'user_id' => $request->user,
                'client_id' => $clientTraffic->id,
                'traffic' => $request->total * config('traffic_client.convert_to_gb'),
                'status' => RenewStatusEnum::RENEW->value,
            ]);
        });
        return view('dashboard');
    }
}
