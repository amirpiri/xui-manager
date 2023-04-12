<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
