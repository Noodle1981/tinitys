<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HearingAid extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'fitting_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
