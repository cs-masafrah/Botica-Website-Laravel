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
        Schema::create('abandoned_cart_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status')->default('active');
            $table->integer('abandoned_after_minutes')->default(60);
            $table->integer('send_after_minutes')->default(1440);
            $table->integer('max_reminders')->default(3);
            $table->text('email_template')->nullable();
            $table->string('email_subject');
            $table->boolean('include_coupon')->default(false);
            $table->string('coupon_code')->nullable();
            $table->decimal('discount_amount', 12, 4)->nullable();
            $table->string('discount_type')->nullable();
            $table->json('channel_ids')->nullable();
            $table->json('customer_group_ids')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('include_coupon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abandoned_cart_rules');
    }
};
