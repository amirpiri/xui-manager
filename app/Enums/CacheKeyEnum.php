<?php declare(strict_types=1);

namespace App\Enums;

use function PHPUnit\Framework\matches;

enum CacheKeyEnum: string
{

    case ADMIN_SESSION = 'admin_session';

    case CLIENT_TRAFFIC_PAGE_ = 'client_traffic_page_';

    public function duration(): string
    {
        return match ($this) {
            CacheKeyEnum::ADMIN_SESSION => '86400',
            CacheKeyEnum::CLIENT_TRAFFIC_PAGE_ => '3600',
        };
    }
}
