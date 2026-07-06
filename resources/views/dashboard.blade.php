@extends('layouts.app')

@section('content')
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
                        <div class="metric-value" id="kelembapanValue">{{ $currentData['kelembapan'] ?? 0 }}<small>%</small>
                        </div>
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

                        <span id="batchStatusBadge"
                            class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                            Status: {{ ucfirst($batchInfo['status'] ?? 'None') }} <i class="bi bi-chevron-down ms-1"></i>
                        </span>
                    </div>

                    <div class="mb-2 d-flex justify-content-between text-muted small">
                        <div>Fase Saat Ini: <span class="text-primary fw-bold"
                                id="faseValue">[{{ $currentData['fase'] ?? '-' }}]</span></div>
                        <div>Last Sync: <span id="timestampValue"
                                class="fw-semibold">{{ $currentData['timestamp'] ?? '-' }}</span></div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-muted small">AI Prediction Progress (Kematangan): <span
                                    id="kematanganValue"
                                    class="text-success fw-bold">{{ $currentData['kematangan_pct'] ?? 0 }} %</span></span>
                            <span class="fw-bold text-dark small">Hari ke-<span
                                    id="hariValue">{{ $currentData['hari'] ?? 0 }}</span></span>
                        </div>
                        <div class="progress progress-custom">
                            <div class="progress-bar" id="kematanganBar"
                                style="width: {{ $currentData['kematangan_pct'] ?? 0 }}%"></div>
                        </div>
                    </div>

                    <div class="d-flex gap-4 mb-3 align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-dot bg-success"></span>
                            <span id="predictionStatus"
                                class="badge bg-success small">{{ $currentData['prediction_status'] ?? 'completed' }}</span>
                        </div>
                        <div class="fw-semibold text-muted small">
                            Estimasi Selesai: <span id="sisaHariValue"
                                class="text-primary fw-bold">{{ $currentData['sisa_hari'] ?? 0 }}</span> Hari Lagi
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 d-flex gap-2 flex-wrap">
                    @if (($batchInfo['status'] ?? '') == 'draft')
                        <a href="/batch/start" class="btn btn-success btn-action"
                            onclick="return confirm('Mulai batch ini?')">
                            <i class="bi bi-play-fill"></i> Start Batch
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
                        <a href="/batch/create" class="btn btn-primary btn-action">
                            <i class="bi bi-box-seam-fill"></i> 📦 Buat Batch Baru
                        </a>
                        <span class="badge d-inline-flex align-items-center bg-light text-dark border px-3 rounded-3">
                            Batch Selesai / Dibatalkan
                        </span>
                    @else
                        <a href="/batch/create" class="btn btn-primary btn-action">
                            <i class="bi bi-box-seam-fill"></i> 📦 Buat Batch Baru
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">

        <div class="col-xl-8 col-lg-7">
            <div class="card-modern">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up text-muted"></i> Progress Lintasan Parameter : Suhu &
                        CO₂</h5>
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

            <div class="card-modern">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-cpu"></i>
                    Device Control
                </h5>


                <!-- MODE -->
                <div class="border rounded-4 p-3 mb-3 bg-white shadow-sm">

                    <div class="fw-bold mb-2">
                        Mode Operasi
                    </div>


                    <div class="d-flex gap-4">

                        <div class="form-check">

                            <input class="form-check-input" type="radio" name="mode" id="modeAuto" checked>

                            <label class="form-check-label">
                                AUTO
                            </label>

                        </div>



                        <div class="form-check">

                            <input class="form-check-input" type="radio" name="mode" id="modeManual">

                            <label class="form-check-label">
                                MANUAL
                            </label>

                        </div>

                    </div>

                </div>





                <!-- BLOWER -->
                <div class="border rounded-4 p-3 mb-3 bg-white shadow-sm">


                    <div class="d-flex justify-content-between">


                        <h6 class="fw-bold">
                            🌬 Aerasi (Blower)
                        </h6>


                        <!-- STATUS SENSOR -->

                        <span id="kipasValue" class="badge bg-success">

                            ON

                        </span>


                    </div>



                    <small class="text-muted">

                        Status aktual perangkat

                    </small>



                    <hr>



                    <div class="d-flex justify-content-between align-items-center">


                        <span>
                            Manual Command
                        </span>


                        <div class="form-check form-switch">

                            <input class="form-check-input" type="checkbox" id="blowerToggle">

                        </div>


                    </div>


                </div>






                <!-- PENGADUK -->

                <div class="border rounded-4 p-3 bg-white shadow-sm">


                    <div class="d-flex justify-content-between">


                        <h6 class="fw-bold">
                            🔄 Pengaduk
                        </h6>


                        <span id="pengadukValue" class="badge bg-secondary">

                            OFF

                        </span>


                    </div>



                    <small class="text-muted">

                        Status aktual perangkat

                    </small>



                    <hr>



                    <div class="d-flex justify-content-between align-items-center">


                        <span>
                            Manual Command
                        </span>



                        <div class="form-check form-switch">


                            <input class="form-check-input" type="checkbox" id="pengadukToggle">


                        </div>


                    </div>


                </div>






                <div class="border-top mt-3 pt-2 small text-muted d-flex justify-content-between">


                    <div>
                        Row Aktif:
                        <b id="currentRowValue">
                            {{ $system['current_row'] ?? 0 }}
                        </b>
                    </div>


                    <div>
                        Interval:
                        <b>
                            {{ $system['simulation_interval'] ?? 0 }} Detik
                        </b>
                    </div>


                </div>


            </div>

        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card-modern">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-layout-text-window-reverse text-muted"></i> Parameter Data
                        Tabel Log</h5>
                    <button class="btn btn-light border rounded-pill px-4 shadow-sm btn-sm">Filtered by <i
                            class="bi bi-chevron-down ms-1"></i></button>
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
                                    <td colspan="8" class="text-center text-muted">Belum ada data riwayat kompos untuk
                                        batch ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Setup Chart.js
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
                labels: @json($labels ?? []),
                datasets: [{
                        label: 'Suhu (°C)',
                        data: @json($suhuData ?? []),
                        borderColor: '#ef4444',
                        backgroundColor: gradientSuhu,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'CO₂ (ppm)',
                        data: @json($co2Data ?? []),
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

        const response =
            await fetch('/dashboard-data?t=' + Date.now());


        const data =
            await response.json();


        const current =
            data.currentData || {};


        const system =
            data.system || {};


        const control =
            data.control || {};



        // =====================
        // SENSOR CARD
        // =====================

        suhuValue.innerHTML =
            current.suhu + '<small>°C</small>';


        kelembapanValue.innerHTML =
            current.kelembapan + '<small>%</small>';


        co2Value.innerHTML =
            current.co2 + '<small>ppm</small>';


        phValue.innerHTML =
            current.ph;


        timestampValue.innerHTML =
            current.timestamp;


        faseValue.innerHTML =
            current.fase;


        hariValue.innerHTML =
            current.hari;


        currentRowValue.innerHTML =
            system.current_row;



        // =====================
        // AI
        // =====================

        let kematangan =
            current.kematangan_pct ?? 0;


        kematanganValue.innerHTML =
            kematangan + ' %';


        kematanganBar.style.width =
            kematangan + '%';


        sisaHariValue.innerHTML =
            current.sisa_hari;



        // =====================
        // STATUS AKTUAL DEVICE
        // dari current_data
        // =====================


        kipasValue.innerHTML =
            current.kipas == 1
            ? 'ON'
            : 'OFF';


        kipasValue.className =
            current.kipas == 1
            ?
            'badge bg-success'
            :
            'badge bg-secondary';



        pengadukValue.innerHTML =
            current.pengaduk == 1
            ? 'ON'
            : 'OFF';


        pengadukValue.className =
            current.pengaduk == 1
            ?
            'badge bg-success'
            :
            'badge bg-secondary';



        // =====================
        // MANUAL COMMAND
        // dari Firebase control
        // =====================


        blowerToggle.checked =
            control.kipas_manual == 1;


        pengadukToggle.checked =
            control.pengaduk_manual == 1;


        modeAuto.checked =
            control.mode == 'auto';


        modeManual.checked =
            control.mode == 'manual';




        // =====================
        // TABLE LOG
        // =====================


        let html = "";


        (data.history || []).forEach(

            function(item,index){


            html += `

            <tr>

                <td>${index+1}</td>

                <td>
                    Hari ${item.hari}
                </td>

                <td>
                    ${item.timestamp}
                </td>

                <td>
                    ${item.suhu}°C
                </td>

                <td>
                    ${item.kelembapan}%
                </td>

                <td>
                    ${item.co2} ppm
                </td>

                <td>
                    ${item.ph}
                </td>

                <td>

                <span class="badge bg-light text-dark border">

                    ${item.fase}

                </span>

                </td>


            </tr>

            `;

        });



        tableBody.innerHTML =
            html;



    } catch(error){

        console.log(
            "Realtime Error",
            error
        );

    }

}





        // ===============================
        // REALTIME CHART
        // ===============================

        async function refreshCharts() {

            try {


                const response =
                    await fetch(
                        '/chart-data?t=' + Date.now()
                    );


                const data =
                    await response.json();



                mainChart.data.labels =
                    data.labels;



                mainChart
                    .data
                    .datasets[0]
                    .data =
                    data.suhuData;



                mainChart
                    .data
                    .datasets[1]
                    .data =
                    data.co2Data;



                mainChart.update();


            } catch (error) {

                console.log(
                    "Chart Error",
                    error
                );

            }

        }






        // ===============================
        // INTERVAL
        // ===============================

        setInterval(
            refreshDashboard,
            2000
        );


        setInterval(
            refreshCharts,
            5000
        );



        refreshDashboard();

        refreshCharts();




        document
            .getElementById('blowerToggle')
            .addEventListener(
                'change',
                function() {


                    fetch('/device-control', {

                        method: 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content

                        },


                        body: JSON.stringify({

                            type: 'kipas',

                            value: this.checked ? 1 : 0

                        })


                    });


                });



        document
            .getElementById('pengadukToggle')
            .addEventListener(
                'change',
                function() {


                    fetch('/device-control', {

                        method: 'POST',


                        headers: {


                            'Content-Type': 'application/json',


                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content

                        },



                        body: JSON.stringify({


                            type: 'pengaduk',


                            value: this.checked ? 1 : 0


                        })


                    });


                });


        document
            .getElementById('modeAuto')
            .addEventListener(
                'change',
                function() {


                    fetch('/device-control', {

                        method: 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content
                        },


                        body: JSON.stringify({

                            type: 'mode',

                            value: 'auto'

                        })

                    });

                });


        document
            .getElementById('modeManual')
            .addEventListener(
                'change',
                function() {


                    fetch('/device-control', {

                        method: 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .content
                        },


                        body: JSON.stringify({

                            type: 'mode',

                            value: 'manual'

                        })

                    });

                });


    </script>
@endpush
