<?php

namespace App\Services\ConnectionGenerator;

use App\Models\Inbound;

class VlessConnectionGenerator extends AbstractConnectionGenerator
{
    public function __construct(
        protected string  $id,
        protected string  $email,
        protected Inbound $inbound,
        protected string  $url
    )
    {
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        if (((bool)config('telegraph.xui.connection_generator_is_custom'))) {
            return 'vless://' . $this->id . '@' . $this->url . ':443?sni=' .
                config('telegraph.xui.active_domain') .
                '&security=tls&type=ws&path=/chat&host=' .
                config('telegraph.xui.active_domain') .
                '#' . $this->inbound->remark;
        } else {
            $streamSettings = json_decode($this->inbound->stream_settings, true);
            return "vless://{$this->id}@{$this->inbound->remark}:443?type={$streamSettings['network']}&security={$streamSettings['security']}&path={$streamSettings['wsSettings']['path']}&sni={$this->inbound->remark}#$this->email";
        }

    }
}
