<?php

use App\Providers\SeleniumServiceProvider;
use Facebook\WebDriver\Chrome\ChromeDriverService;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\DuskServiceProvider;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('scrap', function() {

    dump(env('DUSK_DRIVER_URL'));
    // dump($chrome);
    // Browser::create("http://localhost:4444", DesiredCapabilities::chrome());
});
