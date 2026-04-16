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
        Schema::table('patient_clinical_histories', function (Blueprint $table) {
            $table->integer('sleep_quality')->default(5); // 0-10
            $table->integer('avg_sleep_hours')->nullable();
            $table->boolean('has_night_shifts')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_clinical_histories', function (Blueprint $table) {
            $table->dropColumn(['sleep_quality', 'avg_sleep_hours', 'has_night_shifts']);
        });
    }
};
