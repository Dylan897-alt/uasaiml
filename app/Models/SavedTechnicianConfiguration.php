<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Technician;

class SavedTechnicianConfiguration extends Model
{
    protected $fillable = ['name'];

    public function technicians()
    {
        return $this->hasMany(
            Technician::class,
            'configuration_id'
        );
    }
}
