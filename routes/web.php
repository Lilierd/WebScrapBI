<?php

use App\Http\Controllers\MarketShareSnapshotController;
use App\Http\Controllers\SnapshotIndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SnapshotIndexController::class, 'index']);

Route::get('snapshot/{snapshotIndex}/share/{marketShare}', [MarketShareSnapshotController::class, 'index']);

Route::get('market-snapshot-index/{snapshotIndex}', [MarketShareSnapshotController::class, 'index'])
->name('browse.market-snapshot.by-snapshot');

Route::get('market-snapshot/{marketShareSnapshot}', [MarketShareSnapshotController::class, 'show'])
 ->name("market-share-snapshot.view");
