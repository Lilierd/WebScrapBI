<?php

namespace App;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class MarketShareRepository {

    // public function __construct()

    public static function loadMarketShare(RemoteWebDriver $driver, string $url) {
        $buffer = [];
        try {
            $driver->get($url);

            // $driver->takeScreenshot(storage_path("{$dateStr}_{$code}.png"));
            $element = $driver->findElement(WebDriverBy::cssSelector('[data-ist-last]'));

            // $element = $driver->findElement(WebDriverBy::cssSelector('body'));
            $tagName = $element->getTagName();
            $value = $element->getDomProperty("innerText");

            $buffer['ist'] = $value;
            dump($value, $tagName);
        }
        catch (Exception $exception) {
            throw $exception;
        }
        finally {
            $driver->quit();
        }

        return $buffer;
    }

}
