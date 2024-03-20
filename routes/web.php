<?php

use App\Http\Controllers\MarketShareSnapshotController;
use App\Http\Controllers\SnapshotIndexController;
use App\MarketShareRepository;
use App\Models\MarketShareSnapshot;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Route::get('/', function () {
    // $codes = [
    //     '1rPADOC',
    //     '1rPAB',
    //     '1rPABCA',
    //     '1rPAC'
    // ];
    // $data = [];


    // $seleniumServerUrl = config('selenium.server_url');
    // $desiredCapabilities = config('selenium.driver_capabilities', DesiredCapabilities::chrome());
    // $driver = RemoteWebDriver::create($seleniumServerUrl, $desiredCapabilities);

    // //Connexion du driver
    // $driver->get('https://boursorama.com/');
    // // $driver->

    // try {
    //     foreach ($codes as $code) {
    //         $url = "https://boursorama.com/cours/{$code}";
    //         $data[$code] = MarketShareRepository::loadMarketShare($driver, $url);
    //     }
    // } catch (Exception $e) {
    //     throw $e;
    // } finally {
    //     $driver->quit();
    //     dd($data);
    // }
    // return $data;
// });


Route::get('/', [SnapshotIndexController::class, 'index']);
Route::get('snapshot/{snapshotIndex}/share/{marketShare}', [MarketShareSnapshotController::class, 'index']);

Route::get('market-snapshot-index/{snapshotIndex}', [MarketShareSnapshotController::class, 'index'])
->name('browse.market-snapshot.by-snapshot');

Route::get('market-snapshot/{marketShareSnapshot}', [MarketShareSnapshotController::class, 'show'])
 ->name("market-share-snapshot.view");
