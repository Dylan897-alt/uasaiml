<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    protected $fillable = [
        'team_name',
        'specialization',
        'operator',
        'mw',
        'cost',
        'configuration_id'
    ];

    public function configuration()
    {
        return $this->belongsTo(
            SavedTechnicianConfiguration::class,
            'configuration_id'
        );
    }
}
