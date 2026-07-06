<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BatchController;
use App\Http\Controllers\SimulationController;


// ==========================
// DASHBOARD
// ==========================

Route::get(
    '/',
    [SimulationController::class,'index']
)
->name('dashboard');




// ==========================
// REALTIME DATA
// ==========================

Route::get(
    '/dashboard-data',
    [SimulationController::class,'dashboardData']
);


Route::get(
    '/chart-data',
    [SimulationController::class,'chartData']
);




// ==========================
// DEVICE CONTROL
// ==========================

Route::post(
    '/device-control',
    [SimulationController::class,'deviceControl']
);




// ==========================
// BATCH CONTROL
// ==========================


Route::get(
    '/batch/create',
    [BatchController::class,'create']
);


Route::get(
    '/batch/start',
    [BatchController::class,'start']
);


Route::get(
    '/batch/pause',
    [BatchController::class,'pause']
);


Route::get(
    '/batch/resume',
    [BatchController::class,'resume']
);


Route::get(
    '/batch/complete',
    [BatchController::class,'complete']
);


Route::get(
    '/batch/cancel',
    [BatchController::class,'cancel']
);