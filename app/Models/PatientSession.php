<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'audiometry_data' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function calibrationResults()
    {
        return $this->hasMany(CalibrationResult::class);
    }
}
