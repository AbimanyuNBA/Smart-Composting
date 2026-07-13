<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak PDF - Data Historis {{ $batch }}</title>
    
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 30px;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 26px;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 8px 0 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        .section-title {
            font-size: 18px;
            color: #2c3e50;
            border-left: 4px solid #3498db;
            padding-left: 10px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .chart-container {
            width: 100%;
            height: 350px;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #bdc3c7;
            padding: 8px 6px;
            text-align: center;
        }
        th {
            background-color: #34495e;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-on {
            color: #27ae60;
            font-weight: bold;
        }
        .status-off {
            color: #e74c3c;
            font-weight: bold;
        }
        .kematangan {
            color: #2980b9;
            font-weight: bold;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .chart-container { page-break-inside: avoid; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Data Historis Smart Composting</h2>
        <p>ID Batch: <strong>{{ $batch }}</strong> &nbsp;|&nbsp; Dicetak pada: {{ now()->format('d M Y H:i') }}</p>
    </div>

    @if(empty($history))
        <p style="text-align:center; margin-top:50px;">Tidak ada data historis untuk batch ini.</p>
    @else
        @php
            usort($history, fn($a, $b) => ($a['hari'] ?? 0) <=> ($b['hari'] ?? 0));
        @endphp
        
        <h3 class="section-title">Grafik Perkembangan Sensor (Seluruh Data Historis)</h3>
        <div class="chart-container">
            <canvas id="historyChart"></canvas>
        </div>

        <h3 class="section-title">Tabel Rincian Data Historis</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Hari</th>
                    <th>Suhu (°C)</th>
                    <th>Kelembapan (%)</th>
                    <th>CO₂ (ppm)</th>
                    <th>pH</th>
                    <th>Fase</th>
                    <th>Kipas</th>
                    <th>Pengaduk</th>
                    <th>Kematangan</th>
                    <th>Sisa Hari</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($history as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['timestamp'] ?? '-' }}</td>
                        <td>{{ $row['hari'] ?? '-' }}</td>
                        <td>{{ number_format($row['suhu'] ?? 0, 1) }}</td>
                        <td>{{ number_format($row['kelembapan'] ?? 0, 1) }}</td>
                        <td>{{ number_format($row['co2'] ?? 0, 0) }}</td>
                        <td>{{ number_format($row['ph'] ?? 0, 2) }}</td>
                        <td>{{ $row['fase'] ?? '-' }}</td>
                        <td>
                            @if (($row['kipas'] ?? 0) == 1)
                                <span class="status-on">ON</span>
                            @else
                                <span class="status-off">OFF</span>
                            @endif
                        </td>
                        <td>
                            @if (($row['pengaduk'] ?? 0) == 1)
                                <span class="status-on">ON</span>
                            @else
                                <span class="status-off">OFF</span>
                            @endif
                        </td>
                        <td class="kematangan">{{ number_format($row['kematangan_pct'] ?? 0, 1) }}%</td>
                        <td>{{ $row['sisa_hari'] ?? 0 }} hari</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <script>
        const historyData = {!! json_encode($history ?? []) !!};
        historyData.sort((a, b) => (a.hari || 0) - (b.hari || 0));

        const labels = historyData.map(row => row.timestamp || `Hari ${row.hari}`);
        const suhuData = historyData.map(row => row.suhu || 0);
        const kelembapanData = historyData.map(row => row.kelembapan || 0);
        const phData = historyData.map(row => row.ph || 0);
        const co2Data = historyData.map(row => row.co2 || 0);
        const kematanganData = historyData.map(row => row.kematangan_pct || 0);

        if (labels.length > 0) {
            // ===============================
            // MAIN CHART (SUHU, KELEMBAPAN, CO2, PH)
            // ===============================
            const ctx = document.getElementById('historyChart').getContext('2d');
            
            // Gradients
            const gradSuhu = ctx.createLinearGradient(0, 0, 0, 300);
            gradSuhu.addColorStop(0, 'rgba(239, 68, 68, 0.4)');
            gradSuhu.addColorStop(1, 'rgba(239, 68, 68, 0)');

            const gradHum = ctx.createLinearGradient(0, 0, 0, 300);
            gradHum.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
            gradHum.addColorStop(1, 'rgba(59, 130, 246, 0)');

            const gradCO2 = ctx.createLinearGradient(0, 0, 0, 300);
            gradCO2.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
            gradCO2.addColorStop(1, 'rgba(16, 185, 129, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Suhu (°C)',
                            data: suhuData,
                            borderColor: '#ef4444',
                            backgroundColor: gradSuhu,
                            fill: true,
                            tension: 0.45,
                            pointRadius: 3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Kelembapan (%)',
                            data: kelembapanData,
                            borderColor: '#3b82f6',
                            backgroundColor: gradHum,
                            fill: true,
                            tension: 0.45,
                            pointRadius: 3,
                            yAxisID: 'y'
                        },
                        {
                            label: 'CO₂ (ppm)',
                            data: co2Data,
                            borderColor: '#10b981',
                            backgroundColor: gradCO2,
                            fill: true,
                            tension: 0.45,
                            pointRadius: 3,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'pH',
                            data: phData,
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
                    animation: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, font: { weight: 'bold' } } }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            type: 'linear', position: 'left',
                            title: { display: true, text: 'Suhu / Kelembapan / pH' },
                            grid: { borderDash: [5, 5] }
                        },
                        y1: {
                            type: 'linear', position: 'right',
                            title: { display: true, text: 'CO₂ (ppm)' },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
            
            // Generate AI Chart Container Dynamically before main chart
            const aiChartHTML = `
            <h3 class="section-title">Grafik Prediksi Kematangan AI</h3>
            <div class="chart-container" style="height: 250px;">
                <canvas id="aiChart"></canvas>
            </div>
            `;
            document.getElementById('historyChart').parentElement.insertAdjacentHTML('afterend', aiChartHTML);
            
            // ===============================
            // AI MATURITY CHART
            // ===============================
            const aiCtx = document.getElementById('aiChart').getContext('2d');
            const gradAI = aiCtx.createLinearGradient(0, 0, 0, 300);
            gradAI.addColorStop(0, 'rgba(16,185,129,0.4)');
            gradAI.addColorStop(1, 'rgba(16,185,129,0)');

            new Chart(aiCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Kematangan (%)',
                        data: kematanganData,
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
                    animation: false,
                    plugins: { legend: { position: 'top' } }
                }
            });
        }

        setTimeout(function() {
            window.print();
        }, 800);
    </script>
</body>
</html>
