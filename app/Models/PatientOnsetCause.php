<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientOnsetCause extends Model
{
    protected $fillable = [
        'patient_id',
        'onset_date',
        'onset_type',
        'initial_intensity_eva',
        'initial_sound_types',
        'has_head_trauma',
        'has_meningitis',
        'has_meniere',
        'has_facial_paralysis',
        'has_herpes_zoster',
        'has_mumps',
        'has_measles',
        'has_rubella',
        'has_typhoid',
        'has_vertigo',
    ];

    protected $casts = [
        'onset_date' => 'date',
        'initial_sound_types' => 'array',
        'has_head_trauma' => 'boolean',
        'has_meningitis' => 'boolean',
        'has_meniere' => 'boolean',
        'has_facial_paralysis' => 'boolean',
        'has_herpes_zoster' => 'boolean',
        'has_mumps' => 'boolean',
        'has_measles' => 'boolean',
        'has_rubella' => 'boolean',
        'has_typhoid' => 'boolean',
        'has_vertigo' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
