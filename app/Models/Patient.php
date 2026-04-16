<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dni',
        'birth_date',
        'gender',
        'occupation',
        'address',
        'city',
        'province',
        'phone',
        'civil_status',
        'has_children',
        'work_status',
        'work_hours',
        'other_disabilities',
        'laterality',
        'sound_type',
        'doctor_id',
        'user_id'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'sound_type' => 'array',
        'has_children' => 'boolean',
    ];

    public function getAge()
    {
        return Carbon::parse($this->birth_date)->age;
    }

    // Relaciones Clínicas Normalizadas
    public function habits()
    {
        return $this->hasOne(PatientHabit::class);
    }

    public function exposure()
    {
        return $this->hasOne(PatientExposure::class);
    }

    public function onsetCause()
    {
        return $this->hasOne(PatientOnsetCause::class);
    }

    public function clinicalHistory()
    {
        return $this->hasOne(PatientClinicalHistory::class);
    }

    public function sessions()
    {
        return $this->hasMany(PatientSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
