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
        Schema::create('calibration_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_session_id')->constrained('patient_sessions')->onDelete('cascade');
            $table->string('layer_id');
            $table->integer('frequency_hz');
            $table->integer('threshold_vol_pct');
            $table->integer('match_vol_pct');
            $table->float('db_sl');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calibration_results');
    }
};
