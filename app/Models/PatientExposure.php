<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientExposure extends Model
{
    protected $fillable = [
        'patient_id',
        'occupational_noise_level',
        'leisure_noise_level',
        'noise_duration_years',
        'protection_used',
        'recent_exposure',
    ];

    protected $casts = [
        'protection_used' => 'boolean',
        'recent_exposure' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
