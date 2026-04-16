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
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
