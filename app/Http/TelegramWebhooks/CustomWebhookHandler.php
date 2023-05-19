<?php

namespace App\Http\TelegramWebhooks;

use App\Enums\GenerateSiteEnum;
use App\Http\TelegramWebhooks\Enums\ChatStateEnum;
use App\Http\TelegramWebhooks\Services\DnsService;
use App\Models\Inbound;
use App\Services\GenerateConnection;
use Carbon\Carbon;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Response;
use Illuminate\Support\Stringable;
use Morilog\Jalali\Jalalian;
use Telegraph;

class CustomWebhookHandler extends WebhookHandler
{
    protected function handleChatMessage(Stringable $text): void
    {
        if ($text->toString() === __('telegram_bot.keyboard.ip_list')) {
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
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
                    Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
                    $this->chat->message(__('telegram_bot.please_choose_one'))
                        ->keyboard(Keyboard::make()->buttons($keyboardArray)->chunk(4))
                        ->send();
                }

                Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
                $this->chat->message(__('telegram_bot.get_subscription_link'))
                    ->keyboard(Keyboard::make()->buttons([
                        Button::make(__('telegram_bot.get'))->action('sendSubscriptionLink'),
                        Button::make(__('telegram_bot.tutorial'))->action('subscriptionTutorial')->param('bool', true),
                    ])->chunk(4))
                    ->send();
            } else {
                \Log::error(json_encode($dnsRecords));
                $this->chat->message('Error');
            }

        } elseif ($text->toString() === __('telegram_bot.keyboard.account')) {

            $this->chat->state = ChatStateEnum::Account_UUID;
            $this->chat->save();

            if (!empty($this->chat->client_uuid)) {
                Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
                $this->chat->message(__('telegram_bot.you_already_have_an_account'))
                    ->removeReplyKeyboard()
                    ->send();

                Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
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
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
            $this->chat->message(__('telegram_bot.to_contact_support_tap_on_link_bellow'))->send();
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
            $this->chat->message(config('telegraph.xui.support_telegram_account'))->send();
        } elseif ($text->toString() === __('telegram_bot.keyboard.tutorials')) {
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
            $this->chat->message(__('telegram_bot.please_choose_one'))->replyKeyboard(ReplyKeyboard::make()->buttons([
                ReplyButton::make(__('telegram_bot.keyboard.android_tutorial')),
                ReplyButton::make(__('telegram_bot.keyboard.ios_tutorial')),
                ReplyButton::make(__('telegram_bot.keyboard.windows_tutorial')),
                ReplyButton::make(__('telegram_bot.keyboard.mac_tutorial')),
                ReplyButton::make(__('telegram_bot.restart')),
            ])->chunk(4))->send();
        } elseif ($text->toString() === __('telegram_bot.keyboard.android_tutorial')) {
            $this->sendVideo(
                config('telegraph.xui.android_tutorial_video_path'),
                'آموزش نرم افزار v2rayng برای اندروید'
            );
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
            $this->chat->message('لینک دانلود: https://play.google.com/store/apps/details?id=com.v2ray.ang')->send();
        } elseif ($text->toString() === __('telegram_bot.keyboard.ios_tutorial')) {
            $this->sendVideo(
                config('telegraph.xui.ios_tutorial_video_path'),
                'آموزش نرم افزار napsternetv برای  IOS'
            );
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
            $this->chat->message('لینک دانلود: https://apps.apple.com/us/app/napsternetv/id1629465476')->send();
        }  elseif ($text->toString() === __('telegram_bot.keyboard.windows_tutorial')) {
            $this->sendVideo(
                config('telegraph.xui.windows_tutorial_video_path'),
                'آموزش نرم افزار nekoray برای ویندوز'
            );
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
            $this->chat->message('لینک دانلود: https://github.com/MatsuriDayo/nekoray')->send();
        }  elseif ($text->toString() === __('telegram_bot.keyboard.mac_tutorial')) {
            $this->sendVideo(
                config('telegraph.xui.mac_tutorial_video_path'),
                ''
            );
        } elseif ($this->chat->state == ChatStateEnum::Account_UUID->value) {

            $extract_uuid_pattern = "/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/";
            $string_to_match = $text->toString();
            preg_match_all($extract_uuid_pattern, $string_to_match, $matches);
            $uuid = $matches[0] ?? [];
            if (count($uuid) == 0) {
                Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
                $this->chat->message(__('telegram_bot.wrong_id'))->send();
                return;
            } else {
                $uuid = $uuid[0];
            }

            $inbound = null;
            $clientData = $this->findUserByClientUUID($uuid, $inbound);

            if (empty($clientData) and !empty($this->chat->client_uuid)) {
                $clientData = $this->findUserByClientUUID($this->chat->client_uuid, $inbound);
            }

            if (!isset($clientData) or empty($clientData)) {
                Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
                $this->chat->message(__('telegram_bot.id_is_not_found'))->send();
                Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
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

                Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
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
            }

        } else {
            $this->start();
        }

    }

    private function pleaseEnterYourId()
    {
        Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
        $this->chat->message(__('telegram_bot.learn_how_to_find_id_from_images_below'))
            ->removeReplyKeyboard()
            ->send();
        Telegraph::chat($this->chat)->chatAction(ChatActions::UPLOAD_PHOTO)->send();
        $this->chat->photo(base_path('public/images/1.jpg'))->send();
        Telegraph::chat($this->chat)->chatAction(ChatActions::UPLOAD_PHOTO)->send();
        $this->chat->photo(base_path('public/images/2.jpg'))->send();
        Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
        $this->chat->message(__('telegram_bot.please_enter_your_account_id'))
            ->send();
    }

    public function start()
    {
        $this->chat->message(__('telegram_bot.welcome'))
            ->send();
        $this->chat->state = ChatStateEnum::Start;
        $this->chat->save();
        Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
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
        Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
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
        Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
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
        Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
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
        if (!((bool)config('telegraph.xui.connection_generator_is_custom'))) {
            if (is_null(config('telegraph.xui.inbound_excludes'))) {
                return Inbound::all();
            } else {
                return Inbound::whereNotIn('id', explode(',', config('telegraph.xui.inbounds')))->get();
            }
        } else {
            return Inbound::whereIn('id', config('telegraph.xui.inbounds'))->get();
        }
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
        Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
        $this->chat->markdownV2('```' . $url . '```')
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('telegram_bot.get_link'))->action('getConfigLink')->param('url', $url),
            ])->chunk(2))->send();
    }

    public function getConfigLink()
    {
        $url = $this->data->get('url');

        if (empty($this->chat->client_uuid)) {
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
            $this->chat->message('آیدی شما ثبت نشده. برای ثبت آیدی دکمه حساب کاربری زیر را بزنید.')->send();
        } else {
            $connection = (new GenerateConnection($this->chat->client_uuid, $url))->execute();
            Telegraph::chat($this->chat)->chatAction(ChatActions::TYPING)->send();
            $this->chat->markdownV2(
                '```' .
                $connection
                .
                '```'
            )->send();
        }
    }

    public function subscriptionTutorial()
    {
        $this->sendVideo(
            config('telegraph.xui.v2rayNG_subscription_tutorial_video_path'),
            __('telegram_bot.v2rayNG_android_subscription_tutorial')
        );
        $this->sendVideo(
            config('telegraph.xui.v2rayNG_subscription_delay_test_tutorial_video_path'),
            __('telegram_bot.v2rayNG_android_subscription_delay_test_tutorial')
        );
        $this->sendVideo(
            config('telegraph.xui.napsternetv_subscription_tutorial_video_path'),
            'آموزش نرم افزار napsternetv برای ios:'
        );
    }

    private function sendVideo(string $configName, string $message)
    {
        if (!empty($configName)) {
            $name = cache()->get($configName);
            if (is_null($name)) {
                $name = $configName;
            }

            Telegraph::chat($this->chat)->chatAction(ChatActions::UPLOAD_VIDEO)->send();

            $response = $this->chat
                ->video($name)
                ->message($message)
                ->send();

            if (!cache()->has($configName)) {
                cache()->put($configName, $response->json('result.video.file_id'), 48 * 60 * 60);
            }
        }
    }

    public function sendSubscriptionLink()
    {
        $this->chat->message('برای اندروید:')->send();

        $this->chat->message(
            config('telegraph.xui.subscription_link_domain') . '/generate/subs/' . $this->chat->client_uuid
        )->send();

        $this->chat->message('برای ios:')->send();

        $this->chat->message(
            config('telegraph.xui.subscription_link_domain') . '/generate/subs/' . $this->chat->client_uuid . '/base64'
        )->send();
    }
}
