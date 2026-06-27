<!DOCTYPE html>
<html>
<head>
    <title>A* Technician Assignment</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f9f9f9; }
        h2, h3 { color: #333; }
        .card {
            background: #fff; border: 1px solid #ddd;
            border-radius: 6px; padding: 12px; margin-bottom: 10px;
        }
        label { display: inline-block; width: 160px; font-weight: bold; }
        input[type="number"], input[type="text"] {
            padding: 5px; width: 120px; border: 1px solid #ccc; border-radius: 4px;
        }
        button {
            padding: 8px 14px; border: none; border-radius: 4px;
            cursor: pointer; color: #fff;
        }
        .btn-add    { background: #28a745; }
        .btn-remove { background: #dc3545; margin-left: 8px; }
        .btn-submit { background: #007bff; font-size: 15px; margin-top: 10px; }
        .error { color: red; margin-bottom: 10px; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>

<h2>A* Technician Assignment</h2>
<p><a href="{{ route('schedular.form') }}">&larr; Back to ACO Scheduler</a></p>

@if(session('error'))
    <p class="error">{{ session('error') }}</p>
@endif

<form action="{{ route('technician.process') }}" method="POST">
    @csrf

    {{-- TECHNICIAN TEAMS --}}
    <h3>Technician Teams</h3>
    <p style="color:#555;font-size:13px;">
        Specialization: min_mw=0, max_mw=30 → small units (≤30 MW) |
        min_mw=31, max_mw=9999 → large units (>30 MW) |
        min_mw=0, max_mw=9999 → all units
    </p>
    <div id="technician-container">
        <div class="card">
            <label>Team Name:</label>
            <input type="text" name="technicians[0][name]" value="Tim A" required>
            <label style="width:90px;">Cost (juta):</label>
            <input type="number" name="technicians[0][cost]" value="15" step="0.1" required>
            <label style="width:70px;">Min MW:</label>
            <input type="number" name="technicians[0][min_mw]" value="31" required>
            <label style="width:70px;">Max MW:</label>
            <input type="number" name="technicians[0][max_mw]" value="9999" required>
            <button type="button" class="btn-remove" onclick="removeRow(this)">Remove</button>
        </div>
        <div class="card">
            <label>Team Name:</label>
            <input type="text" name="technicians[1][name]" value="Tim B" required>
            <label style="width:90px;">Cost (juta):</label>
            <input type="number" name="technicians[1][cost]" value="8" step="0.1" required>
            <label style="width:70px;">Min MW:</label>
            <input type="number" name="technicians[1][min_mw]" value="0" required>
            <label style="width:70px;">Max MW:</label>
            <input type="number" name="technicians[1][max_mw]" value="30" required>
            <button type="button" class="btn-remove" onclick="removeRow(this)">Remove</button>
        </div>
        <div class="card">
            <label>Team Name:</label>
            <input type="text" name="technicians[2][name]" value="Tim C" required>
            <label style="width:90px;">Cost (juta):</label>
            <input type="number" name="technicians[2][cost]" value="6" step="0.1" required>
            <label style="width:70px;">Min MW:</label>
            <input type="number" name="technicians[2][min_mw]" value="0" required>
            <label style="width:70px;">Max MW:</label>
            <input type="number" name="technicians[2][max_mw]" value="9999" required>
            <button type="button" class="btn-remove" onclick="removeRow(this)">Remove</button>
        </div>
    </div>
    <button type="button" class="btn-add" onclick="addTechnician()">+ Add Team</button>

    {{-- MAINTENANCE EVENTS --}}
    <h3 style="margin-top:30px;">Maintenance Events</h3>
    <p style="color:#555;font-size:13px;">
        Enter each maintenance event: which period it falls on and the MW of that unit.
    </p>
    <div id="maintenance-container">
        <div class="card">
            <label>Label:</label>
            <input type="text" name="maintenances[0][label]" value="Unit 1a" required>
            <label style="width:70px;">Period:</label>
            <input type="number" name="maintenances[0][period]" value="1" min="1" max="12" required>
            <label style="width:70px;">Unit MW:</label>
            <input type="number" name="maintenances[0][unit_mw]" value="20" required>
            <button type="button" class="btn-remove" onclick="removeRow(this)">Remove</button>
        </div>
    </div>
    <button type="button" class="btn-add" onclick="addMaintenance()">+ Add Maintenance Event</button>

    <br><br>
    <button type="submit" class="btn-submit">Run A* Assignment</button>
</form>

<script>
    let techIdx = 3;
    let maintIdx = 1;

    function addTechnician() {
        const container = document.getElementById('technician-container');
        const div = document.createElement('div');
        div.className = 'card';
        div.innerHTML = `
            <label>Team Name:</label>
            <input type="text" name="technicians[${techIdx}][name]" required>
            <label style="width:90px;">Cost (juta):</label>
            <input type="number" name="technicians[${techIdx}][cost]" step="0.1" required>
            <label style="width:70px;">Min MW:</label>
            <input type="number" name="technicians[${techIdx}][min_mw]" value="0" required>
            <label style="width:70px;">Max MW:</label>
            <input type="number" name="technicians[${techIdx}][max_mw]" value="9999" required>
            <button type="button" class="btn-remove" onclick="removeRow(this)">Remove</button>
        `;
        container.appendChild(div);
        techIdx++;
    }

    function addMaintenance() {
        const container = document.getElementById('maintenance-container');
        const div = document.createElement('div');
        div.className = 'card';
        div.innerHTML = `
            <label>Label:</label>
            <input type="text" name="maintenances[${maintIdx}][label]" required>
            <label style="width:70px;">Period:</label>
            <input type="number" name="maintenances[${maintIdx}][period]" min="1" max="12" required>
            <label style="width:70px;">Unit MW:</label>
            <input type="number" name="maintenances[${maintIdx}][unit_mw]" required>
            <button type="button" class="btn-remove" onclick="removeRow(this)">Remove</button>
        `;
        container.appendChild(div);
        maintIdx++;
    }

    function removeRow(btn) {
        btn.closest('.card').remove();
    }
</script>

</body>
</html>