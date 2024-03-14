<?php

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Dusk\Browser;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

Route::get('/', function (RemoteWebDriver $driver) {

    // $driver = RemoteWebDriver::create('http://selenium:4444/wd/hub', DesiredCapabilities::chrome());



    dd($driver->quit());

});
