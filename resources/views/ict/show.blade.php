@extends('layouts.default')
@section('dashboard')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-info-subtle p-2 rounded-4">
            <div class="lead__icon">
              <div class="svg-icon svg-icon-2x svg-icon-info">
                @include('partials.icon-pyramid')
              </div>
            </div>
          </div>
          <div class="d-block">
            <div class="ff-preheading">Detail</div>
            <h2>ICT Plan</h2>
          </div>
        </div>
        <div>
          <a href="{{ route('ict.index') }}" class="btn btn-outline-secondary btn-sm">
            <span class="bx bx-arrow-back me-1"></span> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Detail ICT Plan -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card bg-light-subtle border">
              <div class="card-header bg-light">
                <h5 class="mb-0">Informasi ICT Plan</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label fw-bold">Sasaran BUMN</label>
                      <p>{{ $ictPlan->sasaran_bumn }}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">Peristiwa Risiko</label>
                      <p>{{ $peristiwaRisiko }}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">Lokasi Risiko</label>
                      <p>{{ $lokasiRisiko }}</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label fw-bold">Business Process</label>
                      <p>{{ $ictPlan->business_process }}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">Metode Pengujian</label>
                      <p>{{ $ictPlan->metode_pengujian }}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">Tanggal Dibuat</label>
                      <p>{{ $ictPlan->created_at->format('d F Y') }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Key Controls -->
        @foreach($ictPlan->planControls as $index => $planControl)
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border">
              <div class="card-header bg-primary-subtle">
                <h5 class="mb-0 text-primary">Key Control #{{ $index + 1 }}: {{ $planControl->key_control }}</h5>
              </div>
              <div class="card-body">
                <!-- Log Pelaksanaan -->
                <div class="mb-4">
                  <h6 class="mb-3 border-bottom pb-2">Log Pelaksanaan</h6>
                  @if($planControl->dos->count() > 0)
                    <div class="table-responsive">
                      <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                          <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Jenis Kontrol</th>
                            <th>Bentuk Kontrol</th>
                            <th>Level Pengendalian</th>
                            <th>Kesimpulan</th>
                            <th>Hasil Temuan</th>
                            <th>Rencana Tindak Lanjut</th>
                            <th>Batas Waktu</th>
                            <th>PIC</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($planControl->dos->sortByDesc('created_at') as $doIndex => $do)
                            <tr>
                              <td>{{ $doIndex + 1 }}</td>
                              <td>{{ $do->created_at->format('d/m/Y') }}</td>
                              <td>
                                @if($do->jenis_kontrol == 1) Kontrol Operasi
                                @elseif($do->jenis_kontrol == 2) Kontrol Kepatuhan
                                @elseif($do->jenis_kontrol == 3) Kontrol Pelaporan
                                @endif
                              </td>
                              <td>
                                @if($do->bentuk_kontrol == 1) SOP
                                @elseif($do->bentuk_kontrol == 2) Kebijakan
                                @elseif($do->bentuk_kontrol == 3) Sistem Informasi dan Komunikasi
                                @elseif($do->bentuk_kontrol == 4) Sistem Lainnya
                                @endif
                              </td>
                              <td>
                                @if($do->level_pengendalian == 1) Entitas
                                @elseif($do->level_pengendalian == 2) Operasional
                                @endif
                              </td>
                              <td>
                                <span class="badge {{ $do->kesimpulan_akhir == 'Efektif' ? 'bg-success' : 'bg-danger' }}">
                                  {{ $do->kesimpulan_akhir }}
                                </span>
                              </td>
                              <td>{{ $do->hasil_temuan }}</td>
                              <td>{{ $do->rencana_tindak_lanjut }}</td>
                              <td>{{ $do->batas_waktu_penyelesaian ? $do->batas_waktu_penyelesaian->format('d/m/Y') : '-' }}</td>
                              <td>{{ $do->penanggung_jawab }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  @else
                    <div class="alert alert-info">
                      Belum ada pelaksanaan pengujian untuk key control ini.
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        @endforeach

        <!-- Laporan ICT -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border">
              <div class="card-header bg-success-subtle">
                <h5 class="mb-0 text-success">Laporan ICT</h5>
              </div>
              <div class="card-body">
                @if($ictReport)
                  <div class="row">
                    <div class="col-md-12">
                      <div class="mb-3">
                        <label class="form-label fw-bold">Status Tindak Lanjut</label>
                        <div class="p-3 bg-light-subtle border rounded">
                          {!! nl2br(e($ictReport->status_tindak_lanjut)) !!}
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <div class="p-3 bg-light-subtle border rounded">
                          {!! nl2br(e($ictReport->keterangan)) !!}
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Laporan</label>
                        <p>{{ $ictReport->created_at->format('d F Y') }}</p>
                      </div>
                    </div>
                  </div>
                @else
                  <div class="alert alert-warning">
                    Belum ada laporan untuk ICT Plan ini.
                    <a href="{{ route('ict.report', $ictPlan->id) }}" class="alert-link">Buat laporan sekarang</a>.
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="row">
          <div class="col-12 text-end">
            <a href="{{ route('ict.edit', $ictPlan->id) }}" class="btn btn-warning me-2">
              <span class="bx bx-edit me-1"></span> Edit ICT Plan
            </a>
            <a href="{{ route('ict.testing', $ictPlan->id) }}" class="btn btn-primary me-2">
              <span class="bx bx-test-tube me-1"></span> Pelaksanaan Pengujian
            </a>
            <a href="{{ route('ict.report', $ictPlan->id) }}" class="btn btn-success">
              <span class="bx bx-file me-1"></span> {{ $ictReport ? 'Update Laporan' : 'Buat Laporan' }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('styles')
<style>
  .table th, .table td {
    vertical-align: middle;
  }
  
  .badge {
    font-size: 0.85em;
    padding: 0.35em 0.65em;
  }
</style>
@endsection