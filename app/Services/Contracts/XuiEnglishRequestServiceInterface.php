<?php

namespace App\Services\Contracts;

use GuzzleHttp\Exception\GuzzleException;

interface XuiEnglishRequestServiceInterface
{

    /**
     * @param int $time
     * @return string
     */
    public function getSession(int $time = 0): string;

    /**
     * @param int $inboundId
     * @param array $data
     * @return void
     * @throws GuzzleException
     */
    public function updateInbound(int $inboundId, array $data): void;
}
