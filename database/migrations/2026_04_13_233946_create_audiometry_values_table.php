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
        Schema::create('audiometry_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_session_id')->constrained('patient_sessions')->onDelete('cascade');
            $table->enum('ear', ['OD', 'OI']);
            $table->integer('frequency'); // Hz
            $table->enum('type', ['air', 'bone']);
            $table->integer('db_level'); // dB HL
            $table->boolean('masking')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audiometry_values');
    }
};
