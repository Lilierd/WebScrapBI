<?php

namespace App\Jobs;

use App\Contracts\BoursoramaScraper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SnapshotMarket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(array $ids): void
    {
        $scraper = new BoursoramaScraper();
        $scraper->login();
        // foreach($ids as $id) {
            // $scraper->extractMarketShareData($id);
        // }
        $scraper->driver->quit();

    }
}
