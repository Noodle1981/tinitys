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
        Schema::create('hearing_aids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('ear'); // OD, OI
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('type')->nullable(); // RIC, BTE, etc.
            $table->string('technology_level')->nullable();
            $table->string('battery')->nullable();
            $table->date('fitting_date')->nullable();
            $table->json('settings')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearing_aids');
    }
};
