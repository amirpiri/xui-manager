<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ArvanUpdateDnsRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dns:change {newIp}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change dns on arvan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $authorization = ['Authorization' => config('traffic_client.arvan_api_key')];
        $domains = Http::withHeaders($authorization)
            ->get('https://napi.arvancloud.ir/cdn/4.0/domains?per_page=50');


        if ($domains->ok()) {
            $this->info('url downloaded');
            sleep(5);
            foreach (json_decode($domains->body())->data as $domain) {
                $dnsRecords = Http::withHeaders($authorization)
                    ->get("https://napi.arvancloud.ir/cdn/4.0/domains/{$domain->domain}/dns-records");

                if ($dnsRecords->ok()) {
                    foreach (json_decode($dnsRecords->body(), true)['data'] as $dnsRecord) {
                        if ($dnsRecord['type'] === 'a') {
                            $this->info('ds ' . "https://napi.arvancloud.ir/cdn/4.0/domains/{$domain->domain}/dns-records/{$dnsRecord['id']}");
                            $dnsRecord['value'][0]['ip'] = $this->argument('newIp');
                            $update = Http::withHeaders($authorization)
                                ->put("https://napi.arvancloud.ir/cdn/4.0/domains/{$domain->domain}/dns-records/{$dnsRecord['id']}", $dnsRecord);

                            $this->info("{$domain->domain} update: " . $update->body());
                            sleep(1);
                        }
                    }
                }
            }
        }
    }
}
