@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <div>
                <h3 class="mb-0">Detail Rekomendasi Risiko</h3>
                <p class="mb-0 text-muted">{{ $rekomendasi->peristiwa_risiko }}</p>
            </div>
            <div class="ms-auto">
                <a href="{{ route('rekomendasi-risiko.show', ['unit' => $rekomendasi->unit_id, 'periode' => $rekomendasi->periode_id]) }}" class="btn btn-outline-secondary">
                    <span class='bx bx-arrow-back'></span> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent"><span class="nav-item-circle">1</span></span>
                    <span class="h3 mb-0">Data Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <div class="divider mb-5 mt-0">
                    <div class="divider-text"><h5 class="mb-0 ff-heading-sm">Periode Tahun {{ $rekomendasi->periode->tahun }} untuk Divisi {{ $rekomendasi->unit->name }}</h5></div>
                </div>
                <div class="row g-3 gx-md-5">
                    <div class="col-md-6">
                        <div class="form-group"><label class="form-label fw-bold">Sasaran</label><div class="p-3 bg-light rounded">{{ $rekomendasi->target_capaian_kinerja ?? '-' }}</div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label class="form-label fw-bold">Jenis Risiko T2 & T3 KBUMN</label><div class="p-3 bg-light rounded">{{ optional($rekomendasi->jenisRisiko->kategoriRisiko)->title ?? '-' }} - {{ optional($rekomendasi->jenisRisiko)->title ?? '-' }}</div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label class="form-label fw-bold">Peristiwa Risiko</label><div class="p-3 bg-light rounded">{{ $rekomendasi->peristiwa_risiko ?? '-' }}</div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label class="form-label fw-bold">Deskripsi Peristiwa Risiko</label><div class="p-3 bg-light rounded">{{ $rekomendasi->deskripsi_peristiwa_risiko ?? '-' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent"><span class="nav-item-circle">2</span></span>
                    <span class="h3 mb-0">Kontrol</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row gy-3 gx-xxl-6">
                    <div class="col-md-6">
                        <div class="form-group mb-4"><label class="form-label fw-bold">Jenis Kontrol Eksisting</label><div class="p-3 bg-light rounded">{{ optional($rekomendasi->jenisKontrolEksisting)->jenis_kontrol ?? '-' }}</div></div>
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Kontrol Eksisting</label>
                            @forelse($rekomendasi->kontrolEksistings as $kontrol)
                                <div class="p-3 bg-light rounded @if(!$loop->first) mt-2 @endif">{{ $loop->iteration }}. {{ $kontrol->kontrol_eksisting }}</div>
                            @empty
                                <div class="p-3 bg-light rounded">-</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4"><label class="form-label fw-bold">Penilaian Efektivitas Kontrol</label><div class="p-3 bg-light rounded">{{ optional($rekomendasi->penilaianEfektifitasKontrol)->efektivitas_kontrol ?? '-' }}</div></div>
                        <div class="form-group mb-4"><label class="form-label fw-bold">Perkiraan Waktu Mulai Terpapar Risiko</label><div class="p-3 bg-light rounded">{{ $rekomendasi->perkiraan_waktu_terpapar_risiko_mulai ? \Carbon\Carbon::parse($rekomendasi->perkiraan_waktu_terpapar_risiko_mulai)->format('d F Y') : '-' }}</div></div>
                        <div class="form-group mb-4"><label class="form-label fw-bold">Perkiraan Waktu Selesai Terpapar Risiko</label><div class="p-3 bg-light rounded">{{ $rekomendasi->perkiraan_waktu_terpapar_risiko_akhir ? \Carbon\Carbon::parse($rekomendasi->perkiraan_waktu_terpapar_risiko_akhir)->format('d F Y') : '-' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent"><span class="nav-item-circle">3</span></span>
                    <span class="h3 mb-0">Penyebab Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($rekomendasi->penyebabRisikos as $penyebab)
                        <li class="list-group-item">{{ $penyebab->penyebab_risiko }}</li>
                    @empty
                        <li class="list-group-item text-muted">Tidak ada data penyebab risiko.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent"><span class="nav-item-circle">4</span></span>
                    <span class="h3 mb-0">Key Risk Indicator</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Key Risk Indicator</th>
                                <th width="15%">Satuan KRI</th>
                                <th width="15%">Batas Aman</th>
                                <th width="15%">Batas Siaga</th>
                                <th width="15%">Batas Bahaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekomendasi->kris as $kri)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $kri->kri ?? '-' }}</td>
                                    <td>{{ $kri->satuan_kri ?? '-' }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $kri->batas_aman ?? '-' }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning">{{ $kri->batas_waspada ?? '-' }}</span></td>
                                    <td class="text-center"><span class="badge bg-danger">{{ $kri->batas_bahaya ?? '-' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada Key Risk Indicator.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endsection