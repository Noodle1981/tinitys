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
            // Antecedentes Familiares específicos
            $table->boolean('fam_father')->default(false);
            $table->boolean('fam_mother')->default(false);
            $table->boolean('fam_grandparents')->default(false);
            $table->boolean('fam_uncles')->default(false);
            $table->boolean('fam_siblings')->default(false);

            // Otras afecciones otológicas
            $table->boolean('has_vertigo')->default(false);
            
            // Enfermedades generales e infecciosas
            $table->boolean('has_trauma')->default(false);
            $table->boolean('has_meningitis')->default(false);
            $table->boolean('has_facial_paralysis')->default(false);
            $table->boolean('has_herpes_zoster')->default(false);
            $table->boolean('has_mumps')->default(false);
            $table->boolean('has_rubella')->default(false);
            $table->boolean('has_measles')->default(false);
            $table->boolean('has_typhoid')->default(false);
            $table->boolean('has_typhus')->default(false);

            // Registro exacto de Ototóxicos
            $table->boolean('use_streptomycin')->default(false);
            $table->boolean('use_gentamicin')->default(false);
            $table->boolean('use_kanamycin')->default(false);
            $table->boolean('use_tobramycin')->default(false);
            $table->boolean('use_furosemide')->default(false);
            $table->boolean('use_ethacrynic_acid')->default(false);
            $table->boolean('use_vancomycin')->default(false);

            // Comorbilidades adicionales
            $table->boolean('has_headaches')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_clinical_histories', function (Blueprint $table) {
            $table->dropColumn([
                'fam_father', 'fam_mother', 'fam_grandparents', 'fam_uncles', 'fam_siblings',
                'has_vertigo', 'has_trauma', 'has_meningitis', 'has_facial_paralysis', 
                'has_herpes_zoster', 'has_mumps', 'has_rubella', 'has_measles', 
                'has_typhoid', 'has_typhus', 'use_streptomycin', 'use_gentamicin', 
                'use_kanamycin', 'use_tobramycin', 'use_furosemide', 'use_ethacrynic_acid', 
                'use_vancomycin', 'has_headaches'
            ]);
        });
    }
};
