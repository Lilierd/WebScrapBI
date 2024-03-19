<?php

namespace App\Console\Commands;

use App\MarketShareRepository;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Console\Command;

class LoadMarketShare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-market-share {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(MarketShareRepository $marketShareRepository, string $code)
    {
        $data = $marketShareRepository->loadMarketShare("https://www.boursorama.com/cours/{$code}");

        $this->line("Found current value for {$code}:");
        $this->info($data);

    }
}
