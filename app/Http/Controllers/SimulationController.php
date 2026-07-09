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
                ->orderByKey()
                ->limitToLast(10)
                ->getValue();
        }




        $history =
            array_values(
                $history ?? []
            );



        // grafik harus urutan waktu naik

        $history =
            array_slice(
                $history,
                -10
            );



        return response()
            ->json([


                'labels' =>
                array_column(
                    $history,
                    'hari'
                ),


                'suhuData' =>
                array_column(
                    $history,
                    'suhu'
                ),


                'kelembapanData' =>
                array_column(
                    $history,
                    'kelembapan'
                ),


                'phData' =>
                array_column(
                    $history,
                    'ph'
                ),


                'co2Data' =>
                array_column(
                    $history,
                    'co2'
                ),


                'kematanganData' =>
                array_column(
                    $history,
                    'kematangan_pct'
                )

            ]);
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



        // ===============================
        // AMBIL SEMUA BATCH
        // ===============================

        $batches =
            $database
            ->getReference('batches')
            ->getValue();



        $selectedBatch =
            request('batch');



        // default batch terakhir

        if (!$selectedBatch && $batches) {


            $keys =
                array_keys($batches);


            $selectedBatch =
                end($keys);
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
