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
        return 'vless://' . $this->id . '@' . $this->address . ':443?sni=' .
        config('telegraph.xui.active_domain') .
        '&security=tls&type=ws&path=/chat&host=' .
        config('telegraph.xui.active_domain') .
        '#' . $this->inbound->remark;
    }
}
