<?php

namespace App\Console\Commands;

use App\Contracts\BoursoramaScraper;
use App\Models\MarketShare;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GetFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boursorama:get-file';

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
        dump($marketShare);

        $fileArray = $boursoramaScraper->extractMarketShareFileFromPage($marketShare);
        dump($fileArray);
        $csvFile = $this->asCsv($fileArray, $marketShare);
        dump(File::get($csvFile));
    }

    public function asCsv(array $fileArray, MarketShare $marketShare)
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
}
