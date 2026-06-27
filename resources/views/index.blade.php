<!DOCTYPE html>
<html>

<head>
    <title>Optimization Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f9f9f9;
        }

        h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 6px;
        }

        h3 {
            color: #555;
        }

        table {
            border-collapse: collapse;
            margin-bottom: 20px;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: center;
        }

        th {
            background: #007bff;
            color: #fff;
        }

        tr:nth-child(even) {
            background: #f0f4ff;
        }

        .section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 16px;
        }

        .overtime-badge {
            background: #dc3545;
            color: #fff;
            border-radius: 4px;
            font-size: 11px;
            padding: 2px 6px;
            margin-left: 4px;
        }

        .dist-row {
            margin-bottom: 8px;
            padding: 8px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
        }

        .ok {
            color: #28a745;
            font-weight: bold;
        }

        .warn {
            color: #ffc107;
            font-weight: bold;
        }

        .bad {
            color: #dc3545;
            font-weight: bold;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        .no-astar {
            color: #888;
            font-style: italic;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <style>
        /* Styling container untuk grafik */
        .chart-container {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        #network-chart {
            height: 400px;
            border: 1px solid #eee;
            background: #fafafa;
        }
    </style>
</head>

<body>

    {{-- ===== ACO RESULT ===== --}}
    <div class="section">
        <h2>ACO — Maintenance Schedule</h2>

        <h3>Solution Matrix (Event × Period)</h3>
        <table>
            <tr>
                <th>Event</th>
                @for ($t = 1; $t <= 12; $t++)
                    <th>P{{ $t }}</th>
                @endfor
            </tr>
            @for ($i = 0; $i < count($solution); $i++)
                <tr>
                    <td><b>{{ $event_labels[$i] }}</b></td>
                    @for ($t = 0; $t < 12; $t++)
                        <td>{{ $solution[$i][$t] == 1 ? '✓' : '' }}</td>
                    @endfor
                </tr>
            @endfor
        </table>

        <h3>Distribution per Period</h3>
        @foreach ($distribution as $t => $data)
            <div class="dist-row">
                <b>Period {{ $t + 1 }}</b> &nbsp;|&nbsp;
                Used: {{ $data['used'] }} MW &nbsp;|&nbsp;
                Remaining: <span
                    class="
            {{ $data['remaining'] >= 115 ? 'ok' : ($data['remaining'] >= 100 ? 'warn' : 'bad') }}
        ">{{ $data['remaining'] }}
                    MW</span>
                &nbsp;|&nbsp;
                Maintenance: {{ count($data['units']) > 0 ? implode(', ', $data['units']) : '—' }}
            </div>
        @endforeach
    </div>

    {{-- ===== A* RESULT ===== --}}
    <div class="section">
        <h2>A* — Technician Assignment</h2>

        @if ($astar)
            <p class="total">Total Cost: Rp {{ number_format($astar['total_cost'], 2) }} juta</p>

            <table>
                <tr>
                    <th>#</th>
                    <th>Event</th>
                    <th>Period</th>
                    <th>Assigned Team</th>
                    <th>Cost (juta)</th>
                </tr>
                @foreach ($astar['assignments'] as $i => $a)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $a['event'] }}</td>
                        <td>P{{ $a['period'] }}</td>
                        <td>{{ $a['team'] }}</td>
                        <td>
                            {{ $a['cost'] }}
                            @if ($a['overtime'])
                                <span class="overtime-badge">+20% overtime</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        @else
            <p class="no-astar">No technician teams configured. Add teams in the form to see A* assignment.</p>
        @endif
    </div>

    <div class="section">
        <h2>Visualisasi Hasil</h2>

        <div class="chart-container">
            <h3>Heatmap Jadwal Maintenance (Event × Periode)</h3>
            <div id="heatmap-chart"></div>
        </div>

        <div class="chart-container">
            <h3>Distribusi Daya per Periode (Kapasitas Total: {{ $total_mw ?? 150 }} MW)</h3>
            <div id="bar-daya-chart"></div>
        </div>

        <div class="chart-container">
            <h3>Grafik Konvergensi (ACO Score & A* Cost)</h3>
            <div style="display: flex; gap: 20px;">
                <div id="line-aco-chart" style="flex: 1;"></div>
                <div id="line-astar-chart" style="flex: 1;"></div>
            </div>
        </div>

        <div class="chart-container">
            <h3>Network Diagram Lintasan Semut</h3>
            <div id="network-chart"></div>
        </div>
    </div>

    <script>
        // Heatmap
        var heatmapOptions = {
            series: @json($heatmapData),
            chart: {
                height: 350,
                type: 'heatmap'
            },
            dataLabels: {
                enabled: false
            },
            colors: ["#008FFB"],
            title: {
                text: 'Warna Biru Menunjukkan Jadwal Maintenance Terpilih'
            },
            xaxis: {
                type: 'category'
            }
        };
        var heatmapChart = new ApexCharts(document.querySelector("#heatmap-chart"), heatmapOptions);
        heatmapChart.render();


        // Bar chart distribusi daya
        var barDayaOptions = {
            series: [{
                name: 'Daya Terpakai (Maintenance)',
                data: @json($dayaUsed)
            }, {
                name: 'Cadangan Daya Tersisa',
                data: @json($dayaRemaining)
            }],
            chart: {
                type: 'bar',
                height: 350,
                stacked: true
            },
            xaxis: {
                categories: @json($categoriesDaya)
            },
            colors: ['#dc3545', '#28a745'], // Merah untuk terpakai, hijau untuk sisa aman
            plotOptions: {
                bar: {
                    horizontal: false
                }
            },
            legend: {
                position: 'top'
            }
        };
        var barDayaChart = new ApexCharts(document.querySelector("#bar-daya-chart"), barDayaOptions);
        barDayaChart.render();


        // line chart konvergensi
        var acoLineOptions = {
            series: [{
                name: "Best Fitness Score",
                data: @json($acoHistory ?? [])
            }],
            chart: {
                type: 'line',
                height: 250
            },
            title: {
                text: 'Konvergensi Nilai Score ACO'
            },
            xaxis: {
                categories: Array.from({
                    length: @json(count($acoHistory ?? []))
                }, (_, i) => 'Iterasi ' + (i + 1)),
                title: {
                    text: 'Progres Iterasi'
                }
            }
        };
        new ApexCharts(document.querySelector("#line-aco-chart"), acoLineOptions).render();

        // A* estimasi harga per iterasi
        var astarLineOptions = {
            series: [{
                name: "Total Cost (Juta)",
                data: @json($astarHistory ?? [])
            }],
            chart: {
                type: 'line',
                height: 250
            },
            title: {
                text: 'Estimasi Biaya Pencarian A* per Iterasi'
            },
            colors: ['#ffc107'],
            xaxis: {
                title: {
                    text: 'Node Terbuka'
                }
            }
        };
        new ApexCharts(document.querySelector("#line-astar-chart"), astarLineOptions).render();


        // Network diagram
        // Membuat node Event dan node Periode
        var nodes = [];
        var edges = [];

        // Tambah Node Utama Pemisah/Start (Opsional) atau langsung Event ke Periode
        // Di sini kita hubungkan Event -> ke Periode berdasarkan solusi terbaik ACO
        var eventLabels = @json($event_labels);
        var solutionMatrix = @json($solution);

        // Buat Node untuk Event
        eventLabels.forEach((label, index) => {
            nodes.push({
                id: 'E_' + index,
                label: 'Event ' + label,
                color: '#97C2FC',
                shape: 'diamond'
            });
        });

        // Buat Node untuk Periode
        for (var t = 1; t <= 12; t++) {
            nodes.push({
                id: 'P_' + t,
                label: 'Periode ' + t,
                color: '#FFFF00',
                shape: 'square'
            });
        }

        // Buat Jalur (Edges) berdasarkan kecocokan solusi matriks [event][periode] == 1
        for (var i = 0; i < solutionMatrix.length; i++) {
            for (var t = 0; t < 12; t++) {
                if (solutionMatrix[i][t] == 1) {
                    edges.push({
                        from: 'E_' + i,
                        to: 'P_' + (t + 1),
                        arrows: 'to',
                        label: 'dipilih',
                        color: {
                            color: '#848484',
                            hover: '#007bff'
                        }
                    });
                }
            }
        }

        var container = document.getElementById('network-chart');
        var data = {
            nodes: new vis.DataSet(nodes),
            edges: new vis.DataSet(edges)
        };
        var options = {
            physics: {
                enabled: true,
                barnesHut: {
                    gravitationalConstant: -2000
                }
            },
            interaction: {
                hover: true
            }
        };
        var network = new vis.Network(container, data, options);
    </script>

    <p><a href="{{ route('schedular.form') }}">&larr; Back to Configuration</a></p>

</body>

</html>
