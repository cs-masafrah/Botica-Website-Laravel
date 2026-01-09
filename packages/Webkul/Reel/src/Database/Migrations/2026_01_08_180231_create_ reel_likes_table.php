<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReelLikesTable extends Migration
{
    public function up()
    {
        Schema::create('reel_likes', function (Blueprint $table) {
            $table->id(); // This creates bigint unsigned

            // Match the customers.id type (bigint unsigned)
            $table->unsignedBigInteger('reel_id');
            $table->unsignedInteger('customer_id')->nullable();

            $table->timestamps();

            // Use the same engine and charset as customers table
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // Add foreign keys
            $table->foreign('reel_id')
                ->references('id')
                ->on('reels')
                ->onDelete('cascade');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->unique(['reel_id', 'customer_id']);

            // Add indexes for performance
            $table->index('reel_id');
            $table->index('customer_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reel_likes');
    }
}
