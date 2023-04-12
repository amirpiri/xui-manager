<?php

namespace App\Console\Commands;

use App\Models\ClientTraffic;
use App\Models\Inbound;
use Illuminate\Console\Command;

class IncreaseTrafficAndExpireCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inbound:increase {traffic} {expire_time}';

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
        $expireTime = $this->argument('expire_time') * 86400 * 1000;
        $traffic = $this->argument('traffic') * config('traffic_client.convert_to_gb');
        \DB::transaction(function () use ($traffic, $expireTime) {
            ClientTraffic::where('total', '<>', 0)->increment('total', $traffic);
            ClientTraffic::where('total', '<>', 0)->increment('expiry_time', $expireTime);
            $inbounds = Inbound::all();
            foreach ($inbounds as $inbound) {
                $settings = json_decode($inbound->settings, true);
                foreach ($settings['clients'] as $key => $client) {

                    $settings['clients'][$key]['totalGB'] = (int)$client['totalGB'] + $traffic;
                    $settings['clients'][$key]['expiryTime'] = (int)$client['expiryTime'] + $expireTime;
                }

                $inbound->settings = json_encode($settings);
                Inbound::where('id', $inbound->id)->update($inbound->toArray());
            }
        });


    }
}
