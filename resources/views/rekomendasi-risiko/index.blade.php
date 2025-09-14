@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="bg-info-subtle p-2 rounded-4">
                    <div class="lead__icon">
                        <div class="svg-icon svg-icon-2x svg-icon-info">
                            @include('partials.icon-layer')
                        </div>
                    </div>
                </div>
                <div class="d-block">
                    <div class="ff-preheading">Daftar Periode</div>
                    <h2>Rekomendasi Risiko Divisi</h2>
                </div>
            </div>

            <div class="card-body">
                <form id="filter-index-form" action="{{ route('rekomendasi-risiko.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <select name="periode_id" id="periode_id" class="form-select select2" onchange="this.form.submit()">
                            @foreach($periodes as $periode)
                                <option value="{{ $periode->id }}" {{ $selectedPeriodeId == $periode->id ? 'selected' : '' }}>
                                    {{ $periode->tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="unit_id" id="unit_id" class="form-select select2" onchange="this.form.submit()">
                            <option value="">Semua Divisi</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <div class="">
                    <table class="table table-hover" id="rekomendasi-index-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tahun</th>
                                <th>Divisi</th>
                                <th class="text-center">Jumlah Rekomendasi</th>
                                <th class="white-space-nowrap no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dataUnits as $index => $unit)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $periodes->find($selectedPeriodeId)->tahun ?? '-' }}</td>
                                <td>{{ $unit->name }}</td>
                                <td class="text-center">{{ $unit->rekomendasi_risikos_count }}</td>
                                <td class="white-space-nowrap">
                                    <a href="{{ route('rekomendasi-risiko.show', ['unit' => $unit->id, 'periode' => $selectedPeriodeId]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Lihat Rekomendasi Risiko">
                                        <span class="bx bx-list-check"></span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data untuk ditampilkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#rekomendasi-index-table').DataTable({
            "searching": false,
            "paging": true,
            "info": true,
            "columnDefs": [
                { "orderable": false, "targets": 'no-sort' }
            ]
        });
    });
</script>
@endpush