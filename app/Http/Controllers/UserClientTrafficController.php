<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\StoreUserClientTrafficRequest;
use App\Models\ClientTraffic;
use App\Models\User;
use App\Models\UserClientTraffic;


class UserClientTrafficController extends Controller
{

    public function show()
    {
        $admins = User::all();
        $userClientTraffic = UserClientTraffic::all()->pluck('client_traffic_id')->toArray();
        $remainingClientTraffic = ClientTraffic::whereNotIn('id', $userClientTraffic)->get();
        return view('clients.user_client_traffic', ['admins' => $admins, 'remainingUsers' => $remainingClientTraffic]);
    }

    public function store(StoreUserClientTrafficRequest $request)
    {
        UserClientTraffic::create([
            'client_traffic_id' => $request->user,
            'user_id' => $request->reseller,
        ]);

        return redirect()->route('traffic-client-user.show');
    }
}
