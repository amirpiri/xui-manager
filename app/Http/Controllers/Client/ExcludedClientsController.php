<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientTraffic;
use Illuminate\Http\Request;

class ExcludedClientsController extends Controller
{
    public function __invoke()
    {
        return view('clients.index', ['clients' => (new ClientTraffic)->getClientsInformationList(auth()->user(), '', true)]);
    }
}
