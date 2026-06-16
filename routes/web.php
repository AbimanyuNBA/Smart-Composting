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

Route::get(
    '/simulation-control',
    [SimulationController::class, 'index']
);

Route::get(
    '/dashboard',
    [SimulationController::class, 'index']
);

Route::get(
    '/batch/create',
    [SimulationController::class, 'create']
);



Route::get(
    '/chart-data',
    [SimulationController::class, 'chartData']
);

Route::get(
    '/predict-test',
    [SimulationController::class, 'predict']
);

Route::get(
    '/batch/create',
    [BatchController::class, 'create']
);

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


Route::get('/dashboard-data', [SimulationController::class, 'dashboardData']);
