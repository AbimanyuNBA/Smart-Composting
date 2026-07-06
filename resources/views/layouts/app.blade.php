<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Composting Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>

<body>

    <div class="sidebar">

        <div class="logo-box">
            <div class="logo-icon">🌱</div>
            <div class="logo-text">
                <div class="title">Smart Composting</div>
                <div class="subtitle">IoT &amp; AI System</div>
            </div>
        </div>

        <a href="/" class="menu-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="bi bi-house-door-fill"></i>
            Dashboard
        </a>

        <a href="/data-log" class="menu-link {{ request()->is('data-log') ? 'active' : '' }}">
            <i class="bi bi-table"></i>
            Data Log
        </a>

        <a href="/history" class="menu-link {{ request()->is('history') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i>
            Data Historis
        </a>

        <a href="/control-device" class="menu-link {{ request()->is('control-device') ? 'active' : '' }}">
            <i class="bi bi-cpu"></i>
            Control Device
        </a>

        <a href="#" class="menu-link logout">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>

    </div>

    <div class="main-content">

        <div class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-list topbar-toggle"></i>
                <span class="status-dot-inline bg-success"></span>
                <span class="topbar-batch-label">Batch Aktif : <b>{{ $activeBatch ?? '-' }}</b></span>
                @php $statusKey = strtolower($batchInfo['status'] ?? 'none'); @endphp
                <span class="status-pill {{ $statusKey }}">Status : {{ ucfirst($batchInfo['status'] ?? 'None') }}</span>
            </div>

            <div class="d-flex align-items-center gap-4">
                <div class="topbar-meta"><i class="bi bi-clock-history"></i> Last Sync : <b id="topbarSync">{{ $currentData['timestamp'] ?? '-' }}</b></div>
                <div class="topbar-meta"><i class="bi bi-calendar3"></i> <b id="topbarHari">Hari ke-{{ $currentData['hari'] ?? 0 }}</b></div>
                <div class="d-flex align-items-center gap-2 user-box">
                    <div class="avatar-circle"><i class="bi bi-mortarboard-fill"></i></div>
                    <span class="fw-bold">{{ auth()->user()->name ?? 'School' }}</span>
                    <i class="bi bi-chevron-down text-muted small"></i>
                </div>
            </div>
        </div>

        @yield('content')
    </div>

    @stack('scripts')

</body>

</html>