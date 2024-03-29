<?php

namespace App\Console\Commands\Boursorama;

use parallel\Runtime;

use App\Contracts\BoursoramaScraper;
use App\Jobs\SnapshotMarket;
use App\Models\MarketShare;
use App\Models\MarketShareSnapshot;
use App\Models\SnapshotIndex;
use DateInterval;
use DateTimeInterface;
use Facebook\WebDriver\Cookie;
use Fiber;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use SebastianBergmann\Timer\Timer;

class Test extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boursorama:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description =    "Fait des choses";

    public function handle()
    {
        // $this->resumee();
        // $this->info("Logging in from main thread");
        // $realScraper = new BoursoramaScraper();
        // $realSid = $realScraper->driver->getSessionID();
        // $realScraper->login();
        // $this->info("Logged in from main thread");
        // $this->table(
        //     ["SID"],
        //     [
        //         ["SID" => $realSid]
        //     ]
        // );

        // $realScraper->driver->quit();

        // $buffer = [];
        // for ($i = 0; $i < 10; $i++) {
        //     $buffer[$i] = new BoursoramaScraper();
        //     $buffer[$i]->driver->quit();
        // }
        // unset($buffer);
        SnapshotMarket::dispatch(
            [1, 2, 3, 4, 5]
        );
    }


    public function resumee(int $current = 1)
    {
        $sc = new BoursoramaScraper();
        $sc->login();
        $sc->takeScreenshot();

        // $sc->driver->quit();

        $cookie = Storage::get("/cookie.txt");
        // ? Rajouter le rappatriement du cookie :
        // ? Paramétrer le compose de selenium pour partager un volume entre les nodes ?
        // ? Stocker les sessions selenium en BD ?
        $sc2 = new BoursoramaScraper();
        $sc->driver->manage()->addCookie(new Cookie("BRS_PROFIL", $cookie));
        $sc2->driver->get('https://boursorama.com/');
        $sc2->takeScreenshot();
        $sc2->driver->quit();
    }
}
