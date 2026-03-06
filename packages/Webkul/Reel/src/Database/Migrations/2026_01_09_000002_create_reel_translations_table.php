<?php
// Database/Migrations/2024_01_01_000002_create_reel_translations_table.php

namespace Webkul\Reel\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reel_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reel_id');
            $table->string('locale')->index();

            // Translatable fields
            $table->string('title');
            $table->text('caption')->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('reel_id')
                ->references('id')
                ->on('reels')
                ->onDelete('cascade');

            // Unique constraint for locale per reel
            $table->unique(['reel_id', 'locale']);

            // Indexes
            $table->index(['reel_id', 'locale']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reel_translations');
    }
};
