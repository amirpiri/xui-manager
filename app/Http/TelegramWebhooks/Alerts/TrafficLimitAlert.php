<?php

namespace App\Http\TelegramWebhooks\Alerts;

use App\Models\ClientTraffic;
use App\Models\Inbound;
use Carbon\Carbon;
use DefStudio\Telegraph\Models\TelegraphChat;
use Morilog\Jalali\Jalalian;

class TrafficLimitAlert
{
    public function __invoke()
    {
        \Log::debug('starting alert ..................');
        $onGigaBytesInBytes = 1073741824;
        $chats = TelegraphChat::whereNotNull('client_uuid')->get();
        foreach ($chats as $chat) {
            try {
                if (!empty($chat->email) and !is_null($clientTraffic = ClientTraffic::where('email', $chat->email)->first())) {

                    \Log::debug('$clientData => ', $clientTraffic->toArray());

                    if (!empty($clientTraffic['expiry_time'])) {
                        $expiryTime = Carbon::createFromTimestamp($clientTraffic['expiry_time'] / 1000);
                        \Log::debug($expiryTime);
                        if ($expiryTime->diffInHours(Carbon::now()) <= 24) {
                            \Log::debug('your_account_will_be_expire_in_24_hours');
                            $chat->message(
                                __(
                                    'telegram_bot.your_account_will_be_expire_in_24_hours',
                                    ['telegram_account' => config('telegraph.xui.support_telegram_account')]
                                )
                            )->send();
                            $chat->traffic_limit_notified_at = Carbon::now()->toDateTimeString();
                            $chat->save();
                            continue;
                        }
                    }

                    $remainingTraffic = $clientTraffic['total'] - ($clientTraffic['up'] + $clientTraffic['down']);
                    \Log::debug('remaining traffic => ' . $remainingTraffic);

                    if (is_null($chat->traffic_limit_notified_at)) {
                        $checkAlertTimeLimit = true;
                    } else {
                        $checkAlertTimeLimit = Carbon::parse($chat->traffic_limit_notified_at)->addMinutes(2)->isPast();
                    }

                    if ($remainingTraffic <= $onGigaBytesInBytes and $remainingTraffic > 0 and $checkAlertTimeLimit) {
                        \Log::debug('your_traffic_limit_will_be_reached_soon');
                        $chat->message(
                            __(
                                'telegram_bot.your_traffic_limit_will_be_reached_soon',
                                ['telegram_account' => config('telegraph.xui.support_telegram_account')]
                            )
                        )->send();
                        $chat->traffic_limit_notified_at = Carbon::now()->toDateTimeString();
                        $chat->save();
                    }
                }
            } catch (\Exception $exception) {
                \Log::error($exception);
            }
        }
    }
}
