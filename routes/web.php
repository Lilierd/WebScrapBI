<?php

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Dusk\Browser;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

Route::get('/', function (RemoteWebDriver $driver) {

    // $driver = RemoteWebDriver::create('http://selenium:4444/wd/hub', DesiredCapabilities::chrome());
    try {
        $driver->get('https://boursorama.com/bourse/');
        $driver->findElement(WebDriverBy::cssSelector('div[title="CAC 40"]'));
    }
    catch (Exception $exception) {
        throw $exception;
    }
    finally {
        $driver->quit();
    }

    return "Hey";
});
