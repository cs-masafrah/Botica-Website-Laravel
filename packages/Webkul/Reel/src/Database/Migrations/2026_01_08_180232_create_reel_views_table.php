<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReelViewsTable extends Migration
{
    public function up()
    {
        Schema::create('reel_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reel_id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->foreign('reel_id')->references('id')->on('reels')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');

            $table->index(['reel_id', 'customer_id', 'session_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reel_views');
    }
}
