<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundClientIp extends Model
{
    protected $connection = 'sqlite_secondary';

    protected $table = 'inbound_client_ips';

    public $timestamps = false;

    protected $guarded = [];
}
