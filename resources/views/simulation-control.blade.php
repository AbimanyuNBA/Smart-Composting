<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Composting Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #e0eaf5 0%, #e6efe9 100%);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #2c3e50;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: 80px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            gap: 20px;
            z-index: 100;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #6c757d;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: 0.3s;
        }

        .icon-box.active {
            background: #3b82f6;
            color: white;
        }

        .icon-box:hover:not(.active) {
            background: #f8f9fa;
            color: #3b82f6;
        }

        .main-content {
            margin-left: 90px;
            padding: 20px 30px;
        }

        .card-modern {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            padding: 24px;
            height: 100%;
        }

        .metric-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 10px;
            color: #1a1d20;
        }

        .metric-value small {
            font-size: 1rem;
            color: #6c757d;
        }

        .progress-custom {
            height: 12px;
            border-radius: 10px;
            background-color: #e9ecef;
            margin-top: 8px;
        }
        
        .progress-custom .progress-bar {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
        }
        .form-switch .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .table-custom th {
            background: transparent;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
            border-bottom: 2px solid #edf2f7;
        }
        .table-custom td {
            background: transparent;
            font-size: 0.95rem;
            vertical-align: middle;
            border-bottom: 1px solid #edf2f7;
        }
        
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        .btn-action {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }
        .btn-action:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="icon-box text-success" style="margin-bottom: 30px;">
            <i class="bi bi-house-door-fill"></i>
        </div>
        <div class="icon-box active"><i class="bi bi-grid-fill"></i></div>
        <div class="icon-box"><i class="bi bi-plus-square"></i></div>
        <div class="icon-box"><i class="bi bi-image"></i></div>
        
        <div style="flex-grow: 1;"></div>
        
        <div class="icon-box"><i class="bi bi-gear"></i></div>
        <div class="icon-box"><i class="bi bi-person-circle"></i></div>
    </div>

    <div class="main-content">
        
        <div class="row g-3 mb-3">
            
            <div class="col-xl-5 col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card-modern">
                            <div class="metric-title"><i class="bi bi-thermometer-half"></i> Suhu (°C)</div>
                            <div class="metric-value" id="suhuValue">{{ $currentData['suhu'] ?? 0 }}<small>°C</small></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card-modern">
                            <div class="metric-title"><i class="bi bi-droplet"></i> Kelembapan (%)</div>
                            <div class="metric-value" id="kelembapanValue">{{ $currentData['kelembapan'] ?? 0 }}<small>%</small></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card-modern">
                            <div class="metric-title"><i class="bi bi-cloud-haze2"></i> CO₂ (ppm)</div>
                            <div class="metric-value" id="co2Value">{{ $currentData['co2'] ?? 0 }}<small>ppm</small></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card-modern">
                            <div class="metric-title"><i class="bi bi-virus"></i> pH</div>
                            <div class="metric-value" id="phValue">{{ $currentData['ph'] ?? 0.0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7 col-lg-6">
                <div class="card-modern d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="mb-0 fw-bold">{{ $activeBatch ?? 'Tidak Ada Batch Aktif' }} - Status Overview</h4>
                            
                            <span id="batchStatusBadge" class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                                Status: {{ ucfirst($batchInfo['status'] ?? 'None') }} <i class="bi bi-chevron-down ms-1"></i>
                            </span>
                        </div>
                        
                        <div class="mb-2 d-flex justify-content-between text-muted small">
                            <div>Fase Saat Ini: <span class="text-primary fw-bold" id="faseValue">[{{ $currentData['fase'] ?? '-' }}]</span></div>
                            <div>Last Sync: <span id="timestampValue" class="fw-semibold">{{ $currentData['timestamp'] ?? '-' }}</span></div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-muted small">AI Prediction Progress (Kematangan): <span id="kematanganValue" class="text-success fw-bold">{{ $currentData['kematangan_pct'] ?? 0 }} %</span></span>
                                <span class="fw-bold text-dark small">Hari ke-<span id="hariValue">{{ $currentData['hari'] ?? 0 }}</span></span>
                            </div>
                            <div class="progress progress-custom">
                                <div class="progress-bar" id="kematanganBar" style="width: {{ $currentData['kematangan_pct'] ?? 0 }}%"></div>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-3 align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="status-dot bg-success"></span> 
                                <span id="predictionStatus" class="badge bg-success small">{{ $currentData['prediction_status'] ?? 'completed' }}</span>
                            </div>
                            <div class="fw-semibold text-muted small">
                                Estimasi Selesai: <span id="sisaHariValue" class="text-primary fw-bold">{{ $currentData['sisa_hari'] ?? 0 }}</span> Hari Lagi
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3 d-flex gap-2 flex-wrap">
                        {{-- DRAFT STATUS --}}
                        @if (($batchInfo['status'] ?? '') == 'draft')
                            <a href="/batch/start" class="btn btn-success btn-action" onclick="return confirm('Mulai batch ini?')">
                                <i class="bi bi-play-fill"></i> Start Batch
                            </a>

                        {{-- ACTIVE STATUS --}}
                        @elseif (($batchInfo['status'] ?? '') == 'active')
                            <a href="/batch/pause" class="btn btn-warning btn-action text-white" onclick="return confirm('Pause batch ini?')">
                                <i class="bi bi-pause-fill"></i> Pause
                            </a>
                            <a href="/batch/complete" class="btn btn-success btn-action" onclick="return confirm('Selesaikan batch ini?')">
                                <i class="bi bi-check-all"></i> Complete
                            </a>
                            <a href="/batch/cancel" class="btn btn-danger btn-action" onclick="return confirm('Batalkan batch ini?')">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>

                        {{-- PAUSED STATUS --}}
                        @elseif (($batchInfo['status'] ?? '') == 'paused')
                            <a href="/batch/resume" class="btn btn-success btn-action" onclick="return confirm('Lanjutkan batch ini?')">
                                <i class="bi bi-play-fill"></i> Resume
                            </a>
                            <a href="/batch/complete" class="btn btn-primary btn-action" onclick="return confirm('Selesaikan batch ini?')">
                                <i class="bi bi-check-all"></i> Complete
                            </a>
                            <a href="/batch/cancel" class="btn btn-danger btn-action" onclick="return confirm('Batalkan batch ini?')">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>

                        {{-- COMPLETED / CANCELLED STATUS (Tombol Buat Batch Baru) --}}
                        @elseif (in_array(($batchInfo['status'] ?? ''), ['completed', 'cancelled']))
                            <a href="/batch/create" class="btn btn-primary btn-action" onclick="return confirm('Buat batch baru?')">
                                <i class="bi bi-box-seam-fill"></i> 📦 Buat Batch Baru
                            </a>
                            <span class="badge d-inline-flex align-items-center bg-light text-dark border px-3 rounded-3">
                                Batch Selesai / Dibatalkan
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            
            <div class="col-xl-8 col-lg-7">
                <div class="card-modern">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-graph-up text-muted"></i> Progress Lintasan Parameter : Suhu & CO₂</h5>
                        <div class="d-flex gap-3 text-muted small fw-semibold">
                            <span class="text-danger">● Temp (°C)</span>
                            <span class="text-primary">● CO₂ (ppm)</span>
                        </div>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="card-modern d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-sliders text-muted"></i> Device Control & Status</h5>
                            <span id="statusValue" class="small fw-bold"></span> </div>

                        <div class="border rounded-4 p-3 mb-3 bg-white shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold">Aerasi (Blower)</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="kipasValue" class="badge bg-light text-dark border small">OFF</span>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="blowerToggle" {{ ($currentData['kipas'] ?? 0) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-3 text-muted small">
                                <div class="form-check"><input class="form-check-input" type="radio" name="bMode" id="bM" checked><label class="form-check-label" for="bM">Manual</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="bMode" id="bA"><label class="form-check-label" for="bA">Auto</label></div>
                            </div>
                        </div>

                        <div class="border rounded-4 p-3 bg-white shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 fw-bold">EM4 Pump (Pengaduk)</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="pengadukValue" class="badge bg-light text-dark border small">OFF</span>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="em4Toggle" {{ ($currentData['pengaduk'] ?? 0) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-3 text-muted small">
                                <div class="form-check"><input class="form-check-input" type="radio" name="eMode" id="eM" checked><label class="form-check-label" for="eM">Manual</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="eMode" id="eA"><label class="form-check-label" for="eA">Auto</label></div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-2 mt-2 text-muted small d-flex justify-content-between">
                        <div>Row Aktif: <span id="currentRowValue" class="fw-bold text-dark">{{ $system['current_row'] ?? 0 }}</span></div>
                        <div>Interval: <span class="fw-bold text-dark">{{ $system['simulation_interval'] ?? 0 }} Detik</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="card-modern">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-layout-text-window-reverse text-muted"></i> Parameter Data Tabel Log</h5>
                        <button class="btn btn-light border rounded-pill px-4 shadow-sm btn-sm">Filtered by <i class="bi bi-chevron-down ms-1"></i></button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Suhu</th>
                                    <th>Kelembapan</th>
                                    <th>CO₂</th>
                                    <th>pH</th>
                                    <th>Label Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($labels as $index => $label)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>Hari {{ $label }}</td>
                                        <td>{{ now()->format('H:i') }} am</td>
                                        <td>{{ $suhuData[$index] ?? '-' }}°C</td>
                                        <td>{{ $kelembapanData[$index] ?? '-' }}%</td>
                                        <td>{{ number_format($co2Data[$index] ?? 0) }} ppm</td>
                                        <td>{{ $phData[$index] ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ ($suhuData[$index] ?? 0) > 50 ? 'Fase Termofilik' : 'Fase Mesofilik' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada data riwayat kompos untuk batch ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('mainChart').getContext('2d');
        
        let gradientSuhu = ctx.createLinearGradient(0, 0, 0, 300);
        gradientSuhu.addColorStop(0, 'rgba(239, 68, 68, 0.3)');
        gradientSuhu.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

        let gradientCO2 = ctx.createLinearGradient(0, 0, 0, 300);
        gradientCO2.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        gradientCO2.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        const mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [
                    {
                        label: 'Suhu (°C)',
                        data: @json($suhuData),
                        borderColor: '#ef4444',
                        backgroundColor: gradientSuhu,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'CO₂ (ppm)',
                        data: @json($co2Data),
                        borderColor: '#3b82f6',
                        backgroundColor: gradientCO2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Suhu (°C)' },
                        grid: { borderDash: [5, 5] }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'CO₂ (ppm)' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
        async function refreshDashboard() {
            try {
                const response = await fetch('/dashboard-data');
                const data = await response.json();
                const current = data.currentData || {};
                const system = data.system || {};

                if (Object.keys(current).length === 0) return;

                document.getElementById('suhuValue').innerHTML = current.suhu + '<small>°C</small>';
                document.getElementById('kelembapanValue').innerHTML = current.kelembapan + '<small>%</small>';
                document.getElementById('phValue').innerHTML = current.ph;
                document.getElementById('co2Value').innerHTML = current.co2 + '<small>ppm</small>';

                document.getElementById('timestampValue').innerHTML = current.timestamp ?? '-';
                document.getElementById('hariValue').innerHTML = current.hari ?? '-';
                document.getElementById('faseValue').innerHTML = current.fase ?? '-';
                document.getElementById('kematanganValue').innerHTML = (current.kematangan_pct ?? '-') + ' %';
                document.getElementById('sisaHariValue').innerHTML = (current.sisa_hari ?? '-');
                document.getElementById('currentRowValue').innerHTML = system.current_row;

                document.getElementById('kipasValue').innerHTML = current.kipas == 1 ? 'ON' : 'OFF';
                document.getElementById('kipasValue').className = current.kipas == 1 ? 'badge bg-success text-white small' : 'badge bg-light text-dark border small';
                document.getElementById('blowerToggle').checked = current.kipas == 1;

                document.getElementById('pengadukValue').innerHTML = current.pengaduk == 1 ? 'ON' : 'OFF';
                document.getElementById('pengadukValue').className = current.pengaduk == 1 ? 'badge bg-success text-white small' : 'badge bg-light text-dark border small';
                document.getElementById('em4Toggle').checked = current.pengaduk == 1;

                document.getElementById('statusValue').innerHTML = system.simulation_running ?
                    '<span class="badge bg-success"><i class="bi bi-cpu-fill me-1"></i> RUNNING</span>' :
                    '<span class="badge bg-danger"><i class="bi bi-stop-fill me-1"></i> STOPPED</span>';

                document.getElementById('predictionStatus').innerHTML = current.prediction_status ?? 'completed';
                document.getElementById('predictionStatus').className = current.prediction_status === 'completed' ? 'badge bg-success small' : 'badge bg-danger small';

                document.getElementById('kematanganBar').style.width = current.kematangan_pct + '%';

            } catch (error) {
                console.error("Gagal melakukan sinkronisasi realtime database:", error);
            }
        }

        async function refreshCharts() {
            try {
                const response = await fetch('/chart-data');
                const data = await response.json();

                mainChart.data.labels = data.labels;
                mainChart.data.datasets[0].data = data.suhuData;
                mainChart.data.datasets[1].data = data.co2Data;
                mainChart.update();
            } catch (error) {
                console.error("Gagal melakukan sinkronisasi grafik:", error);
            }
        }

        // Interval Loop Sinkronisasi
        setInterval(refreshDashboard, 2000);
        setInterval(refreshCharts, 5000);
    </script>
</body>
</html>