<?php

namespace App\Providers;

use App\Services\ClientTrafficService;
use App\Services\Contracts\ClientTrafficServiceInterface;
use App\Services\Contracts\XuiEnglishRequestServiceInterface;
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
        $this->app->bind(
            ClientTrafficServiceInterface::class,
            ClientTrafficService::class
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
