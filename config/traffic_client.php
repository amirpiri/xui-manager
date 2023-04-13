<?php

return [
    'convert_to_gb' => env('CONVERT_TO_GB', (1024 * 1024 * 1024)),
    'cloudflare_inbound_id' => env('CLOUDFLARE_INBOUND_ID', 0),
    'address_url' => env('ADDRESS_URL'),
    'generate_site' => env('GENERATE_SITE',\App\Enums\GenerateSiteEnum::OTHER->value),
];
