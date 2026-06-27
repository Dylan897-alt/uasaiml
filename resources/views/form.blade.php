<!DOCTYPE html>
<html>

<head>
    <title>ACO Scheduler Input</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f9f9f9;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: inline-block;
            width: 200px;
            font-weight: bold;
        }

        input[type="number"] {
            padding: 5px;
            width: 100px;
        }

        .unit-row {
            background: #fff;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        button {
            padding: 8px 15px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button.add-btn {
            background: #28a745;
        }

        button.remove-btn {
            background: #dc3545;
            margin-left: 10px;
        }
    </style>
</head>

<body>

    <h2>ACO Maintenance Scheduler Configuration</h2>

    <form action="{{ route('schedular.process') }}" method="POST">
        @csrf

        <h3>System Settings</h3>
        <div class="form-group">
            <label>Total Capacity (MW):</label>
            <input type="number" name="total_mw" value="150" required>
        </div>
        <div class="form-group">
            <label>Number of Ants:</label>
            <input type="number" name="num_ants" value="30" required>
        </div>

        <h3>Units Configuration</h3>
        <div class="form-group">
            <label>Load Configuration:</label>
            <select id="load-unit-config" onchange="loadUnitConfiguration(this)">
                <option value="">-- Create New / Clear --</option>
                @foreach ($unitConfigurations as $config)
                    <option value="{{ $config->id }}">{{ $config->name }}</option>
                @endforeach
            </select>
        </div>

        <div id="units-container">
            <div class="unit-row">
                <span><b>Unit 1</b></span> |
                <label style="width:auto; margin-left:10px;">Capacity (MW):</label>
                <input type="number" name="units[0][mw]" value="20" required>

                <label style="width:auto; margin-left:10px;">Maintenance Count (Per 6 Months):</label>
                <input type="number" name="units[0][events]" value="2" min="1" required>
            </div>
        </div>

        <button type="button" class="add-btn" onclick="addUnitRow()">+ Add Unit</button>

        <input type="hidden" name="unit_configuration_name" id="unitConfigName">
        <button type="button" onclick="saveUnitConfiguration()">
            Save Unit Configuration
        </button>
        <br><br>
        <hr>

        <h3>Technician Teams Configuration</h3>
        <div class="form-group">
            <label>Load Configuration:</label>
            <select id="load-technician-config" onchange="loadTechnicianConfiguration(this)">
                <option value="">-- Create New / Clear --</option>
                @foreach ($technicianConfigurations as $config)
                    <option value="{{ $config->id }}">{{ $config->name }}</option>
                @endforeach
            </select>
        </div>

        <div id="teams-container">
            <div class="unit-row">
                <span><b>Team 1</b></span> |

                <label style="width:auto; margin-left:10px;">Team Name:</label>
                <input type="text" name="teams[0][name]" value="Team A" required>

                <label style="width:auto; margin-left:10px;">Specialization:</label>
                <select name="teams[0][type]" onchange="toggleSpecialization(this)">
                    <option value="condition">MW Condition</option>
                    <option value="all">Generalis</option>
                </select>

                <span class="specialization-fields">
                    <select name="teams[0][operator]">
                        <option value=">=">>=</option>
                        <option value="<=">
                            <=< /option>
                    </select>

                    <input type="number" name="teams[0][mw_limit]" value="30" style="width:80px;">
                </span>

                <label style="width:auto; margin-left:10px;">Cost:</label>
                <input type="number" name="teams[0][cost]" value="15" required>
            </div>
        </div>

        <button type="button" class="add-btn" onclick="addTeamRow()">
            + Add Team
        </button>

        <input type="hidden" name="technician_configuration_name" id="technicianConfigName">
        <button type="button" onclick="saveTechnicianConfiguration()">
            Save Technician Configuration
        </button>
        <br><br>
        <hr>

        <button type="submit">
            Run Optimization
        </button>
    </form>

    <script>
        function addUnitRow(mw = '', events = '') {
            const container = document.getElementById('units-container');
            const index = container.children.length; // Mengikuti jumlah baris aktif saat ini

            const row = document.createElement('div');
            row.className = 'unit-row';
            row.innerHTML = `
            <span><b>Unit <span class="unit-index">${index + 1}</span></b></span> | 
            <label style="width:auto; margin-left:10px;">Capacity (MW):</label>
            <input type="number" name="units[${index}][mw]" value="${mw}" placeholder="e.g. 15" required>
            
            <label style="width:auto; margin-left:10px;">Maintenance Count (Per 6 Months):</label>
            <input type="number" name="units[${index}][events]" value="${events}" placeholder="e.g. 2" min="1" required>
            <button type="button" class="remove-btn" onclick="removeUnitRow(this)">Remove</button>
        `;
            container.appendChild(row);
        }

        function removeUnitRow(button) {
            button.parentElement.remove();
            reindexUnitRows();
        }

        function reindexUnitRows() {
            const rows = document.querySelectorAll('#units-container .unit-row');
            rows.forEach((row, index) => {
                row.querySelector('.unit-index').innerText = index + 1;
                row.querySelector('input[name$="[mw]"]').name = `units[${index}][mw]`;
                row.querySelector('input[name$="[events]"]').name = `units[${index}][events]`;
            });
        }

        function loadUnitConfiguration(select) {
            const container = document.getElementById('units-container');
            container.innerHTML = ''; // Kosongkan form unit saat ini

            if (!select.value) {
                addUnitRow(20, 2); // default jika pilih clear
                return;
            }

            const config = unitConfigurations.find(c => c.id == select.value);
            if (config && config.units) {
                config.units.forEach(unit => {
                    addUnitRow(unit.capacity, unit.maintenance_count);
                });
            }
        }

        function addTeamRow(data = null) {
            const container = document.getElementById('teams-container');
            const index = container.children.length;

            // Set default values jika input baru kosongan
            const name = data ? data.team_name : '';
            const type = data ? data.specialization : 'condition';
            const operator = data ? data.operator : '>=';
            const mw_limit = data ? data.mw : '30';
            const cost = data ? data.cost : '';

            const row = document.createElement('div');
            row.className = 'unit-row';
            row.innerHTML = `
            <span><b>Team <span class="team-index">${index + 1}</span></b></span> |

            <label style="width:auto; margin-left:10px;">Team Name:</label>
            <input type="text" name="teams[${index}][name]" value="${name}" placeholder="Team Name" required>

            <label style="width:auto; margin-left:10px;">Specialization:</label>
            <select name="teams[${index}][type]" onchange="toggleSpecialization(this)">
                <option value="condition" ${type === 'condition' ? 'selected' : ''}>MW Condition</option>
                <option value="all" ${type === 'all' ? 'selected' : ''}>Generalis</option>
            </select>

            <span class="specialization-fields" style="display: ${type === 'all' ? 'none' : 'inline'};">
                <select name="teams[${index}][operator]">
                    <option value=">=" ${operator === '>=' ? 'selected' : ''}>&gt;=</option>
                    <option value="<=" ${operator === '<=' ? 'selected' : ''}>&lt;=</option>
                </select>

                <input type="number" name="teams[${index}][mw_limit]" value="${mw_limit}" style="width:80px;">
            </span>

            <label style="width:auto; margin-left:10px;">Cost:</label>
            <input type="number" name="teams[${index}][cost]" value="${cost}" placeholder="Cost" required>

            <button type="button" class="remove-btn" onclick="removeTeamRow(this)">Remove</button>
        `;
            container.appendChild(row);
        }

        function removeTeamRow(button) {
            button.parentElement.remove();
            reindexTeamRows();
        }

        function reindexTeamRows() {
            const rows = document.querySelectorAll('#teams-container .unit-row');
            rows.forEach((row, index) => {
                row.querySelector('.team-index').innerText = index + 1;
                row.querySelector('input[name$="[name]"]').name = `teams[${index}][name]`;
                row.querySelector('select[name$="[type]"]').name = `teams[${index}][type]`;
                row.querySelector('select[name$="[operator]"]').name = `teams[${index}][operator]`;
                row.querySelector('input[name$="[mw_limit]"]').name = `teams[${index}][mw_limit]`;
                row.querySelector('input[name$="[cost]"]').name = `teams[${index}][cost]`;
            });
        }

        function loadTechnicianConfiguration(select) {
            const container = document.getElementById('teams-container');
            container.innerHTML = ''; // Kosongkan form team saat ini

            if (!select.value) {
                addTeamRow(); // default row kosongan
                return;
            }

            const config = technicianConfigurations.find(c => c.id == select.value);
            if (config && config.technicians) {
                config.technicians.forEach(tech => {
                    addTeamRow(tech);
                });
            }
        }

        function toggleSpecialization(select) {
            const fields = select.parentElement.querySelector('.specialization-fields');
            if (select.value === 'all') {
                fields.style.display = 'none';
            } else {
                fields.style.display = 'inline';
            }
        }
    </script>

    <script>
        function saveUnitConfiguration() {
            const name = prompt("Enter Unit Configuration Name");

            if (!name || name.trim() === '') {
                alert("Configuration name is required");
                return;
            }

            document.getElementById('unitConfigName').value = name;

            const form = document.querySelector('form');

            form.action = "{{ route('units.save') }}";
            form.submit();
        }

        function saveTechnicianConfiguration() {
            const name = prompt("Enter Technician Configuration Name");

            if (!name || name.trim() === '') {
                alert("Configuration name is required");
                return;
            }

            document.getElementById('technicianConfigName').value = name;

            const form = document.querySelector('form');

            form.action = "{{ route('technicians.save') }}";
            form.submit();
        }
    </script>

    <script>
        const unitConfigurations =
            @json($unitConfigurations);

        const technicianConfigurations =
            @json($technicianConfigurations);
    </script>
</body>

</html>
