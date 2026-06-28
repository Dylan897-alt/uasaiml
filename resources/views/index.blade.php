<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ACO Maintenance Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050913;
            --card: #09111f;
            --text: #edf5ff;
            --muted: #8ea5cb;
            --accent: #5df0ff;
            --accent2: #ff64ff;
            --good: #58ffa3;
            --warn: #ffc75f;
            --bad: #ff5ea7;
            --shadow: 0 30px 60px rgba(0, 0, 0, .34);
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top left, rgba(93, 240, 255, .12), transparent 24%),
                radial-gradient(circle at bottom right, rgba(255, 100, 255, .12), transparent 18%),
                linear-gradient(180deg, #02040a 0%, #070d1f 100%);
        }

        * {
            box-sizing: border-box;
        }

        .app-shell {
            width: min(1240px, calc(100% - 36px));
            margin: 0 auto;
            padding: 28px 0 42px;
        }

        .topbar {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .brand h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.6vw, 3rem);
            letter-spacing: .02em;
        }

        .brand p {
            margin: 0;
            color: var(--muted);
            font-size: .98rem;
            max-width: 720px;
            line-height: 1.7;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .06);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, .12);
            text-decoration: none;
        }

        .summary-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 28px;
        }

        .summary-card {
            border-radius: 24px;
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: var(--shadow);
            padding: 26px;
        }

        .summary-card h3 {
            margin: 0 0 12px;
            font-size: 1rem;
            color: var(--muted);
            letter-spacing: .01em;
        }

        .summary-card p {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.05;
        }

        .dashboard-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: 2fr 1fr;
            margin-bottom: 28px;
        }

        .card {
            position: relative;
            border-radius: 30px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: var(--shadow);
            padding: 28px;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(140deg, rgba(93, 240, 255, .08), transparent 45%);
            pointer-events: none;
            opacity: .7;
        }

        .card > * {
            position: relative;
            z-index: 1;
        }

        h2 {
            margin: 0 0 16px;
            font-size: 1.6rem;
            letter-spacing: .01em;
        }

        h3 {
            margin: 0 0 12px;
            font-size: 1.05rem;
            color: var(--muted);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            color: var(--accent);
            border: 1px solid rgba(93, 240, 255, .16);
            background: rgba(93, 240, 255, .06);
            font-size: .92rem;
            margin-bottom: 20px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 640px;
        }

        th,
        td {
            padding: 12px 14px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        th {
            color: var(--accent);
            font-weight: 700;
            text-transform: uppercase;
            font-size: .83rem;
            letter-spacing: .08em;
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, .03);
        }

        .status-ok {
            color: var(--good);
            font-weight: 700;
        }

        .status-warn {
            color: var(--warn);
            font-weight: 700;
        }

        .status-bad {
            color: var(--bad);
            font-weight: 700;
        }

        .chart-container {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 26px;
            padding: 18px;
            margin-bottom: 22px;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .02);
        }

        .chart-title {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 1rem;
        }

        #network-chart {
            height: 380px;
            border-radius: 20px;
            background: rgba(0, 0, 0, .12);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .padded-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 18px;
        }

        .padded-row > div {
            flex: 1 1 200px;
            min-width: 180px;
        }

        .value-card {
            border-radius: 20px;
            padding: 18px;
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .value-card p {
            margin: 0;
            color: var(--muted);
            font-size: .95rem;
        }

        .value-card strong {
            display: block;
            margin-top: 9px;
            font-size: 1.5rem;
            color: var(--text);
        }

        .no-astar {
            color: var(--muted);
            font-style: italic;
        }

        .overtime-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 9px;
            border-radius: 999px;
            background: rgba(255, 94, 167, .16);
            color: var(--bad);
            font-size: .78rem;
            white-space: nowrap;
        }

        @media (max-width: 980px) {
            .summary-grid,
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
</head>

<body>
    <div class="app-shell">
        <div class="topbar">
            <div class="brand">
                <h1>ACO Maintenance Dashboard</h1>
                <p>Ringkasan hasil ACO dan A* untuk jadwal maintenance unit serta penugasan tim teknisi.</p>
            </div>
            <a href="{{ route('schedular.form') }}" class="back-link">&larr; Kembali ke Konfigurasi</a>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total Capacity</h3>
                <p>{{ $total_mw ?? 150 }} MW</p>
            </div>
            <div class="summary-card">
                <h3>Ant Count</h3>
                <p>{{ $num_ants ?? 0 }}</p>
            </div>
            <div class="summary-card">
                <h3>Unit Count</h3>
                <p>{{ $num_units ?? 0 }}</p>
            </div>
            <div class="summary-card">
                <h3>Team Count</h3>
                <p>{{ $num_teams ?? 0 }}</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="pill">ACO Maintenance Schedule</div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Event</th>
                                @for ($t = 1; $t <= 12; $t++)
                                    <th>P{{ $t }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solution as $i => $row)
                                <tr>
                                    <td><strong>{{ $event_labels[$i] }}</strong></td>
                                    @foreach ($row as $value)
                                        <td>{!! $value == 1 ? '<span class="status-ok">✓</span>' : '&mdash;' !!}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="padded-row" style="margin-top:20px;">
                    @foreach ($distribution as $t => $data)
                        <div class="value-card">
                            <p>Period {{ $t + 1 }}</p>
                            <strong>{{ $data['used'] }} MW</strong>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="pill">A* Technician Assignment</div>
                @if ($astar)
                    <div class="value-card" style="margin-bottom:18px;">
                        <p>Total Cost</p>
                        <strong>Rp {{ number_format($astar['total_cost'], 2) }} juta</strong>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Event</th>
                                    <th>Period</th>
                                    <th>Team</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
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
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="no-astar">Tidak ada konfigurasi tim teknisi. Tambahkan tim dan jalankan ulang optimasi.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <h2>Visualisasi Hasil</h2>
            <div class="chart-container">
                <h3 class="chart-title">Heatmap Jadwal Maintenance</h3>
                <div id="heatmap-chart"></div>
            </div>
            <div class="chart-container">
                <h3 class="chart-title">Distribusi Daya per Periode</h3>
                <div id="bar-daya-chart"></div>
            </div>
            <div class="chart-container">
                <h3 class="chart-title">Konvergensi ACO & A*</h3>
                <div style="display:flex; flex-wrap:wrap; gap:20px;">
                    <div id="line-aco-chart" style="flex:1; min-width:280px; height:280px;"></div>
                    <div id="line-astar-chart" style="flex:1; min-width:280px; height:280px;"></div>
                </div>
            </div>
            <div class="chart-container">
                <h3 class="chart-title">Network Diagram Lintasan Semut</h3>
                <div id="network-chart"></div>
            </div>
        </div>
    </div>

    <script>
        const heatmapOptions = {
            series: @json($heatmapData),
            chart: {
                height: 340,
                type: 'heatmap',
                toolbar: { show: false }
            },
            plotOptions: {
                heatmap: { shadeIntensity: 0.6, radius: 4 }
            },
            dataLabels: { enabled: false },
            colors: ['#00d4ff'],
            legend: { position: 'top' },
            xaxis: { type: 'category', labels: { style: { colors: '#b8c7e0' } } },
            yaxis: { labels: { style: { colors: '#b8c7e0' } } },
            tooltip: { theme: 'dark' }
        };
        new ApexCharts(document.querySelector('#heatmap-chart'), heatmapOptions).render();

        const barDayaOptions = {
            series: [{ name: 'Used MW', data: @json($dayaUsed) }, { name: 'Remaining MW', data: @json($dayaRemaining) }],
            chart: { type: 'bar', height: 340, stacked: true, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: false, borderRadius: 12 } },
            colors: ['#ff5ea7', '#58ffa3'],
            xaxis: { categories: @json($categoriesDaya), labels: { style: { colors: '#b8c7e0' } } },
            legend: { position: 'top' },
            tooltip: { theme: 'dark' }
        };
        new ApexCharts(document.querySelector('#bar-daya-chart'), barDayaOptions).render();

        const acoLineOptions = {
            series: [{ name: 'Best Score', data: @json($acoHistory ?? []) }],
            chart: { type: 'line', height: 280, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4 },
            colors: ['#5df0ff'],
            xaxis: { categories: Array.from({ length: @json(count($acoHistory ?? [])) }, (_, i) => 'Iterasi ' + (i + 1)), labels: { style: { colors: '#b8c7e0' } } },
            tooltip: { theme: 'dark' }
        };
        new ApexCharts(document.querySelector('#line-aco-chart'), acoLineOptions).render();

        const astarLineOptions = {
            series: [{ name: 'Cost', data: @json($astarHistory ?? []) }],
            chart: { type: 'line', height: 280, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4 },
            colors: ['#ff64ff'],
            xaxis: { labels: { style: { colors: '#b8c7e0' } } },
            tooltip: { theme: 'dark' }
        };
        new ApexCharts(document.querySelector('#line-astar-chart'), astarLineOptions).render();

        const nodes = [];
        const edges = [];
        const eventLabels = @json($event_labels);
        const solutionMatrix = @json($solution);

        eventLabels.forEach((label, index) => {
            nodes.push({ id: 'E_' + index, label: 'Event ' + label, color: '#8bc7ff', shape: 'diamond' });
        });

        for (let t = 1; t <= 12; t++) {
            nodes.push({ id: 'P_' + t, label: 'P' + t, color: '#ff98ff', shape: 'box' });
        }

        solutionMatrix.forEach((row, i) => {
            row.forEach((value, t) => {
                if (value === 1) {
                    edges.push({ from: 'E_' + i, to: 'P_' + (t + 1), arrows: 'to', color: { color: '#a8edff', highlight: '#5df0ff' }, smooth: true });
                }
            });
        });

        const container = document.getElementById('network-chart');
        const data = { nodes: new vis.DataSet(nodes), edges: new vis.DataSet(edges) };
        const options = { physics: { enabled: true, barnesHut: { gravitationalConstant: -2000, springLength: 120 } }, interaction: { hover: true } };
        new vis.Network(container, data, options);
    </script>
</body>

</html>
