<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessSensorData extends Command
{
    protected $signature = 'compost:process';

    protected $description =
        'Process Sensor AI Smart Composting';


    private function database()
    {
        return app('firebase.database');
    }


    public function handle()
    {
        $database =
            $this->database();


        // ======================
        // ACTIVE BATCH
        // ======================

        $system =
            $database
            ->getReference('system')
            ->getValue();


        $activeBatch =
            $system['active_batch'] ?? null;


        if (!$activeBatch) {

            $this->info(
                'Tidak ada batch aktif'
            );

            return;
        }



        // ======================
        // STATUS BATCH
        // ======================


        $status =
            $database
            ->getReference(
                "batches/$activeBatch/status"
            )
            ->getValue();


        if ($status !== 'active') {

            $this->info(
                "Batch $activeBatch : $status"
            );

            return;
        }



        // ======================
        // CURRENT DATA
        // ======================


        $data =
            $database
            ->getReference(
                "batches/$activeBatch/current_data"
            )
            ->getValue();


        if (!$data) {

            $this->info(
                'Data sensor kosong'
            );

            return;
        }



        // ======================
        // CEK DATA BARU
        // ======================


        if (
            ($data['prediction_status'] ?? '')
            !== 'waiting'
        ) {

            $this->info(
                'Belum ada data baru'
            );

            return;
        }



        // ======================
        // DEFAULT SENSOR
        // ======================


        $data['hari'] =
            (int)
            ($data['hari'] ?? 1);


        $data['timestamp'] =
            $data['timestamp']
            ??
            now()->toDateTimeString();


        $data['pengaduk'] =
            (int)
            ($data['pengaduk'] ?? 0);


        $data['kipas'] =
            (int)
            ($data['kipas'] ?? 0);




        // ======================
        // RANDOM FOREST ONNX
        // ======================


        $python =
            env('PYTHON_PATH');


        $pythonFile =
            base_path(
                'python/predict_onnx.py'
            );


        $command =
            "\"$python\" \"$pythonFile\" "

            . $data['hari'] . " "

            . (float)$data['suhu'] . " "

            . (float)$data['kelembapan'] . " "

            . (float)$data['ph'] . " "

            . (int)$data['co2'] . " "

            . $data['pengaduk'] . " "

            . $data['kipas']

            . " 2>&1";


        $output =
            shell_exec($command);


        $start =
            strrpos(
                $output,
                '{'
            );



        // ======================
        // HASIL AI
        // ======================


        if ($start !== false) {


            $result =
                json_decode(

                    substr(
                        $output,
                        $start
                    ),

                    true
                );


            $data['kematangan_pct'] =
                round(
                    $result['kematangan_pct'],
                    2
                );


            $data['sisa_hari'] =
                (int)
                round(
                    $result['sisa_hari']
                );


            $data['prediction_status'] =
                'completed';

        } else {


            $data['kematangan_pct'] =
                0;


            $data['sisa_hari'] =
                0;


            $data['prediction_status'] =
                'failed';
        }



        // ======================
        // UPDATE CURRENT DATA
        // ======================


        $database
            ->getReference(
                "batches/$activeBatch/current_data"
            )
            ->set(
                $data
            );



        // ======================
        // SIMPAN HISTORY
        // ======================


        $database
            ->getReference(
                "batches/$activeBatch/history"
            )
            ->push(
                $data
            );


        $this->info(
            "Berhasil proses $activeBatch"
        );
    }
}