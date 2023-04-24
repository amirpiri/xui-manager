<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerUser extends Model
{
    protected $connection = 'xui';

    protected $table = 'users';

    public $timestamps = false;

    protected $guarded = [];
}
