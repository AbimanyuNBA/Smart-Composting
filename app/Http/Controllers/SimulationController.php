<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimulationController extends Controller
{
    private function getActiveBatch()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        return $system['active_batch'] ?? null;
    }


    // ===============================
// REALTIME CARD + TABLE
// ===============================
public function dashboardData()
{
    $database = app('firebase.database');


    $system = $database
        ->getReference('system')
        ->getValue();


    $activeBatch =
        $this->getActiveBatch();



    $currentData = [];

    $history = [];



    if ($activeBatch) {


        // DATA TERBARU SENSOR

        $currentData = $database
            ->getReference(
                "batches/$activeBatch/current_data"
            )
            ->getValue();



        // HISTORY SENSOR

        $history = $database
            ->getReference(
                "batches/$activeBatch/history"
            )
            ->getValue();

    }




    // =====================
    // FORMAT HISTORY TABLE
    // =====================


    $history =
        array_values($history ?? []);



    // ambil 20 terakhir

    $history =
        array_slice(
            $history,
            -10
        );



    // terbaru tampil paling atas

    $history =
        array_reverse($history);





    return response()->json([


        'system' =>
            $system,


        'currentData' =>
            $currentData,


        // untuk tabel realtime
        'history' =>
            $history


    ]);
}



    // ===============================
    // HALAMAN DASHBOARD
    // ===============================
    public function index()
    {
        $database = app('firebase.database');


        $system = $database
            ->getReference('system')
            ->getValue();


        $activeBatch =
            $this->getActiveBatch();


        $batchInfo = [];

        $currentData = [];

        $history = [];


        if ($activeBatch) {


            $batchInfo = $database
                ->getReference(
                    "batches/$activeBatch"
                )
                ->getValue();


            $currentData = $database
                ->getReference(
                    "batches/$activeBatch/current_data"
                )
                ->getValue();


            $history = $database
                ->getReference(
                    "batches/$activeBatch/history"
                )
                ->getValue();
        }


        // ambil data terakhir
        $history =
            array_values($history ?? []);


        $history =
            array_slice($history, -10);


        // untuk tabel awal
        // terbaru berada di atas
        $tableHistory =
            array_reverse($history);



        $labels = [];
        $timestamps = [];
        $suhuData = [];
        $kelembapanData = [];
        $phData = [];
        $co2Data = [];
        $kematanganData = [];


        foreach ($tableHistory as $item) {


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





    // ===============================
    // REALTIME CHART + TABLE
    // ===============================
    public function chartData()
    {
        $database = app('firebase.database');


        $activeBatch =
            $this->getActiveBatch();


        $history = [];


        if ($activeBatch) {


            $history = $database
                ->getReference(
                    "batches/$activeBatch/history"
                )
                ->getValue();

        }



        $history =
            array_values($history ?? []);



        // 20 data terakhir
        $history =
            array_slice($history, -10);



        // khusus tabel
        // terbaru tampil atas
        $tableHistory =
            array_reverse($history);



        $labels = [];
        $suhuData = [];
        $kelembapanData = [];
        $phData = [];
        $co2Data = [];
        $kematanganData = [];



        // grafik tetap urutan waktu naik
        foreach ($history as $item) {


            $labels[] =
                $item['hari'] ?? '';


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



        return response()->json([


            'labels' =>
                $labels,


            'suhuData' =>
                $suhuData,


            'kelembapanData' =>
                $kelembapanData,


            'phData' =>
                $phData,


            'co2Data' =>
                $co2Data,


            'kematanganData' =>
                $kematanganData,


            // untuk tabel realtime
            'history' =>
                $tableHistory

        ]);
    }





    // ===============================
    // PREDIKSI ONNX
    // ===============================
    public function predict()
    {

        $database =
            app('firebase.database');


        $activeBatch =
            $this->getActiveBatch();



        if (!$activeBatch) {


            return response()->json([

                'message' =>
                    'Tidak ada batch aktif'

            ],404);
        }



        $currentData =
            $database
                ->getReference(
                    "batches/$activeBatch/current_data"
                )
                ->getValue();



        if (!$currentData) {


            return response()->json([

                'message' =>
                    'Data sensor belum tersedia'

            ],404);

        }



        $python =
            env('PYTHON_PATH');



        $pythonFile =
            base_path(
                'python/predict_onnx.py'
            );



        $command =
            "\"$python\" \"$pythonFile\" "

            .$currentData['hari']." "

            .$currentData['suhu']." "

            .$currentData['kelembapan']." "

            .$currentData['ph']." "

            .$currentData['co2']." "

            .$currentData['pengaduk']." "

            .$currentData['kipas']

            ." 2>&1";



        $output =
            shell_exec($command);



        $start =
            strrpos($output,'{');



        if ($start === false) {


            return response()->json([

                'message'=>
                    'Prediksi gagal'

            ],500);
        }



        $result =
            json_decode(
                substr($output,$start),
                true
            );



        $database
            ->getReference(
                "batches/$activeBatch/current_data/kematangan_pct"
            )
            ->set(
                $result['kematangan_pct']
            );


        $database
            ->getReference(
                "batches/$activeBatch/current_data/sisa_hari"
            )
            ->set(
                $result['sisa_hari']
            );


        $database
            ->getReference(
                "batches/$activeBatch/current_data/prediction_status"
            )
            ->set('completed');



        return response()->json([

            'message'=>
                'Prediksi berhasil disimpan',


            'result'=>
                $result

        ]);
    }
}