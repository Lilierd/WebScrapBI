<?php

namespace App\Console\Commands;

use App\Models\MarketShare;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class SearchMarketShare extends Command implements Isolatable
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
            $selectorTable = WebDriverBy::cssSelector("table.c-table.c-table--generic.c-table--generic.c-shadow-overflow__table-fixed-column.c-table-top-flop");
            $selectorLinks = WebDriverBy::cssSelector("tr td a");
            $selectorPagination = WebDriverBy::cssSelector('div[role=navigation]');

            $selectorPages = WebDriverBy::cssSelector('a');

            $selectorIsin = WebDriverBy::cssSelector('h2.c-faceplate__isin');

            $URL = "";
            $nextURL = "https://www.boursorama.com/bourse/actions/cotations/";
            $visitedPageLinks = [];

            do {
                $URL = $nextURL;
                $this->info("Scraping market share urls on : {$URL}");

                array_push($visitedPageLinks, $URL);
                $driver->get($URL);

                $driver->wait(5)->until(WebDriverExpectedCondition::presenceOfElementLocated($selectorPagination));
                $navigation = $driver->findElement($selectorPagination);

                $pages = $navigation->findElements($selectorPages);
                // Gérer changement de pages
                // foreach($pages)
                $bufferPageLinks = [];
                $lastURL = $pages[array_key_last($pages)];

                foreach ($pages as $page) {
                    // dd($page->getTagName());
                    $pageUrl = $page->getDomProperty('href');
                    $this->info("Found new possible result result page for market share urls: {$pageUrl}");
                    if (
                        !in_array($pageUrl, $visitedPageLinks)
                    ) {
                        $this->info("Adding this to future pages to scrap: {$pageUrl}");
                        array_push($bufferPageLinks, $pageUrl);
                    } else {
                        $this->warn("Not adding this to future pages to scrap: {$pageUrl}");
                    }
                }
                $nextURL = $bufferPageLinks[0] ?? null;
                $this->info("Next page would be: {$nextURL}");

                $table = $driver->findElement($selectorTable);
                $linksElements = $table->findElements($selectorLinks);

                $sharesData = array_map(function (RemoteWebElement $element) {
                    $dataName = $element->getDomProperty("innerText");
                    return [
                        'name' => $dataName,
                        'url' => $element->getDomProperty("href")
                    ];
                }, $linksElements);


                foreach ($sharesData as $data) {
                    $driver->navigate()->to($data['url']);
                    $dataIsin = $driver->findElement($selectorIsin)->getDomProperty("innerText");

                    MarketShare::create([
                        'name'  => $data['name'],
                        'isin'  => $dataIsin,
                        'url'   => $data['url']
                    ]);
                }
            } while ($nextURL !== null);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $driver->quit();
        }
    }
}
