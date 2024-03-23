<?php

namespace App\Console\Commands;

use App\Contracts\BoursoramaDriver;
use App\Contracts\BoursoramaScraper;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;
use Exception;
use App\Models\MarketShare;

class ScrapeMarketSharesFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-market-shares-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $marketShares = MarketShare::find(1);   //TODO: remettre ::all(); une fois le foreach mis en place

        $scraper = new BoursoramaScraper();
        try {
            $scraper->login();
        } catch(Exception $exception) {
            throw $exception;
        } finally {
            return;
        }
    }
}
