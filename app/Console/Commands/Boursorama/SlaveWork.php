<?php

namespace App\Console\Commands\Boursorama;

use App\Contracts\BoursoramaScraper;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;

class SlaveWork extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boursorama:slave-work
                            {sid : selenium session id}
                            {ms : market share id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =    'Fait le travail';

    public function handle()
    {
        $this->info("Logging in from slave thread");
        $scraper = BoursoramaScraper::resumeSession($this->argument("sid"));
        $scraper->extractMarketShareData($this->argument("ms"));

        sleep(5);


    }
}
