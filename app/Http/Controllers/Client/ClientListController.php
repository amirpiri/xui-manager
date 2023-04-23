<?php

namespace App\Http\Controllers\Client;

use App\Enums\CacheKeyEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Inbound;
use App\Models\UserClientTraffic;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClientListController extends Controller
{
    public function __invoke()
    {
        $result = $this->getClientTraffic();
        return view('clients.index', ['clients' => $result]);
    }

    /**
     * @return LengthAwarePaginator
     */
    protected function getClientTraffic(): LengthAwarePaginator
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

        return $result->whereNotNull('client_traffics.email')
            ->where('client_traffics.email', '<>', '')
            ->where('client_traffics.total', '<>', 0)
            ->orderBy('client_traffics.enable')
            ->orderBy('expire_date')
            ->paginate(20);
    }
}
