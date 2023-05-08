<?php

namespace App\Console\Commands;

use App\Models\ClientTraffic;
use App\Models\Inbound;
use App\Services\Contracts\XuiEnglishRequestServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class TransferClientToCloudflareCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inbound:transfer {uuid} {inboundId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var XuiEnglishRequestServiceInterface
     */
    protected XuiEnglishRequestServiceInterface $xuiRequestService;

    /**
     * Execute the console command.
     * @throws GuzzleException
     */
    public function handle()
    {
        /** @var XuiEnglishRequestServiceInterface $xuiRequestService */
        $this->xuiRequestService = resolve(XuiEnglishRequestServiceInterface::class);

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
        $targetInbound = $this->updateSourceInbound($inbound, $newInboundConfig);

        $this->updateDestinationInbound($targetInbound, $configMustTransferred);


        ClientTraffic::where('email', $configMustTransferred['email'])
            ->update([
                'inbound_id' => $this->argument('inboundId'),
            ]);
    }

    /**
     * @param Inbound $inbound
     * @param array $newInboundConfig
     * @return Inbound
     * @throws GuzzleException
     */
    protected function updateSourceInbound(Inbound $inbound, array $newInboundConfig): Inbound
    {
        $inboundUpdating = $inbound->toArray();
        $inboundUpdating['settings'] = $newInboundConfig;
        $inboundUpdating['enable'] = (bool)$inboundUpdating['enable'];
        $this->xuiRequestService->updateInbound($inbound->id, $inboundUpdating);
        return Inbound::where('id', $this->argument('inboundId'))
            ->first();
    }

    /**
     * @param Inbound $targetInbound
     * @param array $configMustTransferred
     * @return void
     * @throws GuzzleException
     */
    protected function updateDestinationInbound(Inbound $targetInbound, array $configMustTransferred): void
    {
        $targetInboundUpdating = $targetInbound->toArray();
        $targetInboundUpdating['settings']['clients'][] = $configMustTransferred;
        $targetInboundUpdating['enable'] = (bool)$targetInboundUpdating['enable'];
        $this->xuiRequestService->updateInbound($this->argument('inboundId'), $targetInboundUpdating);
    }
}
