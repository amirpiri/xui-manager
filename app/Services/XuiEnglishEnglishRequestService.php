<?php declare(strict_types=1);

namespace App\Services;

use App\Enums\CacheKeyEnum;
use App\Helpers\UrlHelperTrait;
use App\Models\ServerUser;
use App\Services\Contracts\XuiEnglishRequestServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class XuiEnglishEnglishRequestService implements XuiEnglishRequestServiceInterface
{
    use UrlHelperTrait;

    public const UPDATE_ROUTE = 'xui/inbound/update/';

    public const LOGIN_ROUTE = 'login';

    protected string $baseUrl;


    public function __construct(protected Client $guzzleClient)
    {
        $this->baseUrl = config('traffic_client.xui_panel_address');
    }

    /**
     * @return array|null
     * @throws GuzzleException
     */
    private function loginRequest(): ?array
    {
        $jar = new CookieJar();
        $admin = ServerUser::first();
        $response = $this->guzzleClient->request('post',
            $this->generateFullUrl($this->baseUrl, self::LOGIN_ROUTE),
            [
                'cookies' => $jar,
                'json' => [
                    'username' => $admin->username,
                    'password' => $admin->password,
                ]
            ]
        );
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $it = $jar->getIterator();
            return $it->current()->toArray();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSession(int $time = 0): string
    {
        $session = '';
        if (Cache::has(CacheKeyEnum::ADMIN_SESSION->value)) {
            $session = Cache::get(CacheKeyEnum::ADMIN_SESSION->value);
        } else {
            try {
                $response = $this->loginRequest();
                if (is_null($response) and $time === 0) {
                    $this->getSession(1);
                } elseif (!is_null($response)) {
                    $session = $response['Value'];
                    Cache::put(CacheKeyEnum::ADMIN_SESSION->value, $session, CacheKeyEnum::ADMIN_SESSION->duration());
                }
            } catch (GuzzleException $e) {
                $this->getSession(1);
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
            }

        }
        return $session;
    }

    /**
     * @inheritDoc
     */
    public function updateInbound(int $inboundId, array $data): void
    {
        $this->guzzleClient->request('post',
            $this->generateFullUrl($this->baseUrl, self::UPDATE_ROUTE . $inboundId),
            [
                'cookies' => CookieJar::fromArray(
                    ['session' => $this->getSession()],
                    parse_url($this->baseUrl)['host']
                ),
                'json' => [
                    'up' => $data['up'],
                    'down' => $data['down'],
                    'total' => $data['total'],
                    'remark' => $data['remark'],
                    'enable' => $data['enable'],
                    'expiryTime' => $data['expiry_time'],
                    'listen' => $data['listen'],
                    'port' => $data['port'],
                    'protocol' => $data['protocol'],
                    'settings' => $data['settings'],
                    'streamSettings' => $data['stream_settings'],
                    'sniffing' => $data['sniffing'],
                ]
            ]
        );
    }
}
