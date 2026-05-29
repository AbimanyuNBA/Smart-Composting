<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;

#[Signature('compost:simulate')]
#[Description('Simulator Smart Composting')]
class SimulateCompostData extends Command
{
    public function handle()
    {
        $database = app('firebase.database');

        while (true) {

            $system = $database
                ->getReference('system')
                ->getValue();

            if (!$system['simulation_running']) {

                $this->info('Simulator STOPPED');

                sleep(1);

                continue;
            }

            $currentRow = $system['current_row'];

            $file =
                'D:/POLMAN/PENELITIAN/DATASET/dataset_fermentasi_kompos-fix.csv';

            $rows =
                array_map('str_getcsv', file($file));

            $totalRows =
                count($rows) - 1;

            if ($currentRow > $totalRows) {

                $database
                    ->getReference('system/simulation_running')
                    ->set(false);

                $this->info('Dataset selesai');

                break;
            }

            $dataRow = $rows[$currentRow];

            // =====================
            // DATA SENSOR
            // =====================

            $data = [

                'timestamp'  => $dataRow[0],
                'hari'       => (int)$dataRow[1],
                'fase'       => $dataRow[2],

                'suhu'       => (float)$dataRow[3],
                'kelembapan' => (float)$dataRow[4],
                'ph'         => (float)$dataRow[5],
                'co2'        => (int)$dataRow[6],

                'kipas'      => (int)$dataRow[7],
                'pengaduk'   => (int)$dataRow[8],
            ];

            // =====================
            // PREDIKSI ONNX
            // =====================

            $python = env('PYTHON_PATH');

            $pythonFile =
                base_path('python/predict_onnx.py');

            $command =
                "\"$python\" \"$pythonFile\" "
                . $data['hari'] . " "
                . $data['suhu'] . " "
                . $data['kelembapan'] . " "
                . $data['ph'] . " "
                . $data['co2'] . " "
                . $data['pengaduk'] . " "
                . $data['kipas']
                . " 2>&1";

            $output = shell_exec($command);

            $start = strrpos($output, '{');

            if ($start !== false) {

                $json =
                    substr($output, $start);

                $result =
                    json_decode($json, true);

                $data['kematangan_pct'] =
                    round($result['kematangan_pct'], 2);

                $data['sisa_hari'] =
                    (int) round($result['sisa_hari']);

                $data['prediction_status'] =
                    'completed';
            } else {

                $data['kematangan_pct'] = 0;
                $data['sisa_hari'] = 0;
                $data['prediction_status'] = 'failed';
            }

            // =====================
            // SIMPAN SEKALI SAJA
            // =====================

            $database
                ->getReference('current_data')
                ->set($data);

            $database
                ->getReference('history')
                ->push($data);

            $database
                ->getReference('system/current_row')
                ->set($currentRow + 1);

            $this->info(
                "Baris {$currentRow} | Kematangan: {$data['kematangan_pct']}% | Sisa Hari: {$data['sisa_hari']}"
            );

            sleep($system['simulation_interval']);
        }
    }
}