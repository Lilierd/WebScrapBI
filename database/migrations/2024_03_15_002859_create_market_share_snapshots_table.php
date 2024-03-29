<?php

use App\Models\MarketShare;
use App\Models\SnapshotIndex;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    static protected int $DECIMAL_PRECISION = 9;
    static protected int $DECIMALS_AFTER = 5;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_share_snapshots', function (Blueprint $table) {
            $table->id();

            //VOLUME Column
            $table->bigInteger(
                'volume',
                false,
                false
            )
                ->nullable(false);

            $table->decimal(
                'last_value',
                static::$DECIMAL_PRECISION,
                static::$DECIMALS_AFTER
            );

            //OPENING Price : Market
            $table->decimal(
                'open_value',
                static::$DECIMAL_PRECISION,
                static::$DECIMALS_AFTER
            );

            //CLOSING : Market
            $table->decimal(
                'close_value',
                static::$DECIMAL_PRECISION,
                static::$DECIMALS_AFTER
            );

            //HIGH_PRICE Column
            $table->decimal(
                'high_value',
                static::$DECIMAL_PRECISION,
                static::$DECIMALS_AFTER
            )
                ->nullable(false);

            //LOW_PRICE Column
            $table->decimal(
                'low_value',
                static::$DECIMAL_PRECISION,
                static::$DECIMALS_AFTER
            )
                ->nullable(false);

            //FK SnapshotIndex
            $table->foreignIdFor(SnapshotIndex::class)
                ->nullable(false);

            //FK MarketShare
            $table->foreignIdFor(MarketShare::class)
                ->nullable(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_share_snapshots');
    }
};
