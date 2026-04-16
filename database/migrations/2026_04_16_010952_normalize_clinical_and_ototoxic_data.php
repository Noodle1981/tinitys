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
        // 1. Limpieza de la tabla patients (quitar campos que se van a relacionar)
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'onset_date', 'intensity_eva', 'distress_eva', 'onset_type', 'triggers',
                'has_head_trauma', 'has_meningitis', 'has_meniere', 'has_facial_paralysis',
                'has_herpes_zoster', 'has_mumps', 'has_measles', 'has_rubella', 'has_typhoid', 'has_vertigo',
                'has_otalgia', 'has_otorrhea', 'has_serous_otitis', 'has_ear_obstruction', 'has_ossicular_chain_lesion',
                'smoking', 'alcohol', 'coffee', 'leisure_noise', 'noise_protection', 'recent_noise_exposure',
                'uses_ototoxics', 'family_hearing_loss', 'comorbidities', 'mental_health_context', 'medications'
            ]);
        });

        // 2. Tabla de Hábitos (Cuantificable)
        Schema::create('patient_habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->integer('smoking_level')->default(0); // 0-10
            $table->integer('alcohol_level')->default(0); // 0-10
            $table->integer('coffee_level')->default(0); // 0-10
            $table->timestamps();
        });

        // 3. Tabla de Exposiciones (Ruido y Entorno)
        Schema::create('patient_exposures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->integer('occupational_noise_level')->default(0); // 0-10
            $table->integer('leisure_noise_level')->default(0); // 0-10
            $table->integer('noise_duration_years')->nullable();
            $table->boolean('protection_used')->default(false);
            $table->boolean('recent_exposure')->default(false);
            $table->timestamps();
        });

        // 4. Tabla de Causas de Inicio (El "Origen")
        Schema::create('patient_onset_causes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->date('onset_date')->nullable();
            $table->string('onset_type')->nullable(); // Súbito / Gradual
            $table->integer('initial_intensity_eva')->default(5);
            $table->json('initial_sound_types')->nullable();
            // Gatillos/Causas
            $table->boolean('has_head_trauma')->default(false);
            $table->boolean('has_meningitis')->default(false);
            $table->boolean('has_meniere')->default(false);
            $table->boolean('has_facial_paralysis')->default(false);
            $table->boolean('has_herpes_zoster')->default(false);
            $table->boolean('has_mumps')->default(false);
            $table->boolean('has_measles')->default(false);
            $table->boolean('has_rubella')->default(false);
            $table->boolean('has_typhoid')->default(false);
            $table->boolean('has_vertigo')->default(false);
            $table->timestamps();
        });

        // 5. Tabla de Historia Clínica (Ototóxicos y Síntomas)
        Schema::create('patient_clinical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            // Ototóxicos
            $table->boolean('uses_aminoglycosides')->default(false);
            $table->boolean('uses_salicylates')->default(false);
            $table->boolean('uses_loop_diuretics')->default(false);
            $table->boolean('uses_quinine')->default(false);
            // Condiciones proxy
            $table->boolean('has_malaria')->default(false);
            $table->boolean('has_rheumatism')->default(false);
            $table->boolean('has_tuberculosis')->default(false);
            $table->boolean('has_heart_failure')->default(false);
            $table->boolean('has_hypertension')->default(false);
            // Otológicos
            $table->boolean('has_otalgia')->default(false);
            $table->boolean('has_otorrhea')->default(false);
            $table->boolean('has_serous_otitis')->default(false);
            $table->boolean('has_ear_obstruction')->default(false);
            $table->boolean('has_ossicular_chain_lesion')->default(false);
            // Otros
            $table->boolean('family_hearing_loss')->default(false);
            $table->text('mental_health_context')->nullable();
            $table->text('medications')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_clinical_histories');
        Schema::dropIfExists('patient_onset_causes');
        Schema::dropIfExists('patient_exposures');
        Schema::dropIfExists('patient_habits');
        
        Schema::table('patients', function (Blueprint $table) {
            // Re-añadir campos básicos si se revierte (abreviado por brevedad)
            $table->json('comorbidities')->nullable();
        });
    }
};
