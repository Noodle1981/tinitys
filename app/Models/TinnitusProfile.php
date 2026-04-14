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
        'recommendations',
    ];

    protected $casts = [
        'recommendations' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
