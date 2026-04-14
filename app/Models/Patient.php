<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'dni',
        'name',
        'birth_date',
        'gender',
        'occupation',
        'laterality',
        'sound_type',
        'evolution_time',
        'comorbidities',
        'mental_health_context',
        'medications',
        'doctor_id',
        'user_id',
        'age', // Keep for compatibility if needed
        'noise_exposure',
        'tinnitus_symptom',
        'vertigo_symptom',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'comorbidities' => 'array',
        'sound_type' => 'array',
        'noise_exposure' => 'boolean',
        'tinnitus_symptom' => 'boolean',
        'vertigo_symptom' => 'boolean',
    ];

    public function getAge()
    {
        return $this->birth_date ? $this->birth_date->age : $this->age;
    }

    public function sessions()
    {
        return $this->hasMany(PatientSession::class);
    }
}
