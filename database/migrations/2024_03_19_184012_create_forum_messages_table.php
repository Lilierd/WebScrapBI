<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forum_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('id', false)
                ->primary();

            $table->string('title')
                ->nullable();
            $table->string('author');
            $table->string('content');
            $table->string('boursorama_date');

            $table->unsignedBigInteger('forum_message_id') //parent_id
                ->nullable()
                ->references('id')
                ->on('forum_messages');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_messages');
    }
};
