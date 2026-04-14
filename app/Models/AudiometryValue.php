<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudiometryValue extends Model
{
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(PatientSession::class, 'patient_session_id');
    }
}
