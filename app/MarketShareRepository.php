<?php

namespace App;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PhpParser\Node\Expr\Array_;

class MarketShareRepository
{

    // public function __construct()

    public static function loadMarketShare(RemoteWebDriver $driver = null, string $url = null)
    {
        $buffer = [];
        try {
            $driver->get($url);
            // sleep(1);

            $selectorName = WebDriverBy::cssSelector('a.c-faceplate__company-link');
            $selectorIsin = WebDriverBy::cssSelector('h2.c-faceplate__isin');
            $selectorLastValue = WebDriverBy::cssSelector('span[data-ist-last]');
            $selectorHighValue = WebDriverBy::cssSelector('span[data-ist-high]');
            $selectorLowValue = WebDriverBy::cssSelector('span[data-ist-low]');
            $selectorOpenValue = WebDriverBy::cssSelector('span[data-ist-open]');
            $selectorCloseValue = WebDriverBy::cssSelector('span[data-ist-previousclose]');
            $selectorVolume = WebDriverBy::cssSelector('span[data-ist-totalvolume]');


            $dataName = $driver->findElement($selectorName)->getDomProperty("innerText");
            $dataIsin = $driver->findElement($selectorIsin)->getDomProperty("innerText");
            $dataLastValue = $driver->findElement($selectorLastValue)->getDomProperty("innerText");
            $dataHighValue = $driver->findElement($selectorHighValue)->getDomProperty("innerText");
            $dataLowValue = $driver->findElement($selectorLowValue)->getDomProperty("innerText");
            $dataOpenValue = $driver->findElement($selectorOpenValue)->getDomProperty("innerText");
            $dataCloseValue = $driver->findElement($selectorCloseValue)->getDomProperty("innerText");
            $dataVolume = $driver->findElement($selectorVolume)->getDomProperty("innerText");
            $dataTime = null; //TODO: Mettre dans date de maintenace

            $driver->wait(5, 1)->until(WebDriverExpectedCondition::presenceOfElementLocated($selectorVolume));
            // $driver->takeScreenshot(storage_path("{$dateStr}_{$code}.png"));
            $marketShareData = [ // Nomenclature relative aux tables Models de Eloquent (snake_case)
                'name' => $dataName,
                'isin' => $dataIsin,
                'last_value' => $dataLastValue,
                'high_value' => $dataHighValue,
                'low_value' => $dataLowValue,
                'open_value' => $dataOpenValue,
                'volume' => $dataVolume,
                'close_value' => $dataCloseValue
                //'dataTime'=> $driver->findElement($dataTime)->getDomProperty("innerText")
            ];
        } catch (Exception $exception) {
            throw $exception;
        }
        // finally {
        // $driver->quit();
        // }

        return $marketShareData;
    }

    public static function downloadExcel(RemoteWebDriver $driver = null, string $url = null)
    {
    }
}
