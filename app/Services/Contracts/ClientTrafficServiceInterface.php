<?php

namespace App\Services\Contracts;

interface ClientTrafficServiceInterface
{
    public function transferUser(string $uuid, int $inboundId);
}
