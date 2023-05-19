<?php

namespace App\Services\ConnectionGenerator;

use App\Models\Inbound;

class VlessConnectionGenerator extends AbstractConnectionGenerator
{
    public function __construct(
        protected string  $id,
        protected string  $email,
        protected Inbound $inbound
    )
    {
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        $streamSettings = json_decode($this->inbound->stream_settings, true);
        return "vless://{$this->id}@{$this->inbound->remark}:443?type={$streamSettings['network']}&security={$streamSettings['security']}&path={$streamSettings['wsSettings']['path']}&sni={$this->inbound->remark}#$this->email";

    }
}
