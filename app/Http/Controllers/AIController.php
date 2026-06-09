<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AIController extends Controller
{
    public function showForm()
    {
        return view('form');
    }

    public function processScheduling(Request $request)
    {
        $total_mw = (int) $request->input('total_mw', 150);
        $num_ants = (int) $request->input('num_ants', 30);
        $units_input = $request->input('units', []);

        $listrik = [];
        $unit_groups = [];
        $event_labels = [];

        $currentIndex = 0;
        $unitCounter = 1;

        foreach ($units_input as $unit) {
            $mw = (int) $unit['mw'];

            // AUTOMATIC MULTIPLICATION: 
            // We multiply the 6-month requirement by 2 to span across the 12-month period.
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

        $num_events = count($listrik);
        $num_periods = 12;
        $alpha = 1;
        $beta = 3;
        $evaporation_rate = 0.4;

        if ($num_events === 0) {
            return redirect()->back()->with('error', 'Please add at least one unit.');
        }

        $pheromone = array_fill(0, $num_events, array_fill(0, $num_periods, 0));

        // =====================
        // FUNCTIONS
        // =====================
        $calculateAttractiveness = function ($remaining_mw) {
            if ($remaining_mw < 100) return 0.0001;
            if ($remaining_mw < 115) return 0.5;
            return 1.0;
        };

        $scoreSolution = function ($solution) use ($listrik, $total_mw, $num_periods) {
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
        };

        $constructSolution = function () use (
            $num_events,
            $num_periods,
            $listrik,
            $total_mw,
            &$pheromone,
            $unit_groups,
            $calculateAttractiveness,
            $alpha,
            $beta
        ) {
            $used_mw = array_fill(0, $num_periods, 0);
            $solution = array_fill(0, $num_events, array_fill(0, $num_periods, 0));

            for ($i = 0; $i < $num_events; $i++) {
                $attractiveness = [];
                $numerator = [];
                $valid_mask = array_fill(0, $num_periods, 1);

                for ($t = 0; $t < $num_periods; $t++) {
                    $remaining = $total_mw - ($used_mw[$t] + $listrik[$i]);
                    $attractiveness[$t] = $calculateAttractiveness($remaining);
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

                // Since total events is doubled, this splits it perfectly back to the semester count.
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
            }

            return $solution;
        };

        // =====================
        // MAIN LOOP
        // =====================
        $bestSolution = null;
        $bestScore = -INF;
        $scoreHistory = [];

        for ($iter = 0; $iter < 20; $iter++) {
            $ants = [];

            for ($a = 0; $a < $num_ants; $a++) {
                $sol = $constructSolution();
                $ants[] = $sol;
                $s = $scoreSolution($sol);

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
                $c = $scoreSolution($ant);
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

        // =====================
        // PERIOD DISTRIBUTION
        // =====================
        $distribution = [];

        for ($t = 0; $t < $num_periods; $t++) {
            $used = 0;
            $scheduled = [];

            for ($i = 0; $i < $num_events; $i++) {
                if ($bestSolution[$i][$t] == 1) {
                    $used += $listrik[$i];

                    preg_match('/^\d+/', $event_labels[$i], $matches);
                    $unit_number = $matches[0] ?? ($i + 1);

                    $scheduled[] = "unit " . $unit_number;
                }
            }
            sort($scheduled);

            $distribution[$t] = [
                'used' => $used,
                'remaining' => $total_mw - $used,
                'units' => array_unique($scheduled)
            ];
        }

        return view('index', [
            'solution' => $bestSolution,
            'distribution' => $distribution,
            'event_labels' => $event_labels
        ]);
    }
}
