<?php

namespace App\Http\Controllers\Client;

use App\Enums\CacheKeyEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Inbound;
use App\Models\UserClientTraffic;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $result = Inbound::select([
            DB::raw('(client_traffics.total - (client_traffics.down + client_traffics.up)) as remaining'),
            'client_traffics.id',
            'client_traffics.email as email',
            'remark',
            'client_traffics.enable',
            'client_traffics.down as down',
            'client_traffics.up as up',
            'client_traffics.total',
            'client_traffics.expiry_time as expire_date'
        ])->leftJoin('client_traffics', 'inbounds.id', '=', 'client_traffics.inbound_id');

        if (auth()->user()->role === UserRoleEnum::RESELLER->value) {
            $userId = UserClientTraffic::select(['client_traffic_id'])
                ->where('user_id', auth()->user()->id)
                ->get()->toArray();
            $result = $result->whereIn('client_traffics.id', $userId);
        }

        if (!empty($search)) {
            $inbound = null;
            $clientData = $this->findUserByClientUUID($search, $inbound);

            if (!empty($clientData) and isset($clientData['email'])) {
                $result->where('email', 'like', '%' . $clientData['email'] . '%');
            } else {
                $result->where('email', 'like', '%' . $search . '%');
            }
        }

        return $result->whereNotNull('client_traffics.email')
            ->where('client_traffics.email', '<>', '')
            ->where('client_traffics.total', '<>', 0)
            ->orderBy('client_traffics.enable')
            ->orderBy('expire_date')
            ->paginate(20);
    }


    private function findUserByClientUUID(string $uuid, &$foundedInbound): ?array
    {
        $inbounds = $this->getInbounds();
        foreach ($inbounds as $inbound) {
            $clients = json_decode($inbound->settings, true);
            $clients = collect($clients['clients']);
            $clientData = $clients->where('id', $uuid)->first();
            if (!empty($clientData)) {
                $foundedInbound = $inbound;
                break;
            }
        }
        return $clientData ?? null;
    }

    private function getInbounds()
    {
        if (!((bool)config('telegraph.xui.connection_generator_is_custom'))) {
            if (is_null(config('telegraph.xui.inbound_excludes'))) {
                return Inbound::all();
            } else {
                return Inbound::whereNotIn('id', explode(',', config('telegraph.xui.inbounds')))->get();
            }
        } else {
            return Inbound::whereIn('id', config('telegraph.xui.inbounds'))->get();
        }
    }
}
