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
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up text-muted"></i> Progress Lintasan Parameter</h6>

                    {{-- ===== FILTER PARAMETER GRAFIK ===== --}}
                    <div class="chart-filter-group d-flex flex-wrap gap-1">
                        <label class="chart-filter-pill filter-suhu">
                            <input type="checkbox" data-series="suhu" checked>
                            <span>● Suhu (°C)</span>
                        </label>
                        <label class="chart-filter-pill filter-kelembapan">
                            <input type="checkbox" data-series="kelembapan">
                            <span>● Kelembapan (%)</span>
                        </label>
                        <label class="chart-filter-pill filter-co2">
                            <input type="checkbox" data-series="co2" checked>
                            <span>● CO₂ (ppm)</span>
                        </label>
                        <label class="chart-filter-pill filter-ph">
                            <input type="checkbox" data-series="ph">
                            <span>● pH</span>
                        </label>
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

                {{-- FASE CHIP: warna & ikon ditentukan dari nilai fase yang dikirim Firebase --}}
                @php
                    $faseName = $currentData['fase'] ?? '-';
                    $faseKey = strtolower($faseName);
                    if (str_contains($faseKey, 'termo')) {
                        $faseIcon = 'bi-fire';
                        $faseClass = 'fase-hot';
                        $faseDesc = 'Suhu tinggi, aktivitas mikroba memuncak';
                    } elseif (str_contains($faseKey, 'dingin') || str_contains($faseKey, 'cooling')) {
                        $faseIcon = 'bi-snow2';
                        $faseClass = 'fase-cool';
                        $faseDesc = 'Suhu menurun, dekomposisi melambat';
                    } elseif (str_contains($faseKey, 'matang') || str_contains($faseKey, 'matur')) {
                        $faseIcon = 'bi-patch-check-fill';
                        $faseClass = 'fase-mature';
                        $faseDesc = 'Kompos siap digunakan';
                    } else {
                        $faseIcon = 'bi-thermometer-low';
                        $faseClass = 'fase-meso';
                        $faseDesc = 'Dekomposisi awal, suhu naik bertahap';
                    }
                @endphp

                <div class="fase-chip {{ $faseClass }} mb-3" id="faseChip">
                    <div class="fase-chip-icon"><i class="bi {{ $faseIcon }}" id="faseIcon"></i></div>
                    <div>
                        <div class="fase-chip-title">Fase <span id="faseValue">{{ $faseName }}</span></div>
                        <div class="fase-chip-desc" id="faseDesc">{{ $faseDesc }}</div>
                    </div>
                </div>



                <div class="ai-hero mb-3">
                    <div class="ai-hero-label"><i class="bi bi-cpu-fill"></i> Prediksi Kematangan AI</div>
                    <div class="ai-hero-value"><span id="kematanganValue">{{ $currentData['kematangan_pct'] ?? 0 }}</span><small>% matang</small></div>
                    <div class="progress progress-custom mb-2">
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
                        <div class="device-title"><span id="kipasIcon" class="device-anim-icon">🌬</span> Aerasi (Blower)</div>
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
                        <div class="device-title"><span id="pengadukIcon" class="device-anim-icon">🔄</span> Pengaduk</div>
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

@push('styles')
    <style>
        /* ===== Filter pill chart ===== */
        .chart-filter-group { font-size: .78rem; }
        .chart-filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            user-select: none;
            font-weight: 600;
            color: #9ca3af;
            background: #fff;
            transition: all .15s ease;
        }
        .chart-filter-pill input { display: none; }
        .chart-filter-pill:has(input:checked) {
            color: #111827;
            border-color: currentColor;
            background: #f9fafb;
        }
        .filter-suhu:has(input:checked)        { color: #ef4444; }
        .filter-kelembapan:has(input:checked)  { color: #3b82f6; }
        .filter-co2:has(input:checked)         { color: #10b981; }
        .filter-ph:has(input:checked)          { color: #8b5cf6; }

        /* ===== Animasi status device ===== */
        .device-anim-icon {
            display: inline-block;
            transition: transform .2s ease;
        }
        .device-anim-icon.is-spinning {
            animation: device-spin 1.1s linear infinite;
        }
        @keyframes device-spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // ==========================================================
        // Setup Chart.js — 4 parameter (Suhu, Kelembapan, CO2, pH)
        // ==========================================================
        const ctx = document.getElementById('mainChart').getContext('2d');

        function makeGradient(ctx, colorRgba) {
            const g = ctx.createLinearGradient(0, 0, 0, 320);
            g.addColorStop(0, colorRgba(0.3));
            g.addColorStop(1, colorRgba(0.0));
            return g;
        }

        const gradientSuhu       = makeGradient(ctx, a => `rgba(239, 68, 68, ${a})`);
        const gradientKelembapan = makeGradient(ctx, a => `rgba(59, 130, 246, ${a})`);
        const gradientCO2        = makeGradient(ctx, a => `rgba(16, 185, 129, ${a})`);
        const gradientPh         = makeGradient(ctx, a => `rgba(139, 92, 246, ${a})`);

        const mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels ?? []),
                datasets: [
                    {
                        key: 'suhu',
                        label: 'Suhu (°C)',
                        data: @json($suhuData ?? []),
                        borderColor: '#ef4444',
                        backgroundColor: gradientSuhu,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        yAxisID: 'y',
                        hidden: false
                    },
                    {
                        key: 'kelembapan',
                        label: 'Kelembapan (%)',
                        data: @json($kelembapanData ?? []),
                        borderColor: '#3b82f6',
                        backgroundColor: gradientKelembapan,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        yAxisID: 'y2',
                        hidden: true
                    },
                    {
                        key: 'co2',
                        label: 'CO₂ (ppm)',
                        data: @json($co2Data ?? []),
                        borderColor: '#10b981',
                        backgroundColor: gradientCO2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        yAxisID: 'y1',
                        hidden: false
                    },
                    {
                        key: 'ph',
                        label: 'pH',
                        data: @json($phData ?? []),
                        borderColor: '#8b5cf6',
                        backgroundColor: gradientPh,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        yAxisID: 'y3',
                        hidden: true
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
                        type: 'linear', position: 'left', display: true,
                        title: { display: true, text: 'Suhu (°C)' },
                        grid: { borderDash: [5, 5] }
                    },
                    y1: {
                        type: 'linear', position: 'right', display: true,
                        title: { display: true, text: 'CO₂ (ppm)' },
                        grid: { drawOnChartArea: false }
                    },
                    y2: {
                        type: 'linear', position: 'right', display: false,
                        title: { display: true, text: 'Kelembapan (%)' },
                        grid: { drawOnChartArea: false }
                    },
                    y3: {
                        type: 'linear', position: 'left', display: false,
                        title: { display: true, text: 'pH' },
                        grid: { drawOnChartArea: false },
                        min: 0, max: 14
                    }
                }
            }
        });

        // Checkbox filter -> toggle dataset + axis-nya
        const axisForKey = { suhu: 'y', kelembapan: 'y2', co2: 'y1', ph: 'y3' };
        document.querySelectorAll('.chart-filter-pill input[data-series]').forEach(box => {
            box.addEventListener('change', function () {
                const key = this.dataset.series;
                const ds = mainChart.data.datasets.find(d => d.key === key);
                if (!ds) return;
                ds.hidden = !this.checked;
                mainChart.options.scales[axisForKey[key]].display = this.checked;
                mainChart.update();
            });
        });

        function applyChartData(labels, dataByKey) {
            mainChart.data.labels = labels;
            mainChart.data.datasets.forEach(ds => {
                ds.data = dataByKey[ds.key] || [];
            });
            mainChart.update();
        }

        // ==========================================================
        // FASE CHIP: update warna/ikon saat data realtime berubah
        // ==========================================================
        function updateFaseChip(faseText) {
            const faseKey = (faseText || '').toLowerCase();
            const faseChip = document.getElementById('faseChip');
            const faseIcon = document.getElementById('faseIcon');
            const faseDesc = document.getElementById('faseDesc');

            let faseClass = 'fase-meso';
            let faseIconClass = 'bi-thermometer-low';
            let faseDescText = 'Dekomposisi awal, suhu naik bertahap';

            if (faseKey.includes('termo')) {
                faseClass = 'fase-hot';
                faseIconClass = 'bi-fire';
                faseDescText = 'Suhu tinggi, aktivitas mikroba memuncak';
            } else if (faseKey.includes('dingin') || faseKey.includes('cooling')) {
                faseClass = 'fase-cool';
                faseIconClass = 'bi-snow2';
                faseDescText = 'Suhu menurun, dekomposisi melambat';
            } else if (faseKey.includes('matang') || faseKey.includes('matur')) {
                faseClass = 'fase-mature';
                faseIconClass = 'bi-patch-check-fill';
                faseDescText = 'Kompos siap digunakan';
            }

            faseChip.className = 'fase-chip ' + faseClass + ' mb-3';
            faseIcon.className = 'bi ' + faseIconClass;
            faseDesc.innerHTML = faseDescText;
        }

        // ==========================================================
        // Animasi status alat (spin saat ON)
        // ==========================================================
        function setDeviceAnim(elId, isOn) {
            const el = document.getElementById(elId);
            if (!el) return;
            el.classList.toggle('is-spinning', !!isOn);
        }

        // ==========================================================
        // FIREBASE CLIENT SDK — realtime listener
        // Laravel hanya untuk render halaman awal + auth.
        // Semua data sensor & tabel didengarkan langsung dari sini.
        //
        // GANTI firebaseConfig di bawah dengan config project Firebase
        // Anda (yang sama dipakai kreait/laravel-firebase di backend).
        // Sesuaikan juga path RTDB (sensor/current, control, history,
        // system, batch) dengan struktur database Anda yang sebenarnya.
        // ==========================================================
    </script>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
        import {
            getDatabase, ref, onValue, set, child
        } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

        const firebaseConfig = {
            databaseURL: "{{ env('FIREBASE_DATABASE_URL') }}",
        };

        const app = initializeApp(firebaseConfig);
        const db = getDatabase(app);

        // --- Path realtime database ---
        const systemRef  = ref(db, 'system');
        const controlRef = ref(db, 'control');

        let currentListener = null;
        let historyListener = null;
        let batchListener = null;
        let activeBatchId = null;

        let latestControlMode = 'auto';
        let latestCurrent = {};

        // 1) SYSTEM (ambil active_batch untuk mendengarkan path batch yang dinamis)
        onValue(systemRef, (snapshot) => {
            const system = snapshot.val() || {};
            currentRowValue.innerHTML = system.current_row || 1;

            const newActiveBatchId = system.active_batch;
            if (newActiveBatchId && newActiveBatchId !== activeBatchId) {
                activeBatchId = newActiveBatchId;

                // Hapus listener sebelumnya jika ada
                if (currentListener) currentListener();
                if (historyListener) historyListener();
                if (batchListener) batchListener();

                // Setup listener baru untuk active batch saat ini
                const activeBatchRef = ref(db, `batches/${activeBatchId}`);
                const currentRef = ref(db, `batches/${activeBatchId}/current_data`);
                const historyRef = ref(db, `batches/${activeBatchId}/history`);

                // A. SENSOR TERKINI
                currentListener = onValue(currentRef, (currentSnap) => {
                    const current = currentSnap.val() || {};
                    latestCurrent = current;

                    suhuValue.innerHTML = (current.suhu !== undefined ? current.suhu : 0) + '<small> °C</small>';
                    kelembapanValue.innerHTML = (current.kelembapan !== undefined ? current.kelembapan : 0) + '<small> %</small>';
                    co2Value.innerHTML = (current.co2 !== undefined ? current.co2 : 0) + '<small> ppm</small>';
                    phValue.innerHTML = current.ph !== undefined ? current.ph : '0.0';

                    const timestamp = current.timestamp || '-';
                    if (document.getElementById('timestampValue')) {
                        timestampValue.innerHTML = timestamp;
                    }
                    const topbarSync = document.getElementById('topbarSync');
                    if (topbarSync) topbarSync.innerHTML = timestamp;

                    const fase = current.fase || '-';
                    faseValue.innerHTML = fase;
                    updateFaseChip(fase);

                    const hari = current.hari !== undefined ? current.hari : 0;
                    if (document.getElementById('hariValue')) {
                        hariValue.innerHTML = hari;
                    }
                    const topbarHari = document.getElementById('topbarHari');
                    if (topbarHari) topbarHari.innerHTML = 'Hari ke-' + hari;

                    const kematangan = current.kematangan_pct ?? 0;
                    kematanganValue.innerHTML = kematangan;
                    kematanganBar.style.width = kematangan + '%';
                    sisaHariValue.innerHTML = current.sisa_hari ?? 0;

                    recomputeDeviceStatus(current);
                });

                // B. BATCH STATUS BADGE
                batchListener = onValue(activeBatchRef, (batchSnap) => {
                    const batch = batchSnap.val() || {};
                    const badge = document.getElementById('batchStatusBadge');
                    if (badge && batch.status) {
                        const label = batch.status.charAt(0).toUpperCase() + batch.status.slice(1);
                        badge.innerHTML = 'Status: ' + label + ' <i class="bi bi-chevron-down ms-1"></i>';
                    }
                });

                // C. HISTORY TABLE & CHART
                historyListener = onValue(historyRef, (historySnap) => {
                    const raw = historySnap.val() || {};
                    const items = Object.values(raw);

                    // Sort descending untuk tabel (terbaru di atas)
                    const sortedItems = [...items].sort((a, b) => {
                        const timeA = a.timestamp || '';
                        const timeB = b.timestamp || '';
                        return timeB.localeCompare(timeA);
                    });
                    const tableItems = sortedItems.slice(0, 10);

                    let html = "";
                    tableItems.forEach((item, index) => {
                        html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>Hari ${item.hari ?? 0}</td>
                            <td>${item.timestamp ?? '-'}</td>
                            <td>${item.suhu ?? 0}°C</td>
                            <td>${item.kelembapan ?? 0}%</td>
                            <td>${Number(item.co2 ?? 0).toLocaleString()} ppm</td>
                            <td>${item.ph ?? 0}</td>
                            <td><span class="badge bg-light text-dark border">${item.fase ?? '-'}</span></td>
                        </tr>`;
                    });
                    tableBody.innerHTML = html || '<tr><td colspan="8" class="text-center text-muted py-4">Belum ada data riwayat kompos untuk batch ini.</td></tr>';

                    // Urutan waktu naik untuk visualisasi grafik kontinu (10 data terakhir)
                    const chartItems = items.slice(-10);
                    applyChartData(
                        chartItems.map(i => 'Hari ' + (i.hari ?? 0)),
                        {
                            suhu: chartItems.map(i => i.suhu ?? 0),
                            kelembapan: chartItems.map(i => i.kelembapan ?? 0),
                            co2: chartItems.map(i => i.co2 ?? 0),
                            ph: chartItems.map(i => i.ph ?? 0)
                        }
                    );
                });
            }
        });

        // 2) CONTROL (mode auto/manual + manual command)
        onValue(controlRef, (snapshot) => {
            const control = snapshot.val() || {};
            latestControlMode = control.mode || 'auto';

            modeAuto.checked = latestControlMode === 'auto';
            modeManual.checked = latestControlMode === 'manual';

            blowerToggle.checked = control.kipas_manual == 1;
            pengadukToggle.checked = control.pengaduk_manual == 1;

            const showManual = latestControlMode !== 'auto';
            blowerManualBox.style.display = showManual ? 'flex' : 'none';
            pengadukManualBox.style.display = showManual ? 'flex' : 'none';
            blowerToggle.disabled = !showManual;
            pengadukToggle.disabled = !showManual;

            recomputeDeviceStatus(latestCurrent, control);
        });

        function recomputeDeviceStatus(current, control) {
            latestCurrent = current || latestCurrent;
            control = control || {};

            let kipasStatus = 0;
            let pengadukStatus = 0;

            if (latestControlMode === 'auto') {
                kipasStatus = latestCurrent.kipas ?? 0;
                pengadukStatus = latestCurrent.pengaduk ?? 0;
            } else {
                kipasStatus = control.kipas_manual ?? 0;
                pengadukStatus = control.pengaduk_manual ?? 0;
            }

            kipasValue.innerHTML = kipasStatus == 1 ? 'ON' : 'OFF';
            kipasValue.className = kipasStatus == 1 ? 'badge bg-success' : 'badge bg-secondary';
            setDeviceAnim('kipasIcon', kipasStatus == 1);

            pengadukValue.innerHTML = pengadukStatus == 1 ? 'ON' : 'OFF';
            pengadukValue.className = pengadukStatus == 1 ? 'badge bg-success' : 'badge bg-secondary';
            setDeviceAnim('pengadukIcon', pengadukStatus == 1);
        }

        // ==========================================================
        // DEVICE CONTROL — tulis langsung ke Firebase RTDB
        // ==========================================================
        blowerToggle.addEventListener('change', function () {
            set(child(controlRef, 'kipas_manual'), this.checked ? 1 : 0);
        });

        pengadukToggle.addEventListener('change', function () {
            set(child(controlRef, 'pengaduk_manual'), this.checked ? 1 : 0);
        });

        modeAuto.addEventListener('change', function () {
            set(child(controlRef, 'mode'), 'auto');
        });

        modeManual.addEventListener('change', function () {
            set(child(controlRef, 'mode'), 'manual');
        });
    </script>
    </script>
@endpush