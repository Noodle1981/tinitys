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
            $table->boolean('has_hearing_aid')->default(false);
            $table->string('hearing_aid_side')->nullable(); // Izquierdo, Derecho, Ambos
            
            $table->boolean('has_cochlear_implant')->default(false);
            $table->string('cochlear_implant_side')->nullable(); // Izquierdo, Derecho, Ambos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_clinical_histories', function (Blueprint $table) {
            $table->dropColumn(['has_hearing_aid', 'hearing_aid_side', 'has_cochlear_implant', 'cochlear_implant_side']);
        });
    }
};
