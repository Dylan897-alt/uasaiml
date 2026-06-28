<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\SavedUnitConfiguration;
use App\Models\Technician;
use App\Models\SavedTechnicianConfiguration;
use Illuminate\Validation\ValidationException;

class SaveController extends Controller
{
    public function saveUnitConfiguration(Request $request)
    {
        session()->flash('active_step', 1);

        $validated = $request->validate([
            'unit_configuration_name' => ['required', 'string', 'max:255'],
            'units' => ['required', 'array', 'min:1'],
            'units.*.mw' => ['required', 'numeric'],
            'units.*.events' => ['required', 'numeric'],
        ], [
            'unit_configuration_name.required' => 'Nama konfigurasi unit wajib diisi.',
            'units.required' => 'Minimal satu unit wajib diisi.',
            'units.*.mw.required' => 'Capacity MW setiap unit wajib diisi.',
            'units.*.events.required' => 'Maintenance Count setiap unit wajib diisi.',
        ]);

        $config = SavedUnitConfiguration::create([
            'name' => $validated['unit_configuration_name']
        ]);

        foreach ($validated['units'] as $unit) {

            Unit::create([
                'configuration_id' => $config->id,
                'capacity' => $unit['mw'],
                'maintenance_count' => $unit['events']
            ]);
        }

        return back()->with(
            'success',
            'Unit configuration saved.'
        )->with('active_step', 1);
    }

    public function saveTechnicianConfiguration(Request $request)
    {
        session()->flash('active_step', 2);

        $validated = $request->validate([
            'technician_configuration_name' => ['required', 'string', 'max:255'],
            'teams' => ['required', 'array', 'min:1'],
            'teams.*.name' => ['required', 'string', 'max:255'],
            'teams.*.type' => ['required', 'in:condition,all'],
            'teams.*.operator' => ['nullable', 'in:>=,<='],
            'teams.*.mw_limit' => ['nullable', 'numeric'],
            'teams.*.cost' => ['required', 'numeric'],
        ], [
            'technician_configuration_name.required' => 'Nama konfigurasi teknisi wajib diisi.',
            'teams.required' => 'Minimal satu tim teknisi wajib diisi.',
            'teams.*.name.required' => 'Nama tim wajib diisi.',
            'teams.*.type.required' => 'Specialization wajib dipilih.',
            'teams.*.cost.required' => 'Cost setiap tim wajib diisi.',
        ]);

        foreach ($validated['teams'] as $index => $team) {
            if ($team['type'] === 'condition' && (($team['operator'] ?? null) === null || ($team['mw_limit'] ?? null) === null)) {
                throw ValidationException::withMessages([
                    "teams.$index.mw_limit" => 'Operator dan batas MW wajib diisi untuk MW Condition.',
                ]);
            }
        }

        $config = SavedTechnicianConfiguration::create([
            'name' => $validated['technician_configuration_name']
        ]);

        foreach ($validated['teams'] as $team) {

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
        )->with('active_step', 2);
    }
}
