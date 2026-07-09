<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;
use App\Http\Controllers\SimulationController;


#[Signature('compost:simulate')]
#[Description('Simulator Smart Composting')]
class SimulateCompostData extends Command
{
    public function handle()
    {

        $database =
            app('firebase.database');


        while (true) {


            // =========================
            // CEK SYSTEM
            // =========================


            $system =
                $database
                ->getReference('system')
                ->getValue();



            $activeBatch =
                $system['active_batch'] ?? null;



            if (!$activeBatch) {


                sleep(1);

                continue;
            }




            // =========================
            // CEK STATUS BATCH
            // =========================


            $batchInfo =
                $database
                ->getReference(
                    "batches/$activeBatch"
                )
                ->getValue();



            $status =
                $batchInfo['status']
                ?? 'draft';




            if ($status !== 'active') {


                $this->info(
                    "Batch $activeBatch : $status"
                );


                sleep(1);


                continue;
            }







            // =========================
            // AMBIL DATA CSV
            // =========================


            $currentRow =
                $system['current_row'] ?? 1;



            $file =
                storage_path(
                    'dataset/dataset_fermentasi_kompos-fix.csv'
                );



            $rows =
                array_map(
                    'str_getcsv',
                    file($file)
                );



            $totalRows =
                count($rows) - 1;




            if ($currentRow > $totalRows) {


                $database
                    ->getReference(
                        'system/simulation_running'
                    )
                    ->set(false);



                $this->info(
                    'Dataset selesai'
                );


                break;
            }





            $dataRow =
                $rows[$currentRow];







            // =========================
            // DATA SENSOR SAJA
            // (SIMULASI ESP32)
            // =========================


            $data = [


                'timestamp' =>

                    $dataRow[0],



                'hari' =>

                    (int)
                    $dataRow[1],



                'fase' =>

                    $dataRow[2],



                'suhu' =>

                    (float)
                    $dataRow[3],



                'kelembapan' =>

                    (float)
                    $dataRow[4],



                'ph' =>

                    (float)
                    $dataRow[5],



                'co2' =>

                    (int)
                    $dataRow[6],



                'kipas' =>

                    (int)
                    $dataRow[7],



                'pengaduk' =>

                    (int)
                    $dataRow[8],

            ];







            // =========================
            // KIRIM KE PROCESSOR AI
            // =========================


            app(
                SimulationController::class
            )
            ->receiveSensor(
                $data
            );








            // =========================
            // UPDATE BARIS CSV
            // =========================


            $database
                ->getReference(
                    'system/current_row'
                )
                ->set(
                    $currentRow + 1
                );






            $this->info(

                "Baris {$currentRow} dikirim | AI processed"

            );





            sleep(
                $system['simulation_interval']
                ?? 5
            );

        }

    }
}