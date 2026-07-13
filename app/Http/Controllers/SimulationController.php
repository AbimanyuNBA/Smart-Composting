<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class SimulationController extends Controller
{


    private function database()
    {
        return app('firebase.database');
    }



    private function getActiveBatch()
    {

        $system =
            $this->database()
            ->getReference('system')
            ->getValue();


        return
            $system['active_batch'] ?? null;
    }






    // ====================================
    // REALTIME DASHBOARD
    // SENSOR + CONTROL + TABLE
    // ====================================

    public function dashboardData()
    {
       


        $database =
            $this->database();



        $system =
            $database
            ->getReference('system')
            ->getValue();



        // tombol manual
        $control =
            $database
            ->getReference('control')
            ->getValue();



        $activeBatch =
            $this->getActiveBatch();



        $currentData = [];

        $history = [];



        if ($activeBatch) {


            // feedback alat / sensor

            $currentData =
                $database
                ->getReference(
                    "batches/$activeBatch/current_data"
                )
                ->getValue();




            // log sensor

            $history =
                $database
                ->getReference(
                    "batches/$activeBatch/history"
                )
                ->orderByKey()
                ->limitToLast(10)
                ->getValue();
        }



        $history =
            array_values(
                $history ?? []
            );



        // tabel 10 terbaru

        $tableHistory =
            array_reverse(

                array_slice(
                    $history,
                    -10
                )

            );





        return response()
            ->json([


                'system' =>
                $system,


                // command manual
                'control' =>
                $control,


                // status alat asli
                'currentData' =>
                $currentData,


                // tabel
                'history' =>
                $tableHistory

            ]);
    }







    // ====================================
    // HALAMAN DASHBOARD
    // ====================================


    public function index()
    {

        $database =
            $this->database();


        $system =
            $database
            ->getReference('system')
            ->getValue();


        $activeBatch =
            $system['active_batch']
            ?? null;


        $batchInfo = [];

        $currentData = [];

        $history = [];



        if ($activeBatch) {


            // ambil informasi kecil saja
            $status =
                $database
                ->getReference(
                    "batches/$activeBatch/status"
                )
                ->getValue();


            $batchInfo = [

                'status' => $status

            ];



            // realtime sensor
            $currentData =
                $database
                ->getReference(
                    "batches/$activeBatch/current_data"
                )
                ->getValue();



            // ambil 10 history terakhir saja
            $history =
                $database
                ->getReference(
                    "batches/$activeBatch/history"
                )
                ->orderByKey()
                ->limitToLast(10)
                ->getValue();
        }



        $history =
            array_values(
                $history ?? []
            );


        $labels = [];

        $timestamps = [];

        $suhuData = [];

        $kelembapanData = [];

        $phData = [];

        $co2Data = [];

        $kematanganData = [];



        foreach ($history as $item) {


            $labels[] =
                $item['hari'] ?? '';


            $timestamps[] =
                $item['timestamp'] ?? '-';


            $suhuData[] =
                $item['suhu'] ?? 0;


            $kelembapanData[] =
                $item['kelembapan'] ?? 0;


            $phData[] =
                $item['ph'] ?? 0;


            $co2Data[] =
                $item['co2'] ?? 0;


            $kematanganData[] =
                $item['kematangan_pct'] ?? 0;
        }




        return view(
            'dashboard',
            compact(

                'system',

                'activeBatch',

                'batchInfo',

                'currentData',

                'labels',

                'timestamps',

                'suhuData',

                'kelembapanData',

                'phData',

                'co2Data',

                'kematanganData'

            )
        );
    }









    // ====================================
    // CHART REALTIME
    // ====================================


public function chartData()
{
    $database = $this->database();
    $activeBatch = $this->getActiveBatch();

    $history = [];
    if ($activeBatch) {
        $history = $database
            ->getReference("batches/$activeBatch/history")
            ->orderByKey()
            ->getValue(); // ambil semua, bukan limitToLast(10)
    }

    $history = array_values($history ?? []);

    // Kelompokkan per hari, lalu rata-ratakan tiap metrik
    $grouped = [];
    foreach ($history as $row) {
        $hari = $row['hari'] ?? 0;
        $grouped[$hari]['suhu'][] = $row['suhu'] ?? 0;
        $grouped[$hari]['kelembapan'][] = $row['kelembapan'] ?? 0;
        $grouped[$hari]['ph'][] = $row['ph'] ?? 0;
        $grouped[$hari]['co2'][] = $row['co2'] ?? 0;
        $grouped[$hari]['kematangan_pct'][] = $row['kematangan_pct'] ?? 0;
    }

    ksort($grouped);

    $labels = [];
    $suhuData = [];
    $kelembapanData = [];
    $phData = [];
    $co2Data = [];
    $kematanganData = [];

    foreach ($grouped as $hari => $metrics) {
        $labels[] = "Hari $hari";
        $suhuData[] = round(array_sum($metrics['suhu']) / count($metrics['suhu']), 1);
        $kelembapanData[] = round(array_sum($metrics['kelembapan']) / count($metrics['kelembapan']), 1);
        $phData[] = round(array_sum($metrics['ph']) / count($metrics['ph']), 2);
        $co2Data[] = round(array_sum($metrics['co2']) / count($metrics['co2']), 1);
        $kematanganData[] = round(array_sum($metrics['kematangan_pct']) / count($metrics['kematangan_pct']), 1);
    }

    return response()->json(compact(
        'labels', 'suhuData', 'kelembapanData', 'phData', 'co2Data', 'kematanganData'
    ));
}







    // ====================================
    // UPDATE DEVICE CONTROL
    // ====================================

    public function deviceControl(Request $request)
    {


        $database =
            $this->database();




        if ($request->type == 'kipas') {


            $database
                ->getReference(
                    'control/kipas_manual'
                )
                ->set(
                    (int)$request->value
                );
        }





        if ($request->type == 'pengaduk') {


            $database
                ->getReference(
                    'control/pengaduk_manual'
                )
                ->set(
                    (int)$request->value
                );
        }






        if ($request->type == 'mode') {


            $database
                ->getReference(
                    'control/mode'
                )
                ->set(
                    $request->value
                );
        }





        return response()
            ->json([

                'success' => true

            ]);
    }

    // ===============================
    // DATA LOG PAGE
    // ===============================
    public function dataLog()
    {

        $database =
            $this->database();


        $activeBatch =
            $this->getActiveBatch();


        $history = [];


        if ($activeBatch) {


            $history =
                $database
                ->getReference(
                    "batches/$activeBatch/history"
                )
                ->getValue();
        }


        $history =
            array_reverse(
                array_values(
                    $history ?? []
                )
            );


        // manual pagination firebase array

        $page =
            request()
            ->get(
                'page',
                1
            );


        $perPage = 10;


        $items =
            array_slice(
                $history,
                ($page - 1) * $perPage,
                $perPage
            );


        $logs =
            new \Illuminate\Pagination\LengthAwarePaginator(

                $items,

                count($history),

                $perPage,

                $page,

                [
                    'path' => request()->url()
                ]

            );



        return view(
            'data-log',
            compact(
                'logs',
                'activeBatch'
            )
        );
    }

    // ===================================
    // DATA HISTORIS BATCH
    // ===================================
    public function history()
    {

        $database =
            $this->database();



        $batchKeys =
            $database
            ->getReference('batches')
            ->getChildKeys();

        $batches = !empty($batchKeys) ? array_combine($batchKeys, $batchKeys) : [];


        $selectedBatch =
            request('batch');



        // default batch terakhir

        if (!$selectedBatch && !empty($batchKeys)) {


            $selectedBatch =
                end($batchKeys);
        }





        // ===============================
        // AMBIL HISTORY BATCH
        // ===============================

        $history = [];



        if ($selectedBatch) {


            $history =
                $database
                ->getReference(
                    "batches/$selectedBatch/history"
                )
                ->getValue();
        }




        $history =
            array_values(
                $history ?? []
            );




        // ===============================
        // DATA UNTUK GRAFIK
        // ===============================


        $labels = [];

        $suhu = [];

        $kelembapan = [];

        $co2 = [];

        $ph = [];

        $kematangan = [];




        foreach ($history as $row) {



            $labels[] =
                "Hari " .
                ($row['hari'] ?? 0);




            $suhu[] =
                $row['suhu'] ?? 0;




            $kelembapan[] =
                $row['kelembapan'] ?? 0;




            $co2[] =
                $row['co2'] ?? 0;




            $ph[] =
                $row['ph'] ?? 0;




            $kematangan[] =
                $row['kematangan_pct'] ?? 0;
        }





        // ===============================
        // PAGINATION REKAP TABEL
        // ===============================


        $page =
            request()
            ->get(
                'page',
                1
            );



        $perPage = 10;



        // terbaru tampil atas

        $tableHistory =
            array_reverse($history);




        $items =
            array_slice(

                $tableHistory,


                ($page - 1)
                    *
                    $perPage,


                $perPage

            );





        $logs =
            new \Illuminate\Pagination\LengthAwarePaginator(


                $items,


                count($tableHistory),


                $perPage,


                $page,


                [

                    'path' =>
                    request()->url(),


                    'query' =>
                    request()->query()

                ]

            );







        return view(

            'history',

            compact(


                'batches',


                'selectedBatch',


                'labels',


                'suhu',


                'kelembapan',


                'co2',


                'ph',


                'kematangan',


                'logs'


            )

        );
    }


public function downloadCsv(Request $request)
{
    $batch = $request->get('batch');
    if (!$batch) return back();

    $history = $this->database()->getReference("batches/$batch/history")->getValue();
    $history = array_values($history ?? []);

    // Urutkan berdasarkan hari (ascending)
    usort($history, fn($a, $b) => ($a['hari'] ?? 0) <=> ($b['hari'] ?? 0));

    // Mengubah header menjadi .xls (Excel HTML format) agar mendukung warna dan layout rapi
    $headers = [
        "Content-type"        => "application/vnd.ms-excel; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=history_batch_{$batch}.xls",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $columns = [
        'No', 'Hari', 'Timestamp', 'Suhu (°C)', 'Kelembapan (%)', 'pH',
        'CO2 (ppm)', 'Fase', 'Kipas', 'Pengaduk', 'Kematangan (%)', 'Sisa Hari (hari)'
    ];

    $callback = function () use ($history, $columns) {
        $file = fopen('php://output', 'w');

        // Struktur HTML dasar untuk Excel beserta CSS styling warna & border
        $html = '
        <html xmlns:x="urn:schemas-microsoft-com:office:excel">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>History Batch</x:Name>
                            <x:WorksheetOptions>
                                <x:Print>
                                    <x:ValidPrinterInfo/>
                                </x:Print>
                            </x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
            </xml>
            <![endif]-->
            <style>
                table { border-collapse: collapse; font-family: Arial, sans-serif; }
                th { background-color: #4CAF50; color: white; font-weight: bold; border: 1px solid #dddddd; padding: 8px; text-align: center; }
                td { border: 1px solid #dddddd; padding: 6px; text-align: left; }
                .center { text-align: center; }
                .right { text-align: right; }
                
                /* Style untuk status warna */
                .status-on { background-color: #d4edda; color: #155724; font-weight: bold; text-align: center; }
                .status-off { background-color: #f8d7da; color: #721c24; font-weight: bold; text-align: center; }
            </style>
        </head>
        <body>
        <table>
            <thead>
                <tr>';
        
        foreach ($columns as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }
        
        $html .= '</tr>
            </thead>
            <tbody>';

        foreach ($history as $index => $row) {
            $kipasStatus = ($row['kipas'] ?? 0) == 1 ? 'ON' : 'OFF';
            $kipasClass = $kipasStatus == 'ON' ? 'status-on' : 'status-off';

            $pengadukStatus = ($row['pengaduk'] ?? 0) == 1 ? 'ON' : 'OFF';
            $pengadukClass = $pengadukStatus == 'ON' ? 'status-on' : 'status-off';

            $html .= '<tr>';
            $html .= '<td class="center">' . ($index + 1) . '</td>';
            $html .= '<td>Hari ' . htmlspecialchars($row['hari'] ?? 0) . '</td>';
            $html .= '<td class="center">' . htmlspecialchars($row['timestamp'] ?? '-') . '</td>';
            $html .= '<td class="right">' . number_format($row['suhu'] ?? 0, 1) . '</td>';
            $html .= '<td class="right">' . number_format($row['kelembapan'] ?? 0, 1) . '</td>';
            $html .= '<td class="right">' . number_format($row['ph'] ?? 0, 2) . '</td>';
            $html .= '<td class="right">' . number_format($row['co2'] ?? 0, 0) . '</td>';
            $html .= '<td class="center">' . htmlspecialchars($row['fase'] ?? '-') . '</td>';
            
            // Kolom dengan status warna dinamis
            $html .= '<td class="' . $kipasClass . '">' . $kipasStatus . '</td>';
            $html .= '<td class="' . $pengadukClass . '">' . $pengadukStatus . '</td>';
            
            $html .= '<td class="right">' . number_format($row['kematangan_pct'] ?? 0, 1) . ' %</td>';
            $html .= '<td class="center">' . htmlspecialchars($row['sisa_hari'] ?? 0) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        fwrite($file, $html);
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

    public function printPdf(Request $request)
    {
        $batch = $request->get('batch');
        if (!$batch) return back();

        $history = $this->database()->getReference("batches/$batch/history")->getValue();
        $history = array_values($history ?? []);
        
        return view('pdf-history', compact('history', 'batch'));
    }

    // ===================================
    // HALAMAN CONTROL DEVICE
    // ===================================
    public function controlDevice()
    {

        $database =
            $this->database();


        $system =
            $database
            ->getReference('system')
            ->getValue();


        $control =
            $database
            ->getReference('control')
            ->getValue();


        $activeBatch =
            $this->getActiveBatch();



        $currentData = [];


        if ($activeBatch) {


            $currentData =
                $database
                ->getReference(
                    "batches/$activeBatch/current_data"
                )
                ->getValue();
        }



        return view(

            'control-device',

            compact(

                'system',

                'control',

                'activeBatch',

                'currentData'

            )

        );
    }



    
}
