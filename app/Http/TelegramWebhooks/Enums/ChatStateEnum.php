<?php

namespace App\Http\TelegramWebhooks\Enums;

enum ChatStateEnum: string
{
    case Start = 'start';
    case Account_UUID = 'account_uuid';
}
