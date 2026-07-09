<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function create()
{
    $database = app('firebase.database');


    // ==========================
    // CEK BATCH AKTIF
    // ==========================

    $system =
        $database
        ->getReference('system')
        ->getValue();


    $activeBatch =
        $system['active_batch'] ?? null;



    if ($activeBatch) {


        $currentBatch =
            $database
            ->getReference(
                "batches/$activeBatch"
            )
            ->getValue();


        $status =
            $currentBatch['status'] ?? null;



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





    // ==========================
    // CARI NOMOR BATCH TERAKHIR
    // ==========================


    $batches =
        $database
        ->getReference('batches')
        ->getValue();



    $lastNumber = 0;



    if ($batches) {


        foreach ($batches as $key => $value) {


            $number =
                (int)
                str_replace(
                    'batch_',
                    '',
                    $key
                );


            if ($number > $lastNumber) {

                $lastNumber = $number;

            }

        }

    }




    $batchId =
        'batch_' .
        str_pad(
            $lastNumber + 1,
            3,
            '0',
            STR_PAD_LEFT
        );





    // ==========================
    // CREATE BATCH BARU
    // ==========================


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
                0,


            // kosongkan tempat sensor baru
            'current_data' =>
                null,


            'history' =>
                null

        ]);





    // ==========================
    // UPDATE SYSTEM
    // ==========================


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




    return redirect('/')
        ->with(
            'success',
            "Batch baru berhasil dibuat: $batchId"
        );
}

    public function start()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        $activeBatch =
            $system['active_batch'] ?? null;

        if (!$activeBatch) {

            return redirect('/')
                ->with(
                    'error',
                    'Tidak ada batch aktif'
                );
        }

        $database
            ->getReference(
                "batches/$activeBatch/status"
            )
            ->set('active');

        $database
            ->getReference(
                "batches/$activeBatch/start_date"
            )
            ->set(now()->toDateTimeString());

        $database
            ->getReference(
                "batches/$activeBatch/start_timestamp"
            )
            ->set(now()->timestamp);

        return redirect('/')
            ->with(
                'success',
                "Batch $activeBatch berhasil dimulai"
            );
    }

    public function pause()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        $activeBatch =
            $system['active_batch'] ?? null;

        if (!$activeBatch) {

            return redirect('/')
                ->with(
                    'error',
                    'Tidak ada batch aktif'
                );
        }

        $database
            ->getReference(
                "batches/$activeBatch/status"
            )
            ->set('paused');

        return redirect('/')
            ->with(
                'success',
                "Batch $activeBatch berhasil di-pause"
            );
    }

    public function resume()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        $activeBatch =
            $system['active_batch'] ?? null;

        if (!$activeBatch) {

            return redirect('/')
                ->with(
                    'error',
                    'Tidak ada batch aktif'
                );
        }

        $database
            ->getReference(
                "batches/$activeBatch/status"
            )
            ->set('active');

        return redirect('/')
            ->with(
                'success',
                "Batch $activeBatch berhasil di-resume"
            );
    }

    public function complete()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        $activeBatch =
            $system['active_batch'] ?? null;

        if (!$activeBatch) {

            return redirect('/')
                ->with(
                    'error',
                    'Tidak ada batch aktif'
                );
        }

        $database
            ->getReference(
                "batches/$activeBatch/status"
            )
            ->set('completed');

        $database
            ->getReference(
                "batches/$activeBatch/end_date"
            )
            ->set(now()->toDateTimeString());

        $database
            ->getReference(
                "batches/$activeBatch/end_timestamp"
            )
            ->set(now()->timestamp);

        return redirect('/')
            ->with(
                'success',
                "Batch $activeBatch berhasil diselesaikan"
            );
    }

    public function cancel()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        $activeBatch =
            $system['active_batch'] ?? null;

        if (!$activeBatch) {

            return redirect('/')
                ->with(
                    'error',
                    'Tidak ada batch aktif'
                );
        }

        $database
            ->getReference(
                "batches/$activeBatch/status"
            )
            ->set('cancelled');

        $database
            ->getReference(
                "batches/$activeBatch/end_date"
            )
            ->set(now()->toDateTimeString());

        $database
            ->getReference(
                "batches/$activeBatch/end_timestamp"
            )
            ->set(now()->timestamp);

        return redirect('/')
            ->with(
                'success',
                "Batch $activeBatch berhasil dibatalkan"
            );
    }
}