<?php

namespace App;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

class MarketShareRepository
{

    // public function __construct()

    public static function loadMarketShare(RemoteWebDriver $driver = null, string $url = null)
    {
        $buffer = [];
        try {
            $driver->get($url);
            // sleep(1);

            $dataLastValue = WebDriverBy::cssSelector('span[data-ist-last]');
            $dataHighValue = WebDriverBy::cssSelector('span[data-ist-last]');
            $dataLowValue = WebDriverBy::cssSelector('span[data-ist-last]');
            $dataCloseValue = WebDriverBy::cssSelector('span[data-ist-last]');
            $dataVolume = WebDriverBy::cssSelector('span[data-ist-last]');

            $driver->wait(5, 1)->until(WebDriverExpectedCondition::presenceOfElementLocated($dataVolume));
            // $driver->takeScreenshot(storage_path("{$dateStr}_{$code}.png"));
            $element = $driver->findElement($dataLastValue);

            // $element = $driver->findElement(WebDriverBy::cssSelector('body'));
            $tagName = $element->getTagName();
            $value = $element->getDomProperty("innerText");

            $buffer['ist'] = $value;
            dump($value, $tagName);
        } catch (Exception $exception) {
            throw $exception;
        }
        // finally {
        // $driver->quit();
        // }

        return $buffer;
    }

    public static function downloadExcel(RemoteWebDriver $driver = null, string $url = null)
    {

    }
}
