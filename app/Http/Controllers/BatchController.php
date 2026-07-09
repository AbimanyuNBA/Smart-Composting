<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BatchController extends Controller
{


    private function database()
    {
        return app('firebase.database');
    }





    // ==========================
    // CREATE BATCH
    // ==========================


    public function create()
    {

        $database =
            $this->database();



        // cek active batch

        $activeBatch =
            $database
            ->getReference(
                'system/active_batch'
            )
            ->getValue();




        if ($activeBatch) {


            // ambil status saja
            // jangan ambil history


            $status =
                $database
                ->getReference(
                    "batches/$activeBatch/status"
                )
                ->getValue();




            if (
                in_array(
                    $status,
                    [
                        'draft',
                        'active',
                        'paused'
                    ]
                )
            ) {


                return redirect('/')
                    ->with(
                        'error',
                        "Batch $activeBatch belum selesai"
                    );

            }

        }







        // ======================
        // NOMOR BATCH
        // ======================


        $lastNumber =
            $database
            ->getReference(
                'system/last_batch_number'
            )
            ->getValue()
            ?? 0;



        $newNumber =
            $lastNumber + 1;




        $batchId =
            'batch_' .
            str_pad(
                $newNumber,
                3,
                '0',
                STR_PAD_LEFT
            );








        // ======================
        // CREATE DATA
        // ======================


        $database
            ->getReference(
                "batches/$batchId"
            )
            ->set([


                'status' =>
                    'draft',


                'start_date' =>
                    '-',


                'start_timestamp' =>
                    0,


                'end_date' =>
                    '-',


                'end_timestamp' =>
                    0


            ]);









        // update system


        $database
            ->getReference(
                'system/active_batch'
            )
            ->set(
                $batchId
            );



        $database
            ->getReference(
                'system/current_row'
            )
            ->set(1);




        $database
            ->getReference(
                'system/last_batch_number'
            )
            ->set(
                $newNumber
            );





        return redirect('/');


    }









    // ==========================
    // START
    // ==========================


    public function start()
    {

        $database =
            $this->database();



        $activeBatch =
            $database
            ->getReference(
                'system/active_batch'
            )
            ->getValue();




        if (!$activeBatch) {


            return redirect('/')
                ->with(
                    'error',
                    'Tidak ada batch aktif'
                );

        }




        $database
            ->getReference(
                "batches/$activeBatch"
            )
            ->update([


                'status' =>
                    'active',


                'start_date' =>
                    now()->toDateTimeString(),


                'start_timestamp' =>
                    now()->timestamp

            ]);





        return redirect('/');

    }








    // ==========================
    // PAUSE
    // ==========================


    public function pause()
    {

        return $this->changeStatus(
            'paused'
        );

    }







    // ==========================
    // RESUME
    // ==========================


    public function resume()
    {

        return $this->changeStatus(
            'active'
        );

    }







    // ==========================
    // COMPLETE
    // ==========================


    public function complete()
    {

        return $this->finishBatch(
            'completed'
        );

    }







    // ==========================
    // CANCEL
    // ==========================


    public function cancel()
    {

        return $this->finishBatch(
            'cancelled'
        );

    }









    private function changeStatus($status)
    {

        $database =
            $this->database();



        $activeBatch =
            $database
            ->getReference(
                'system/active_batch'
            )
            ->getValue();



        if ($activeBatch) {


            $database
                ->getReference(
                    "batches/$activeBatch/status"
                )
                ->set(
                    $status
                );

        }



        return redirect('/');

    }










    private function finishBatch($status)
    {


        $database =
            $this->database();



        $activeBatch =
            $database
            ->getReference(
                'system/active_batch'
            )
            ->getValue();




        if ($activeBatch) {


            $database
                ->getReference(
                    "batches/$activeBatch"
                )
                ->update([


                    'status' =>
                        $status,


                    'end_date' =>
                        now()->toDateTimeString(),


                    'end_timestamp' =>
                        now()->timestamp

                ]);

        }



        return redirect('/');


    }


}