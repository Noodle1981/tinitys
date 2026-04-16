<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientClinicalHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'uses_aminoglycosides',
        'uses_salicylates',
        'uses_loop_diuretics',
        'uses_quinine',
        'has_malaria',
        'has_rheumatism',
        'has_tuberculosis',
        'has_heart_failure',
        'has_hypertension',
        'has_otalgia',
        'has_otorrhea',
        'has_serous_otitis',
        'has_ear_obstruction',
        'has_ossicular_chain_lesion',
        'family_hearing_loss',
        'mental_health_context',
        'medications',
        'intensity_eva',
        'distress_eva',
        'sleep_quality',
        'avg_sleep_hours',
        'has_night_shifts',
    ];

    protected $casts = [
        'uses_aminoglycosides' => 'boolean',
        'uses_salicylates' => 'boolean',
        'uses_loop_diuretics' => 'boolean',
        'uses_quinine' => 'boolean',
        'has_malaria' => 'boolean',
        'has_rheumatism' => 'boolean',
        'has_tuberculosis' => 'boolean',
        'has_heart_failure' => 'boolean',
        'has_hypertension' => 'boolean',
        'has_otalgia' => 'boolean',
        'has_otorrhea' => 'boolean',
        'has_serous_otitis' => 'boolean',
        'has_ear_obstruction' => 'boolean',
        'has_ossicular_chain_lesion' => 'boolean',
        'family_hearing_loss' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
