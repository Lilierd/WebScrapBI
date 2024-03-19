<?php

namespace App;

use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PhpParser\Node\Expr\Array_;

class MarketShareRepository {

    // public function __construct()

    public static function loadMarketShare(RemoteWebDriver $driver = null, string $url = null) {
        $buffer = [];
        try {
            $driver->get($url);
            // sleep(1);

            $dataName = WebDriverBy::cssSelector('a.c-faceplate__company-link');
            $dataISIN = WebDriverBy::cssSelector('h2.c-faceplate__isin');
            $dataLastValue = WebDriverBy::cssSelector('span[data-ist-last]');
            $dataHighValue = WebDriverBy::cssSelector('span[data-ist-high]');
            $dataLowValue = WebDriverBy::cssSelector('span[data-ist-low]');
            $dataOpenValue = WebDriverBy::cssSelector('span[data-ist-open]');
            $dataVolume = WebDriverBy::cssSelector('span[data-ist-totalvolume]');
            $dataCloseValue = WebDriverBy::cssSelector('span[data-ist-previousclose]');
            $dataTime = null; //TODO: Mettre dans date de maintenace

            $driver->wait(5, 1)->until(WebDriverExpectedCondition::presenceOfElementLocated($dataVolume));
            // $driver->takeScreenshot(storage_path("{$dateStr}_{$code}.png"));
            $values = array(
                'dataName'=> $driver->findElement($dataName)->getDomProperty("innerText"),
                'dataISIN'=> $driver->findElement($dataISIN)->getDomProperty("innerText"),
                'dataLastValue'=> $driver->findElement($dataLastValue)->getDomProperty("innerText"),
                'dataHighValue'=> $driver->findElement($dataHighValue)->getDomProperty("innerText"),
                'dataLowValue'=> $driver->findElement($dataLowValue)->getDomProperty("innerText"),
                'dataOpenValue'=> $driver->findElement($dataOpenValue)->getDomProperty("innerText"),
                'dataVolume'=> $driver->findElement($dataVolume)->getDomProperty("innerText"),
                'dataCloseValue'=> $driver->findElement($dataCloseValue)->getDomProperty("innerText")
                //'dataTime'=> $driver->findElement($dataTime)->getDomProperty("innerText")
            );

            /* $tagName = $element->getTagName();
            $value = $element->getDomProperty("innerText"); */

            /* $buffer['ist'] = $value;
            dump($value, $tagName); */
            dump(json_encode($values));
        }
        catch (Exception $exception) {
            throw $exception;
        }
        // finally {
            // $driver->quit();
        // }

        return $buffer;
    }

}
