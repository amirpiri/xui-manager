<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTraffic extends Model
{
    protected $connection = 'xui';
    protected $table = 'client_traffics';
}
