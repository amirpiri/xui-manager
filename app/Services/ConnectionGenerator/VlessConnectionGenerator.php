<?php

namespace App\Services\ConnectionGenerator;

use App\Models\Inbound;

class VlessConnectionGenerator extends AbstractConnectionGenerator
{
    protected array $template = [
        'add' => 'cloudflare',
        'aid' => '0',
        'alpn' => '',
        'fp' => '',
        'host' => 'sni',
        'id' => 'uuid',
        'net' => 'ws',
        'path' => '/path',
        'port' => '443',
        'ps' => 'email',
        'scy' => 'auto',
        'sni' => 'sni',
        'tls' => 'tls',
        'type' => '',
        'v' => '2',
    ];

    public function __construct(
        protected string  $address,
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
        return 'vless://' . $this->id . '@' . $this->inbound->address . ':443?sni=' .
        config('telegraph.xui.active_domain') .
        '&security=tls&type=ws&path=/chat&host=' .
        config('telegraph.xui.active_domain') .
        '#' . $this->inbound->remark;
    }
}
