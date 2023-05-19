<?php

namespace App\Services;

use App\Models\ClientTraffic;
use App\Models\Inbound;

class GenerateConnection
{
    const PREFIX = 'ConnectionGenerator';

    public function __construct(
        private string $uuid,
        private string $url = ''
    )
    {
    }

    /**
     * @return string
     */
    public function execute(): string
    {

        $inbound = Inbound::where('settings', 'like', "%{$this->uuid}%")
            ->firstOrFail();

        $client = ClientTraffic::where('email', $this->getClientEmail($inbound, $this->uuid))->firstOrFail();
        $class = 'App\\Services\\ConnectionGenerator\\' . ucfirst($inbound->protocol) . self::PREFIX;
        return (new $class(
            $this->uuid,
            $client->email,
            $inbound,
            $this->url
        ))->generate();

    }

    /**
     * @param Inbound|null $inbound
     * @param string $uuid
     * @return string|null
     */
    protected function getClientEmail(?Inbound $inbound, string $uuid): ?string
    {
        $clients = json_decode($inbound->settings, true)['clients'];
        foreach ($clients as $client) {
            if ($client['id'] === $uuid) {
                return $client['email'];
            }
        }
        return null;
    }
}
