<?php

namespace App\Console\Commands;

use App\Models\ClientTraffic;
use App\Models\Inbound;
use Illuminate\Console\Command;

class VpnGenerateCommand extends Command
{
    const PREFIX = 'ConnectionGenerator';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vpn:generate {uuid} {address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uuid = $this->argument('uuid');
        try {
            $inbound = Inbound::where('settings', 'like', "%{$uuid}%")
                ->firstOrFail();

            $client = ClientTraffic::where('email', $this->getClientEmail($inbound, $uuid))->firstOrFail();
            $class = 'App\\Services\\ConnectionGenerator\\' . ucfirst($inbound->protocol) . self::PREFIX;
            $this->info( (new $class(
                $this->argument('address'),
                $uuid,
                $client->email,
                $inbound
            ))->generate());
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
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
