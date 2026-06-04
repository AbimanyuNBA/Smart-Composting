<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Laravel\Firebase\Facades\Firebase;




Route::get('/', function () {
    return view('welcome');
});

//koneksi ke firestore

Route::get('/firebase-test', function () {

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/firebase_credentials.json'));

    $firestore = new FirestoreClient([
        'projectId' => 'smart-composting',
    ]);

    return 'Firebase Connected Successfully';
});



//koneksi ke realtime database

Route::get('/firebase-db-test', function () {

    $database = Firebase::database();

    $database->getReference('test')
        ->set([
            'message' => 'Hello Firebase',
            'time' => now()->toDateTimeString()
        ]);

    return 'Berhasil kirim ke Realtime Database';
});


Route::get('/time-test', function () {
    return now()->toDateTimeString();
});


//data test

Route::get('/current-data-test', function () {

    $database = app('firebase.database');

    $system = $database
        ->getReference('system')
        ->getValue();

    $activeBatch =
        $system['active_batch'] ?? 'batch_001';

    $database
        ->getReference(
            "batches/$activeBatch/current_data"
        )
        ->set([
            'timestamp' => now()->toDateTimeString(),

            'hari' => 1,
            'fase' => 'Mesofilik',

            'suhu' => 28.1,
            'kelembapan' => 85.5,
            'ph' => 4.82,
            'co2' => 596,

            'kipas' => 1,
            'pengaduk' => 0,

            'kematangan_pct' => 5,
            'sisa_hari' => 20,

            'prediction_status' => 'completed'
        ]);

    return "Current Data berhasil dikirim ke $activeBatch";
});


//history test

Route::get('/history-test', function () {

    $database = app('firebase.database');

    $system = $database
        ->getReference('system')
        ->getValue();

    $activeBatch =
        $system['active_batch'] ?? 'batch_001';

    $currentData = $database
        ->getReference(
            "batches/$activeBatch/current_data"
        )
        ->getValue();

    if (!$currentData) {

        return "Current Data belum ada";
    }

    $database
        ->getReference(
            "batches/$activeBatch/history"
        )
        ->push($currentData);

    return "History berhasil ditambahkan ke $activeBatch";
});



//test baca data
Route::get('/csv-test', function () {

    $file = 'D:/POLMAN/PENELITIAN/DATASET/dataset_fermentasi_kompos-fix.csv';

    if (!file_exists($file)) {
        return 'File tidak ditemukan';
    }

    $handle = fopen($file, 'r');

    $header = fgetcsv($handle);

    $firstRow = fgetcsv($handle);

    fclose($handle);

    return response()->json([
        'header' => $header,
        'row1' => $firstRow
    ]);
});

//test masuk firebase
Route::get('/csv-to-firebase-test', function () {

    $database = app('firebase.database');

    $system = $database
        ->getReference('system')
        ->getValue();

    $activeBatch =
        $system['active_batch'] ?? 'batch_001';

    $file = 'D:/POLMAN/PENELITIAN/DATASET/dataset_fermentasi_kompos-fix.csv';

    $handle = fopen($file, 'r');

    $header = fgetcsv($handle);

    $row = fgetcsv($handle);

    fclose($handle);

    $data = [
        'timestamp' => $row[0],
        'hari' => (int)$row[1],
        'fase' => $row[2],

        'suhu' => (float)$row[3],
        'kelembapan' => (float)$row[4],
        'ph' => (float)$row[5],
        'co2' => (int)$row[6],

        'kipas' => (int)$row[7],
        'pengaduk' => (int)$row[8],

        'prediction_status' => 'pending'
    ];

    $database
        ->getReference(
            "batches/$activeBatch/current_data"
        )
        ->set($data);

    $database
        ->getReference(
            "batches/$activeBatch/history"
        )
        ->push($data);

    return response()->json($data);
});

//simulasi: input data dummy simulasi next (klik)
Route::get('/simulate-next', function () {

    $database = app('firebase.database');

    $system = $database
        ->getReference('system')
        ->getValue();

    $activeBatch =
        $system['active_batch'] ?? 'batch_001';

    $currentRow =
        $system['current_row'];

    $file =
        'D:/POLMAN/PENELITIAN/DATASET/dataset_fermentasi_kompos-fix.csv';

    $rows =
        array_map('str_getcsv', file($file));

    $dataRow =
        $rows[$currentRow];

    $data = [
        'timestamp' => $dataRow[0],
        'hari' => (int)$dataRow[1],
        'fase' => $dataRow[2],

        'suhu' => (float)$dataRow[3],
        'kelembapan' => (float)$dataRow[4],
        'ph' => (float)$dataRow[5],
        'co2' => (int)$dataRow[6],

        'kipas' => (int)$dataRow[7],
        'pengaduk' => (int)$dataRow[8],

        'prediction_status' => 'pending'
    ];

    $database
        ->getReference(
            "batches/$activeBatch/current_data"
        )
        ->set($data);

    $database
        ->getReference(
            "batches/$activeBatch/history"
        )
        ->push($data);

    $database
        ->getReference('system/current_row')
        ->set($currentRow + 1);

    return response()->json([
        'active_batch' => $activeBatch,
        'current_row' => $currentRow,
        'status' => 'success'
    ]);
});


//tombol simulasi
// Route::get('/simulation/start', function () {

//     $database = app('firebase.database');

//     $database
//         ->getReference('system/simulation_running')
//         ->set(true);

//     return 'Simulation Started';
// });

// Route::get('/simulation/stop', function () {

//     $database = app('firebase.database');

//     $database
//         ->getReference('system/simulation_running')
//         ->set(false);

//     return 'Simulation Stopped';
// });


Route::get('/simulation/start', [SimulationController::class, 'start']);
Route::get('/simulation/stop', [SimulationController::class, 'stop']);

Route::get(
    '/simulation-control',
    [SimulationController::class, 'index']
);

Route::get(
    '/dashboard-data',
    [SimulationController::class, 'dashboardData']
);

Route::get(
    '/chart-data',
    [SimulationController::class, 'chartData']
);


Route::get('/onnx-test', function () {

    $python = env('PYTHON_PATH');

    $pythonFile =
        base_path('python/predict_onnx.py');

    $command =
        "\"$python\" \"$pythonFile\" 9 52.2 65.8 6.14 4238 0 1 2>&1";

    $output = shell_exec($command);

    $start = strrpos($output, '{');

    $json = substr($output, $start);

    return response()->json(
        json_decode($json, true)
    );
});


//preiksi test

Route::get(
    '/predict-test',
    [SimulationController::class, 'predict']
);




Route::get('/batch/create',[BatchController::class, 'create']);
Route::get(
    '/batch/start',
    [BatchController::class, 'start']
);

Route::get(
    '/batch/pause',
    [BatchController::class, 'pause']
);

Route::get(
    '/batch/resume',
    [BatchController::class, 'resume']
);

Route::get(
    '/batch/complete',
    [BatchController::class, 'complete']
);

Route::get(
    '/batch/cancel',
    [BatchController::class, 'cancel']
);