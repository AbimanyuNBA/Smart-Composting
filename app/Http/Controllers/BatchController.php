<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function create()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        $activeBatch =
            $system['active_batch'] ?? null;

        if ($activeBatch) {

            $currentBatch = $database
                ->getReference(
                    "batches/$activeBatch"
                )
                ->getValue();

            $status =
                $currentBatch['status'] ?? 'draft';

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

                return redirect('/simulation-control')
                    ->with(
                        'error',
                        "Batch $activeBatch belum selesai"
                    );
            }
        }

        $batches = $database
            ->getReference('batches')
            ->getValue();

        $batchCount =
            count($batches ?? []);

        $newBatchNumber =
            $batchCount + 1;

        $batchId =
            'batch_' .
            str_pad(
                $newBatchNumber,
                3,
                '0',
                STR_PAD_LEFT
            );

        $database
            ->getReference(
                "batches/$batchId"
            )
            ->set([
                'status' => 'draft',

                'start_date' => '-',
                'start_timestamp' => 0,

                'end_date' => '-',
                'end_timestamp' => 0
            ]);

        $database
            ->getReference(
                'system/active_batch'
            )
            ->set($batchId);

        $database
            ->getReference(
                'system/current_row'
            )
            ->set(1);

        return redirect('/simulation-control')
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

            return redirect('/simulation-control')
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

        // sementara masih pakai simulator lama

        $database
            ->getReference(
                'system/simulation_running'
            )
            ->set(true);

        return redirect('/simulation-control')
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

            return redirect('/simulation-control')
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

        $database
            ->getReference(
                'system/simulation_running'
            )
            ->set(false);

        return redirect('/simulation-control')
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

            return redirect('/simulation-control');
        }

        $database
            ->getReference(
                "batches/$activeBatch/status"
            )
            ->set('active');

        $database
            ->getReference(
                'system/simulation_running'
            )
            ->set(true);

        return redirect('/simulation-control')
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

            return redirect('/simulation-control')
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

        $database
            ->getReference(
                'system/simulation_running'
            )
            ->set(false);

        return redirect('/simulation-control')
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

            return redirect('/simulation-control')
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

        $database
            ->getReference(
                'system/simulation_running'
            )
            ->set(false);

        return redirect('/simulation-control')
            ->with(
                'success',
                "Batch $activeBatch berhasil dibatalkan"
            );
    }
}
