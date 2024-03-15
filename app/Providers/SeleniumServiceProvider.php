<?php

namespace App\Providers;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SeleniumServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RemoteWebDriver::class, function (Application $app) {


            $driverURL = config('rwd.driver_url', 'http://selenium:4444/wd/hub');
            $desiredCapabilities = config('rwd.driver_capabilities', DesiredCapabilities::chrome());

            return RemoteWebDriver::create($driverURL, $desiredCapabilities);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/rwd.php' => config_path('rwd.php'),
        ]);
    }
}
