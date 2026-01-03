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
        Schema::table('attribute_options', function (Blueprint $table) {
            if (!Schema::hasColumn('attribute_options', 'image')) {
                $table->string('image')->nullable()->after('swatch_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_options', function (Blueprint $table) {
            $table->dropColumnIfExists('image');
        });
    }
};
