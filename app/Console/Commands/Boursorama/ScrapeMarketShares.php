<?php

namespace App\Console\Commands\Boursorama;

use App\Contracts\BoursoramaScraper;
use App\Models\MarketShare;
use App\Models\MarketShareSnapshot;
use App\Models\SnapshotIndex;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Stringable;
use Illuminate\Support\Str;

class ScrapeMarketShares extends Command
{
    // public function __construct(protected BoursoramaScraper $scraper)
    // {
    //     parent::__construct();
    // }

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
    public function handle()
    {
        // $configMarketShares = config('boursorama.known', ['1rPAB']); // A terme vers BD

        $scraper = new BoursoramaScraper();

        $marketShares = MarketShare::all();
        // $configMarketShares = $m;

        // $marketShares = $this->choice(
        //     question: "Choose the market shares",
        //     choices: $configMarketShares,
        //     multiple: true
        // );

        $snapshotIndex = SnapshotIndex::create([
            'snapshot_time' => now()
        ]);

        // $buffer = [];
        try {
            foreach ($marketShares as $marketShare) {
                // $url = "https://boursorama.com/cours/{$marketShare->url}";
                try {
                    $data = $scraper->extractMarketShareDataFromUrl($marketShare->url);
                    $this->info("Data for {$marketShare->name} on {$marketShare->url}");
                    foreach ($data as $dataName => $dataValue) {
                        $string = Str::of($dataName)->padRight(16)->pipe(function (Stringable $str) use ($dataValue) {
                            return "{$str}: {$dataValue}";
                        });

                        $this->line($string);
                    }
                    MarketShareSnapshot::create([
                        ...$data,
                        'snapshot_index_id' => $snapshotIndex->getKey(),
                        'market_share_id' => $marketShare->getKey()
                    ]);
                } catch (Exception $e) {
                    $this->error("Error on {$marketShare->name}");
                    $this->line($e);
                }
            };
        } catch (Exception $e) {
            throw $e;
        }

        // Populating DB
        // foreach ($buffer as $isin => $data) {
        //     $marketShare = MarketShare::where('isin', $isin)->first();

        // }
    }
}
