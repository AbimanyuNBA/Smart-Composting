@extends('layouts.app')

@section('content')
    {{-- ===================== METRIC CARDS ===================== --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <div class="metric-icon icon-red"><i class="bi bi-thermometer-half"></i></div>
                <div>
                    <div class="metric-label">Suhu (°C)</div>
                    <div class="metric-value" id="suhuValue">{{ $currentData['suhu'] ?? 0 }}<small> °C</small></div>
                    <span class="badge-normal"><i class="bi bi-check-circle-fill"></i> Normal</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <div class="metric-icon icon-blue"><i class="bi bi-droplet-fill"></i></div>
                <div>
                    <div class="metric-label">Kelembapan (%)</div>
                    <div class="metric-value" id="kelembapanValue">{{ $currentData['kelembapan'] ?? 0 }}<small> %</small>
                    </div>
                    <span class="badge-normal"><i class="bi bi-check-circle-fill"></i> Normal</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <div class="metric-icon icon-green"><i class="bi bi-cloud-haze2-fill"></i></div>
                <div>
                    <div class="metric-label">CO₂ (ppm)</div>
                    <div class="metric-value" id="co2Value">{{ $currentData['co2'] ?? 0 }}<small> ppm</small></div>
                    <span class="badge-normal"><i class="bi bi-check-circle-fill"></i> Normal</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="metric-card">
                <div class="metric-icon icon-purple"><i class="bi bi-droplet-half"></i></div>
                <div>
                    <div class="metric-label">pH</div>
                    <div class="metric-value" id="phValue">{{ $currentData['ph'] ?? 0.0 }}</div>
                    <span class="badge-normal"><i class="bi bi-check-circle-fill"></i> Normal</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== CHART + AI PREDICTION ===================== --}}
    <div class="row g-4 mb-4">

        <div class="col-xl-8 col-lg-7">
            <div class="card-modern">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up text-muted"></i> Progress Lintasan Parameter : Suhu
                        &amp; CO₂</h6>
                    <div class="d-flex gap-3 text-muted small fw-semibold">
                        <span class="text-danger">● Temp (°C)</span>
                        <span class="text-primary">● CO₂ (ppm)</span>
                    </div>
                </div>
                <div style="height: 320px;">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card-modern d-flex flex-column">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-cpu text-muted"></i> AI Prediction (Kematangan)</h6>
                    <span id="batchStatusBadge" class="status-dropdown-badge">
                        Status: {{ ucfirst($batchInfo['status'] ?? 'None') }} <i class="bi bi-chevron-down ms-1"></i>
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="batch-name">{{ $activeBatch ?? 'Tidak Ada Batch Aktif' }}</span>
                    <span class="text-muted small text-end">Last Sync:<br><span id="timestampValue"
                            class="fw-semibold text-dark">{{ $currentData['timestamp'] ?? '-' }}</span></span>
                </div>

                <div class="mb-3 d-flex justify-content-between text-muted small">
                    <div>Fase Saat Ini: <span class="text-primary fw-bold"
                            id="faseValue">{{ $currentData['fase'] ?? '-' }}</span></div>
                    <div class="fw-bold text-dark">Hari ke-<span id="hariValue">{{ $currentData['hari'] ?? 0 }}</span></div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-muted small">AI Prediction Progress</span>
                        <span id="kematanganValue" class="text-success fw-bold">{{ $currentData['kematangan_pct'] ?? 0 }}
                            %</span>
                    </div>
                    <div class="progress progress-custom">
                        <div class="progress-bar" id="kematanganBar"
                            style="width: {{ $currentData['kematangan_pct'] ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span id="predictionStatus"
                        class="badge-soft-success">{{ $currentData['prediction_status'] ?? 'completed' }}</span>
                    <span class="small text-muted">Estimasi: <b class="text-primary"
                            id="sisaHariValue">{{ $currentData['sisa_hari'] ?? 0 }}</b> hari lagi</span>
                </div>

                <div class="mt-auto d-flex gap-2 flex-wrap">
                    @if (($batchInfo['status'] ?? '') == 'draft')
                        <a href="/batch/start" class="btn btn-success btn-action"
                            onclick="return confirm('Mulai batch ini?')">
                            <i class="bi bi-play-fill"></i> Start
                        </a>
                    @elseif (($batchInfo['status'] ?? '') == 'active')
                        <a href="/batch/pause" class="btn btn-warning btn-action text-white"
                            onclick="return confirm('Pause batch ini?')">
                            <i class="bi bi-pause-fill"></i> Pause
                        </a>
                        <a href="/batch/complete" class="btn btn-success btn-action"
                            onclick="return confirm('Selesaikan batch ini?')">
                            <i class="bi bi-check-all"></i> Complete
                        </a>
                        <a href="/batch/cancel" class="btn btn-danger btn-action"
                            onclick="return confirm('Batalkan batch ini?')">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    @elseif (($batchInfo['status'] ?? '') == 'paused')
                        <a href="/batch/resume" class="btn btn-success btn-action"
                            onclick="return confirm('Lanjutkan batch ini?')">
                            <i class="bi bi-play-fill"></i> Resume
                        </a>
                        <a href="/batch/complete" class="btn btn-primary btn-action"
                            onclick="return confirm('Selesaikan batch ini?')">
                            <i class="bi bi-check-all"></i> Complete
                        </a>
                        <a href="/batch/cancel" class="btn btn-danger btn-action"
                            onclick="return confirm('Batalkan batch ini?')">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    @elseif (in_array($batchInfo['status'] ?? '', ['completed', 'cancelled']))
                        <a href="/batch/create" class="btn btn-primary btn-action w-100">
                            <i class="bi bi-box-seam-fill"></i> Buat Batch Baru
                        </a>
                    @else
                        <a href="/batch/create" class="btn btn-primary btn-action w-100">
                            <i class="bi bi-box-seam-fill"></i> Buat Batch Baru
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ===================== TABLE + DEVICE CONTROL ===================== --}}
    <div class="row g-4">

        <div class="col-xl-8 col-lg-7">
            <div class="card-modern">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-layout-text-window-reverse text-muted"></i> Parameter Data
                        Tabel Log</h6>
                    <button class="btn btn-light border filter-pill px-4">Filtered by <i
                            class="bi bi-chevron-down ms-1"></i></button>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom mb-0">
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
                            @forelse($labels ?? [] as $index => $label)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>Hari {{ $label }}</td>
                                    <td>{{ $timestamps[$index] ?? '-' }}</td>
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
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data riwayat kompos
                                        untuk batch ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card-modern">

                <h6 class="fw-bold mb-3"><i class="bi bi-sliders text-muted"></i> Device Control</h6>

                <div class="device-box mb-3">
                    <div class="device-title mb-2">Mode Operasi</div>
                    <div class="mode-toggle-group">
                        <div class="form-check">
                            <input class="form-check-input visually-hidden" type="radio" name="mode" id="modeAuto"
                                checked>
                            <label class="form-check-label" for="modeAuto">AUTO</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input visually-hidden" type="radio" name="mode"
                                id="modeManual">
                            <label class="form-check-label" for="modeManual">MANUAL</label>
                        </div>
                    </div>
                </div>

                <div class="device-box">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="device-title">🌬 Aerasi (Blower)</div>
                        <span id="kipasValue" class="badge bg-success">ON</span>
                    </div>
                    <div class="device-sub">Status aktual perangkat</div>
                    <hr>
                    <div id="blowerManualBox" class="d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold">Manual Command</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="blowerToggle">
                        </div>
                    </div>
                </div>

                <div class="device-box">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="device-title">🔄 Pengaduk</div>
                        <span id="pengadukValue" class="badge bg-secondary">OFF</span>
                    </div>
                    <div class="device-sub">Status aktual perangkat</div>
                    <hr>
                    <div id="pengadukManualBox" class="d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold">Manual Command</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="pengadukToggle">
                        </div>
                    </div>
                </div>

                <div class="border-top mt-3 pt-3 device-footer">
                    <div>Row Aktif: <b id="currentRowValue">{{ $system['current_row'] ?? 0 }}</b></div>
                    <div>Interval: <b>{{ $system['simulation_interval'] ?? 0 }} Detik</b></div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Setup Chart.js
        const ctx = document.getElementById('mainChart').getContext('2d');

        let gradientSuhu = ctx.createLinearGradient(0, 0, 0, 320);
        gradientSuhu.addColorStop(0, 'rgba(239, 68, 68, 0.3)');
        gradientSuhu.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

        let gradientCO2 = ctx.createLinearGradient(0, 0, 0, 320);
        gradientCO2.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        gradientCO2.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        const mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels ?? []),
                datasets: [{
                        label: 'Suhu (°C)',
                        data: @json($suhuData ?? []),
                        borderColor: '#ef4444',
                        backgroundColor: gradientSuhu,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'CO₂ (ppm)',
                        data: @json($co2Data ?? []),
                        borderColor: '#3b82f6',
                        backgroundColor: gradientCO2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Suhu (°C)'
                        },
                        grid: {
                            borderDash: [5, 5]
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'CO₂ (ppm)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // ===============================
        // REALTIME DASHBOARD + TABLE
        // ===============================
        async function refreshDashboard() {
            try {
                const response = await fetch('/dashboard-data?t=' + Date.now());
                const data = await response.json();

                const current = data.currentData || {};
                const system = data.system || {};
                const control = data.control || {};

                // SENSOR CARD
                suhuValue.innerHTML = current.suhu + '<small> °C</small>';
                kelembapanValue.innerHTML = current.kelembapan + '<small> %</small>';
                co2Value.innerHTML = current.co2 + '<small> ppm</small>';
                phValue.innerHTML = current.ph;
                timestampValue.innerHTML = current.timestamp;
                document.getElementById('topbarSync').innerHTML = current.timestamp;
                faseValue.innerHTML = current.fase;
                hariValue.innerHTML = current.hari;
                document.getElementById('topbarHari').innerHTML = 'Hari ke-' + current.hari;
                currentRowValue.innerHTML = system.current_row;

                // AI
                let kematangan = current.kematangan_pct ?? 0;
                kematanganValue.innerHTML = kematangan + ' %';
                kematanganBar.style.width = kematangan + '%';
                sisaHariValue.innerHTML = current.sisa_hari;

                // ===============================
                // STATUS DEVICE SESUAI MODE
                // ===============================

                let kipasStatus = 0;
                let pengadukStatus = 0;


                // MODE AUTO
                if (control.mode === 'auto') {

                    kipasStatus =
                        current.kipas ?? 0;

                    pengadukStatus =
                        current.pengaduk ?? 0;

                }


                // MODE MANUAL
                else {

                    kipasStatus =
                        control.kipas_manual ?? 0;

                    pengadukStatus =
                        control.pengaduk_manual ?? 0;

                }


                // tampil status kipas
                kipasValue.innerHTML =
                    kipasStatus == 1 ? 'ON' : 'OFF';


                kipasValue.className =
                    kipasStatus == 1 ?
                    'badge bg-success' :
                    'badge bg-secondary';


                // tampil status pengaduk
                pengadukValue.innerHTML =
                    pengadukStatus == 1 ? 'ON' : 'OFF';


                pengadukValue.className =
                    pengadukStatus == 1 ?
                    'badge bg-success' :
                    'badge bg-secondary';

                // MANUAL COMMAND
                // ===============================
                // MODE CONTROL
                // ===============================

                modeAuto.checked =
                    control.mode == 'auto';


                modeManual.checked =
                    control.mode == 'manual';


                // isi posisi switch manual

                blowerToggle.checked =
                    control.kipas_manual == 1;


                pengadukToggle.checked =
                    control.pengaduk_manual == 1;



                // ===============================
                // HIDE MANUAL COMMAND SAAT AUTO
                // ===============================

                if (control.mode === 'auto') {


                    blowerManualBox.style.display =
                        'none';


                    pengadukManualBox.style.display =
                        'none';


                    // matikan akses switch
                    blowerToggle.disabled = true;

                    pengadukToggle.disabled = true;


                } else {


                    blowerManualBox.style.display =
                        'flex';


                    pengadukManualBox.style.display =
                        'flex';


                    blowerToggle.disabled = false;

                    pengadukToggle.disabled = false;

                }



                // TABLE LOG
                let html = "";
                (data.history || []).forEach(function(item, index) {
                    html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>Hari ${item.hari}</td>
                        <td>${item.timestamp}</td>
                        <td>${item.suhu}°C</td>
                        <td>${item.kelembapan}%</td>
                        <td>${item.co2} ppm</td>
                        <td>${item.ph}</td>
                        <td><span class="badge bg-light text-dark border">${item.fase}</span></td>
                    </tr>`;
                });
                tableBody.innerHTML = html;

            } catch (error) {
                console.log("Realtime Error", error);
            }
        }

        // ===============================
        // REALTIME CHART
        // ===============================
        async function refreshCharts() {
            try {
                const response = await fetch('/chart-data?t=' + Date.now());
                const data = await response.json();

                mainChart.data.labels = data.labels;
                mainChart.data.datasets[0].data = data.suhuData;
                mainChart.data.datasets[1].data = data.co2Data;
                mainChart.update();
            } catch (error) {
                console.log("Chart Error", error);
            }
        }

        setInterval(refreshDashboard, 2000);
        setInterval(refreshCharts, 5000);

        refreshDashboard();
        refreshCharts();

        document.getElementById('blowerToggle').addEventListener('change', function() {
            fetch('/device-control', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: 'kipas',
                    value: this.checked ? 1 : 0
                })
            });
        });

        document.getElementById('pengadukToggle').addEventListener('change', function() {
            fetch('/device-control', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: 'pengaduk',
                    value: this.checked ? 1 : 0
                })
            });
        });

        document.getElementById('modeAuto').addEventListener('change', function() {
            fetch('/device-control', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: 'mode',
                    value: 'auto'
                })
            });
        });

        document.getElementById('modeManual').addEventListener('change', function() {
            fetch('/device-control', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: 'mode',
                    value: 'manual'
                })
            });
        });
    </script>
@endpush
