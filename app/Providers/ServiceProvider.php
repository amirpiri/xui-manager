<?php

namespace App\Providers;

use App\Services\Contracts\InboundServiceInterface;
use App\Services\Contracts\XuiEnglishRequestServiceInterface;
use App\Services\InboundService;
use App\Services\XuiEnglishEnglishRequestService;
use Illuminate\Support\ServiceProvider as MainServiceProvider;

class ServiceProvider extends MainServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            XuiEnglishRequestServiceInterface::class,
            XuiEnglishEnglishRequestService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
