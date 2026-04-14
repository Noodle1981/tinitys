<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinnitusMapping extends Model
{
    protected $fillable = [
        'patient_id',
        'initiated_by',
        'layers_config',
        'master_volume',
    ];

    protected $casts = [
        'layers_config' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
