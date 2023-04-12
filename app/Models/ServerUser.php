<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerUser extends Model
{
    protected $connection = 'sqlite_secondary';

    protected $table = 'users';

    public $timestamps = false;

    protected $guarded = [];
}
