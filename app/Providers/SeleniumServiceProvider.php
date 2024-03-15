<?php

namespace App\Providers;

use Facebook\WebDriver\Chrome\ChromeOptions;
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

            $seleniumServerUrl = config('selenium.server_url');
            $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());

            return RemoteWebDriver::create($seleniumServerUrl, $desiredCapabilities);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/selenium.php' => config_path('selenium.php'),
        ]);
    }
}
