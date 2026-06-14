<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Composting Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #e0eaf5 0%, #e6efe9 100%);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #2c3e50;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: 80px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            gap: 20px;
            z-index: 100;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #6c757d;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
        }

        .icon-box.active { background: #3b82f6; color: white; }
        .icon-box:hover:not(.active) { background: #f8f9fa; color: #3b82f6; }

        .main-content {
            margin-left: 90px;
            padding: 20px 30px;
        }

        .card-modern {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            padding: 24px;
            height: 100%;
        }

        .metric-title { font-size: 0.85rem; font-weight: 600; color: #6c757d; display: flex; align-items: center; gap: 8px; }
        .metric-value { font-size: 2rem; font-weight: 700; margin-top: 10px; color: #1a1d20; }
        .metric-value small { font-size: 1rem; color: #6c757d; }
        .progress-custom { height: 12px; border-radius: 10px; background-color: #e9ecef; margin-top: 8px; }
        .progress-custom .progress-bar { background: linear-gradient(90deg, #10b981 0%, #059669 100%); border-radius: 10px; transition: width 0.5s ease; }
        .form-switch .form-check-input { width: 3rem; height: 1.5rem; }
        .form-switch .form-check-input:checked { background-color: #10b981; border-color: #10b981; }
        .table-custom th { color: #6c757d; font-weight: 600; font-size: 0.9rem; border-bottom: 2px solid #edf2f7; }
        .table-custom td { font-size: 0.95rem; vertical-align: middle; border-bottom: 1px solid #edf2f7; }
        .btn-action { border-radius: 12px; padding: 10px 20px; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-action:hover { transform: translateY(-2px); }
    </style>
</head>
<body>

    @include('layouts.sidebar')

    <div class="main-content">
        @yield('content')
    </div>

    @stack('scripts')
    
</body>
</html>