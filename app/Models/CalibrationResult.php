<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalibrationResult extends Model
{
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(PatientSession::class, 'patient_session_id');
    }
}
