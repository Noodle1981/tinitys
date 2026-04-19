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
        Schema::table('patient_habits', function (Blueprint $table) {
            $table->dropColumn('substances_details');
            
            $table->string('tobacco_usage')->nullable();
            $table->string('alcohol_usage')->nullable();
            $table->string('coffee_usage')->nullable();
            $table->string('energy_usage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_habits', function (Blueprint $table) {
            $table->string('substances_details')->nullable();
            
            $table->dropColumn(['tobacco_usage', 'alcohol_usage', 'coffee_usage', 'energy_usage']);
        });
    }
};
