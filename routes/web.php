<?php

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/{code?}', function (RemoteWebDriver $driver, string|null $code = "1rPADOC") {

    // $driver = RemoteWebDriver::create('http://selenium:4444/wd/hub', DesiredCapabilities::chrome());
    // $code = "1rPADOC";
    $response = "NO DATA FOR {$code}";
    $dateStr = date('Y-m-d_h-i');
    $value = "undefined";
    try {
        $driver->get("https://www.boursorama.com/cours/{$code}/");

        // $driver->takeScreenshot(storage_path("{$dateStr}_{$code}.png"));
        $element = $driver->findElement(WebDriverBy::cssSelector('[data-ist-last]'));

        // $element = $driver->findElement(WebDriverBy::cssSelector('body'));
        $tagName = $element->getTagName();
        $value;
        parse_str($element->isDisplayed(), $value);

        dump($value, $tagName);
    }
    catch (Exception $exception) {
        throw $exception;
    }
    finally {
        $driver->quit();
    }

    return $value;
});
