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
        'tls' => 'tls',
        'type' => '',
        'v' => '2',
    ];

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
        $connection = $this->template;
        $connection['add'] = $this->inbound->remark;
        $connection['host'] = "";
        $connection['id'] = $this->id;
        $connection['net'] = $streamSettings['network'];
        $connection['path'] = $streamSettings['wsSettings']['path'];
        $connection['ps'] = $this->email;
        $connection['tls'] = $streamSettings['security'];
        return "{$this->inbound->protocol}://" . base64_encode(json_encode($connection));
    }
}
