<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SavedUnitConfiguration;
use App\Models\SavedTechnicianConfiguration;
use Illuminate\Validation\ValidationException;

class AIController extends Controller
{
    public function showForm()
    {
        $unitConfigurations = SavedUnitConfiguration::with('units')->get();
        $technicianConfigurations = SavedTechnicianConfiguration::with('technicians')->get();

        return view('form', compact(
            'unitConfigurations',
            'technicianConfigurations'
        ));
    }

    // HELPERS

    private function calculateAttractiveness($remaining_mw)
    {
        if ($remaining_mw < 100) return 0.0001;
        if ($remaining_mw < 115) return 0.5;
        return 1.0;
    }

    private function scoreSolution($solution, $listrik, $total_mw, $num_periods)
    {
        $penalty = 0;
        for ($t = 0; $t < $num_periods; $t++) {
            $total_used = 0;
            for ($i = 0; $i < count($listrik); $i++) {
                $total_used += $solution[$i][$t] * $listrik[$i];
            }
            $remaining = $total_mw - $total_used;
            if ($remaining < 100) $penalty += 1000;
            elseif ($remaining < 115) $penalty += 100;
        }
        return 1 / (1 + $penalty);
    }

    private function buildEvents(array $units_input)
    {
        $listrik = [];
        $unit_groups = [];
        $event_labels = [];
        $currentIndex = 0;
        $unitCounter = 1;

        foreach ($units_input as $unit) {
            $mw = (int) $unit['mw'];
            $events_count = (int) $unit['events'] * 2;
            $group = [];
            $alphabet = range('a', 'z');

            for ($j = 0; $j < $events_count; $j++) {
                $listrik[] = $mw;
                $group[] = $currentIndex;
                $event_labels[] = $unitCounter . ($alphabet[$j] ?? $j);
                $currentIndex++;
            }

            $unit_groups[] = $group;
            $unitCounter++;
        }

        return [
            'listrik' => $listrik,
            'unit_groups' => $unit_groups,
            'event_labels' => $event_labels,
        ];
    }

    private function constructSolution(
        $num_events,
        $num_periods,
        $listrik,
        $total_mw,
        &$pheromone,
        $unit_groups,
        $alpha,
        $beta,
        $maxEventsPerPeriod
    ) {
        $used_mw = array_fill(0, $num_periods, 0);
        $eventsPerPeriod = array_fill(0, $num_periods, 0);
        $solution = array_fill(0, $num_events, array_fill(0, $num_periods, 0));

        for ($i = 0; $i < $num_events; $i++) {
            $attractiveness = [];
            $numerator = [];
            $valid_mask = array_fill(0, $num_periods, 1);

            for ($t = 0; $t < $num_periods; $t++) {
                if ($eventsPerPeriod[$t] >= $maxEventsPerPeriod) {
                    $valid_mask[$t] = 0;
                }
            }

            for ($t = 0; $t < $num_periods; $t++) {
                $remaining = $total_mw - ($used_mw[$t] + $listrik[$i]);
                $attractiveness[$t] = $this->calculateAttractiveness($remaining);
            }

            for ($t = 0; $t < $num_periods; $t++) {
                $pher = pow($pheromone[$i][$t], $alpha);
                $heur = pow($attractiveness[$t], $beta);
                $numerator[$t] = $pher * $heur;
            }

            $my_group = [];
            foreach ($unit_groups as $group) {
                if (in_array($i, $group)) {
                    $my_group = $group;
                    break;
                }
            }

            $max_per_sem = max(intdiv(count($my_group), 2), 1);
            $sem1 = 0;
            $sem2 = 0;

            foreach ($my_group as $member) {
                if ($member < $i) {
                    for ($t = 0; $t < $num_periods; $t++) {
                        if ($solution[$member][$t] == 1) {
                            $valid_mask[$t] = 0;
                            if ($t < 6) $sem1++;
                            else $sem2++;
                        }
                    }
                }
            }

            for ($t = 0; $t < $num_periods; $t++) {
                if ($t < 6 && $sem1 >= $max_per_sem) $valid_mask[$t] = 0;
                if ($t >= 6 && $sem2 >= $max_per_sem) $valid_mask[$t] = 0;
            }

            for ($t = 0; $t < $num_periods; $t++) {
                $numerator[$t] *= $valid_mask[$t];
            }

            $sum = array_sum($numerator);

            if ($sum == 0) {
                $prob = $valid_mask;
                $sumV = array_sum($prob);
                for ($t = 0; $t < $num_periods; $t++) {
                    $prob[$t] = $prob[$t] / ($sumV ?: 1);
                }
            } else {
                for ($t = 0; $t < $num_periods; $t++) {
                    $prob[$t] = $numerator[$t] / $sum;
                }
            }

            $r = mt_rand() / mt_getrandmax();
            $cum = 0;
            $chosen = 0;

            for ($t = 0; $t < $num_periods; $t++) {
                $cum += $prob[$t];
                if ($r <= $cum) {
                    $chosen = $t;
                    break;
                }
            }

            $solution[$i][$chosen] = 1;
            $used_mw[$chosen] += $listrik[$i];
            $eventsPerPeriod[$chosen]++;
        }

        return $solution;
    }

    // ACO
    private function runACO($total_mw, $num_ants, $units_input, $maxEventsPerPeriod)
    {
        $eventData = $this->buildEvents($units_input);
        $listrik = $eventData['listrik'];
        $unit_groups = $eventData['unit_groups'];
        $event_labels = $eventData['event_labels'];

        $num_events = count($listrik);
        $num_periods = 12;
        $alpha = 1;
        $beta = 3;
        $evaporation_rate = 0.4;

        $pheromone = array_fill(0, $num_events, array_fill(0, $num_periods, 0));

        $bestSolution = null;
        $bestScore = -INF;
        $scoreHistory = [];

        for ($iter = 0; $iter < 20; $iter++) {
            $ants = [];

            for ($a = 0; $a < $num_ants; $a++) {
                $sol = $this->constructSolution(
                    $num_events,
                    $num_periods,
                    $listrik,
                    $total_mw,
                    $pheromone,
                    $unit_groups,
                    $alpha,
                    $beta,
                    $maxEventsPerPeriod
                );
                $ants[] = $sol;
                $s = $this->scoreSolution($sol, $listrik, $total_mw, $num_periods);

                if ($s > $bestScore) {
                    $bestScore = $s;
                    $bestSolution = $sol;
                }
            }

            for ($i = 0; $i < $num_events; $i++) {
                for ($t = 0; $t < $num_periods; $t++) {
                    $pheromone[$i][$t] *= (1 - $evaporation_rate);
                }
            }

            foreach ($ants as $ant) {
                $c = $this->scoreSolution($ant, $listrik, $total_mw, $num_periods);
                for ($i = 0; $i < $num_events; $i++) {
                    for ($t = 0; $t < $num_periods; $t++) {
                        if ($ant[$i][$t] == 1) {
                            $pheromone[$i][$t] += $c;
                        }
                    }
                }
            }

            $scoreHistory[] = $bestScore;

            if (count($scoreHistory) >= 3) {
                $n = count($scoreHistory);
                if ($scoreHistory[$n - 1] == $scoreHistory[$n - 2] && $scoreHistory[$n - 2] == $scoreHistory[$n - 3]) {
                    break;
                }
            }
        }

        // Build distribution
        $distribution = [];
        for ($t = 0; $t < $num_periods; $t++) {
            $used = 0;
            $scheduled = [];
            for ($i = 0; $i < $num_events; $i++) {
                if ($bestSolution[$i][$t] == 1) {
                    $used += $listrik[$i];
                    preg_match('/^\d+/', $event_labels[$i], $matches);
                    $unit_number = $matches[0] ?? ($i + 1);
                    $scheduled[] = 'unit ' . $unit_number;
                }
            }
            sort($scheduled);
            $distribution[$t] = [
                'used'      => $used,
                'remaining' => $total_mw - $used,
                'units'     => array_unique($scheduled),
            ];
        }

        return [
            'solution'     => $bestSolution,
            'distribution' => $distribution,
            'event_labels' => $event_labels,
            'listrik'      => $listrik,
            'num_events'   => $num_events,
            'num_periods'  => $num_periods,
            'history'      => $scoreHistory,
        ];
    }

    // A*

    private function runAstar($acoResult, $teams_input)
    {
        $bestSolution  = $acoResult['solution'];
        $event_labels  = $acoResult['event_labels'];
        $listrik       = $acoResult['listrik'];
        $num_events    = $acoResult['num_events'];
        $num_periods   = $acoResult['num_periods'];

        // Build events list from ACO output
        $events = [];
        for ($i = 0; $i < $num_events; $i++) {
            for ($t = 0; $t < $num_periods; $t++) {
                if ($bestSolution[$i][$t] == 1) {
                    $events[] = [
                        'label'   => $event_labels[$i],
                        'period'  => $t + 1,
                        'unit_mw' => $listrik[$i],
                    ];
                }
            }
        }
        usort($events, fn($a, $b) => $a['period'] - $b['period']);

        // Build teams from form input
        $teams = [];
        foreach ($teams_input as $t) {
            $type     = $t['type'] ?? 'all';
            $operator = $t['operator'] ?? '>=';
            $mw_limit = isset($t['mw_limit']) ? (int) $t['mw_limit'] : 0;

            if ($type === 'all') {
                $min_mw = 0;
                $max_mw = 9999;
            } elseif ($operator === '>=') {
                $min_mw = $mw_limit;
                $max_mw = 9999;
            } else {
                $min_mw = 0;
                $max_mw = $mw_limit;
            }

            $teams[] = [
                'name'   => $t['name'],
                'cost'   => (float) $t['cost'],
                'min_mw' => $min_mw,
                'max_mw' => $max_mw,
            ];
        }

        $numTeams  = count($teams);
        $numEvents = count($events);

        // Cheapest cost for h(n)
        $minCostGlobal = PHP_INT_MAX;
        foreach ($teams as $tt) {
            if ($tt['cost'] < $minCostGlobal) $minCostGlobal = $tt['cost'];
        }

        // A* open list
        $openList = [[
            'f'           => 0,
            'g'           => 0,
            'idx'         => 0,
            'streaks'     => array_fill(0, $numTeams, 0),
            'assignments' => [],
            'busy'        => array_fill(0, $numTeams, -1),
        ]];

        $bestResult = null;
        $astarHistory = [];

        while (!empty($openList)) {
            usort($openList, fn($a, $b) => $a['f'] <=> $b['f']);
            $node = array_shift($openList);

            $astarHistory[] = round($node['f'], 2);

            $idx = $node['idx'];

            if ($idx >= $numEvents) {
                $bestResult = $node;
                break;
            }

            $event   = $events[$idx];
            $period  = $event['period'];
            $unit_mw = $event['unit_mw'];

            foreach ($teams as $ti => $team) {
                if ($unit_mw < $team['min_mw'] || $unit_mw > $team['max_mw']) continue;
                if ($node['busy'][$ti] === $period) continue;

                // Cek apakah penugasan ini berturut-turut dengan bulan sebelumnya
                $isConsecutive = ($node['busy'][$ti] === $period - 1);

                // Jika tidak berturut-turut (habis istirahat), streak dianggap 0 sebelum hitung biaya.
                if ($isConsecutive) {
                    $currentStreak = $node['streaks'][$ti];
                } else {
                    $currentStreak = 0;
                }

                $cost = $team['cost'];

                // Jika streak berturut-turut sudah mencapai 3 atau lebih
                if ($currentStreak >= 3) {
                    $jumlahBulanOvertime = $currentStreak - 3 + 1; // Bulan ke-4 = 1 kali lembur, dst
                    $cost = $team['cost'] * pow(1.2, $jumlahBulanOvertime);
                }

                $newG = $node['g'] + $cost;

                // Update streak untuk disimpan ke node berikutnya
                $newStreaks = $node['streaks'];
                if ($isConsecutive) {
                    $newStreaks[$ti]++;
                } else {
                    $newStreaks[$ti] = 1; // Streak dimulai ulang dari 1 karena habis istirahat
                }

                $remaining = $numEvents - $idx - 1;
                $h         = $remaining * $minCostGlobal;

                $newBusy         = $node['busy'];
                $newBusy[$ti]    = $period;

                $newAssignments   = $node['assignments'];
                $newAssignments[] = [
                    'event'    => $event['label'],
                    'period'   => $period,
                    'team'     => $team['name'],
                    'cost'     => round($cost, 2),
                    'overtime' => $currentStreak >= 3,
                ];

                $openList[] = [
                    'f'           => $newG + $h,
                    'g'           => $newG,
                    'idx'         => $idx + 1,
                    'streaks'     => $newStreaks,
                    'assignments' => $newAssignments,
                    'busy'        => $newBusy,
                ];
            }
        }

        if (!$bestResult) return null;

        return [
            'assignments' => $bestResult['assignments'],
            'total_cost'  => round($bestResult['g'], 2),
            'history'     => $astarHistory,
        ];
    }

    // PROGRAM MAIN

    public function processScheduling(Request $request)
    {
        session()->flash('active_step', 2);

        $validated = $request->validate([
            'total_mw' => ['required', 'numeric'],
            'num_ants' => ['required', 'numeric'],
            'units' => ['required', 'array', 'min:1'],
            'units.*.mw' => ['required', 'numeric'],
            'units.*.events' => ['required', 'numeric'],
            'teams' => ['required', 'array', 'min:1'],
            'teams.*.name' => ['required', 'string', 'max:255'],
            'teams.*.type' => ['required', 'in:condition,all'],
            'teams.*.operator' => ['nullable', 'in:>=,<='],
            'teams.*.mw_limit' => ['nullable', 'numeric'],
            'teams.*.cost' => ['required', 'numeric'],
        ], [
            'required' => 'Semua input wajib diisi sebelum menjalankan optimasi.',
            'numeric' => 'Input angka harus berupa angka valid.',
        ]);

        foreach ($validated['teams'] as $index => $team) {
            if ($team['type'] === 'condition' && (($team['operator'] ?? null) === null || ($team['mw_limit'] ?? null) === null)) {
                throw ValidationException::withMessages([
                    "teams.$index.mw_limit" => 'Operator dan batas MW wajib diisi untuk MW Condition.',
                ]);
            }
        }

        $total_mw    = (int) $validated['total_mw'];
        $num_ants    = (int) $validated['num_ants'];
        $units_input = $validated['units'];
        $teams_input = $validated['teams'];

        if (empty($units_input)) {
            return redirect()->back()->with('error', 'Please add at least one unit.');
        }

        // Run ACO
        $maxEventsPerPeriod = INF;

        if (count($teams_input) > 0) {
            $maxEventsPerPeriod = count($teams_input);
        }

        $acoResult = $this->runACO($total_mw, $num_ants, $units_input, $maxEventsPerPeriod);

        // Run A* using ACO output
        $astarResult = null;
        $astarHistoryData = [];

        if (!empty($teams_input)) {
            $astarResult = $this->runAstar($acoResult, $teams_input);
            if ($astarResult) {
                $astarHistoryData = $astarResult['history'];
            }
        }

        // 1. Siapkan data untuk Heatmap (Event x Periode)
        $heatmapData = [];
        for ($i = 0; $i < $acoResult['num_events']; $i++) {
            $dataPeriode = [];
            for ($t = 0; $t < $acoResult['num_periods']; $t++) {
                $dataPeriode[] = [
                    'x' => 'P' . ($t + 1),
                    'y' => $acoResult['solution'][$i][$t] // Nilainya 1 jika maintenance, 0 jika tidak
                ];
            }
            $heatmapData[] = [
                'name' => $acoResult['event_labels'][$i],
                'data' => $dataPeriode
            ];
        }

        // 2. Siapkan data untuk Bar Chart Distribusi Daya
        $categoriesDaya = [];
        $dayaUsed = [];
        $dayaRemaining = [];
        foreach ($acoResult['distribution'] as $t => $data) {
            $categoriesDaya[] = 'P' . ($t + 1);
            $dayaUsed[] = $data['used'];
            $dayaRemaining[] = $data['remaining'];
        }

        return view('index', [
            'solution'     => $acoResult['solution'],
            'distribution' => $acoResult['distribution'],
            'event_labels' => $acoResult['event_labels'],
            'astar'        => $astarResult,
            'total_mw'    => $total_mw,
            'num_ants'    => $num_ants,
            'num_units'   => count($units_input),
            'num_teams'   => count($teams_input),
            // Data tambahan untuk Visualisasi:
            'heatmapData'  => $heatmapData,
            'categoriesDaya' => $categoriesDaya,
            'dayaUsed'     => $dayaUsed,
            'dayaRemaining' => $dayaRemaining,
            'acoHistory'   => $acoResult['history'],
            'astarHistory' => $astarHistoryData,
        ]);
    }
}
