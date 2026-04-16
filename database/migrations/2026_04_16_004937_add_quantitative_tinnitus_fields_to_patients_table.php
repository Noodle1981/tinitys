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
            $table->date('onset_date')->nullable();
            $table->integer('intensity_eva')->nullable();
            $table->integer('distress_eva')->nullable();
            $table->string('onset_type')->nullable(); // Súbito / Gradual
            $table->json('triggers')->nullable(); // Gatillos / Desencadenantes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'onset_date',
                'intensity_eva',
                'distress_eva',
                'onset_type',
                'triggers'
            ]);
        });
    }
};
