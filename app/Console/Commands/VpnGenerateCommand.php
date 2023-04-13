<?php

namespace App\Console\Commands;

use App\Models\Inbound;
use App\Services\GenerateConnection;
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

        try {
            $this->info(
                (new GenerateConnection($this->argument('uuid'), $this->argument('address')))->execute()
            );
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
