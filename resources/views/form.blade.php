<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance Scheduler</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f9fc;
            --bg-soft: #eef3f8;
            --card: #ffffff;
            --card-strong: #f6f8fb;
            --text: #101828;
            --muted: #667085;
            --border: #d0d5dd;
            --accent: #2563eb;
            --accent2: #db2777;
            --danger: #e11d48;
            --success: #059669;
            --shadow: 0 24px 54px rgba(16, 24, 40, .12);
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top left, rgba(37, 99, 235, .08), transparent 25%),
                radial-gradient(circle at top right, rgba(219, 39, 119, .07), transparent 18%),
                linear-gradient(180deg, var(--bg) 0%, var(--bg-soft) 100%);
            overflow-x: hidden;
        }

        body.dark {
            --bg: #02040a;
            --bg-soft: #0a1123;
            --card: #0f172d;
            --card-strong: #12203a;
            --text: #edf5ff;
            --muted: #9bb7dc;
            --border: #1d2944;
            --accent: #5df0ff;
            --accent2: #ff64ff;
            --danger: #ff5ea7;
            --success: #58ffa3;
            --shadow: 0 30px 60px rgba(0, 0, 0, .38);
            background: radial-gradient(circle at top left, rgba(93, 240, 255, .14), transparent 25%),
                radial-gradient(circle at top right, rgba(255, 100, 255, .12), transparent 18%),
                linear-gradient(180deg, #02040a 0%, #070d1f 100%);
        }

        * {
            box-sizing: border-box;
        }

        .app-shell {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 40px;
        }

        .topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(93, 240, 255, .24), rgba(255, 100, 255, .24));
            color: var(--accent);
            font-weight: 700;
            box-shadow: 0 0 28px rgba(93, 240, 255, .24);
        }

        .brand h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.6vw, 2.8rem);
            line-height: 1.05;
        }

        .brand p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: .98rem;
            max-width: 520px;
        }

        .theme-switch {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 152px;
            padding: 10px 16px 10px 10px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            cursor: pointer;
            transition: background .22s ease;
            box-shadow: 0 10px 26px rgba(16, 24, 40, .08);
            font: inherit;
            font-weight: 600;
        }

        .theme-switch:hover {
            background: var(--card-strong);
        }

        .theme-icon {
            display: inline-flex;
            position: relative;
            align-items: center;
            justify-content: center;
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: #f59e0b;
            box-shadow: 0 0 0 7px rgba(245, 158, 11, .16);
            flex: 0 0 auto;
        }

        .theme-icon::after {
            content: '';
            position: absolute;
            width: 13px;
            height: 13px;
            border-radius: 999px;
            background: transparent;
            transform: translateX(18px);
            transition: transform .2s ease, background .2s ease;
        }

        body.dark .theme-icon {
            background: #e5e7eb;
            box-shadow: 0 0 0 7px rgba(229, 231, 235, .14);
        }

        body.dark .theme-icon::after {
            background: var(--card);
            transform: translateX(5px) translateY(-3px);
        }

        #themeLabel {
            min-width: 78px;
        }

        .hero {
            position: relative;
            overflow: hidden;
            border-radius: 30px;
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 42px;
            margin-bottom: 32px;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(93, 240, 255, .12), transparent 22%),
                radial-gradient(circle at bottom right, rgba(255, 100, 255, .1), transparent 20%);
            pointer-events: none;
        }

        .hero-grid {
            display: grid;
            gap: 24px;
            grid-template-columns: 1.2fr .9fr;
            align-items: center;
        }

        .hero-text h2 {
            margin: 0 0 18px;
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.05;
        }

        .hero-text p {
            margin: 0;
            color: var(--muted);
            max-width: 640px;
            line-height: 1.8;
            font-size: 1rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 26px;
        }

        .btn,
        .ghost-btn,
        .btn-secondary {
            font: inherit;
            border: none;
            outline: none;
            border-radius: 999px;
            cursor: pointer;
            transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn {
            padding: 16px 28px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #050814;
            box-shadow: 0 18px 45px rgba(93, 240, 255, .28);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 24px 60px rgba(93, 240, 255, .32);
        }

        .ghost-btn,
        .btn-secondary {
            padding: 14px 24px;
            color: var(--text);
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .ghost-btn:hover,
        .btn-secondary:hover {
            background: rgba(255, 255, 255, .08);
        }

        .feature-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 32px;
        }

        .feature-card {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            background: var(--card);
            border: 1px solid var(--border);
            padding: 24px;
            min-height: 170px;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .02);
        }

        .feature-card h3 {
            margin: 0 0 12px;
            font-size: 1.05rem;
            color: var(--text);
        }

        .feature-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.75;
        }

        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            color: var(--accent);
            font-size: .92rem;
        }

        .feature-pill::before {
            content: '›';
            color: var(--accent2);
        }

        .stepper {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-bottom: 28px;
        }

        .feature-pill::before {
            content: '>';
        }

        .stepper button {
            flex: 1 1 160px;
            padding: 14px 18px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
        }

        .stepper button.active {
            background: linear-gradient(135deg, rgba(93, 240, 255, .15), rgba(255, 100, 255, .15));
            color: var(--text);
            box-shadow: 0 16px 35px rgba(93, 240, 255, .13);
        }

        .stepper button:hover {
            transform: translateY(-1px);
        }

        .step-section {
            display: none;
            opacity: 0;
            transform: translateY(20px);
            transition: all .35s ease;
        }

        .step-section.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .card {
            position: relative;
            border-radius: 28px;
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 28px;
            margin-bottom: 28px;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(150deg, rgba(93, 240, 255, .1), transparent 35%, rgba(255, 100, 255, .08));
            pointer-events: none;
            opacity: .62;
        }

        .card > * {
            position: relative;
            z-index: 1;
        }

        .card h2 {
            margin-top: 0;
            font-size: 1.45rem;
            letter-spacing: .01em;
        }

        .grid-2 {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .form-group {
            display: grid;
            gap: 10px;
        }

        .form-group label {
            color: var(--muted);
            font-size: .95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px 16px;
            background: var(--card-strong);
            color: var(--text);
            outline: none;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .02);
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: rgba(93, 240, 255, .45);
            background: rgba(255, 255, 255, .08);
        }

        .item-card {
            display: grid;
            gap: 16px;
            padding: 20px 22px;
            border-radius: 22px;
            border: 1px solid var(--border);
            background: color-mix(in srgb, var(--card-strong) 78%, transparent);
        }

        .item-card header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .item-card header h3 {
            margin: 0;
            font-size: 1rem;
            color: var(--text);
        }

        .item-card header .note {
            color: var(--muted);
            font-size: .9rem;
            margin-top: 6px;
        }

        .item-card button.remove-btn {
            border: none;
            outline: none;
            background: rgba(255, 94, 167, .14);
            color: #ff9fc7;
            padding: 10px 16px;
            border-radius: 14px;
            cursor: pointer;
            transition: background .22s ease;
        }

        .item-card button.remove-btn:hover {
            background: rgba(255, 94, 167, .24);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .note {
            color: var(--muted);
            font-size: .95rem;
        }

        .alert {
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(93, 240, 255, .12);
            color: var(--text);
        }

        .alert-success {
            border-color: rgba(5, 150, 105, .24);
            background: rgba(5, 150, 105, .1);
        }

        .alert-error {
            border-color: rgba(225, 29, 72, .28);
            background: rgba(225, 29, 72, .1);
        }

        .condition-row {
            display: grid;
            grid-template-columns: minmax(96px, 120px) minmax(0, 1fr);
            gap: 10px;
        }

        .toast {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 30;
            width: min(360px, calc(100vw - 32px));
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid rgba(225, 29, 72, .28);
            background: color-mix(in srgb, var(--card) 94%, transparent);
            color: var(--text);
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(14px);
            pointer-events: none;
            transition: opacity .2s ease, transform .2s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 20;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(2, 6, 23, .58);
            backdrop-filter: blur(10px);
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-panel {
            width: min(460px, 100%);
            border-radius: 24px;
            border: 1px solid var(--border);
            background: var(--card);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .modal-panel h3 {
            margin: 0 0 8px;
            font-size: 1.25rem;
        }

        .modal-panel p {
            margin: 0 0 18px;
            color: var(--muted);
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .small-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(93, 240, 255, .08);
            color: var(--accent);
            font-size: .92rem;
        }

        @media (max-width: 920px) {
            .hero-grid,
            .grid-2 {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .stepper button {
                flex: 1 1 100%;
            }
        }
    </style>
</head>

<body class="light">
    <div class="app-shell">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="topbar">
            <div class="brand">
                <div>
                    <h1>Maintenance Scheduler</h1>
                    <p>Optimasi jadwal maintenance unit dan penugasan tim teknisi dalam satu alur terstruktur.</p>
                </div>
            </div>
            <button class="theme-switch" id="themeToggle" type="button">
                <span class="theme-icon" aria-hidden="true"></span>
                <span id="themeLabel">Light Mode</span>
            </button>
        </div>

        <section id="step-0" class="step-section active">
            <div class="hero">
                <div class="hero-grid">
                    <div class="hero-text">
                        <h2>Selamat datang di sistem optimasi pemeliharaan</h2>
                        <p>Website ini membantu Anda mengatur konfigurasi unit, memasukkan tim teknisi, dan menjalankan algoritma ACO + A* untuk menghasilkan jadwal maintenance yang lebih efisien dan terkontrol.</p>
                        <div class="hero-actions">
                            <button class="btn" type="button" onclick="goToStep(1)">Mulai</button>
                            <button class="ghost-btn" type="button" onclick="scrollToFeatures()">Lihat Fitur</button>
                        </div>
                    </div>
                    <div class="feature-grid">
                        <div class="feature-card">
                            <h3>Input Unit Configuration</h3>
                            <p>Masukkan banyak unit maintenance, kapasitas MW, dan jumlah kebutuhan maintenance setiap periode.</p>
                            <div class="feature-pill">Atur unit secara dinamis</div>
                        </div>
                        <div class="feature-card">
                            <h3>Input Teams Configuration</h3>
                            <p>Tambahkan tim teknisi dengan nama, spesialisasi kondisi MW, dan biaya penugasan.</p>
                            <div class="feature-pill">Tentukan tim terbaik</div>
                        </div>
                        <div class="feature-card">
                            <h3>Run Optimization</h3>
                            <p>Jalankan ACO untuk jadwal dan A* untuk assignment dengan hasil dashboard yang lengkap.</p>
                            <div class="feature-pill">Visualisasi modern</div>
                        </div>
                        <div class="feature-card">
                            <h3>Dark / Light Mode</h3>
                            <p>Pilih tema sesuai preferensi dengan tampilan neon yang bergerak dan kontras tinggi.</p>
                            <div class="feature-pill">Tema neon</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <form id="schedulerForm" action="{{ route('schedular.process') }}" method="POST">
            @csrf
            <section id="step-1" class="step-section">
                 <h1>ACO Maintenance Scheduler</h1>
                <div class="stepper">
                    <button class="active" type="button">1. Input Unit Configuration</button>
                    <button type="button">2. Input Teams Configuration</button>
                </div>

                <div class="card">
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="total_mw">Total Capacity (MW)</label>
                            <input id="total_mw" type="number" name="total_mw" value="150" required>
                        </div>
                        <div class="form-group">
                            <label for="num_ants">Number of Ants</label>
                            <input id="num_ants" type="number" name="num_ants" value="30" required>
                        </div>
                    </div>
                    <p class="note">Masukkan total kapasitas dan jumlah semut untuk algoritma ACO.</p>
                </div>

                <div class="card">
                    <header class="card-header">
                        <div>
                            <h2>Input Unit Configuration</h2>
                        </div>
                        <span class="small-pill">Step 1 of 2</span>
                    </header>
                    <div class="alert">Tambahkan konfigurasi banyak unit, kapasitas MW, dan frekuensi maintenance per 6 bulan.</div>
                    <div id="units-container"></div>
                    <div class="actions">
                        <button class="btn-secondary" type="button" onclick="addUnitRow()">+ Tambah Unit</button>
                        <button class="btn" type="button" onclick="saveUnitConfiguration()">Save Unit Configuration</button>
                    </div>
                </div>

                <div class="actions">
                    <button class="btn-secondary" type="button" onclick="goToStep(0)">Kembali</button>
                    <button class="btn" type="button" onclick="goToStep(2)">Next</button>
                </div>
            </section>

            <section id="step-2" class="step-section">
                <h1>A* Maintenance Scheduler</h1>
                <div class="stepper">
                    <button type="button">1. Input Unit Configuration</button>
                    <button class="active" type="button">2. Input Teams Configuration</button>
                </div>

                <div class="card">
                    <header class="card-header">
                        <div>
                            <h2>Input Teams Configuration</h2>
                        </div>
                        <span class="small-pill">Step 2 of 2</span>
                    </header>
                    <div class="alert">Masukkan tim teknisi, nama tim, spesialisasi MW, dan biaya.</div>
                    <div id="teams-container"></div>
                    <div class="actions">
                        <button class="btn-secondary" type="button" onclick="addTeamRow()">+ Tambah Tim</button>
                        <button class="btn" type="button" onclick="saveTechnicianConfiguration()">Save Technician Configuration</button>
                    </div>
                </div>

                <div class="actions">
                    <button class="btn-secondary" type="button" onclick="goToStep(1)">Back</button>
                    <button class="btn" type="button" onclick="submitOptimization()">Run Optimization</button>
                </div>
            </section>

            <input type="hidden" name="unit_configuration_name" id="unitConfigName">
            <input type="hidden" name="technician_configuration_name" id="technicianConfigName">
        </form>
    </div>

    <div class="modal-backdrop" id="configNameModal" aria-hidden="true">
        <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="configNameTitle">
            <h3 id="configNameTitle">Simpan Konfigurasi</h3>
            <p id="configNameDescription">Masukkan nama konfigurasi sebelum menyimpan.</p>
            <div class="form-group">
                <label for="configNameInput">Configuration Name</label>
                <input id="configNameInput" type="text" autocomplete="off">
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" type="button" id="configNameCancel">Cancel</button>
                <button class="btn" type="button" id="configNameConfirm">Save</button>
            </div>
        </div>
    </div>

    <div class="toast" id="appToast" role="status" aria-live="polite"></div>

    <script>
        const unitConfigurations = @json($unitConfigurations);
        const technicianConfigurations = @json($technicianConfigurations);

        const themeToggle = document.getElementById('themeToggle');
        const themeLabel = document.getElementById('themeLabel');
        const appToast = document.getElementById('appToast');
        const configNameModal = document.getElementById('configNameModal');
        const configNameTitle = document.getElementById('configNameTitle');
        const configNameDescription = document.getElementById('configNameDescription');
        const configNameInput = document.getElementById('configNameInput');
        const configNameCancel = document.getElementById('configNameCancel');
        const configNameConfirm = document.getElementById('configNameConfirm');
        let toastTimer = null;
        let configNameResolver = null;
        const draftKey = 'maintenanceSchedulerDraft';
        const savedStateKey = 'maintenanceSchedulerSavedState';
        let isRestoringDraft = false;

        function setTheme(theme) {
            document.body.classList.remove('light', 'dark');
            document.body.classList.add(theme);
            themeLabel.textContent = theme === 'light' ? 'Light Mode' : 'Dark Mode';
            localStorage.setItem('maintenanceSchedulerTheme', theme);
        }

        function toggleTheme() {
            const next = document.body.classList.contains('light') ? 'dark' : 'light';
            setTheme(next);
        }

        themeToggle.addEventListener('click', toggleTheme);

        function goToStep(step) {
            document.querySelectorAll('.step-section').forEach(section => section.classList.remove('active'));
            document.getElementById('step-' + step).classList.add('active');
            saveDraft();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function scrollToFeatures() {
            document.querySelector('.hero').scrollIntoView({ behavior: 'smooth' });
        }

        function showToast(message) {
            window.clearTimeout(toastTimer);
            appToast.textContent = message;
            appToast.classList.add('show');
            toastTimer = window.setTimeout(() => {
                appToast.classList.remove('show');
            }, 3200);
        }

        function getSavedState() {
            try {
                return JSON.parse(sessionStorage.getItem(savedStateKey)) || { unit: false, technician: false };
            } catch (error) {
                return { unit: false, technician: false };
            }
        }

        function setSavedState(nextState) {
            sessionStorage.setItem(savedStateKey, JSON.stringify({
                ...getSavedState(),
                ...nextState,
            }));
        }

        function collectRows(containerSelector) {
            return Array.from(document.querySelectorAll(`${containerSelector} .item-card`)).map(item => {
                const values = {};
                item.querySelectorAll('input, select').forEach(field => {
                    const key = field.name.match(/\[([^\]]+)\]$/)?.[1];
                    if (key) values[key] = field.value;
                });
                return values;
            });
        }

        function saveDraft() {
            if (isRestoringDraft) return;
            sessionStorage.setItem(draftKey, JSON.stringify({
                step: Number(document.querySelector('.step-section.active')?.id?.replace('step-', '') || 0),
                total_mw: document.getElementById('total_mw')?.value || '',
                num_ants: document.getElementById('num_ants')?.value || '',
                units: collectRows('#units-container'),
                teams: collectRows('#teams-container'),
            }));
        }

        function restoreDraft() {
            const rawDraft = sessionStorage.getItem(draftKey);
            if (!rawDraft) return false;

            try {
                const draft = JSON.parse(rawDraft);
                isRestoringDraft = true;
                document.getElementById('total_mw').value = draft.total_mw ?? 150;
                document.getElementById('num_ants').value = draft.num_ants ?? 30;

                document.getElementById('units-container').innerHTML = '';
                (draft.units?.length ? draft.units : [{ mw: 20, events: 2 }]).forEach(unit => {
                    addUnitRow(unit.mw, unit.events);
                });

                document.getElementById('teams-container').innerHTML = '';
                (draft.teams?.length ? draft.teams : [{ name: '', type: 'condition', operator: '>=', mw_limit: 30, cost: '' }]).forEach(team => {
                    addTeamRow({
                        team_name: team.name,
                        specialization: team.type,
                        operator: team.operator,
                        mw: team.mw_limit,
                        cost: team.cost,
                    });
                });
                isRestoringDraft = false;
                return Number.isInteger(draft.step) ? draft.step : false;
            } catch (error) {
                isRestoringDraft = false;
                return false;
            }
        }

        function addUnitRow(mw = '', events = '') {
            const container = document.getElementById('units-container');
            const index = container.children.length;
            const item = document.createElement('section');
            item.className = 'item-card';
            item.innerHTML = `
                <header>
                    <div>
                        <h3>Unit ${index + 1}</h3>
                        <div class="note">Kapasitas dalam MW dan maintenance count per 6 bulan.</div>
                    </div>
                    <button type="button" class="remove-btn" onclick="removeUnitRow(this)">Remove</button>
                </header>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Capacity (MW)</label>
                        <input type="number" name="units[${index}][mw]" value="${mw}" required>
                    </div>
                    <div class="form-group">
                        <label>Maintenance Count</label>
                        <input type="number" name="units[${index}][events]" value="${events}" required>
                    </div>
                </div>
            `;
            container.appendChild(item);
            saveDraft();
        }

        function removeUnitRow(button) {
            button.closest('.item-card').remove();
            reindexUnitRows();
            setSavedState({ unit: false });
            saveDraft();
        }

        function reindexUnitRows() {
            document.querySelectorAll('#units-container .item-card').forEach((item, index) => {
                item.querySelector('h3').textContent = 'Unit ' + (index + 1);
                item.querySelector('input[name$="[mw]"]').name = `units[${index}][mw]`;
                item.querySelector('input[name$="[events]"]').name = `units[${index}][events]`;
            });
        }

        function loadUnitConfiguration(select) {
            const container = document.getElementById('units-container');
            container.innerHTML = '';
            if (!select.value) {
                addUnitRow(20, 2);
                return;
            }
            const config = unitConfigurations.find(c => c.id == select.value);
            if (config && config.units) {
                config.units.forEach(unit => addUnitRow(unit.capacity, unit.maintenance_count));
            }
        }

        function addTeamRow(data = null) {
            const container = document.getElementById('teams-container');
            const index = container.children.length;
            const name = data ? data.team_name : '';
            const type = data ? data.specialization : 'condition';
            const operator = data ? data.operator : '>=';
            const mw_limit = data ? data.mw : 30;
            const cost = data ? data.cost : '';
            const item = document.createElement('section');
            item.className = 'item-card';
            item.innerHTML = `
                <header>
                    <div>
                        <h3>Team ${index + 1}</h3>
                        <div class="note">Nama tim, spesialisasi, dan biaya penugasan.</div>
                    </div>
                    <button type="button" class="remove-btn" onclick="removeTeamRow(this)">Remove</button>
                </header>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Team Name</label>
                        <input type="text" name="teams[${index}][name]" value="${name}" required>
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <select name="teams[${index}][type]" onchange="toggleSpecialization(this)">
                            <option value="condition" ${type === 'condition' ? 'selected' : ''}>MW Condition</option>
                            <option value="all" ${type === 'all' ? 'selected' : ''}>General</option>
                        </select>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group specialization-group" style="display: ${type === 'all' ? 'none' : 'block'};">
                        <label>MW Condition</label>
                        <div class="condition-row">
                            <select name="teams[${index}][operator]">
                                <option value=">=" ${operator === '>=' ? 'selected' : ''}>&gt;=</option>
                                <option value="<=" ${operator === '<=' ? 'selected' : ''}>&lt;=</option>
                            </select>
                            <input type="number" name="teams[${index}][mw_limit]" value="${mw_limit}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Cost</label>
                        <input type="number" name="teams[${index}][cost]" value="${cost}" required>
                    </div>
                </div>
            `;
            container.appendChild(item);
            toggleSpecialization(item.querySelector('select[name$="[type]"]'));
            saveDraft();
        }

        function removeTeamRow(button) {
            button.closest('.item-card').remove();
            reindexTeamRows();
            setSavedState({ technician: false });
            saveDraft();
        }

        function reindexTeamRows() {
            document.querySelectorAll('#teams-container .item-card').forEach((item, index) => {
                item.querySelector('h3').textContent = 'Team ' + (index + 1);
                item.querySelector('input[name$="[name]"]').name = `teams[${index}][name]`;
                item.querySelector('select[name$="[type]"]').name = `teams[${index}][type]`;
                item.querySelector('select[name$="[operator]"]').name = `teams[${index}][operator]`;
                item.querySelector('input[name$="[mw_limit]"]').name = `teams[${index}][mw_limit]`;
                item.querySelector('input[name$="[cost]"]').name = `teams[${index}][cost]`;
            });
        }

        function loadTechnicianConfiguration(select) {
            const container = document.getElementById('teams-container');
            container.innerHTML = '';
            if (!select.value) {
                addTeamRow();
                return;
            }
            const config = technicianConfigurations.find(c => c.id == select.value);
            if (config && config.technicians) {
                config.technicians.forEach(tech => addTeamRow(tech));
            }
        }

        function toggleSpecialization(select) {
            const card = select.closest('.item-card');
            const group = card.querySelector('.specialization-group');
            const isGeneral = select.value === 'all';
            group.style.display = isGeneral ? 'none' : 'block';
            group.querySelectorAll('select, input').forEach(input => {
                input.disabled = isGeneral;
                input.required = !isGeneral;
            });
            setSavedState({ technician: false });
            saveDraft();
        }

        function closeConfigurationNameModal(value = null) {
            configNameModal.classList.remove('show');
            configNameModal.setAttribute('aria-hidden', 'true');
            if (configNameResolver) {
                configNameResolver(value);
                configNameResolver = null;
            }
        }

        function askConfigurationName(label) {
            configNameTitle.textContent = `Simpan Konfigurasi ${label}`;
            configNameDescription.textContent = `Masukkan nama konfigurasi ${label} agar bisa digunakan lagi nanti.`;
            configNameInput.value = '';
            configNameModal.classList.add('show');
            configNameModal.setAttribute('aria-hidden', 'false');
            window.setTimeout(() => configNameInput.focus(), 40);

            return new Promise(resolve => {
                configNameResolver = resolve;
            });
        }

        function submitFormTo(route, excludedSelector = null, onValid = null) {
            const form = document.getElementById('schedulerForm');
            form.action = route;
            saveDraft();
            const excludedFields = excludedSelector
                ? Array.from(document.querySelectorAll(`${excludedSelector} input, ${excludedSelector} select`))
                : [];
            excludedFields.forEach(field => field.disabled = true);
            if (!form.reportValidity()) {
                excludedFields.forEach(field => field.disabled = false);
                showToast('Mohon lengkapi semua input terlebih dahulu.');
                return;
            }
            if (onValid) onValid();
            form.requestSubmit();
        }

        async function saveUnitConfiguration() {
            const name = await askConfigurationName('Unit');
            if (!name) return;
            document.getElementById('unitConfigName').value = name;
            submitFormTo('{{ route('units.save') }}', '#teams-container', () => setSavedState({ unit: true }));
        }

        async function saveTechnicianConfiguration() {
            const name = await askConfigurationName('Teknisi');
            if (!name) return;
            document.getElementById('technicianConfigName').value = name;
            submitFormTo('{{ route('technicians.save') }}', null, () => setSavedState({ technician: true }));
        }

        function submitOptimization() {
            const teamCount = document.querySelectorAll('#teams-container .item-card').length;
            if (teamCount === 0) {
                showToast('Tambahkan minimal satu tim teknisi sebelum menjalankan optimasi.');
                return;
            }
            const savedState = getSavedState();
            if (!savedState.unit || !savedState.technician) {
                showToast('Simpan Unit Configuration dan Technician Configuration terlebih dahulu sebelum menjalankan optimasi.');
                return;
            }
            submitFormTo('{{ route('schedular.process') }}');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const restoredStep = restoreDraft();
            if (!document.querySelectorAll('#units-container .item-card').length) {
                addUnitRow(20, 2);
            }
            if (!document.querySelectorAll('#teams-container .item-card').length) {
                addTeamRow();
            }
            setTheme(localStorage.getItem('maintenanceSchedulerTheme') || 'light');
            goToStep(restoredStep !== false ? restoredStep : {{ session('active_step', 0) }});
        });

        document.getElementById('schedulerForm').addEventListener('input', event => {
            if (isRestoringDraft) return;
            if (event.target.closest('#units-container') || event.target.name === 'total_mw' || event.target.name === 'num_ants') {
                setSavedState({ unit: false });
            }
            if (event.target.closest('#teams-container')) {
                setSavedState({ technician: false });
            }
            saveDraft();
        });

        document.getElementById('schedulerForm').addEventListener('change', event => {
            if (isRestoringDraft) return;
            if (event.target.closest('#units-container') || event.target.name === 'total_mw' || event.target.name === 'num_ants') {
                setSavedState({ unit: false });
            }
            if (event.target.closest('#teams-container')) {
                setSavedState({ technician: false });
            }
            saveDraft();
        });

        configNameConfirm.addEventListener('click', () => {
            const name = configNameInput.value.trim();
            if (!name) {
                showToast('Nama konfigurasi wajib diisi sebelum menyimpan.');
                configNameInput.focus();
                return;
            }
            closeConfigurationNameModal(name);
        });

        configNameCancel.addEventListener('click', () => closeConfigurationNameModal());

        configNameInput.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                configNameConfirm.click();
            }
        });

        configNameModal.addEventListener('click', event => {
            if (event.target === configNameModal) {
                closeConfigurationNameModal();
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && configNameModal.classList.contains('show')) {
                closeConfigurationNameModal();
            }
        });
    </script>
</body>

</html>
