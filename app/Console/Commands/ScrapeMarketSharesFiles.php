<?php

namespace App\Console\Commands;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
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
    public function handle(RemoteWebDriver $driver)
    {
        /* try {
            $driver->get("https://www.boursorama.com/espace-membres/telecharger-cours/paris");

            $selectorInputCode = WebDriverBy::cssSelector('input#quote_search_customIndexesList');
            $driver->findElement($selectorInputCode)->getDomProperty("innerText") = $code;

        } catch (Exception $exception) {
            throw $exception;
        } */

        $marketShares = MarketShare::find(1);   //TODO: remettre ::all(); une fois le foreach mis en place


        try {
            $driver->get("https://www.boursorama.com/espace-membres/telecharger-cours/paris");

            //TODO: Faire la connexion
            config('boursorama.username');
            config('boursorama.password');

            /* foreach ($marketShares as $marketShare) {
                try {

                    $code = $marketShare->isin;

                } catch (Exception $e) {
                    $this->error("Error on {$marketShare->name}");
                    $this->line($e);
                }
            } */

            $code = $marketShares->isin;

            $selectorInputCode = WebDriverBy::cssSelector('input#quote_search_customIndexesList');
            $inputCode = $driver->findElement($selectorInputCode)->click();
            $driver->getKeyboard()->sendKeys($code);



        } catch (Exception $e) {
            throw $e;
        }
    }
}
