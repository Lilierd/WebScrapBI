<?php

namespace App\Console\Commands;

use App\Contracts\BoursoramaScraper;
use App\Models\MarketShare;
use Illuminate\Console\Command;

class GetMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boursorama:get-messages';

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

        $marketShare = MarketShare::find(1);

        $data = $boursoramaScraper->extractForumMessagesFromPage($marketShare);

        dump($data);
    }


}
