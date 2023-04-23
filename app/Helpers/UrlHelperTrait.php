<?php

namespace App\Helpers;

trait UrlHelperTrait
{

    public string $urlSeparator = '/';

    /**
     * @param string $baseUrl
     * @param string $route
     * @return string
     */
    public function generateFullUrl(string $baseUrl, string $route): string
    {
        return trim($baseUrl, $this->urlSeparator) . $this->urlSeparator . trim($route);
    }
}
