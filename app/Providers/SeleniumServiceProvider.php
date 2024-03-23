<?php

namespace App\Providers;

use App\Contracts\BoursoramaScraper;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SeleniumServiceProvider extends ServiceProvider
{

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        // RemoteWebDriver::class => \Facebook\WebDriver\Remote\RemoteWebDriver::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->app->bind(RemoteWebDriver::class, function(Application $app) {
        //     $seleniumServerUrl = config('selenium.server_url');
        //     $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());

        //     $chromeOptions = new ChromeOptions();
        //     $chromeOptions->addArguments(['--start-fullscreen']);
        //     $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        //     // $chromeOptions->setExperimentalOption('excludeSwitches', ['disable-popup-blocking']);
        //     return RemoteWebDriver::create(
        //         selenium_server_url: $seleniumServerUrl,
        //         desired_capabilities: $desiredCapabilities,
        //     );
        // });
        // $this->app->
        // $this->app->singleton(BoursoramaScraper::class, function (Application $app) {
            // $seleniumServerUrl = config('selenium.server_url');
            // $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());

            // $chromeOptions = new ChromeOptions();
            // $chromeOptions->addArguments(['--start-fullscreen']);
            // $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
            // // $chromeOptions->setExperimentalOption('excludeSwitches', ['disable-popup-blocking']);
            // return new BoursoramaScraper();
        // });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/selenium.php' => config_path('selenium.php'),
        ]);
    }
}
