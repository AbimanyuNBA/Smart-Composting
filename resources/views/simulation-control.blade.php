<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Composting Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- <meta http-equiv="refresh" content="5"> --}}

    <style>
        body {
            background: #f4f7fb;
        }

        .dashboard-title {
            font-weight: 700;
            color: #2c3e50;
        }

        .card-modern {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            transition: .3s;
        }

        .card-modern:hover {
            transform: translateY(-4px);
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .ai-value {
            font-size: 3rem;
            font-weight: 700;
            color: #198754;
        }

        #sisaHariValue {
            font-size: 3rem;
            font-weight: 700;
        }

        .chart-card {
            height: 420px;
        }

        .status-running {
            color: #198754;
            font-weight: bold;
        }

        .status-stopped {
            color: #dc3545;
            font-weight: bold;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 15px;
        }
    </style>

</head>

<body>

    <div class="container-fluid py-4">

        <h1 class="dashboard-title mb-4">
            🌱 Smart Composting Dashboard
        </h1>

        {{-- REALTIME MONITORING --}}
        <div class="row g-4">

            <div class="col-lg-3 col-md-6">
                <div class="card card-modern p-3">
                    <h6>Suhu</h6>
                    <div class="card-value" id="suhuValue">
                        {{ $currentData['suhu'] ?? 0 }} °C
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-modern p-3">
                    <h6>Kelembapan</h6>
                    <div class="card-value" id="kelembapanValue">
                        {{ $currentData['kelembapan'] ?? 0 }} %
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-modern p-3">
                    <h6>pH</h6>
                    <div class="card-value" id="phValue">
                        {{ $currentData['ph'] ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card card-modern p-3">
                    <h6>CO₂</h6>
                    <div class="card-value" id="co2Value">
                        {{ $currentData['co2'] ?? 0 }}
                    </div>
                </div>
            </div>

        </div>

        {{-- DETAIL + CONTROL --}}
        <div class="row mt-4 g-4">

            <div class="col-lg-6">

                <div class="card card-modern p-4">

                    <h4 class="section-title">
                        Informasi Pengomposan
                    </h4>

                    <table class="table">

                        <tr>
                            <th width="180">Timestamp</th>
                            <td id="timestampValue">{{ $currentData['timestamp'] ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Hari</th>
                            <td id="hariValue">{{ $currentData['hari'] ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Fase</th>
                            <td id="faseValue">{{ $currentData['fase'] ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Kipas</th>
                            <td id="kipasValue">
                                {{ $currentData['kipas'] ?? 0 ? 'ON' : 'OFF' }}
                            </td>
                        </tr>

                        <tr>
                            <th>Pengaduk</th>
                            <td id="pengadukValue">
                                {{ $currentData['pengaduk'] ?? 0 ? 'ON' : 'OFF' }}
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="card card-modern p-4">

                    <h4 class="section-title">
                        Control Panel
                    </h4>

                    <table class="table">

                        <tr>
                            <th width="180">Status</th>
                            <td id="statusValue">

                                @if ($system['simulation_running'])
                                    <span class="status-running">
                                        RUNNING
                                    </span>
                                @else
                                    <span class="status-stopped">
                                        STOPPED
                                    </span>
                                @endif

                            </td>
                        </tr>

                        <tr>
                            <th>Current Row</th>
                            <td id="currentRowValue">{{ $system['current_row'] }}</td>
                        </tr>

                        <tr>
                            <th>Interval</th>
                            <td>{{ $system['simulation_interval'] }} Detik</td>
                        </tr>

                    </table>

                    <div class="d-flex gap-2 flex-wrap">

                        <a href="/simulation/start" class="btn btn-success">
                            ▶ Start
                        </a>

                        <a href="/simulation/stop" class="btn btn-danger">
                            ⏹ Stop
                        </a>

                        <a href="/simulation/reset" class="btn btn-warning">
                            🔄 Reset
                        </a>

                    </div>

                </div>

            </div>

        </div>

        {{-- AI PREDICTION --}}
        <div class="row mt-4 g-4">

            {{-- AI PREDICTION --}}
            <div class="row mt-4 g-4">

                <div class="col-lg-6">

                    <div class="card card-modern p-4 h-100">

                        <h4 class="section-title">
                            🤖 AI Prediction - Kematangan
                        </h4>

                        <div id="kematanganValue" class="ai-value">

                            {{ $currentData['kematangan_pct'] ?? 0 }} %

                        </div>

                        <div class="my-3">

                            <span id="predictionStatus" class="badge bg-success">

                                {{ $currentData['prediction_status'] ?? 'completed' }}

                            </span>

                        </div>

                        <div class="progress" style="height:28px;">

                            <div id="kematanganBar" class="progress-bar bg-success fw-bold"
                                style="width: {{ $currentData['kematangan_pct'] ?? 0 }}%;">

                                {{ $currentData['kematangan_pct'] ?? 0 }}%

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="card card-modern p-4 h-100">

                        <h4 class="section-title">
                            📅 Estimasi Selesai
                        </h4>

                        <div class="ai-value text-primary" id="sisaHariValue">

                            {{ $currentData['sisa_hari'] ?? 0 }}

                        </div>

                        <h5 class="text-muted">
                            Hari Lagi
                        </h5>

                    </div>

                </div>

            </div>



        </div>

        {{-- CHARTS --}}
        <div class="row mt-4 g-4">

            <div class="col-lg-6">
                <div class="card card-modern p-3 chart-card">
                    <h5>📈 Grafik Suhu</h5>
                    <canvas id="suhuChart"></canvas>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-modern p-3 chart-card">
                    <h5>💧 Grafik Kelembapan</h5>
                    <canvas id="kelembapanChart"></canvas>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-modern p-3 chart-card">
                    <h5>⚗ Grafik pH</h5>
                    <canvas id="phChart"></canvas>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-modern p-3 chart-card">
                    <h5>🌫 Grafik CO₂</h5>
                    <canvas id="co2Chart"></canvas>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card card-modern p-3 chart-card">
                    <h5>🤖 Grafik Kematangan AI</h5>
                    <canvas id="kematanganChart"></canvas>
                </div>
            </div>

        </div>

    </div>

    <script>
        const labels = @json($labels);

        const suhuChart = new Chart(
            document.getElementById('suhuChart'), {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Suhu',
                        data: @json($suhuData),
                        borderColor: '#dc3545',
                        tension: 0.4
                    }]
                }
            }
        );
        const kelembapanChart = new Chart(
            document.getElementById('kelembapanChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Kelembapan (%)',
                        data: @json($kelembapanData),
                        borderColor: '#0d6efd',
                        tension: 0.4,
                        fill: false
                    }]
                }
            }
        );

        const phChart = new Chart(
            document.getElementById('phChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'pH',
                        data: @json($phData),
                        borderColor: '#198754',
                        tension: 0.4,
                        fill: false
                    }]
                }
            }
        );

        const co2Chart = new Chart(
            document.getElementById('co2Chart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'CO₂ (ppm)',
                        data: @json($co2Data),
                        borderColor: '#6f42c1',
                        tension: 0.4,
                        fill: false
                    }]
                }
            }
        );


        const kematanganChart = new Chart(
            document.getElementById('kematanganChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Kematangan (%)',
                        data: @json($kematanganData),
                        borderColor: '#fd7e14',
                        tension: 0.4,
                        fill: false
                    }]
                }
            }
        );
    </script>

    <script>
        async function refreshDashboard() {
            try {

                const response =
                    await fetch('/dashboard-data');

                const data =
                    await response.json();

                const current =
                    data.currentData;

                const system =
                    data.system;

                document.getElementById('suhuValue')
                    .innerHTML =
                    current.suhu + ' °C';

                document.getElementById('kelembapanValue')
                    .innerHTML =
                    current.kelembapan + ' %';

                document.getElementById('phValue')
                    .innerHTML =
                    current.ph;

                document.getElementById('co2Value')
                    .innerHTML =
                    current.co2;

                document.getElementById('timestampValue')
                    .innerHTML =
                    current.timestamp ?? '-';

                document.getElementById('hariValue')
                    .innerHTML =
                    current.hari ?? '-';

                document.getElementById('faseValue')
                    .innerHTML =
                    current.fase ?? '-';

                document.getElementById('kipasValue')
                    .innerHTML =
                    current.kipas == 1 ? 'ON' : 'OFF';

                document.getElementById('pengadukValue')
                    .innerHTML =
                    current.pengaduk == 1 ? 'ON' : 'OFF';

                document.getElementById('kematanganValue')
                    .innerHTML =
                    (current.kematangan_pct ?? '-') + ' %';

                document.getElementById('sisaHariValue')
                    .innerHTML =
                    (current.sisa_hari ?? '-');

                document.getElementById('currentRowValue')
                    .innerHTML =
                    system.current_row;

                document.getElementById('statusValue')
                    .innerHTML =
                    system.simulation_running ?
                    '<span class="status-running">RUNNING</span>' :
                    '<span class="status-stopped">STOPPED</span>';



                document.getElementById(
                        'predictionStatus'
                    ).innerHTML =
                    current.prediction_status;

                if (current.prediction_status === 'completed') {
                    document.getElementById(
                            'predictionStatus'
                        ).className =
                        'badge bg-success';
                } else {
                    document.getElementById(
                            'predictionStatus'
                        ).className =
                        'badge bg-danger';
                }

                document.getElementById(
                        'kematanganBar'
                    ).style.width =
                    current.kematangan_pct + '%';

                document.getElementById(
                        'kematanganBar'
                    ).innerHTML =
                    current.kematangan_pct + '%';

            } catch (error) {
                console.error(error);
            }
        }

        setInterval(refreshDashboard, 2000);
    </script>


    <script>
        async function refreshCharts() {
            try {

                const response =
                    await fetch('/chart-data');

                const data =
                    await response.json();

                // SUHU
                suhuChart.data.labels = data.labels;
                suhuChart.data.datasets[0].data = data.suhuData;
                suhuChart.update();

                // KELEMBAPAN
                kelembapanChart.data.labels = data.labels;
                kelembapanChart.data.datasets[0].data = data.kelembapanData;
                kelembapanChart.update();

                // PH
                phChart.data.labels = data.labels;
                phChart.data.datasets[0].data = data.phData;
                phChart.update();

                // CO2
                co2Chart.data.labels = data.labels;
                co2Chart.data.datasets[0].data = data.co2Data;
                co2Chart.update();


                //kematangan
                kematanganChart.data.labels =
                    data.labels;

                kematanganChart.data.datasets[0].data =
                    data.kematanganData;

                kematanganChart.update();

            } catch (error) {
                console.error(error);
            }
        }

        setInterval(refreshCharts, 5000);
    </script>
</body>

</html>
