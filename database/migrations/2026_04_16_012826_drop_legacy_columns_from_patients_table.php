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
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['age', 'noise_exposure', 'tinnitus_symptom', 'vertigo_symptom', 'evolution_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->integer('age')->nullable();
            $table->boolean('noise_exposure')->default(false);
            $table->boolean('tinnitus_symptom')->default(false);
            $table->boolean('vertigo_symptom')->default(false);
            $table->string('evolution_time')->nullable();
        });
    }
};
