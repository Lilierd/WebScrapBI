<?php

use App\MarketShareRepository;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/{code?}/', function (string|null $code = "1rPADOC") {
    $codes = [
        '1rPADOC',
        '1rPAB',
        '1rPABCA',
        '1rPAC'
    ];
    $data = [];


    $seleniumServerUrl = config('selenium.server_url');
    $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());
    $driver = RemoteWebDriver::create($seleniumServerUrl, $desiredCapabilities);

    //Connexion du driver

    //Ensuite on bz
    try {
        foreach ($codes as $code) {
            $url = "https://boursorama.com/cours/{$code}";
            $data[$code] = MarketShareRepository::loadMarketShare($driver, $url);
        }
    } catch (Exception $e) {
        throw $e;
    } finally {
        $driver->quit();
    }
    return $data;
});
