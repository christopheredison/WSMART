@extends('layouts.default')

@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-lg-10">
    <div class="card mb-3">
      <div class="card-header d-flex flex-between-center bg-light">
        <h2 class="h4 mb-0">Detail Project</h2>
        <div class="lead__icon lead__icon_sm">
            {{-- Tombol Status (Active/Inactive) --}}
            @if($project->project_status == 1)
                <span class="badge bg-success rounded-pill">Active</span>
            @else
                <span class="badge bg-secondary rounded-pill">Inactive</span>
            @endif
        </div>
      </div>

      <div class="card-body">

        {{-- Section 1: Informasi Utama --}}
        <h5 class="text-primary mb-3"><i class="bx bx-building me-2"></i>Informasi Umum</h5>
        <div class="row mb-2">
            <div class="col-md-3 fw-bold text-muted">Nama Project (Full)</div>
            <div class="col-md-9 fw-bold">{{ $meta['nama_spk_full'] ?? $project->project_name }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3 fw-bold text-muted">Kode SPK / Project</div>
            <div class="col-md-9">{{ $project->project_code }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3 fw-bold text-muted">Profit Center</div>
            <div class="col-md-9">{{ $project->profit_center ?? '-' }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3 fw-bold text-muted">Divisi</div>
            <div class="col-md-9">
                {{ $project->divisi->name ?? ($meta['divisiname'] ?? '-') }}
                <small class="text-muted">({{ $project->cost_center_parent ?? '-' }})</small>
            </div>
        </div>

        <hr class="my-4">

        {{-- Section 2: Kontrak & Keuangan --}}
        <h5 class="text-primary mb-3"><i class="bx bx-money me-2"></i>Kontrak & Nilai</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Nilai Kontrak (NK)</div>
                    <div class="col-md-7 fw-bold text-success">
                        Rp {{ number_format($project->nk, 0, ',', '.') }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Omset (Meta)</div>
                    <div class="col-md-7">
                        @if(isset($meta['omset']))
                            Rp {{ number_format((float)$meta['omset'], 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Jenis Kontrak</div>
                    <div class="col-md-7">{{ $meta['jenis_kontrak_id'] ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Klasifikasi</div>
                    <div class="col-md-7">
                        {{ $meta['klasifikasi_name'] ?? '-' }}
                        @if(isset($meta['klasifikasi_id'])) <small>({{ $meta['klasifikasi_id'] }})</small> @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Tanggal Mulai</div>
                    <div class="col-md-7">
                        {{ $project->tanggal_mulai ? \Carbon\Carbon::parse($project->tanggal_mulai)->translatedFormat('d F Y') : '-' }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Tanggal Selesai</div>
                    <div class="col-md-7">
                        {{ $project->masa_pelaksanaan_end ? \Carbon\Carbon::parse($project->masa_pelaksanaan_end)->translatedFormat('d F Y') : '-' }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">BAST 1</div>
                    <div class="col-md-7">{{ isset($meta['bast1']) ? \Carbon\Carbon::parse($meta['bast1'])->format('d/m/Y') : '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Pembayaran</div>
                    <div class="col-md-7">{{ $meta['pembayaran_name'] ?? '-' }}</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Section 3: Manajemen & Pemilik --}}
        <h5 class="text-primary mb-3"><i class="bx bx-user-pin me-2"></i>Manajemen & Lokasi</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Manager Proyek</div>
                    <div class="col-md-7">{{ $meta['manager_proyek'] ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">NIP MP</div>
                    <div class="col-md-7">{{ $meta['nip_mp'] ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Kontak MP</div>
                    <div class="col-md-7">
                        <div><i class="bx bx-phone text-muted me-1"></i> {{ $meta['telp_manajer'] ?? '-' }}</div>
                        <div><i class="bx bx-envelope text-muted me-1"></i> {{ $meta['email_mp'] ?? '-' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Pemilik Proyek</div>
                    <div class="col-md-7">{{ $meta['nm_pemilik'] ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Sumber Dana</div>
                    <div class="col-md-7">{{ $meta['sumberdana'] ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Lokasi/Provinsi</div>
                    <div class="col-md-7">{{ $meta['province_name'] ?? '-' }} ({{ $meta['country_name'] ?? '-' }})</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Koordinat</div>
                    <div class="col-md-7">
                        @if(isset($meta['latitude']) && isset($meta['longitude']))
                            {{-- Link Google Maps yang Benar --}}
                            <a href="https://www.google.com/maps?q={{ $meta['latitude'] }},{{ $meta['longitude'] }}"
                              target="_blank"
                              class="text-decoration-none">
                                <i class="bx bx-map text-danger"></i>
                                {{ $meta['latitude'] }}, {{ $meta['longitude'] }}
                            </a>
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Debug Raw Meta (Opsional, hapus kalau production) --}}
        {{--
        <hr>
        <details>
            <summary class="text-muted cursor-pointer">Lihat Raw Data API</summary>
            <pre class="bg-light p-3 mt-2 rounded border" style="font-size: 0.8rem;">{{ json_encode($meta, JSON_PRETTY_PRINT) }}</pre>
        </details>
        --}}

      </div>

      <div class="card-footer border-top d-flex justify-content-end bg-light">
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
          <span class="bx bx-arrow-back me-1"></span> Kembali ke List
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
