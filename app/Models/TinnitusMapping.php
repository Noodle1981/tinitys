<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinnitusMapping extends Model
{
    protected $fillable = [
        'patient_id',
        'initiated_by',
        'ear',                  // 'OI', 'OD', 'ambos'
        'left_layers_config',
        'right_layers_config',
        'master_volume',
        'config_version',
    ];

    protected $casts = [
        'left_layers_config'  => 'array',
        'right_layers_config' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
