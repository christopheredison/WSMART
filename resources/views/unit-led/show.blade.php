@extends('layouts.default')
@section('dashboard')
    @include('partials.success-message')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="lead__icon bg-warning-subtle">
                            <div class="svg-icon svg-icon-warning">
                                @include('partials.icon-layer')
                            </div>
                        </div>
                        <h2 class="h3">Detail Loss Event Unit</h2>
                        <div class="col-auto ms-auto">
                            <a href="{{ route('unit-led.edit', $lossEvent->id) }}" class="btn btn-info btn-sm">
                                <i class="bx bx-edit"></i>
                                <span class="ms-1">Edit</span>
                            </a>
                            <a href="{{ route('unit-led.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bx bx-arrow-back"></i>
                                <span class="ms-1">Kembali</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Kejadian</label>
                            <p class="form-control-plaintext">{{ $lossEvent->nama_kejadian ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tanggal Kejadian</label>
                            <p class="form-control-plaintext">{{ $lossEvent->tanggal_kejadian ? \Carbon\Carbon::parse($lossEvent->tanggal_kejadian)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Identifikasi Kejadian</label>
                            <p class="form-control-plaintext">{{ $lossEvent->identifikasi_kejadian ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kategori Kejadian</label>
                            <p class="form-control-plaintext">{{ $lossEvent->kategoriKejadian->kategori_kejadian ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sumber Penyebab Kejadian</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->sumber_penyebab_kejadian == 1)
                                    Internal
                                @elseif($lossEvent->sumber_penyebab_kejadian == 2)
                                    Eksternal
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Penyebab Masalah</label>
                            <p class="form-control-plaintext">{{ $lossEvent->penyebab_masalah ?? '-' }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Penanganan saat Kejadian</label>
                            <p class="form-control-plaintext">{{ $lossEvent->penanganan_kejadian ?? '-' }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Deskripsi Kejadian - Risk Event</label>
                            <p class="form-control-plaintext">{{ $lossEvent->deskripsi_kejadian ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kategori Risiko BUMN</label>
                            <p class="form-control-plaintext">
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
                            <p class="form-control-plaintext">{{ $lossEvent->kategoriRisiko->title ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Jenis Risiko</label>
                            <p class="form-control-plaintext">{{ $lossEvent->jenisRisiko->title ?? '-' }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Penjelasan Kerugian</label>
                            <p class="form-control-plaintext">{{ $lossEvent->penjelasan_kerugian ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nilai Kerugian</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->nilai_kerugian_finansial && $lossEvent->nilai_kerugian_finansial > 0)
                                    Rp {{ number_format($lossEvent->nilai_kerugian_finansial, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kejadian Berulang</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->kejadian_berulang == 1)
                                    Ya
                                @elseif($lossEvent->kejadian_berulang == 0)
                                    Tidak
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @if($lossEvent->kejadian_berulang == 1)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Frekuensi Kejadian</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->frekuensi_kejadian)
                                    {{ $lossEvent->frekuensi_kejadian }} kali
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @endif
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Mitigasi yang Direncanakan</label>
                            <p class="form-control-plaintext">{{ $lossEvent->rencana_mitigasi ?? '-' }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Realisasi Mitigasi</label>
                            <p class="form-control-plaintext">{{ $lossEvent->realisasi_mitigasi ?? '-' }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Perbaikan Mendatang</label>
                            <p class="form-control-plaintext">{{ $lossEvent->perbaikan_mendatang ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Pihak Terkait</label>
                            <p class="form-control-plaintext">{{ $lossEvent->unit_penanggung_jawab ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status Asuransi</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->status_asuransi == 1)
                                    Ya
                                @elseif($lossEvent->status_asuransi == 0)
                                    Tidak
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @if($lossEvent->status_asuransi == 1)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nilai Premi</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->nilai_premi && $lossEvent->nilai_premi > 0)
                                    Rp {{ number_format($lossEvent->nilai_premi, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nilai Klaim</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->nilai_klaim && $lossEvent->nilai_klaim > 0)
                                    Rp {{ number_format($lossEvent->nilai_klaim, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status dalam Risk Register</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->status_risk_register == 1)
                                    Ya
                                @elseif($lossEvent->status_risk_register == 0)
                                    Tidak
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @if($lossEvent->status_risk_register == 1)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">No Urut Risiko</label>
                            <p class="form-control-plaintext">{{ $lossEvent->no_urut_risiko ?? '-' }}</p>
                        </div>
                        @endif
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Biaya Risiko Inheren</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->biaya_risiko_inheren && $lossEvent->biaya_risiko_inheren > 0)
                                    Rp {{ number_format($lossEvent->biaya_risiko_inheren, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Biaya Upaya Perbaikan</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->biaya_upaya_perbaikan && $lossEvent->biaya_upaya_perbaikan > 0)
                                    Rp {{ number_format($lossEvent->biaya_upaya_perbaikan, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hasil dari Perbaikan</label>
                            <p class="form-control-plaintext">
                                @if($lossEvent->hasil_perbaikan && $lossEvent->hasil_perbaikan > 0)
                                    Rp {{ number_format($lossEvent->hasil_perbaikan, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection