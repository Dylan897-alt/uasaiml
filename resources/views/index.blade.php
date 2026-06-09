<!DOCTYPE html>
<html>

<head>
    <title>ACO Scheduling Result</title>
    <style>
        table {
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        td,
        th {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>Best Solution (Event x Period)</h2>

    <table>
        <tr>
            <th></th>
            @for ($t = 1; $t <= 12; $t++)
                <th>P{{ $t }}</th>
            @endfor
        </tr>

        @for ($i = 0; $i < count($solution); $i++)
            <tr>
                <td>{{ $event_labels[$i] }}</td>
                @for ($t = 0; $t < 12; $t++)
                    <td>{{ $solution[$i][$t] }}</td>
                @endfor
            </tr>
        @endfor
    </table>

    <h2>Distribution per Period</h2>

    @foreach ($distribution as $t => $data)
        <div style="margin-bottom: 10px;">
            <b>Period {{ $t + 1 }}</b><br>
            Used: {{ $data['used'] }} MW <br>
            Remaining: {{ $data['remaining'] }} MW <br>
            Units: {{ implode(', ', $data['units']) }}
        </div>
    @endforeach
    <p><a href="{{ route('schedular.form') }}" style="text-decoration: none; color: #007bff;">&larr; Back to configuration</a></p>

</body>

</html>
