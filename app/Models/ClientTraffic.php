<?php

namespace App\Models;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClientTraffic extends Model
{
    protected $connection = 'xui';

    protected $table = 'client_traffics';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function inbound(): BelongsTo
    {
        return $this->belongsTo(Inbound::class);
    }

    /**
     * @param User $user
     * @param string|null $search
     * @param bool $excludedUsers
     * @return LengthAwarePaginator
     */
    public function getClientsInformationList(User $user, ?string $search = '', bool $excludedUsers = false): LengthAwarePaginator
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

        if ($excludedUsers === false) {
            $result = $result->whereNotIn('inbounds.id', explode(',', config('telegraph.xui.inbound_excludes')));
        } else {
            $result = $result->whereIn('inbounds.id', explode(',', config('telegraph.xui.inbound_excludes')));
        }
        if ($user->role === UserRoleEnum::RESELLER->value) {
            $userId = UserClientTraffic::select(['client_traffic_id'])
                ->where('user_id', $user->id)
                ->get()->toArray();
            $result = $result->whereIn('client_traffics.id', $userId);
        }

        if (!empty($search) && $excludedUsers === false) {
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

    /**
     * @param string $uuid
     * @param $foundedInbound
     * @return array|null
     */
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

    /**
     * @return Collection
     */
    private function getInbounds(): Collection
    {
        return (new Inbound)->getInbounds();
    }
}
