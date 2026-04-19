<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientHabit extends Model
{
    protected $fillable = [
        'patient_id',
        'smoking_level',
        'alcohol_level',
        'coffee_level',
        'activity_frequency',
        'last_24h_exposure',
        'tobacco_usage',
        'alcohol_usage',
        'coffee_usage',
        'energy_usage',
        'risk_discotecas',
        'risk_musica_fuerte',
        'risk_caza',
        'risk_tiro',
        'risk_militar',
        'risk_motociclismo',
        'risk_automovilismo',
        'risk_submarinismo',
        'risk_none',
    ];

    protected $casts = [
        'last_24h_exposure' => 'boolean',
        'risk_discotecas' => 'boolean',
        'risk_musica_fuerte' => 'boolean',
        'risk_caza' => 'boolean',
        'risk_tiro' => 'boolean',
        'risk_militar' => 'boolean',
        'risk_motociclismo' => 'boolean',
        'risk_automovilismo' => 'boolean',
        'risk_submarinismo' => 'boolean',
        'risk_none' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
