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
                <span class="badge bg-dark rounded-pill">Inactive</span>
            @endif
        </div>
      </div>

      <div class="card-body">

        {{-- Section 1: Informasi Utama --}}
        <h5 class="text-primary mb-3"><i class="bx bx-building me-2"></i>Informasi Umum</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Nama Project (Full)</div>
                    <div class="col-md-7 fw-bold">{{ empty($meta['nama_spk_full']) ? $project->project_name : (is_array($meta['nama_spk_full']) ? implode(', ', $meta['nama_spk_full']) : $meta['nama_spk_full']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Nama SPK / Project</div>
                    <div class="col-md-7">{{ empty($meta['nama_spk']) ? $project->project_name : (is_array($meta['nama_spk']) ? implode(', ', $meta['nama_spk']) : $meta['nama_spk']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Kode SPK / Project</div>
                    <div class="col-md-7">{{ $project->project_code }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Profit Center</div>
                    <div class="col-md-7">{{ $project->profit_center ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Divisi</div>
                    <div class="col-md-7">
                        {{ $project->divisi->name ?? (empty($meta['divisiname']) ? '-' : (is_array($meta['divisiname']) ? implode(', ', $meta['divisiname']) : $meta['divisiname'])) }}
                        <div class="text-muted">({{ $project->cost_center_parent ?? '-' }})</div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">SBU</div>
                    <div class="col-md-7">{{ empty($meta['sbu']) ? '-' : (is_array($meta['sbu']) ? implode(', ', $meta['sbu']) : $meta['sbu']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Jenis Proyek</div>
                    <div class="col-md-7">{{ empty($meta['jenisproyek']) ? '-' : (is_array($meta['jenisproyek']) ? implode(', ', $meta['jenisproyek']) : $meta['jenisproyek']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Status Internal</div>
                    <div class="col-md-7">{{ empty($meta['status_proyek_internal_name']) ? '-' : (is_array($meta['status_proyek_internal_name']) ? implode(', ', $meta['status_proyek_internal_name']) : $meta['status_proyek_internal_name']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Proyek Strategis</div>
                    <div class="col-md-7">
                        @if(!empty($meta['strategis_nasional']) && $meta['strategis_nasional'])
                            <span class="badge bg-primary">Nasional</span>
                        @endif
                        @if(!empty($meta['strategis_wika']) && $meta['strategis_wika'])
                            <span class="badge bg-warning text-dark">WIKA</span>
                        @endif
                        @if(empty($meta['strategis_nasional']) && empty($meta['strategis_wika']))
                            -
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4 border-dashed">

        {{-- Section 2: Kontrak & Keuangan --}}
        <h5 class="text-primary mb-3"><i class="bx bx-money me-2"></i>Kontrak & Nilai</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Nomor Kontrak</div>
                    <div class="col-md-7">{{ empty($meta['nomorkontrak']) ? '-' : (is_array($meta['nomorkontrak']) ? implode(', ', $meta['nomorkontrak']) : $meta['nomorkontrak']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Jenis Kontrak</div>
                    <div class="col-md-7">{{ empty($meta['jenis_kontrak_name']) ? '-' : (is_array($meta['jenis_kontrak_name']) ? implode(', ', $meta['jenis_kontrak_name']) : $meta['jenis_kontrak_name']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Nilai Kontrak (OK Total)</div>
                    <div class="col-md-7 fw-bold text-success">
                        Rp {{ number_format($project->nk ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Nilai OK Porsi</div>
                    <div class="col-md-7 fw-bold text-success">
                        Rp {{ number_format($project->nilai_ok_porsi ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Omset (Meta)</div>
                    <div class="col-md-7">
                        @if(!empty($meta['omset']))
                            Rp {{ number_format((float)$meta['omset'], 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Batasan Biaya Risiko</div>
                    <div class="col-md-7 text-primary fw-bold">
                        Rp {{ number_format($project->batasan_biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Porsi WIKA</div>
                    <div class="col-md-7">{{ empty($meta['porsi_wika']) ? '-' : $meta['porsi_wika'] . ' %' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Jenis JO</div>
                    <div class="col-md-7">{{ empty($meta['jenis_jo']) ? '-' : (is_array($meta['jenis_jo']) ? implode(', ', $meta['jenis_jo']) : $meta['jenis_jo']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Pembayaran</div>
                    <div class="col-md-7">{{ empty($meta['pembayaran_name']) ? '-' : (is_array($meta['pembayaran_name']) ? implode(', ', $meta['pembayaran_name']) : $meta['pembayaran_name']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Klasifikasi</div>
                    <div class="col-md-7">
                        {{ empty($meta['klasifikasi_name']) ? '-' : (is_array($meta['klasifikasi_name']) ? implode(', ', $meta['klasifikasi_name']) : $meta['klasifikasi_name']) }}
                        @if(!empty($meta['klasifikasi_id'])) <small>({{ is_array($meta['klasifikasi_id']) ? implode(', ', $meta['klasifikasi_id']) : $meta['klasifikasi_id'] }})</small> @endif
                    </div>
                </div>
                <div class="row mb-2 mt-3 pt-2 border-top">
                    <div class="col-md-5 fw-bold text-muted">Tanggal Mulai</div>
                    <div class="col-md-7">
                        {{ $project->masa_pelaksanaan_start ? \Carbon\Carbon::parse($project->masa_pelaksanaan_start)->translatedFormat('d F Y') : '-' }}
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
                    <div class="col-md-7">{{ !empty($meta['bast1']) ? \Carbon\Carbon::parse($meta['bast1'])->translatedFormat('d F Y') : '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">BAST 2</div>
                    <div class="col-md-7">{{ !empty($meta['bast2']) ? \Carbon\Carbon::parse($meta['bast2'])->translatedFormat('d F Y') : '-' }}</div>
                </div>
            </div>
        </div>

        <hr class="my-4 border-dashed">

        {{-- Section 3: Manajemen & Pemilik --}}
        <h5 class="text-primary mb-3"><i class="bx bx-user-pin me-2"></i>Manajemen & Lokasi</h5>
        <div class="row">
            <div class="col-md-6">
                {{-- Manager --}}
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Manager Proyek</div>
                    <div class="col-md-7">{{ empty($meta['manager_proyek']) ? '-' : (is_array($meta['manager_proyek']) ? implode(', ', $meta['manager_proyek']) : $meta['manager_proyek']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">NIP MP</div>
                    <div class="col-md-7">{{ empty($meta['nip_mp']) ? '-' : (is_array($meta['nip_mp']) ? implode(', ', $meta['nip_mp']) : $meta['nip_mp']) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-5 fw-bold text-muted">Kontak MP</div>
                    <div class="col-md-7">
                        <div><i class="bx bx-phone text-muted me-1"></i> {{ empty($meta['telp_manajer']) ? '-' : (is_array($meta['telp_manajer']) ? implode(', ', $meta['telp_manajer']) : $meta['telp_manajer']) }}</div>
                        <div><i class="bx bx-envelope text-muted me-1"></i> {{ empty($meta['email_mp']) ? '-' : (is_array($meta['email_mp']) ? implode(', ', $meta['email_mp']) : $meta['email_mp']) }}</div>
                    </div>
                </div>

                {{-- Kasie --}}
                <div class="row mb-2 border-top pt-2">
                    <div class="col-md-5 fw-bold text-muted">Kepala Seksi (Kasie)</div>
                    <div class="col-md-7">{{ empty($meta['kasie_name']) ? '-' : (is_array($meta['kasie_name']) ? implode(', ', $meta['kasie_name']) : $meta['kasie_name']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">NIP Kasie</div>
                    <div class="col-md-7">{{ empty($meta['kasie_nip']) ? '-' : (is_array($meta['kasie_nip']) ? implode(', ', $meta['kasie_nip']) : $meta['kasie_nip']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Kontak Kasie</div>
                    <div class="col-md-7">
                        <div><i class="bx bx-phone text-muted me-1"></i> {{ empty($meta['kasie_phone']) ? '-' : (is_array($meta['kasie_phone']) ? implode(', ', $meta['kasie_phone']) : $meta['kasie_phone']) }}</div>
                        <div><i class="bx bx-envelope text-muted me-1"></i> {{ empty($meta['kasie_email']) ? '-' : (is_array($meta['kasie_email']) ? implode(', ', $meta['kasie_email']) : $meta['kasie_email']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Pemilik Proyek</div>
                    <div class="col-md-7">{{ empty($meta['nm_pemilik']) ? '-' : (is_array($meta['nm_pemilik']) ? implode(', ', $meta['nm_pemilik']) : $meta['nm_pemilik']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Sumber Dana</div>
                    <div class="col-md-7">{{ empty($meta['sumberdana']) ? '-' : (is_array($meta['sumberdana']) ? implode(', ', $meta['sumberdana']) : $meta['sumberdana']) }}</div>
                </div>
                <div class="row mb-2 mt-3 pt-2 border-top">
                    <div class="col-md-5 fw-bold text-muted">Lokasi/Provinsi</div>
                    <div class="col-md-7">
                        {{ empty($meta['province_name']) ? '-' : (is_array($meta['province_name']) ? implode(', ', $meta['province_name']) : $meta['province_name']) }}
                        ({{ empty($meta['country_name']) ? '-' : (is_array($meta['country_name']) ? implode(', ', $meta['country_name']) : $meta['country_name']) }})
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Alamat Proyek</div>
                    <div class="col-md-7">{{ empty($meta['alamatproyek']) ? '-' : (is_array($meta['alamatproyek']) ? implode(', ', $meta['alamatproyek']) : $meta['alamatproyek']) }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Koordinat</div>
                    <div class="col-md-7">
                        @if(!empty($meta['latitude']) && !empty($meta['longitude']))
                            <a href="https://www.google.com/maps?q={{ is_array($meta['latitude']) ? $meta['latitude'][0] : $meta['latitude'] }},{{ is_array($meta['longitude']) ? $meta['longitude'][0] : $meta['longitude'] }}"
                              target="_blank"
                              class="text-decoration-none">
                                <i class="bx bx-map text-danger"></i>
                                {{ is_array($meta['latitude']) ? $meta['latitude'][0] : $meta['latitude'] }},
                                {{ is_array($meta['longitude']) ? $meta['longitude'][0] : $meta['longitude'] }}
                            </a>
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4 border-dashed">

        {{-- Section 4: Integrasi Sistem --}}
        <h5 class="text-primary mb-3"><i class="bx bx-data me-2"></i>Integrasi Sistem</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Sistem PMCS</div>
                    <div class="col-md-7">
                        {!! !empty($meta['is_pmcs']) && $meta['is_pmcs'] ? '<span class="badge bg-success">Terintegrasi</span>' : '<span class="badge bg-danger">Tidak</span>' !!}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Sistem SAP</div>
                    <div class="col-md-7">
                        {!! !empty($meta['is_sap']) && $meta['is_sap'] ? '<span class="badge bg-success">Terintegrasi</span>' : '<span class="badge bg-danger">Tidak</span>' !!}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-md-5 fw-bold text-muted">Kode CRM</div>
                    <div class="col-md-7">{{ empty($meta['kode_crm']) ? '-' : (is_array($meta['kode_crm']) ? implode(', ', $meta['kode_crm']) : $meta['kode_crm']) }}</div>
                </div>
            </div>
        </div>

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
