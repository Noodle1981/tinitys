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
        Schema::table('tinnitus_profiles', function (Blueprint $table) {
            $table->integer('fatigue_level')->default(1);
            $table->boolean('has_puna')->default(false);
            $table->boolean('has_cold')->default(false);
            $table->boolean('has_throat_pain')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tinnitus_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'fatigue_level',
                'has_puna',
                'has_cold',
                'has_throat_pain'
            ]);
        });
    }
};
