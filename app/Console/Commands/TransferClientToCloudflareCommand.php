<?php

namespace App\Console\Commands;

use App\Models\ClientTraffic;
use App\Models\Inbound;
use Illuminate\Console\Command;

class TransferClientToCloudflareCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inbound:transfer {uuid}';

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
        $inbound = Inbound::where('settings', 'like', "%$uuid%")->first();
        $configMustTransferred = [];
        $configs = json_decode($inbound->settings, true);
        foreach ($configs['clients'] as $key => $client) {
            if ($client['id'] === $uuid) {
                $configMustTransferred = $client;
                unset($configs['clients'][$key]);
            }
        }
        $inbound->settings = $configs;
        Inbound::where('id', $inbound->id)->update($inbound->toArray());
        $cloudflareInbound = Inbound::where('id', config('traffic_client.cloudflare_inbound_id'))
            ->first();
        $oldConfig = json_decode($cloudflareInbound->settings, true);
        $oldConfig['clients'][] = $configMustTransferred;
        $cloudflareInbound->settings = json_encode($oldConfig);
        Inbound::where('id', config('traffic_client.cloudflare_inbound_id'))
            ->update($cloudflareInbound->toArray());
        ClientTraffic::where('email', $configMustTransferred['email'])
            ->update([
                'inbound_id' => config('traffic_client.cloudflare_inbound_id'),
            ]);
    }
}
