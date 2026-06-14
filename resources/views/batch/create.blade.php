@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="icon-box text-primary shadow-sm" style="width: 50px; height: 50px;">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold">Buat Batch Kompos Baru</h4>
                    <p class="text-muted small mb-0">Konfigurasi parameter awal untuk memulai siklus kompos baru.</p>
                </div>
            </div>

            <hr class="mb-4">

            <form action="/batch/store" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="fw-semibold mb-2">Nama Batch</label>
                    <input type="text" name="batch_name" class="form-control form-control-lg bg-light" placeholder="Contoh: Batch_011_April" required>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="fw-semibold mb-2">Target Hari Kompos</label>
                        <div class="input-group">
                            <input type="number" name="target_days" class="form-control bg-light" value="20" required>
                            <span class="input-group-text bg-white text-muted">Hari</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold mb-2">Metode Kompos</label>
                        <select name="method" class="form-select bg-light">
                            <option value="aerobik">Aerobik (Dengan Blower)</option>
                            <option value="anaerobik">Anaerobik (Tertutup)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-5">
                    <a href="/dashboard" class="btn btn-light border px-4 py-2" style="border-radius: 10px; font-weight: 600;">Batal</a>
                    <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm" style="border-radius: 10px; font-weight: 600;">
                        <i class="bi bi-rocket-takeoff me-2"></i> Mulai Batch Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection