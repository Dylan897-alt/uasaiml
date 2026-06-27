<?php

namespace App\Models;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;

class SavedUnitConfiguration extends Model
{
    protected $fillable = ['name'];

    public function units()
    {
        return $this->hasMany(Unit::class, 'configuration_id');
    }
}
