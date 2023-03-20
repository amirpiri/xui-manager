<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inbound extends Model
{
    protected $connection = 'xui';

    public function clientTraffics()
    {
        return $this->hasMany(ClientTraffic::class, 'inbound_id');
    }
}
