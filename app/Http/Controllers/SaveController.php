<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\SavedUnitConfiguration;
use App\Models\Technician;
use App\Models\SavedTechnicianConfiguration;

class SaveController extends Controller
{
    public function saveUnitConfiguration(Request $request)
    {
        $config = SavedUnitConfiguration::create([
            'name' => $request->unit_configuration_name
        ]);

        foreach ($request->units as $unit) {

            Unit::create([
                'configuration_id' => $config->id,
                'capacity' => $unit['mw'],
                'maintenance_count' => $unit['events']
            ]);
        }

        return back()->with(
            'success',
            'Unit configuration saved.'
        );
    }

    public function saveTechnicianConfiguration(Request $request)
    {
        $config = SavedTechnicianConfiguration::create([
            'name' => $request->technician_configuration_name
        ]);

        foreach ($request->teams as $team) {

            Technician::create([
                'configuration_id' => $config->id,

                'team_name' => $team['name'],

                'specialization' => $team['type'],

                'operator' => $team['operator'] ?? null,

                'mw' => $team['mw_limit'] ?? null,

                'cost' => $team['cost']
            ]);
        }

        return back()->with(
            'success',
            'Technician configuration saved.'
        );
    }
}
