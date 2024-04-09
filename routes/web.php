<?php

use App\Console\Commands\Boursorama\Aggregate;
use App\Http\Controllers\MarketShareSnapshotController;
use App\Http\Controllers\SnapshotIndexController;
use App\Models\SnapshotIndex;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [SnapshotIndexController::class, 'index'])
    ->name('root');
Route::get('/snapshot-index/{snapshotIndex}', [SnapshotIndexController::class, 'show']);

// Route::get('aggregate/{name?}', function (?string $name) {
//     if(Artisan::call("boursorama:aggregate -n --ms={$name} --isolated=-1") === -1) {
//         return "Already running.";
//     };
//     return "Ran.";
// });

Route::get('snapshot/{snapshotIndex}/share/{marketShare}', [MarketShareSnapshotController::class, 'index']);

Route::get('market-snapshot-index/{snapshotIndex}', [MarketShareSnapshotController::class, 'index'])
    ->name('browse.market-snapshot.by-snapshot');

Route::get('market-snapshot/{marketShareSnapshot}', [MarketShareSnapshotController::class, 'show'])
    ->name("market-share-snapshot.view");
