<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinnitusProfile extends Model
{
    protected $fillable = [
        'patient_id',
        'initiated_by',
        'affected_ears',
        'sleep_quality',
        'stress_level',
        'noise_exposure',
        'health_state',
        'alcohol_intake',
        'reliability_index',
        'frequency_perception',
        'left_freq_selected',
        'right_freq_selected',
        'left_index',
        'right_index',
        'left_ear_intensity',
        'right_ear_intensity',
        'discrimination_difficulty',
        'warble_tone_preference',
        'loud_noise_exacerbation',
        'residual_inhibition',
        'fatigue_level',
        'has_puna',
        'has_cold',
        'has_throat_pain',
        'recommendations',
    ];

    protected $casts = [
        'recommendations' => 'array',
        'discrimination_difficulty' => 'boolean',
        'warble_tone_preference' => 'boolean',
        'loud_noise_exacerbation' => 'boolean',
        'has_puna' => 'boolean',
        'has_cold' => 'boolean',
        'has_throat_pain' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
