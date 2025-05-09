@extends('layouts.default')
@section('dashboard')
<div class="row mb-5">
  <div class="col-12">
    <div class="ff-preheading">Prioritas Risiko</div>
    <h3>Risk Owner View Detail</h3>
  </div>
</div>
<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-primary rounded-pill me-3">
          <i class="bx bx-rocket fs-4"></i>
        </div>
        <h6>Target</h6>
      </div>
      <div class="card-body">
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Target Capaian Kinerja
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->target_capaian_kinerja }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Rencana Kegiatan
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->deskripsi_rencana_kegiatan }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Kategori dan Jenis Risiko
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->kategoriRisiko->title }} -
            {{ $identifikasiRisiko->jenisRisiko->title }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Peristiwa Risiko
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->peristiwaRisiko->title }}</p>
        </div>
        <div class="row gx-0">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Deskripsi Peristiwa Risiko
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->deskripsi_peristiwa_risiko }}</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-success rounded-pill me-3">
          <i class='bx bx-bar-chart-square fs-4'></i>
        </div>
        <h6>Key Risk Indicator</h6>
      </div>
      <div class="card-body pt-2">
        <div class="table-responsive-sm">
          <table class="table table-md mb-md-0">
            <thead>
              <tr>
                <th class="index-number align-middle">#</th>
                <th class="key_risk_indicatorl mw-10r align-middle">Key Risk Indicator</th>
                <th class="satuan_kri align-middle">Satuan KRI</th>
                <th class="batas_aman">
                  <div class="alert alert-success text-nowrap">Batas Aman</div>
                </th>
                <th class="batas_waspada">
                  <div class="alert alert-warning text-nowrap">Batas Waspada</div>
                </th>
                <th class="batas_bahaya">
                  <div class="alert alert-danger text-nowrap">Batas Bahaya</div>
                </th>
              </tr>
            </thead>
            <tbody id="kri-body">
              @foreach ($kri as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="key_risk_indicator">{{ $item->kri }}</td>
                <td class="satuan_kri">{{ $item->satuan_kri }}</td>
                <td class="batas_aman">{{ $item->batas_aman }}</td>
                <td class="batas_waspada">{{ $item->batas_waspada }}</td>
                <td class="batas_bahaya">{{ $item->batas_bahaya }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-danger rounded-pill me-3">
          <i class='bx bx-briefcase-alt-2 fs-4'></i>
        </div>
        <h6>Penyebab Risiko</h6>
      </div>
      <div class="card-body">
        <ol>
          @foreach ($penyebabRisiko as $index => $item)
          <li>{{ $item->penyebab_risiko }}</li>
          @endforeach
        </ol>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-secondary rounded-pill me-3">
          <i class='bx bx-cog fs-4'></i>
        </div>
        <h6>Kontrol</h6>
      </div>
      <div class="card-body">
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-6 col-xxl-4 mb-1 mb-md-0">
            Kontrol Eksisting
          </label>
          <p class="col-12 col-md-6 col-xxl-8 mb-0">{{ $identifikasiRisiko->kontrol_eksisting ?? NULL }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-6 col-xxl-4 mb-1 mb-md-0">
            Penilaian Efektivitas Kontrol
          </label>
          <p class="col-12 col-md-6 col-xxl-8 mb-0">{{ $identifikasiRisiko->penilaian_efektifitas_kontrol ?? NULL }}
          </p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-6 col-xxl-4 mb-1 mb-md-0">
            Perkiraan Waktu Terpapar Risiko
          </label>
          <div class="col-12 col-md-6 col-xxl-8 d-flex gap-2">
            <span>{{ \Carbon\Carbon::parse($identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai)->format('j F Y') }}</span>
            s/d
            <span>{{ \Carbon\Carbon::parse($identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir)->format('j F Y') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="pt-3">
    <a href="{{ route('risk-owner.index') }}" class="btn btn-primary">Kembali</a>
  </div>
</div>
@endsection