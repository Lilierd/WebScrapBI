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
            $selectorTable = WebDriverBy::cssSelector("table.c-table.c-table--generic.c-table--generic.c-shadow-overflow__table-fixed-column.c-table-top-flop");
            $selectorLinks = WebDriverBy::cssSelector("tr td a");
            $selectorPagination = WebDriverBy::cssSelector('div[role=navigation]');

            $selectorPages = WebDriverBy::cssSelector('a');

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

                // dump($bufferPageLinks);
                // break;
                // exit;

                $table = $driver->findElement($selectorTable);
                $links = $table->findElements($selectorLinks);

                // dd($links);

                $buffer = [];
                foreach ($links as $link) {
                    $this->info("Scraping links found on: {$URL}");
                    $dataHref = $link->getDomProperty("href");
                    $this->info("Found {$dataHref}...");
                    // $dataIsin = $newDriver;

                    // $regex_isin = '/\/([0-9a-zA-Z]+)\/$/';
                    // $matches = [];
                    // preg_match($regex_isin, $dataHref, $matches, PREG_OFFSET_CAPTURE, 0);
                    // $dataIsin = $matches[1][0];

                    $dataName = $link->getDomProperty("innerText");


                    // $driver->get($dataHref);
                    $selectorIsin = WebDriverBy::cssSelector('h2.c-faceplate__isin');
                    // $dataIsin = $driver->findElement($selectorIsin)->getDomProperty("innerText");



                    $buffer[] = new MarketShare([
                        'name'  => $dataName,
                        // 'isin'  => $dataIsin,
                        'url'   => $dataHref
                    ]);
                }

                foreach ($buffer as $marketShare) {
                    $driver->get($marketShare->url);
                    $selectorIsin = WebDriverBy::cssSelector('h2.c-faceplate__isin');
                    $dataIsin = $driver->findElement($selectorIsin)->getDomProperty("innerText");

                    $marketShare->isin = $dataIsin;
                    $marketShare->save();
                }
            } while ($nextURL !== null);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $driver->quit();
        }
    }
}
