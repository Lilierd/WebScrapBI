<?php

use App\MarketShareRepository;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/{code?}/', function (RemoteWebDriver $driver, string|null $code = "1rPADOC") {
    $codes = [
        '1rPADOC',
        '1rPAB',
        '1rPABCA',
        '1rPAC'
    ];
    $data = [];
    foreach($codes as $code) {
        $url = "https://boursorama.com/cours/{$code}";
        try {
            $data[$code] = MarketShareRepository::loadMarketShare($driver, $url);
        } catch(Exception $e){
            throw new Exception(message: "Error on {$code}", previous: $e);
        }
    }
    return $data;
});
