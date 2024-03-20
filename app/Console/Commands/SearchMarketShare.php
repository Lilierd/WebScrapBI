<?php

namespace App\Console\Commands;

use App\Models\MarketShare;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Console\Command;

class SearchMarketShare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:search-market-share';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(RemoteWebDriver $driver)
    {
        try {
            $URL = "https://www.boursorama.com/bourse/actions/cotations/";
            $selectorTable = WebDriverBy::cssSelector("table.c-table.c-table--generic.c-table--generic.c-shadow-overflow__table-fixed-column.c-table-top-flop");
            $selectorLinks = WebDriverBy::cssSelector("tr td a");
            $selectorPagination = WebDriverBy::cssSelector('div[role=navigation]');

            $selectorPages = WebDriverBy::cssSelector('a');

            $driver->get($URL);
            $driver->wait(5)->until(WebDriverExpectedCondition::presenceOfElementLocated($selectorPagination));
            $navigation = $driver->findElement($selectorPagination);

            $pages = $navigation->findElements($selectorPages);
            // Gérer changement de pages
            // foreach($pages)

            $table = $driver->findElement($selectorTable);
            $links = $table->findElements($selectorLinks);

            // dd($links);
            foreach($links as $link) {
                dump($link->getDomProperty("href"));


                $dataHref = $link->getDomProperty("href");

                $regex_isin = '/\/([0-9a-zA-Z]+)\/$/';
                $matches = [];
                preg_match($regex_isin, $dataHref, $matches, PREG_OFFSET_CAPTURE, 0);
                $dataIsin = $matches[1][0];

                $dataName = $link->getDomProperty("innerText");


                MarketShare::create([
                    'name'  => $dataName,
                    'isin'  => $dataIsin,
                    'url'   => $dataHref
                ]);
            }
        }
        catch (Exception $e) {
            throw $e;
        }
        finally {
            $driver->quit();
        }
    }
}
