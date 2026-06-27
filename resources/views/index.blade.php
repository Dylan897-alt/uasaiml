<!DOCTYPE html>
<html>
<head>
    <title>Optimization Result</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f9f9f9; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 6px; }
        h3 { color: #555; }
        table { border-collapse: collapse; margin-bottom: 20px; width: 100%; }
        td, th { border: 1px solid #ccc; padding: 6px 10px; text-align: center; }
        th { background: #007bff; color: #fff; }
        tr:nth-child(even) { background: #f0f4ff; }
        .section { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 30px; }
        .total { font-size: 18px; font-weight: bold; color: #28a745; margin-bottom: 16px; }
        .overtime-badge { background: #dc3545; color: #fff; border-radius: 4px; font-size: 11px; padding: 2px 6px; margin-left: 4px; }
        .dist-row { margin-bottom: 8px; padding: 8px; background: #fff; border: 1px solid #eee; border-radius: 4px; }
        .ok { color: #28a745; font-weight: bold; }
        .warn { color: #ffc107; font-weight: bold; }
        .bad { color: #dc3545; font-weight: bold; }
        a { color: #007bff; text-decoration: none; }
        .no-astar { color: #888; font-style: italic; }
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
        Remaining: <span class="
            {{ $data['remaining'] >= 115 ? 'ok' : ($data['remaining'] >= 100 ? 'warn' : 'bad') }}
        ">{{ $data['remaining'] }} MW</span>
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

<p><a href="{{ route('schedular.form') }}">&larr; Back to Configuration</a></p>

</body>
</html>