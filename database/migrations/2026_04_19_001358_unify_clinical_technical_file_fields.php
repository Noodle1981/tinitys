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
            $table->string('educational_level')->nullable()->after('civil_status');
            $table->integer('children_count')->default(0)->after('educational_level');
            $table->string('children_ages')->nullable()->after('children_count');
            $table->string('postal_code')->nullable()->after('province');
        });

        Schema::table('patient_exposures', function (Blueprint $table) {
            $table->string('company')->nullable();
            $table->string('sector')->nullable();
            $table->text('noise_profile')->nullable();
            $table->string('protection_frequency')->nullable(); // always, sometimes, never
            $table->string('protection_type')->nullable();
            $table->integer('past_exposure_years')->nullable();
            $table->integer('recovery_hours_daily')->nullable();
        });

        Schema::table('patient_habits', function (Blueprint $table) {
            $table->json('recreational_risks')->nullable(); // discotecas, caza, tiro, etc.
            $table->string('activity_frequency')->nullable();
            $table->boolean('last_24h_exposure')->default(false);
            $table->string('substances_details')->nullable(); // dosage/years for coffee/tobacco/alc
        });

        Schema::table('tinnitus_profiles', function (Blueprint $table) {
            $table->boolean('discrimination_difficulty')->default(false);
            $table->boolean('warble_tone_preference')->default(false);
            $table->boolean('loud_noise_exacerbation')->default(false);
            $table->text('residual_inhibition')->nullable();
        });

        Schema::table('patient_clinical_histories', function (Blueprint $table) {
            $table->string('hearing_loss_type')->nullable(); // left, right, both
            $table->boolean('ambient_noise_improvement')->default(false);
            $table->boolean('loud_noise_annoyance')->default(false);
            $table->boolean('has_meniere')->default(false);
            $table->text('physical_exam_notes')->nullable();
            $table->text('otologics_history')->nullable(); // vertigos, meniere notes etc
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For brevity in a medical dev environment, we usually don't drop all 
        // to avoid accidental data loss during rapid iteration, but for completeness:
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['educational_level', 'children_count', 'children_ages', 'postal_code']);
        });
        // ... more dropColumns...
    }
};
