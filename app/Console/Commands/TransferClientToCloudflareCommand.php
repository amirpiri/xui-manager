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
        $newInboundConfig['disableInsecureEncryption'] = $configs['disableInsecureEncryption'];
        $newInboundConfig = [];
        foreach ($configs['clients'] as $key => $client) {
            if ($client['id'] === $uuid) {
                $configMustTransferred = $client;
                unset($configs['clients'][$key]);
            } else {
                $newInboundConfig['clients'][] = $client;
            }
        }

        Inbound::where('id', $inbound->id)->update(['settings' => json_encode($newInboundConfig)]);
        $cloudflareInbound = Inbound::where('id', config('traffic_client.cloudflare_inbound_id'))
            ->first();
        $oldConfig = json_decode($cloudflareInbound->settings, true);
        $oldConfig['clients'][] = $configMustTransferred;
        Inbound::where('id', config('traffic_client.cloudflare_inbound_id'))
            ->update(['settings' => json_encode($oldConfig)]);
        ClientTraffic::where('email', $configMustTransferred['email'])
            ->update([
                'inbound_id' => config('traffic_client.cloudflare_inbound_id'),
            ]);
    }
}
