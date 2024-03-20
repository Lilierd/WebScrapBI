<?php

namespace App\Console\Commands;

use App\MarketShareRepository;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Stringable;
use Illuminate\Support\Str;

class ScrapeMarketShares extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-market-shares';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape all market shares';

    /**
     * Execute the console command.
     */
    public function handle(RemoteWebDriver $driver)
    {
        $configMarketShares = config('boursorama.known', ['1rPAB']); // A terme vers BD

        $marketShares = $this->choice(
            question: "Choose the market shares",
            choices: $configMarketShares,
            multiple: true
        );

        $buffer = [];
        try {
            foreach($marketShares as $marketShareIsinCode) {
                $url = "https://boursorama.com/cours/{$marketShareIsinCode}";
                $buffer[$marketShareIsinCode] = MarketShareRepository::loadMarketShare($driver, $url);

                $this->info("Data for {$marketShareIsinCode} on {$url}");
                foreach($buffer[$marketShareIsinCode] as $dataName => $dataValue) {
                    $string = Str::of($dataName)->padRight(16)->pipe(function(Stringable $str) use ($dataValue) {
                        return "{$str}: {$dataValue}";
                    });

                    $this->line($string);
                }

            };
        } catch (Exception $e) {
            throw $e;
        } finally {
            $driver->quit();
        }
    }
}
