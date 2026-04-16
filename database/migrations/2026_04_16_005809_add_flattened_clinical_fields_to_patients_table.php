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
            // Infecciones y Generales
            $table->boolean('has_head_trauma')->default(false);
            $table->boolean('has_meningitis')->default(false);
            $table->boolean('has_meniere')->default(false);
            $table->boolean('has_facial_paralysis')->default(false);
            $table->boolean('has_herpes_zoster')->default(false);
            $table->boolean('has_mumps')->default(false);
            $table->boolean('has_measles')->default(false);
            $table->boolean('has_rubella')->default(false);
            $table->boolean('has_typhoid')->default(false);

            // Antecedentes Otológicos
            $table->boolean('has_otalgia')->default(false);
            $table->boolean('has_otorrhea')->default(false);
            $table->boolean('has_serous_otitis')->default(false);
            $table->boolean('has_ear_obstruction')->default(false);
            $table->boolean('has_ossicular_chain_lesion')->default(false);

            // Hábitos
            $table->boolean('smoking')->default(false);
            $table->boolean('alcohol')->default(false);
            $table->boolean('coffee')->default(false);

            // Ruido y Entorno
            $table->boolean('leisure_noise')->default(false);
            $table->boolean('noise_protection')->default(false);
            $table->boolean('recent_noise_exposure')->default(false);

            // Fármacos y Familia
            $table->boolean('uses_ototoxics')->default(false);
            $table->boolean('family_hearing_loss')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'has_head_trauma', 'has_meningitis', 'has_meniere', 'has_facial_paralysis',
                'has_herpes_zoster', 'has_mumps', 'has_measles', 'has_rubella', 'has_typhoid',
                'has_otalgia', 'has_otorrhea', 'has_serous_otitis', 'has_ear_obstruction', 'has_ossicular_chain_lesion',
                'smoking', 'alcohol', 'coffee',
                'leisure_noise', 'noise_protection', 'recent_noise_exposure',
                'uses_ototoxics', 'family_hearing_loss'
            ]);
        });
    }
};
