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

    //CommandeAgregerDonnees()
        //> Connecte la session
        //> Recuperer toutes les actions depuis la navigation du site
            //>
        //> Differentiel avec les actions enregistrées en base: enregistrement en base des nouvelles
            //> MarketShareRepository::loadMarketShare()
            //> Verifier (int en int et string en string) données et création d'un MarketShareSnapshot si il existe pas
            //> Création d'un SnapshotIndex
            //> Scrap les données pour les actions en base
            //> A chaque scrap creation d'un MarketShareSnapshot

})->purpose('Display an inspiring quote');


Artisan::command('scrap', function() {

    dump(env('DUSK_DRIVER_URL'));
    // dump($chrome);
    // Browser::create("http://localhost:4444", DesiredCapabilities::chrome());
});
