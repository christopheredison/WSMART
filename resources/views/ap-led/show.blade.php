@extends('layouts.default')
@section('dashboard')
    @include('partials.success-message')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="lead__icon bg-warning-subtle">
                                <div class="svg-icon svg-icon-warning">
                                    @include('partials.icon-layer')
                                </div>
                            </div>
                            <h2 class="h3 mb-0">Detail Loss Event Anak Perusahaan: {{ $lossEvent->unit->name ?? 'N/A' }}</h2>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('ap-led.edit', ['periode' => $lossEvent->periode_id, 'id' => $lossEvent->id]) }}" class="btn btn-info btn-sm">
                                <span class="bx bx-edit"></span>
                                <span class="ms-1">Edit</span>
                            </a>
                            <a href="{{ route('ap-led.index-by-periode', ['periode' => $lossEvent->periode_id]) }}" class="btn btn-secondary btn-sm">
                                <span class="bx bx-arrow-back"></span>
                                <span class="ms-1">Kembali</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- SECTION: INFORMASI UTAMA --}}
                    <h5 class="mb-3">Informasi Utama Kejadian</h5>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Sumber Data</label>
                            <div>
                                @if($lossEvent->risiko_id)
                                    <a href="{{ route('risk-register-ap.view', ['riskRegister' => $lossEvent->risiko_id]) }}"
                                      target="_blank"
                                      class="text-decoration-none"
                                      data-bs-toggle="tooltip"
                                      title="Klik untuk melihat detail risiko asal"
                                    >
                                        <span class="badge bg-info fs-6">
                                            <i class="bx bx-link-external me-1"></i>
                                            {{ $lossEvent->risiko->peristiwa_risiko ?? 'Detail Risiko Asal' }}
                                        </span>
                                    </a>
                                @else
                                    <span class="badge bg-primary fs-6">Input Manual</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Nama Kejadian</label>
                            <p>{{ $lossEvent->nama_kejadian ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Periode Laporan</label>
                            <p>{{ $lossEvent->periode->nama ?? '-' }} ({{ $lossEvent->periode->tahun ?? '-' }})</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tanggal Kejadian</label>
                            <p>{{ $lossEvent->tanggal_kejadian ? \Carbon\Carbon::parse($lossEvent->tanggal_kejadian)->isoFormat('D MMMM YYYY') : '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Identifikasi Kejadian</label>
                            <p>{{ $lossEvent->identifikasi_kejadian ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kategori Kejadian</label>
                            <p>{{ $lossEvent->kategoriKejadian->kategori_kejadian ?? '-' }}</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- SECTION: KLASIFIKASI RISIKO --}}
                    <h5 class="mb-3">Klasifikasi Risiko</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sumber Penyebab Kejadian</label>
                            <p>
                                @if($lossEvent->sumber_penyebab_kejadian == 1)
                                    Internal
                                @elseif($lossEvent->sumber_penyebab_kejadian == 2)
                                    Eksternal
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        {{-- <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kategori Risiko BUMN</label>
                            <p>
                                @if($lossEvent->kategori_risiko_bumn == 1)
                                    Financial
                                @elseif($lossEvent->kategori_risiko_bumn == 2)
                                    Operational
                                @elseif($lossEvent->kategori_risiko_bumn == 3)
                                    Public & Legal
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kategori Risiko T2 & T3 BUMN</label>
                            <p>{{ $lossEvent->kategoriRisiko->title ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Jenis Risiko</label>
                            <p>{{ $lossEvent->jenisRisiko->title ?? '-' }}</p>
                        </div> --}}
                    </div>

                    <hr class="my-4">

                    {{-- SECTION: DETAIL KERUGIAN --}}
                    <h5 class="mb-3">Detail Kerugian</h5>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Penjelasan Kerugian</label>
                            <p>{{ $lossEvent->penjelasan_kerugian ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nilai Kerugian Finansial</label>
                            <p class="text-danger fw-bold">
                                @if($lossEvent->nilai_kerugian_finansial && $lossEvent->nilai_kerugian_finansial > 0)
                                    Rp {{ number_format($lossEvent->nilai_kerugian_finansial, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kejadian Berulang</label>
                            <p>
                                @if($lossEvent->kejadian_berulang === 1)
                                    Ya
                                @elseif($lossEvent->kejadian_berulang === 0)
                                    Tidak
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @if($lossEvent->kejadian_berulang == 1)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Frekuensi Kejadian</label>
                            <p>
                                @if($lossEvent->frekuensi_kejadian == 6)
                                    6 kali atau lebih per tahun
                                @elseif($lossEvent->frekuensi_kejadian)
                                    {{ $lossEvent->frekuensi_kejadian }} kali per tahun
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>

                    <hr class="my-4">

                    {{-- SECTION: PENYEBAB DAN PENANGANAN --}}
                    <h5 class="mb-3">Penyebab dan Penanganan Saat Kejadian</h5>
                    @forelse($lossEvent->penyebabRisikoLeds as $penyebab)
                        <div class="mb-4">
                            <div class="p-2 rounded mb-2" style="background-color: #e9ecef;">
                                <p class="fw-bold mb-0">
                                    <i class="bx bx-subdirectory-right me-1"></i>
                                    <span class=" me-2">Penyebab {{ $loop->iteration }}:</span>
                                    {{ $penyebab->penyebab_risiko }}
                                </p>
                            </div>

                            @if($penyebab->perlakuanPenyebabRisiko->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Penanganan Saat Kejadian</th>
                                                <th>Output</th>
                                                <th>Biaya</th>
                                                <th>PIC</th>
                                                <th>Timeline</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($penyebab->perlakuanPenyebabRisiko as $perlakuan)
                                                <tr>
                                                    <td>{{ $perlakuan->rencana_perlakuan_risiko ?? '-' }}</td>
                                                    <td>{{ $perlakuan->output_perlakuan_risiko ?? '-' }}</td>
                                                    <td>{{ $perlakuan->biaya_perlakuan_risiko > 0 ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}</td>
                                                    <td>{{ $perlakuan->pic ?? '-' }}</td>
                                                    <td>
                                                        @if($perlakuan->timeline_perlakuan_risiko_start && $perlakuan->timeline_perlakuan_risiko_end)
                                                            {{ \Carbon\Carbon::parse($perlakuan->timeline_perlakuan_risiko_start)->isoFormat('D MMM YYYY') }} - {{ \Carbon\Carbon::parse($perlakuan->timeline_perlakuan_risiko_end)->isoFormat('D MMM YYYY') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-light text-center" role="alert">
                                    Tidak ada data penanganan yang tercatat untuk penyebab ini.
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-info text-center" role="alert">
                            Tidak ada data penyebab dan penanganan yang tercatat.
                        </div>
                    @endforelse

                    <hr class="my-4">

                    {{-- SECTION: INFORMASI ASURANSI --}}
                    <h5 class="mb-3">Informasi Asuransi</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status Asuransi</label>
                            <p>
                                @if($lossEvent->status_asuransi === 1)
                                    Ya
                                @elseif($lossEvent->status_asuransi === 0)
                                    Tidak
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @if($lossEvent->status_asuransi == 1)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nilai Premi</label>
                            <p>
                                @if($lossEvent->nilai_premi && $lossEvent->nilai_premi > 0)
                                    Rp {{ number_format($lossEvent->nilai_premi, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nilai Klaim</label>
                            <p>
                                @if($lossEvent->nilai_klaim && $lossEvent->nilai_klaim > 0)
                                    Rp {{ number_format($lossEvent->nilai_klaim, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
