<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $connection = 'sqlite_secondary';


    protected $table = 'settings';

    public $timestamps = false;

    protected $guarded = [];
}
