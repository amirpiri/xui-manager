<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ClientTraffic;
use App\Models\Inbound;
use App\Services\Contracts\ClientTrafficServiceInterface;
use App\Services\Contracts\XuiEnglishRequestServiceInterface;
use GuzzleHttp\Exception\GuzzleException;

class ClientTrafficService implements ClientTrafficServiceInterface
{
    protected XuiEnglishRequestServiceInterface $xuiRequestService;

    /**
     * @throws GuzzleException
     */
    public function transferUser(string $uuid, int $inboundId)
    {
        $this->xuiRequestService = resolve(XuiEnglishRequestServiceInterface::class);

        $sourceInboundRow = Inbound::where('settings', 'like', "%$uuid%")->first();

        $configMustTransferred = [];
        $configs = json_decode($sourceInboundRow->settings, true);
//        $newInboundConfig['disableInsecureEncryption'] = $configs['disableInsecureEncryption'];


        $newInboundConfig = [];
        foreach ($configs['clients'] as $key => $client) {
            if ($client['id'] === $uuid) {
                $configMustTransferred = $client;
                unset($configs['clients'][$key]);
            } else {
                $newInboundConfig['clients'][] = $client;
            }
        }

        $targetInbound = $this->updateSourceInbound($sourceInboundRow, $newInboundConfig, (int)$inboundId);

        $this->updateDestinationInbound($targetInbound, $configMustTransferred, (int)$inboundId);


        ClientTraffic::where('email', $configMustTransferred['email'])
            ->update([
                'inbound_id' => $inboundId,
            ]);
    }

    /**
     * @param Inbound $inbound
     * @param array $newInboundConfig
     * @param int $inboundId
     * @return Inbound
     * @throws GuzzleException
     */
    protected function updateSourceInbound(Inbound $inbound, array $newInboundConfig, int $inboundId): Inbound
    {
        $inboundUpdating = $inbound->toArray();
        $inboundUpdating['settings'] = json_decode($inboundUpdating['settings'],true);
        $inboundUpdating['settings']['clients'] = $newInboundConfig['clients'];
        $inboundUpdating['enable'] = (bool)$inboundUpdating['enable'];
        $inboundUpdating['settings'] = json_encode($inboundUpdating['settings'], JSON_PRETTY_PRINT);
        $this->xuiRequestService->updateInbound($inbound->id, $inboundUpdating);
        return Inbound::where('id', $inboundId)
            ->first();
    }

    /**
     * @param Inbound $targetInbound
     * @param array $configMustTransferred
     * @param int $inboundId
     * @return void
     * @throws GuzzleException
     */
    protected function updateDestinationInbound(Inbound $targetInbound, array $configMustTransferred, int $inboundId): void
    {
        $targetInboundUpdating = $targetInbound->toArray();

        $targetInboundUpdating['settings'] = json_decode($targetInboundUpdating['settings'], true);
        $targetInboundUpdating['settings']['clients'][] = $configMustTransferred;
        $targetInboundUpdating['enable'] = (bool)$targetInboundUpdating['enable'];
        $targetInboundUpdating['settings'] = json_encode($targetInboundUpdating['settings'], JSON_PRETTY_PRINT);
        $this->xuiRequestService->updateInbound($inboundId, $targetInboundUpdating);
    }
}
