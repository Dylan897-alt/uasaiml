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
        <br><br>
        <hr>
        <br>
        <button type="submit">Run ACO Optimization</button>
    </form>

    <script>
        let unitCount = 1;

        function addUnitRow() {
            unitCount++;
            const container = document.getElementById('units-container');
            const row = document.createElement('div');
            row.className = 'unit-row';
            row.innerHTML = `
                <span><b>Unit ${unitCount}</b></span> | 
                <label style="width:auto; margin-left:10px;">Capacity (MW):</label>
                <input type="number" name="units[${unitCount-1}][mw]" placeholder="e.g. 15" required>
                
                <label style="width:auto; margin-left:10px;">Maintenance Count (Per 6 Months):</label>
                <input type="number" name="units[${unitCount-1}][events]" placeholder="e.g. 2" min="1" required>
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
            `;
            container.appendChild(row);
        }
    </script>
</body>

</html>
