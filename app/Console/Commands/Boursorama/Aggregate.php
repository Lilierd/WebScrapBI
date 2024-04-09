<?php

namespace App\Console\Commands\Boursorama;

use App\Contracts\BoursoramaScraper;
use App\Models\MarketShare;
use App\Models\MarketShareSnapshot;
use App\Models\SnapshotIndex;
use DateInterval;
use DateTimeInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use SebastianBergmann\Timer\Timer;
use Illuminate\Support\Facades\File;

class Aggregate extends Command
{

    /**
     * Determine when an isolation lock expires for the command.
     */
    public function isolationLockExpiresAt(): DateTimeInterface|DateInterval
    {
        return now()->addMinutes(120);
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boursorama:aggregate {--fresh} {--download} {--messages} {--ms=*} {--url=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =    "Register to database all data needed
                                {--fresh: tells if navigation should be used rather than database, if no primous scraping was done}
                                {--download: Download file from previous day. (+10s long) }
                                {--messages: Scrap actions with their forum messages.}
                                {--ms: When using in pair with `--no-interaction`, override choices by giving MarketShares name. (Only on snapshoting state as we couldn't prédire le market share de l'url de navigation sans le traverser)}
                                {--url: Aggregate from one or multiple specified URLs.}";


    protected Timer $timer;

    // protected BoursoramaScraper $scraper;

    public function __construct(
        public $isolated = false
    ) {
        parent::__construct();
        $this->timer = new Timer();
    }

    /**
     * Execute the console command.
     */
    public function handle(BoursoramaScraper $boursoramaScraper)
    {
        if ($this->output->isVeryVerbose()) {
            $this->timer->start();
        }

        // * CHECKING COMMAND ARGUMENTS AND OPTIONS
        //! Si on a pas les bons paramètres on throw
        $this->checkParametersAndOptions();

        // * CHECKING COMMAND ARGUMENTS AND OPTIONS
        $this->checkParametersAndOptions();

        // * Creating snapshot index
        $this->info("Creating Snapshot Index...");
        $snapshotIndex = SnapshotIndex::create([
            'snapshot_time' => now()
        ]);
        $this->info("Snapshot Index [{$snapshotIndex->snapshot_time}] created. {$snapshotIndex->snapshot_time}");



        $arbitraryCount =  MarketShare::count();

        /** @var bool */
        $useUrlOption = !empty($this->option('url'));
        /** @var bool */
        $useNamedMarketShareOption = !$this->option('fresh') || !empty($this->option('ms'));
        /** @var bool */
        $useNavigationOption = (!$useUrlOption && !$useNamedMarketShareOption && $this->option('fresh'));


        $mode = $useUrlOption
            ? 'URL'
            : ($useNamedMarketShareOption
                ? 'DATABASE' : ($useNavigationOption ? 'NAVIGATION' : null));

        $this->info("Using {$mode} mode");

        if (!$mode) {
            throw new Exception('Unrecognized parsing mode');
        }
        // * Logging in
        $this->info("Logging in user");
        $username = $boursoramaScraper->login();
        $this->comment("Username is : {$username}");

        switch ($mode) {
            case 'URL':
                $this->useUrlsOption($boursoramaScraper, $snapshotIndex);
                break;
            case 'DATABASE':
                if (!$arbitraryCount) {
                    $this->warn("No market shares found on Database while not using '--fresh' option.");
                    if (!$this->confirm("Do you want to use Boursorama's navigation's strategy instead ?", true)) {
                        $this->warn("Exited without any operations. Database doesn't contain any Market Shares.");
                        $snapshotIndex->delete();
                        return;
                    }
                    // * Fallback vers navigation si on ne peut pas associer le nom à nos données en BD, puisqu'on a pas de BD.
                    $this->useNavigation(boursoramaScraper: $boursoramaScraper, snapshotIndex: $snapshotIndex);
                } else {
                    $this->useDataBase($boursoramaScraper, $snapshotIndex);
                }
                break;
            case 'NAVIGATION':
                if (!$arbitraryCount) {
                    $this->warn("No market shares found on Database while not using '--fresh' option.");
                    if (!$this->confirm("Do you want to use Boursorama's navigation's strategy instead ?", true)) {
                        $this->warn("Exited without any operations. Database doesn't contain any Market Shares.");
                        $snapshotIndex->delete();
                        return;
                    }
                }
                // * Si option fresh : on aggrège en parcourant la navigation
                $this->useNavigation(boursoramaScraper: $boursoramaScraper, snapshotIndex: $snapshotIndex);
                break;
            default:
                break;
        }

        if ($this->output->isVeryVerbose()) {
            $this->showStats($snapshotIndex);
        }
    }

    /**
     * Affiche les statistiques à la fin de la commande
     */
    private function showStats(SnapshotIndex $snapshotIndex)
    {
        // * Si on est très verbeux on affiche un résultat, sinon on s'en fout.
        $snapshotData = MarketShareSnapshot::whereSnapshotIndexId($snapshotIndex->id)
            ->with('marketShare', 'snapshotIndex')
            ->select(
                'last_value',
                'low_value',
                'high_value',
                'open_value',
                'close_value',
                'volume',
                'snapshot_index_id',
                'market_share_id',
                'created_at',
            )
            ->get()
            ->map(function (MarketShareSnapshot $marketShareSnapshot) {
                return [
                    'market_share_isin'     => $marketShareSnapshot->marketShare->isin,
                    'market_share_name'     => $marketShareSnapshot->marketShare->name,
                    'snapshot_index_time'   => $marketShareSnapshot->snapshotIndex->snapshot_time,
                    ...$marketShareSnapshot->only([
                        'last_value',
                        'low_value',
                        'high_value',
                        'open_value',
                        'close_value',
                        'volume',
                        'snapshot_index_id',
                        'market_share_id',
                        'created_at'
                    ]),
                ];
            });
        $this->newLine();
        if (!empty($snapshotData)) {
            $this->info("Database has been populated with:");
            $this->table(array_keys($snapshotData[0]), $snapshotData);
        }

        $stats = [[
            // "PHP_GET_MEMORY_PEAK"   => memory_get_peak_usage(true),
            "PHP_DURATION_TIME"     => $this->timer->stop()->asString(),
            "NEW_DATABASE_MODELS"   => MarketShareSnapshot::whereSnapshotIndexId($snapshotIndex->getKey())->count(),
        ]];

        $this->newLine();
        $this->info("Stats :");
        $this->table(array_keys($stats[0]), $stats);
    }

    /**
     * Aggrège tous les modèles :
     * - MarketShareSnapshot
     * en utilisant les données précedemment récoltées en BD
     */
    private function useDataBase(BoursoramaScraper $boursoramaScraper, SnapshotIndex $snapshotIndex)
    {
        $choices = with(MarketShare::select('id', 'name'), function (EloquentBuilder $builder) {
            if ($this->option("ms")) {
                $builder->whereIn('name', $this->option("ms"));
            }
            return $builder->get()->pluck("name", "id")->toArray();
        });

        $chosen = $this->choice(
            question: "Which market share(s) would you like to snapshot ?",
            choices: $choices,
            default: join(",", array_keys($choices)),
            multiple: true,
        );
        $resolved = MarketShare::whereIn('name', $chosen)->get();

        if ($this->output->isVeryVerbose()) {
            $this->withProgressBar($resolved, function (MarketShare $marketShare) use ($snapshotIndex, $boursoramaScraper) {
                $this->output->write(" Processing {$marketShare->getKey()}");

                $data = $boursoramaScraper->extractMarketShareDataFromModel($marketShare);

                MarketShareSnapshot::create([
                    ...$data,
                    'snapshot_index_id' => $snapshotIndex->getKey(),
                    'market_share_id'   => $marketShare->getKey()
                ]);
                // *
                if ($this->option('download')) {
                    $this->getCsv($boursoramaScraper, $marketShare);
                }
                if ($this->option('messages')) {
                    $boursoramaScraper->extractForumMessagesFromPage($marketShare);
                }
            });
        } else {
            foreach ($resolved as $marketShare) {
                $data = $boursoramaScraper->extractMarketShareDataFromModel($marketShare);
                MarketShareSnapshot::create([
                    ...$data,
                    'snapshot_index_id' => $snapshotIndex->getKey(),
                    'market_share_id'   => $marketShare->getKey()
                ]);
                $boursoramaScraper->extractForumMessagesFromPage($marketShare);
                if ($this->option('messages')) {
                    $boursoramaScraper->extractForumMessagesFromPage($marketShare);
                }
                if ($this->option('download')) {
                    $this->getCsv($boursoramaScraper, $marketShare);
                }
            }
        }
    }

    /**
     * Aggrège tous les modèles :
     * - MarketShare
     * - MarketShareSnapshot
     * en utilisant la navigation de Boursorama
     */
    private function useNavigation(BoursoramaScraper $boursoramaScraper, SnapshotIndex $snapshotIndex)
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
            if ($this->output->isVeryVerbose()) {
                $this->table(array_keys($_pages[0]), [...$_pages]);
            }
        }

        // * Aggrégation de chacune des URLs de chaque actions sur chacune des pages
        if (!$this->confirm("Would you like to continue ?", true)) {
            $this->info("Exiting");
            return;
        }

        $marketSharesUrls = [];
        $this->info("Discovering all Market Shares URLs on each page...");
        if ($this->output->isVeryVerbose()) { // * Version avec progressbar
            $this->withProgressBar($pages, function ($page) use ($boursoramaScraper, &$marketSharesUrls) {
                $this->output->write(" Parsing : {$page}");
                $_marketSharesUrls = $boursoramaScraper->extractMarketSharesUrlsFromPage($page);
                $marketSharesUrls = array_merge($marketSharesUrls, $_marketSharesUrls);
            });
        } else { // * Version sans progressbar
            foreach ($pages as $page) {
                $_marketSharesUrls = $boursoramaScraper->extractMarketSharesUrlsFromPage($page);
                $marketSharesUrls = array_merge($marketSharesUrls, $_marketSharesUrls);
            }
        }

        $totalMarketShares = count($marketSharesUrls);
        if (!empty($marketSharesUrls)) {
            $_marketSharesUrls = array_map(function ($value) {
                return [
                    "URL PAGE" => $value,
                ];
            }, $marketSharesUrls);
            if ($this->output->isVeryVerbose()) {
                $this->table(array_keys($_marketSharesUrls[0]), [...$_marketSharesUrls]);
            }
        }
        $this->newLine();
        $this->info("Discovering finished successfully. Discovered a total of : {$totalMarketShares}");

        // * Aggrégation des données de chaque actions
        if (!$this->confirm("Would you like to continue ?", true)) {
            $this->info("Exiting");
            return;
        }
        $this->info("Visiting all market shares URLs...");
        // * Pour chaque URL on créer une nouvelle snapshot de l'action associée
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
            // * Aggrégation des messages
            if ($this->option('messages')) {
                $boursoramaScraper->extractForumMessagesFromPage($marketShare);
            }
            // *
            if ($this->option('download')) {
                $this->getCsv($boursoramaScraper, $marketShare);
            }
        });
        $this->newLine();
        $this->comment("While traversing, saved found Market Shares data under SnapshotIndex: {$snapshotIndex->id}.");
    }

    /**
     * Cherche à créer des snapshots à partir d'URLs d'actions passées en option
     * de la commande
     */
    private function useUrlsOption(
        BoursoramaScraper $boursoramaScraper,
        SnapshotIndex $snapshotIndex
    ) {
        $urls = $this->option('url');
        // dd($urls);
        foreach ($urls as $url) {
            try {
                $dataFromUrl = $this->parseFromMarketShareUrl($boursoramaScraper, $url);
                $msID = MarketShare::updateOrCreate(
                    [
                        ...Arr::only($dataFromUrl, [
                            'name',
                            'isin',
                            'url'
                        ])
                    ]
                );
                $mssID = MarketShareSnapshot::updateOrCreate(
                    [
                        ...Arr::only($dataFromUrl, [
                            'volume',
                            'last_value',
                            'open_value',
                            'close_value',
                            'high_value',
                            'low_value',
                            'snapshot_index_id',
                        ]),
                        'market_share_id'   => $msID->getKey(),
                        'snapshot_index_id' => $snapshotIndex->getKey()
                    ]
                );

                if ($this->option('messages')) {
                    $boursoramaScraper->extractForumMessagesFromPage($msID);
                }
                if ($this->option('download')) {
                    $this->getCsv($boursoramaScraper, $msID);
                }
            } catch (Exception $e) {
                $this->error($e);
            }
        }
    }

    /**
     *
     */
    private function parseFromMarketShareUrl(BoursoramaScraper $boursoramaScraper, string $url): array|null
    {
        $dataFromUrl = $boursoramaScraper->extractMarketShareDataFromUrl($url);

        return $dataFromUrl;
    }

    /**
     *
     */
    public function checkParametersAndOptions(): void
    {
        try {
            if ($this->output->isVeryVerbose()) {
                $this->line("Options are :");
                dump($this->options());
            }
            $this->validateParameters();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            die;
        }
    }

    /**
     *
     */
    public function validateParameters(): void
    {
        if (
            !empty($this->option('url'))
            && (!empty($this->option('ms')) || $this->option('fresh'))
        ) {
            throw new Exception("Can't use `url` with `ms` or `fresh` options");
        }
    }

    /**
     *
     */
    protected function asCsv(array $fileArray, MarketShare $marketShare): string
    {
        $fileName = Storage::disk('public')->path($marketShare->code . DIRECTORY_SEPARATOR . 'data.csv');
        $file = fopen($fileName, 'w');
        foreach ($fileArray as $arrayLine) {
            fputcsv(
                stream: $file,
                fields: $arrayLine,
                separator: ';',
                enclosure: '"',
                escape: '\\',
                eol: PHP_EOL
            );
        }
        fclose($file);
        return $fileName;
    }

    /**
     *
     */
    public function getCsv(BoursoramaScraper $boursoramaScraper, MarketShare $marketShare)
    {
        $fileArray = $boursoramaScraper->extractMarketShareFileFromPage($marketShare);
        // dump($fileArray);
        $csvFile = $this->asCsv($fileArray, $marketShare);
        // dump(File::get($csvFile));
    }
}
