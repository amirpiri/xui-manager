<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
