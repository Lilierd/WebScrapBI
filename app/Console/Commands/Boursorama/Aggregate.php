<?php

namespace App\Console\Commands\Boursorama;

use App\Contracts\BoursoramaScraper;
use App\Models\MarketShare;
use App\Models\MarketShareSnapshot;
use App\Models\SnapshotIndex;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Cache\Console\ClearCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class Aggregate extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boursorama:aggregate {--fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Register to database all data needed";

    /**
     * Execute the console command.
     */
    public function handle(BoursoramaScraper $boursoramaScraper)
    {
        $this->info("Creating Snapshot Index...");
        $snapshotIndex = SnapshotIndex::create([
            'snapshot_time' => now()
        ]);
        $this->info("Snapshot Index [{$snapshotIndex->snapshot_time}] created. {$snapshotIndex->snapshot_time}");

        if ($this->option("fresh")) {
            $this->useNavigation(boursoramaScraper: $boursoramaScraper, snapshotIndex: $snapshotIndex);
        } else {
            $marketShares = MarketShare::all();
            // dd(count($marketShares));
            $this->withProgressBar($marketShares, function (MarketShare $marketShare) use ($snapshotIndex, $boursoramaScraper) {
                $this->output->write(" Processing {$marketShare->name}");
                $data = $boursoramaScraper->extractMarketShareData($marketShare);

                MarketShareSnapshot::create([
                    ...$data,
                    'snapshot_index_id' => $snapshotIndex->getKey(),
                    'market_share_id'   => $marketShare->getKey()
                ]);
            });
        }

        $snapshotData = MarketShareSnapshot::whereSnapshotIndexId($snapshotIndex->id)->get()->toArray();

        $this->newLine();
        if (!empty($snapshotData)) {
            $this->table(array_keys($snapshotData[0]), [...$snapshotData]);
        }
    }


    /**
     * Aggrège tous les modèles :
     * - MarketShare
     * - MarketShareSnapshot
     */
    protected function useNavigation(BoursoramaScraper $boursoramaScraper, SnapshotIndex $snapshotIndex)
    {
        // * Aggrégation des différentes pages de la navigation exposant les URLs des actions
        $this->info("Discovering Boursorama's navigation...");
        $URL = "https://www.boursorama.com/bourse/actions/cotations/";
        $pages = [$URL];

        $this->withProgressBar($pages, function ($URL) use (&$pages, &$boursoramaScraper) {
            $this->output->write(" Checking on : {$URL}");
            $pages = array_merge($boursoramaScraper->extractNavigationPages($URL));
        });
        $totalNavigation = count($pages);

        $this->newLine();
        $this->info("Discovered {$totalNavigation} pages of market shares on Boursorama's website.");
        if (!empty($pages)) {
            $_pages = array_map(function ($value) {
                return [
                    "URL PAGE" => $value,
                ];
            }, $pages);
            $this->table(array_keys($_pages[0]), [...$_pages]);
        }

        // * Aggrégation de chacune des URLs de chaque actions sur chacune des pages
        if (!$this->confirm("Would you like to continue ?", true)) {
            $this->info("Exiting");
            return;
        }

        $marketSharesUrls = [];
        $this->info("Discovering all Market Shares URLs on each page...");
        $this->withProgressBar($pages, function ($page) use ($boursoramaScraper, &$marketSharesUrls) {
            $this->output->write(" Parsing : {$page}");
            $_marketSharesUrls = $boursoramaScraper->extractMarketSharesUrlsFromPage($page);
            $marketSharesUrls = array_merge($marketSharesUrls, $_marketSharesUrls);
        });
        $totalMarketShares = count($marketSharesUrls);
        if (!empty($marketSharesUrls)) {
            $_marketSharesUrls = array_map(function ($value) {
                return [
                    "URL PAGE" => $value,
                ];
            }, $marketSharesUrls);
            $this->table(array_keys($_marketSharesUrls[0]), [...$_marketSharesUrls]);
        }
        $this->newLine();
        $this->info("Discovering finished successfully. Discovered a total of : {$totalMarketShares}");

        // * Aggrégation des données de chaque actions
        if (!$this->confirm("Would you like to continue ?", true)) {
            $this->info("Exiting");
            return;
        }
        $this->info("Visiting all market shares URLs...");
        // $marketSharesData = [];
        // $_temp = array_slice($marketSharesUrls, 0, 2);
        $this->withProgressBar($marketSharesUrls, function ($marketShareURL) use ($boursoramaScraper, $snapshotIndex) {
            $this->output->write(" Parsing : {$marketShareURL}");
            $marketShareData = $boursoramaScraper->extractMarketShareDataFromUrl($marketShareURL);
            // $marketSharesData[] = $marketShareData;


            $marketSharesFields = [
                'name',
                'isin',
                'url',
            ];
            $marketSharesSnapshotFields = [
                'volume',
                'last_value',
                'open_value',
                'close_value',
                'high_value',
                'low_value',
            ];

            $filteredMs = array_filter($marketShareData, function ($index) use ($marketSharesFields,) {
                return in_array($index, $marketSharesFields);
            }, ARRAY_FILTER_USE_KEY);
            $filteredMsp = array_filter($marketShareData, function ($index) use ($marketSharesSnapshotFields,) {
                return in_array($index, $marketSharesSnapshotFields);
            }, ARRAY_FILTER_USE_KEY);

            $marketShare = MarketShare::updateOrCreate($filteredMs);
            MarketShareSnapshot::create([
                ...$filteredMsp,
                'snapshot_index_id' => $snapshotIndex->id,
                'market_share_id'   => $marketShare->id
            ]);
        });
        $this->newLine();
        $this->comment("While traversing, saved found Market Shares data under SnapshotIndex: {$snapshotIndex->id}.");
    }
}
