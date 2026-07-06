@extends('layouts.app')


@section('content')
    <div class="card-modern">


        <div class="d-flex justify-content-between mb-4">


            <h5 class="fw-bold">

                <i class="bi bi-table"></i>
                Data Log Sensor Lengkap

            </h5>


            <span class="badge bg-success">

                {{ $activeBatch }}

            </span>


        </div>





        <div class="table-responsive">


            <table class="table table-custom">


                <thead>


                    <tr>

                        <th>No</th>

                        <th>Waktu</th>

                        <th>Hari</th>

                        <th>Suhu</th>

                        <th>Kelembapan</th>

                        <th>CO₂</th>

                        <th>pH</th>

                        <th>Fase</th>

                        <th>Kipas</th>

                        <th>Pengaduk</th>



                        <th>Persentase Kematangan</th>

                        <th>Sisa Hari</th>


                    </tr>


                </thead>



                <tbody>


                    @foreach ($logs as $row)
                        <tr>


                            <td>

                                {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}

                            </td>



                            <td>

                                {{ $row['timestamp'] ?? '-' }}

                            </td>


                            <td>

                                Hari {{ $row['hari'] ?? '-' }}

                            </td>



                            <td>

                                {{ $row['suhu'] ?? 0 }}°C

                            </td>



                            <td>

                                {{ $row['kelembapan'] ?? 0 }}%

                            </td>


                            <td>

                                {{ $row['co2'] ?? 0 }} ppm

                            </td>


                            <td>

                                {{ $row['ph'] ?? 0 }}

                            </td>



                            <td>

                                <span class="badge bg-light text-dark border">

                                    {{ $row['fase'] ?? '-' }}

                                </span>

                            </td>



                            <td>

                                @if (($row['kipas'] ?? 0) == 1)
                                    <span class="badge bg-success">ON</span>
                                @else
                                    <span class="badge bg-secondary">OFF</span>
                                @endif

                            </td>




                            <td>

                                @if (($row['pengaduk'] ?? 0) == 1)
                                    <span class="badge bg-success">ON</span>
                                @else
                                    <span class="badge bg-secondary">OFF</span>
                                @endif


                            </td>









                            <td>

                                <b class="text-success">

                                    {{ $row['kematangan_pct'] ?? 0 }}%

                                </b>

                            </td>



                            <td>

                                {{ $row['sisa_hari'] ?? 0 }} hari

                            </td>



                        </tr>
                    @endforeach


                </tbody>


            </table>


        </div>




        <div class="d-flex justify-content-between align-items-center mt-4">


            <div class="text-muted small">

                Menampilkan
                {{ $logs->firstItem() }}
                -
                {{ $logs->lastItem() }}

                dari

                {{ $logs->total() }}

                data

            </div>


            <div>

                {{ $logs->links() }}

            </div>


        </div>



    </div>
@endsection
