<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function audiometryValues()
    {
        return $this->hasMany(AudiometryValue::class);
    }

    public function calibrationResults()
    {
        return $this->hasMany(CalibrationResult::class);
    }
}
