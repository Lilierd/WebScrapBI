<?php

namespace App\Console\Commands\Boursorama;

use App\Contracts\BoursoramaScraper;
use Exception;
use Illuminate\Console\Command;

class LoadMarketShare extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ms-url {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Scrape a market share on Boursorama's website, designated by a valid slug (1rPAB, 1rPABCA...)";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scraper = new BoursoramaScraper();

        $code = $this->argument("code");

        $url = "https://www.boursorama.com/cours/{$code}";
        $this->info("Launching scraping for {$code}");
        $this->line("URL: {$url}");
        $data = [];
        try {
            $data = $scraper->extractMarketShareDataFromUrl($url);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $this->line("Found current value for {$code}:");
            $this->info(
                json_encode(
                    value: $data,
                    flags: JSON_PRETTY_PRINT,
                )
            );
        }
    }
}
