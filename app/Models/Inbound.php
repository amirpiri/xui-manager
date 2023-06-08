<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Inbound extends Model
{
    protected $connection = 'xui';

    protected $table = 'inbounds';

    protected $guarded = [];

    public $timestamps = false;

    public function clientTraffics(): HasMany
    {
        return $this->hasMany(ClientTraffic::class);
    }

    /**
     * @return Collection
     */
    public function getInbounds(): Collection
    {
        if (!((bool)config('telegraph.xui.connection_generator_is_custom'))) {
            if (is_null(config('telegraph.xui.inbound_excludes'))) {
                return Inbound::all();
            } else {
                return Inbound::whereNotIn('id', explode(',', config('telegraph.xui.inbound_excludes')))->get();
            }
        } else {
            return Inbound::whereIn('id', config('telegraph.xui.inbounds'))->get();
        }
    }
}
