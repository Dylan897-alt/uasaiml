<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SavedUnitConfiguration;

class Unit extends Model
{
    protected $fillable = [
        'capacity',
        'maintenance_count',
        'configuration_id'
    ];

    public function configuration()
    {
        return $this->belongsTo(
            SavedUnitConfiguration::class,
            'configuration_id'
        );
    }
}