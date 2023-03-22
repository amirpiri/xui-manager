<?php

namespace App\Http\TelegramWebhooks;

use App\Http\TelegramWebhooks\Enums\ChatStateEnum;
use App\Http\TelegramWebhooks\Services\DnsService;
use App\Models\Inbound;
use Carbon\Carbon;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;
use Morilog\Jalali\Jalalian;
use PHPUnit\Event\Runtime\PHP;
use Storage;

class CustomWebhookHandler extends WebhookHandler
{
    protected function handleChatMessage(Stringable $text): void
    {

        if ($text->toString() === __('telegram_bot.keyboard.ip_list')) {
            $this->chat->message(
                'لیست آی پی چیست؟' . PHP_EOL .
                'به دلیل فیلترینگ شدیدی که این روزها حاکم هست هر آی پی ممکن است روی اینترنت خاصی کار بکند. برای همین ما آی پی‌های هر اینترنت را جدا کردیم.' . PHP_EOL .
                'البته شما می‌توانید آی پی‌های مختلف را روی هر اینترنتی تست کنید و از هر کدام که سرعت بهتری گرفتید همان را استفاده کنید.' . PHP_EOL .
                'آی پی مورد نظر را باید در قسمت address کانفیگ وی پی ان خود وارد کنید و یا با زدن بر روی دریافت لینک ، لینک گانفیگ خود را دریافت کنید.'
            )->send();

            $dnsService = new DnsService(config('cloudflare.dns_zone_id'));
            $dnsRecords = $dnsService->getZoneRecords();

            if ($dnsRecords['success'] === true) {
                $keyboardArray = [];
                if (count($dnsRecords['result'] ?? []) > 0) {
                    foreach ($dnsRecords['result'] as $dnsRecord) {
                        if (!empty($dnsRecord['comment'])) {
                            $keyboardArray[] = Button::make($dnsRecord['comment'])
                                ->action('vpnAddress')
                                ->param('url', $dnsRecord['name']);
                        }
                    }
                }

                if (count($keyboardArray) > 0) {
                    $this->chat->message(__('telegram_bot.please_choose_one'))
                        ->keyboard(Keyboard::make()->buttons($keyboardArray)->chunk(4))
                        ->send();
                }
            } else {
                \Log::error(json_encode($dnsRecords));
                $this->chat->message('Error');
            }

        } elseif ($text->toString() === __('telegram_bot.keyboard.account')) {

            $this->chat->state = ChatStateEnum::Account_UUID;
            $this->chat->save();

            if (!empty($this->chat->client_uuid)) {
                $this->chat->message(__('telegram_bot.you_already_have_an_account'))
                    ->removeReplyKeyboard()
                    ->send();

                $this->chat->message(__('telegram_bot.do_you_want_to_check_previous_id'))
                    ->keyboard(Keyboard::make()->buttons([
                        Button::make(__('telegram_bot.yes'))->action('yesCheckPreviousAccount')->param('yesCheckPreviousAccount', true),
                        Button::make(__('telegram_bot.no'))->action('newId')->param('newId', true),
                    ])->chunk(2))
                    ->send();
            } else {
                $this->pleaseEnterYourId();
            }

        } elseif ($text->toString() === __('telegram_bot.keyboard.contact_support')) {
            $this->chat->message(__('telegram_bot.to_contact_support_tap_on_link_bellow'))->send();
            $this->chat->message(config('telegraph.xui.support_telegram_account'))->send();
        } elseif ($text->toString() === __('telegram_bot.keyboard.tutorials')) {
            $this->chat->message(__('telegram_bot.please_choose_one'))->replyKeyboard(ReplyKeyboard::make()->buttons([
                ReplyButton::make(__('telegram_bot.keyboard.android_tutorial')),
                ReplyButton::make(__('telegram_bot.keyboard.ios_tutorial')),
                ReplyButton::make(__('telegram_bot.keyboard.windows_tutorial')),
                ReplyButton::make(__('telegram_bot.keyboard.mac_tutorial')),
                ReplyButton::make(__('telegram_bot.restart')),
            ])->chunk(4))->send();
        } elseif ($text->toString() === __('telegram_bot.keyboard.android_tutorial')) {
            $this->chat
                ->video(config('telegraph.xui.android_tutorial_video_path'))
                ->message('آموزش نرم افزار v2rayng برای اندروید')
                ->send();
            $this->chat->message('لینک دانلود: https://play.google.com/store/apps/details?id=com.v2ray.ang')->send();
        } elseif ($text->toString() === __('telegram_bot.keyboard.ios_tutorial')) {
            $this->chat
                ->video(config('telegraph.xui.ios_tutorial_video_path'))
                ->message('آموزش نرم افزار napsternetv برای  IOS')
                ->send();
            $this->chat->message('لینک دانلود: https://apps.apple.com/us/app/napsternetv/id1629465476')->send();
        }  elseif ($text->toString() === __('telegram_bot.keyboard.windows_tutorial')) {
            $this->chat
                ->video(config('telegraph.xui.windows_tutorial_video_path'))
                ->message('آموزش نرم افزار nekoray برای ویندوز')
                ->send();
            $this->chat->message('لینک دانلود: https://github.com/MatsuriDayo/nekoray')->send();
        }  elseif ($text->toString() === __('telegram_bot.keyboard.mac_tutorial')) {
            $this->chat
                ->video(config('telegraph.xui.mac_tutorial_video_path'))
                ->send();
        } elseif ($this->chat->state == ChatStateEnum::Account_UUID->value) {

            $inbound = null;
            $clientData = $this->findUserByClientUUID($text->toString(), $inbound);

            if (empty($clientData) and !empty($this->chat->client_uuid)) {
                $clientData = $this->findUserByClientUUID($this->chat->client_uuid, $inbound);
            }

            if (!isset($clientData) or empty($clientData)) {
                $this->chat->message(__('telegram_bot.id_is_not_found'))->send();
                $this->chat->message(__('telegram_bot.to_restart_tap_on_start_command'))->send();
            } else {
                $clientTraffic = $inbound->clientTraffics()
                    ->where('email', $clientData['email'])
                    ->first();

                $remainingTraffic = $clientTraffic['total'] - ($clientTraffic['up'] + $clientTraffic['down']);
                if (!empty($clientData['expiryTime'])) {
                    $expiryTime = Carbon::createFromTimestamp($clientData['expiryTime'] / 1000);
                    $expiryTime = Jalalian::fromCarbon($expiryTime)->format('%A, %d %B %Y');
                }

                $this->chat->message(
                    __(
                        'telegram_bot.user_data',
                        [
                            'remaining' => formatBytes($remainingTraffic),
                            'expiryDate' => $expiryTime ?? '',
                            'email' => $clientData['email'],
                        ]
                    )
                )->keyboard(Keyboard::make()->buttons([
                    Button::make(__('telegram_bot.check_again'))->action('checkAgain')->param('checkAgain', true),
                    Button::make(__('telegram_bot.new_id'))->action('newId')->param('newId', true),
                    Button::make(__('telegram_bot.restart'))->action('start')->param('newId', true),
                ])->chunk(2))->send();

                $this->chat->client_uuid = $clientData['id'];
                $this->chat->inbound_id = $inbound->id;
                $this->chat->email = $clientData['email'];
                $this->chat->save();

//                if (!$this->chat->active_alert) {
//                    $this->chat->message(__('telegram_bot.do_you_want_to_activate_account_alert'))
//                        ->keyboard(Keyboard::make()->buttons([
//                            Button::make(__('telegram_bot.yes'))->action('yes')->param('alert', true),
//                            Button::make(__('telegram_bot.no'))->action('no')->param('alert', false),
//                        ])->chunk(2))
//                        ->send();
//                }
            }

        } else {
            $this->start();
        }

    }

    private function pleaseEnterYourId()
    {
        $this->chat->message(__('telegram_bot.learn_how_to_find_id_from_images_below'))
            ->removeReplyKeyboard()
            ->send();
        $this->chat->photo(base_path('public/images/1.jpg'))->send();
        $this->chat->photo(base_path('public/images/2.jpg'))->send();
        $this->chat->message(__('telegram_bot.please_enter_your_account_id'))
            ->send();
    }

    public function start()
    {
        $this->chat->message(__('telegram_bot.welcome'))
            ->send();
        $this->chat->state = ChatStateEnum::Start;
        $this->chat->save();
        $this->chat->message(__('telegram_bot.please_choose_one'))
            ->replyKeyboard(ReplyKeyboard::make()->buttons([
                ReplyButton::make(__('telegram_bot.keyboard.account')),
                ReplyButton::make(__('telegram_bot.keyboard.ip_list')),
                ReplyButton::make(__('telegram_bot.keyboard.tutorials')),
                ReplyButton::make(__('telegram_bot.keyboard.contact_support')),
            ])->chunk(2))->send();
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        $this->chat->message(__('telegram_bot.i_cant_understand_your_command') . ' ' . $text)->send();
    }

    public function yes()
    {
        $alert = $this->data->get('alert');
        if ($alert) {
            $this->chat->active_alert = true;
            $this->chat->state = ChatStateEnum::Account_UUID;
            $this->chat->save();
        }
        $this->reply("Done");
        $this->deleteKeyboard();
        $this->chat->message(__('telegram_bot.alert_is_activated_successfully'))->send();
    }

    public function no()
    {
        $alert = $this->data->get('alert');
        if (!$alert) {
            $this->chat->active_alert = false;
            $this->chat->state = ChatStateEnum::Account_UUID;
            $this->chat->save();
        }
        $this->reply("Done");
        $this->deleteKeyboard();
        $this->chat->message(__('telegram_bot.alert_is_deactivated_successfully'))->send();
    }

    private function findUserByClientUUID(string $uuid, &$foundedInbound): ?array
    {
        $inbounds = $this->getInbounds();
        foreach ($inbounds as $inbound) {
            $clients = json_decode($inbound->settings, true);
            $clients = collect($clients['clients']);
            $clientData = $clients->where('id', $uuid)->first();
            if (!empty($clientData)) {
                $foundedInbound = $inbound;
                break;
            }
        }
        return $clientData ?? null;
    }

    private function getInbounds()
    {
        return Inbound::whereIn('id', config('telegraph.xui.inbounds'))->get();
    }

    public function yesCheckPreviousAccount()
    {
        $this->handleChatMessage(new Stringable($this->chat->client_uuid));
    }

    public function newId()
    {
        $this->deleteKeyboard();
        $this->pleaseEnterYourId();
    }

    public function checkAgain()
    {
        $this->deleteKeyboard();
        $uuid = $this->chat->client_uuid;
        if (!empty($uuid)) {
            $this->handleChatMessage(new Stringable($uuid));
        }
    }

    public function vpnAddress()
    {
        $url = $this->data->get('url');
        $this->chat->markdownV2('```' . $url . '```')
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('telegram_bot.get_link'))->action('getConfigLink')->param('url', $url),
            ])->chunk(2))->send();
    }

    public function getConfigLink()
    {
        $url = $this->data->get('url');
        if (empty($this->chat->client_uuid)) {
            $this->chat->message('آیدی شما ثبت نشده. برای ثبت آیدی دکمه حساب کاربری زیر را بزنید.')->send();
        } else {
            $this->chat->markdownV2(
                '```' .
                'vless://' .
                $this->chat->client_uuid . '@' . $url . ':443?sni=' .
                config('telegraph.xui.active_domain') .
                '&security=tls&type=ws&path=/chat&host=' .
                config('telegraph.xui.active_domain') .
                '#AmirFalconAC' .
                '```'
            )->send();
        }
    }
}
