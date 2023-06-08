<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientTraffic;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientListController extends Controller
{
    public function __invoke(Request $request)
    {
        $result = $this->getClientTraffic($request->input('search'));
        return view('clients.index', ['clients' => $result]);
    }

    /**
     * @return LengthAwarePaginator
     */
    protected function getClientTraffic(?string $search): LengthAwarePaginator
    {
        return (new ClientTraffic())->getClientsInformationList(auth()->user(),$search);
    }
}
