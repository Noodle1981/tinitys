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
        Schema::create('tinnitus_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->integer('sleep_quality');
            $table->integer('stress_level');
            $table->integer('noise_exposure');
            $table->integer('health_state');
            $table->integer('alcohol_intake');
            $table->integer('reliability_index');
            $table->string('frequency_perception');
            $table->string('left_ear_intensity');
            $table->string('right_ear_intensity');
            $table->json('recommendations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tinnitus_profiles');
    }
};
