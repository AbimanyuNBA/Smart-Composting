@extends('layouts.app')


@section('content')
    {{-- ===================== FILTER BATCH ===================== --}}

    <div class="card-modern mb-4">

        <form method="GET" action="/history">

            <div class="row align-items-center">

                <div class="col-md-4">

                    <label class="fw-bold mb-2">
                        Pilih Batch Kompos
                    </label>


                    <select name="batch" class="form-select" onchange="this.form.submit()">


                        @foreach ($batches ?? [] as $key => $batch)
                            <option value="{{ $key }}" {{ $selectedBatch == $key ? 'selected' : '' }}>

                                {{ $key }}

                            </option>
                        @endforeach


                    </select>

                </div>

                @if($selectedBatch)
                <div class="col-md-8 text-md-end mt-3 mt-md-0 pt-md-4">
                    <a href="/history/download-csv?batch={{ $selectedBatch }}" class="btn btn-success me-2">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Unduh CSV
                    </a>
                    <a href="/history/print-pdf?batch={{ $selectedBatch }}" target="_blank" class="btn btn-danger">
                        <i class="bi bi-printer"></i> Cetak PDF
                    </a>
                </div>
                @endif

            </div>

        </form>

    </div>





    {{-- ===================== CARD SUMMARY ===================== --}}

    <div class="row g-4 mb-4">


        <div class="col-md">

            <div class="metric-card">

                <div>

                    <div class="metric-label">
                        Rata-rata Suhu
                    </div>

                    <div class="metric-value">

                        {{ count($suhu) ? round(array_sum($suhu) / count($suhu), 1) : 0 }}

                        <small>°C</small>

                    </div>

                </div>

            </div>

        </div>




        <div class="col-md">

            <div class="metric-card">

                <div>

                    <div class="metric-label">
                        Rata-rata Kelembapan
                    </div>

                    <div class="metric-value">

                        {{ count($kelembapan) ? round(array_sum($kelembapan) / count($kelembapan), 1) : 0 }}

                        <small>%</small>

                    </div>

                </div>

            </div>

        </div>




        <div class="col-md">

            <div class="metric-card">

                <div>

                    <div class="metric-label">
                        Rata-rata CO₂
                    </div>

                    <div class="metric-value">

                        {{ count($co2) ? round(array_sum($co2) / count($co2)) : 0 }}

                        <small>ppm</small>

                    </div>

                </div>

            </div>

        </div>




        <div class="col-md">

            <div class="metric-card">

                <div>

                    <div class="metric-label">
                        Rata-rata pH
                    </div>

                    <div class="metric-value">

                        {{ count($ph) ? round(array_sum($ph) / count($ph), 2) : 0 }}

                    </div>

                </div>

            </div>

        </div>





        <div class="col-md">

            <div class="metric-card">

                <div>

                    <div class="metric-label">
                        Kematangan Akhir
                    </div>

                    <div class="metric-value">

                        {{ end($kematangan) ?: 0 }}

                        <small>%</small>

                    </div>

                </div>

            </div>

        </div>


    </div>









    {{-- ===================== GRAFIK SENSOR ===================== --}}


    <div class="card-modern mb-4">

<h6 class="fw-bold mb-3">
📈 Grafik Suhu, Kelembapan, CO₂ dan pH
</h6>


<div style="height:430px">

    <canvas id="sensorChart"></canvas>

</div>


</div>



<div class="card-modern mb-4">


<h6 class="fw-bold mb-3">
🤖 Progress Kematangan AI
</h6>


<div style="height:350px">

    <canvas id="aiChart"></canvas>

</div>


</div>






    {{-- ===================== GRAFIK AI ===================== --}}


  






    {{-- ===================== TABLE HISTORY ===================== --}}


    <div class="card-modern">


        <h6 class="fw-bold mb-3">

            Rekap Data Sensor Batch

        </h6>



        <div class="table-responsive">


            <table class="table table-custom">


                <thead>


                    <tr>

                        <th>No</th>
                        <th>Waktu</th>
                        <th>Hari</th>
                        <th>Suhu</th>
                        <th>Kelembapan</th>
                        <th>CO₂</th>
                        <th>pH</th>
                        <th>Kipas</th>
                        <th>Pengaduk</th>
                        <th>AI</th>
                        <th>Sisa Hari</th>

                    </tr>


                </thead>



                <tbody>


                    @foreach ($logs as $index => $row)
                        <tr>


                            <td>

                                {{ $logs->firstItem() + $index }}

                            </td>



                            <td>
                                {{ $row['timestamp'] ?? '-' }}
                            </td>



                            <td>
                                Hari {{ $row['hari'] ?? '-' }}
                            </td>



                            <td>
                                {{ $row['suhu'] ?? 0 }}°C
                            </td>



                            <td>
                                {{ $row['kelembapan'] ?? 0 }}%
                            </td>



                            <td>
                                {{ $row['co2'] ?? 0 }} ppm
                            </td>



                            <td>
                                {{ $row['ph'] ?? 0 }}
                            </td>




                            <td>

                                @if (($row['kipas'] ?? 0) == 1)
                                    <span class="badge bg-success">
                                        ON
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        OFF
                                    </span>
                                @endif

                            </td>





                            <td>

                                @if (($row['pengaduk'] ?? 0) == 1)
                                    <span class="badge bg-success">
                                        ON
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        OFF
                                    </span>
                                @endif

                            </td>




                            <td class="text-success fw-bold">

                                {{ $row['kematangan_pct'] ?? 0 }}%

                            </td>



                            <td>

                                {{ $row['sisa_hari'] ?? 0 }} hari

                            </td>



                        </tr>
                    @endforeach



                </tbody>


            </table>


        </div>



        <div class="mt-3">

            {{ $logs->links() }}

        </div>



    </div>
@endsection



@push('scripts')
    <script>
        // ===============================
        // SENSOR HISTORY CHART
        // ===============================

        const sensorCtx = document
            .getElementById('sensorChart')
            .getContext('2d');


        // gradient suhu
        let gradSuhu = sensorCtx.createLinearGradient(0, 0, 0, 350);

        gradSuhu.addColorStop(0, 'rgba(239,68,68,0.35)');
        gradSuhu.addColorStop(1, 'rgba(239,68,68,0)');


        // gradient kelembapan
        let gradHum = sensorCtx.createLinearGradient(0, 0, 0, 350);

        gradHum.addColorStop(0, 'rgba(59,130,246,0.35)');
        gradHum.addColorStop(1, 'rgba(59,130,246,0)');


        // gradient CO2
        let gradCO2 = sensorCtx.createLinearGradient(0, 0, 0, 350);

        gradCO2.addColorStop(0, 'rgba(16,185,129,0.35)');
        gradCO2.addColorStop(1, 'rgba(16,185,129,0)');





        new Chart(sensorCtx, {


            type: 'line',


            data: {


                labels: @json($labels),



                datasets: [



                    // ================= SUHU =================

                    {

                        label: 'Suhu (°C)',

                        data: @json($suhu),

                        borderColor: '#ef4444',

                        backgroundColor: gradSuhu,

                        fill: true,

                        tension: 0.45,

                        pointRadius: 3,

                        yAxisID: 'y'

                    },




                    // ================= KELEMBAPAN =================


                    {

                        label: 'Kelembapan (%)',

                        data: @json($kelembapan),

                        borderColor: '#3b82f6',

                        backgroundColor: gradHum,

                        fill: true,

                        tension: 0.45,

                        pointRadius: 3,

                        yAxisID: 'y'

                    },




                    // ================= CO2 =================


                    {

                        label: 'CO₂ (ppm)',

                        data: @json($co2),

                        borderColor: '#10b981',

                        backgroundColor: gradCO2,

                        fill: true,

                        tension: 0.45,

                        pointRadius: 3,

                        yAxisID: 'y1'

                    },




                    // ================= PH =================


                    {

                        label: 'pH',

                        data: @json($ph),

                        borderColor: '#8b5cf6',

                        borderWidth: 2,

                        fill: false,

                        tension: 0.45,

                        pointRadius: 3,

                        yAxisID: 'y'

                    }


                ]



            },




            options: {


                responsive: true,


                maintainAspectRatio: false,



                plugins: {


                    legend: {


                        position: 'top',


                        labels: {


                            usePointStyle: true,


                            font: {

                                weight: 'bold'

                            }


                        }


                    }


                },





                scales: {



                    x: {


                        grid: {

                            display: false

                        }


                    },





                    // kiri

                    y: {


                        type: 'linear',


                        position: 'left',


                        title: {


                            display: true,


                            text: 'Suhu / Kelembapan / pH'


                        },



                        grid: {


                            borderDash: [5, 5]


                        }


                    },




                    // kanan khusus CO2


                    y1: {


                        type: 'linear',


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
        // AI MATURITY CHART
        // ===============================


        const aiCtx = document
            .getElementById('aiChart')
            .getContext('2d');



        let gradAI = aiCtx.createLinearGradient(0, 0, 0, 300);


        gradAI.addColorStop(0, 'rgba(16,185,129,0.4)');

        gradAI.addColorStop(1, 'rgba(16,185,129,0)');



        new Chart(aiCtx, {


            type: 'line',


            data: {


                labels: @json($labels),


                datasets: [{


                    label: 'Kematangan (%)',

                    data: @json($kematangan),

                    borderColor: '#10b981',

                    backgroundColor: gradAI,

                    fill: true,

                    tension: 0.45,

                    pointRadius: 3


                }]


            },



            options: {


                responsive: true,


                maintainAspectRatio: false,

                plugins: {


                    legend: {


                        position: 'top'


                    }


                }



            }



        });
    </script>
@endpush
