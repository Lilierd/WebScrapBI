<?php

namespace App\Console\Commands;

use App\Contracts\BoursoramaScraper;
use App\Models\MarketShare;
use Illuminate\Console\Command;

class GetFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-file';

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
        $boursoramaScraper = new BoursoramaScraper();
        // * Logging in
        $this->info("Logging in user");
        $username = $boursoramaScraper->login();
        $this->comment("Username is : {$username}");

        $boursoramaScraper->extractMarketShareFileFromPage(MarketShare::find(1));

    }
}
