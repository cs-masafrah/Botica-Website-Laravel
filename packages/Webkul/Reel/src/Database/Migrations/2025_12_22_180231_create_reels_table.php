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
            Schema::create('reels', function (Blueprint $table) {
                $table->id();

                $table->string('title');
                $table->text('caption')->nullable();

                $table->string('video_path');
                $table->string('thumbnail_path')->nullable();

                $table->integer('duration')->nullable(); // seconds

                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);

                $table->unsignedBigInteger('views_count')->default(0);
                $table->unsignedBigInteger('likes_count')->default(0);

                $table->unsignedBigInteger('created_by')->nullable();

                // ✅ product_id defined WITHOUT after()
                $table->unsignedInteger('product_id')->nullable();

                // ✅ Foreign key
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reels');
    }
};
