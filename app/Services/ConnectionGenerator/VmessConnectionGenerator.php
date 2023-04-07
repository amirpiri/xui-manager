<?php

namespace App\Services\ConnectionGenerator;

use App\Models\Inbound;

class VmessConnectionGenerator extends AbstractConnectionGenerator
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
        $streamSettings = json_decode($this->inbound->stream_settings, true);
        $connection = $this->template;
        $connection['add'] = $this->address;
        $connection['host'] = $this->inbound->remark;
        $connection['id'] = $this->id;
        $connection['net'] = $streamSettings['network'];
        $connection['path'] = $streamSettings['wsSettings']['path'];
        $connection['ps'] = $this->email;
        $connection['sni'] = $this->inbound->remark;
        $connection['tls'] = $streamSettings['security'];
        return "{$this->inbound->protocol}://" . base64_encode(json_encode($connection));
    }
}
