<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimulationController extends Controller
{


    public function dashboardData()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        $currentData = $database
            ->getReference('current_data')
            ->getValue();

        return response()->json([
            'system' => $system,
            'currentData' => $currentData
        ]);
    }

    public function index()
    {
        $database = app('firebase.database');

        $system = $database
            ->getReference('system')
            ->getValue();

        $currentData = $database
            ->getReference('current_data')
            ->getValue();

        $history = $database
            ->getReference('history')
            ->getValue();


        $history = array_values($history ?? []);

        $history = array_slice($history, -20);


        $labels = [];
        $suhuData = [];
        $kelembapanData = [];
        $phData = [];
        $co2Data = [];
        $kematanganData = [];

        foreach ($history as $item) {

            $labels[] = $item['hari'] ?? '';

            $suhuData[] = $item['suhu'] ?? 0;

            $kelembapanData[] = $item['kelembapan'] ?? 0;

            $phData[] = $item['ph'] ?? 0;

            $co2Data[] = $item['co2'] ?? 0;

            $kematanganData[] = $item['kematangan_pct'] ?? 0;
        }


        return view(
            'simulation-control',
            compact(
                'system',
                'currentData',
                'labels',
                'suhuData',
                'kelembapanData',
                'phData',
                'co2Data',
                'kematanganData'
            )
        );
    }
    public function start()
    {
        $database = app('firebase.database');

        $database
            ->getReference('system/simulation_running')
            ->set(true);

        return 'Simulation Started';
    }


    public function stop()
    {
        $database = app('firebase.database');

        $database
            ->getReference('system/simulation_running')
            ->set(false);

        return 'Simulation Stopped';
    }


    public function chartData()
    {
        $database = app('firebase.database');

        $history = $database
            ->getReference('history')
            ->getValue();

        $history = array_values($history ?? []);

        $history = array_slice($history, -20);

        $labels = [];
        $suhuData = [];
        $kelembapanData = [];
        $phData = [];
        $co2Data = [];
        $kematanganData = [];

        foreach ($history as $item) {

            $labels[] = $item['hari'] ?? '';

            $suhuData[] = $item['suhu'] ?? 0;

            $kelembapanData[] =
                $item['kelembapan'] ?? 0;

            $phData[] =
                $item['ph'] ?? 0;

            $co2Data[] =
                $item['co2'] ?? 0;
            $kematanganData[] =
                $item['kematangan_pct'] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'suhuData' => $suhuData,
            'kelembapanData' => $kelembapanData,
            'phData' => $phData,
            'co2Data' => $co2Data,
            'kematanganData' => $kematanganData
        ]);
    }

    public function predict()
    {
        $database = app('firebase.database');

        $currentData = $database
            ->getReference('current_data')
            ->getValue();

        $python = env('PYTHON_PATH');

        $pythonFile =
            base_path('python/predict_onnx.py');

        $command =
            "\"$python\" \"$pythonFile\" "
            . $currentData['hari'] . " "
            . $currentData['suhu'] . " "
            . $currentData['kelembapan'] . " "
            . $currentData['ph'] . " "
            . $currentData['co2'] . " "
            . $currentData['pengaduk'] . " "
            . $currentData['kipas']
            . " 2>&1";

        $output = shell_exec($command);

        $start = strrpos($output, '{');

        $json = substr($output, $start);

        $result = json_decode($json, true);

        $database
            ->getReference('current_data/kematangan_pct')
            ->set($result['kematangan_pct']);

        $database
            ->getReference('current_data/sisa_hari')
            ->set($result['sisa_hari']);

        $database
            ->getReference('current_data/prediction_status')
            ->set('completed');

        return response()->json([
            'message' => 'Prediksi berhasil disimpan',
            'result' => $result
        ]);
    }
}
