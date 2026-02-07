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
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cart_id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->json('cart_items')->nullable();
            $table->decimal('cart_total', 12, 4)->default(0);
            $table->integer('items_count')->default(0);
            $table->boolean('is_converted')->default(false);
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->integer('reminder_count')->default(0);
            $table->unsignedInteger('channel_id')->nullable();
            $table->string('locale')->nullable();
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('set null');

            $table->index('is_converted');
            $table->index('abandoned_at');
            $table->index(['customer_email', 'is_converted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
