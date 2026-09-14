@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')

@php
  $penilaian = $penilaian ?? null;
  $details   = $penilaian ? $penilaian->details->keyBy('parameter_id') : collect();
  $paramsDim = $dimensions;
  $paramsC   = $paramsCapaian;
  $paramsK   = $paramsKpmr;
  $evidenceMap = $evidenceMap ?? collect();
@endphp
@push('styles')
<style>
  /* 1. Layout fixed & kolom pertama sempit */
  .card .table-peta {
    table-layout: fixed;
    width: 100%;
  }
  .table-peta th:first-child,
  .table-peta td:first-child {
    width: 120px;
    white-space: nowrap;
  }

  /* 2. Semua kolom peta dibagi rata */
  .table-peta th:not(:first-child),
  .table-peta td.map-cell {
    width: calc((100% - 120px)/5);
  }

  /* 3. Kotak peta */
  .map-cell {
    padding: .25rem;
    height: 40px;
    vertical-align: middle;
    text-align: center;
    white-space: normal;
    word-break: break-word;
  }

  /* 4. Warna berdasarkan nilai */
  .bg-strong         { background-color: #3cbf87 !important; }  /* untuk “1” */
  .bg-satisfactory   { background-color: #a8e6cf !important; }  /* untuk “2” */
  .bg-fair           { background-color: #ffec99 !important; }  /* untuk “3” */
  .bg-marginal       { background-color: #ffc078 !important; }  /* untuk “4” */
  .bg-unsatisfactory { background-color: #ffa8a8 !important; }  /* untuk “5” */

  /* 5. Highlight sel terpilih */
  .map-cell.current {
    border: 2px solid #d63384 !important;
  }
</style>
@endpush
<div class="container px-0">
  <div class="d-flex justify-content-end mb-3">
    @can('rmi_period_logs')
    <a href="{{ route('penilaian-rmi.logs.show', $period->id) }}" class="btn btn-outline-secondary btn-sm">
      <span class="bx bx-history"></span>
      <span class="ms-1">Log Perubahan</span>
    </a>
    @endcan
  </div>

  {{-- 1. Informasi Periode & Ringkasan --}}
  {{-- <div class="row mb-4">
    <div class="col-md-6">
      <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white">Informasi Periode RMI</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4">Tahun Periode</dt><dd class="col-sm-8">{{ $period->year }}</dd>
            <dt class="col-sm-4">Status</dt>
              <dd class="col-sm-8">
                @if($period->status==1)
                  <span class="badge bg-warning">Dalam Proses</span>
                @else
                  <span class="badge bg-success">Selesai</span>
                @endif
              </dd>
            <dt class="col-sm-4">Score Dimension</dt><dd class="col-sm-8">{{ $period->score_rmi }}</dd>
            <dt class="col-sm-4">Deskripsi Score</dt><dd class="col-sm-8">{{ $period->score_rmi_desc }}</dd>
            <dt class="col-sm-4">Score RMI</dt><dd class="col-sm-8">{{ $period->final_score_rmi }}</dd>

            <dt class="col-sm-4">Tanggal Update</dt><dd class="col-sm-8">{{ $period->updated_at->format('d M Y H:i') }}</dd>
            <dt class="col-sm-4">Penilai</dt>
            <dd class="col-sm-8">
              {{ $period->penilaian ?? '-' }}
              @if($period->tipe_penilaian)
                <span class="">
                  @if($period->tipe_penilaian == 1)
                    (Eksternal)
                  @elseif($period->tipe_penilaian == 2)
                    (Internal)
                  @endif
                </span>
              @endif
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary text-white">Ringkasan Kinerja &amp; KPMR</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-6">Kinerja</dt><dd class="col-sm-6">{{ $period->kinerja }}</dd>
            <dt class="col-sm-6">KPMR</dt><dd class="col-sm-6">{{ $period->kpmr }}</dd>
            <dt class="col-sm-6">Peringkat Komposit Risiko</dt><dd class="col-sm-6">{{ $period->peringkat_komposit_risiko }}</dd>
            <dt class="col-sm-6">Nilai Konversi</dt><dd class="col-sm-6">{{ $period->nilai_konversi }}</dd>
            <dt class="col-md-6">Reviewed by</dt><dd class="col-sm-6">Internal Audit Division</dd>
          </dl>
        </div>
      </div>
    </div>
  </div> --}}

  <div class="row mb-4">
    {{-- CARD 1: INFORMASI UMUM --}}
    <div class="col-md-4">
      <div class="card shadow-sm h-100 border-top border-3 border-primary">
        <div class="card-header bg-white h4">
            Informasi Periode RMI
        </div>
        <div class="card-body">
            <dl class="row mb-0 align-items-center px-3">
                <dt class="col-sm-6 px-0 text-muted">Tahun RMI</dt>
                <dd class="col-sm-6 px-0 fw-bold">{{ $period->year }}</dd>

                <dt class="col-sm-6 px-0 text-muted">Tahun Dinilai</dt>
                <dd class="col-sm-6 px-0 fw-bold">{{ $period->tahun_dinilai ?? '-' }}</dd>

                <dt class="col-sm-6 px-0 text-muted">Status</dt>
                <dd class="col-sm-6 px-0">
                    @if($period->status==1) <span class="badge bg-warning text-dark">Dalam Proses</span>
                    @else <span class="badge bg-success">Selesai</span> @endif
                </dd>

                <dt class="col-sm-6 px-0 text-muted">Tanggal Update</dt>
                <dd class="col-sm-6 px-0">{{ $period->updated_at->format('d M Y H:i') }}</dd>

                <dt class="col-sm-6 px-0 text-muted">Terakhir diubah oleh</dt>
                <dd class="col-sm-6 px-0">{{ $penilaian?->user?->name ?? '-' }}</dd>

                <hr class="my-2 border-light">

                <dt class="col-sm-6 px-0 text-muted">Penilai Internal</dt>
                <dd class="col-sm-6 px-0">{{ $period->penilaian ?? '-' }}</dd>

                <dt class="col-sm-6 px-0 text-muted">Penilai Eksternal</dt>
                <dd class="col-sm-6 px-0">{{ $period->penilai_external ?? '-' }}</dd>

                <dt class="col-sm-6 px-0 text-muted">Reviewed by</dt>
                <dd class="col-sm-6 px-0">Internal Audit Division</dd>
            </dl>
        </div>
      </div>
    </div>

    {{-- CARD 2: PENILAIAN INTERNAL --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-top border-3 border-success">
            <div class="card-header bg-white h4 text-success d-flex justify-content-between">
                <span>Hasil Penilaian Internal</span>
                {{-- <small class="text-muted fw-normal">{{ $period->penilaian ?? 'Mandiri' }}</small> --}}
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6 border-end">
                        <span class="text-muted d-block">Score RMI</span>
                        <span class="fs-4 fw-bold">{{ $period->score_rmi ?? '-' }}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Final Score</span>
                        <span class="fs-4 fw-bold text-success">{{ $period->final_score_rmi ?? '-' }}</span>
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Kinerja</span>
                        <span class="fw-bold">{{ $period->kinerja ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>KPMR</span>
                        <span class="fw-bold">{{ $period->kpmr ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Peringkat Komposit</span>
                        <span class="fw-bold">{{ $period->peringkat_komposit_risiko ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Nilai Konversi</span>
                        <span class="fw-bold">{{ $period->nilai_konversi ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Adjustment</span>
                        <span class="fw-bold text-danger">{{ $period->adjusment_score ?? '-' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- CARD 3: PENILAIAN EKSTERNAL --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-top border-3 border-info">
            <div class="card-header bg-white h4 text-info d-flex justify-content-between">
                <span>Hasil Penilaian Eksternal</span>
                {{-- <small class="text-muted fw-normal">{{ $period->penilai_external ?? '-' }}</small> --}}
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6 border-end">
                        <span class="text-muted d-block">Score RMI</span>
                        <span class="fs-4 fw-bold">{{ $period->score_rmi_external ?? '-' }}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Final Score</span>
                        <span class="fs-4 fw-bold text-info">{{ $period->final_score_rmi_external ?? '-' }}</span>
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Kinerja</span>
                        <span class="fw-bold">{{ $period->kinerja_external ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>KPMR</span>
                        <span class="fw-bold">{{ $period->kpmr_external ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Peringkat Komposit</span>
                        <span class="fw-bold">{{ $period->peringkat_komposit_risiko_external ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Nilai Konversi</span>
                        <span class="fw-bold">{{ $period->nilai_konversi_external ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>Adjustment</span>
                        <span class="fw-bold text-danger">{{ $period->adjusment_score_external ?? '-' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
  </div>

  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <span>Dokumen Pendukung Peringkat Akhir</span>
      <span id="doc-count-badge" class="badge bg-info text-secondary-emphasis">{{ $period->documents->count() }} File</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="document-table">
          <thead>
            <tr>
              <th class="ps-3" style="width: 5%;">#</th>
              <th>Nama File</th>
              <th>Deskripsi</th>
              <th style="width: 15%;">Tgl Upload</th>
              <th class="text-center pe-3" style="width: 12%;">Aksi</th>
            </tr>
          </thead>
          <tbody id="document-table-body">
            @forelse ($period->documents as $document)
              <tr id="doc-row-{{ $document->id }}">
                <td class="ps-3">{{ $loop->iteration }}</td>
                <td><i class="bx bxs-file me-2 text-muted"></i>{{ $document->file_name }}</td>
                <td>{{ $document->description ?? '-' }}</td>
                <td>{{ $document->created_at->format('d M Y') }}</td>
                <td class="text-center pe-3">
                  <div class="d-flex justify-content-center gap-1">
                    <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat dokumen">
                      <i class="bx bx-show"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-document"
                      data-doc-id="{{ $document->id }}"
                      data-period-id="{{ $period->id }}"
                      data-bs-toggle="tooltip"
                      title="Hapus dokumen">
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr id="empty-doc-row">
                <td colspan="5" class="text-center py-4 text-muted">
                  Belum ada dokumen pendukung peringkat akhir.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Peta Komposit Risiko --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">Peta Komposit Risiko</div>
    <div class="card-body table-responsive p-0">
      <table class="table table-bordered text-center mb-0">
        <thead>
          <tr class="bg-teal text-white">
            <th rowspan="2" class="align-middle">Kinerja →</th>
            <th colspan="5">Kualitas Penerapan Manajemen Risiko →</th>
          </tr>
          <tr class="bg-navy text-white">
            <th class="map-cell">Strong</th>
            <th class="map-cell">Satisfactory</th>
            <th class="map-cell">Fair</th>
            <th class="map-cell">Marginal</th>
            <th class="map-cell">Unsatisfactory</th>
          </tr>
        </thead>
        <tbody>
          @php
            // Matriks 5×5
            $matrix = [
              [1,1,2,3,3],
              [1,2,2,3,4],
              [2,2,3,4,4],
              [2,3,4,4,5],
              [3,3,4,5,5],
            ];

            $rowLabels   = ['Sangat Baik','Baik','Cukup','Kurang','Buruk'];
            $valueClasses = [
              1 => 'bg-strong',
              2 => 'bg-satisfactory',
              3 => 'bg-fair',
              4 => 'bg-marginal',
              5 => 'bg-unsatisfactory',
            ];

            // Dapatkan ID skala (1–5) dari PenilaianCapaianKinerja
            $pen  = $penilaian;
            $kRow = ($pen?->capaian_kinerja ?? 1) - 1;
            $kCol = ($pen?->kpmr ?? 1) - 1;
          @endphp

          @foreach($matrix as $r => $cols)
            <tr>
              {{-- Label baris --}}
              <th class="align-middle">{{ $rowLabels[$r] }}</th>

              @foreach($cols as $c => $cell)
                @php
                  // cek sel aktif berdasarkan (baris=kRow, kolom=kCol)
                  $isCurrent = ($r === $kRow && $c === $kCol);
                  // kelas berdasarkan nilai sel (1–5)
                  $bgClass = $valueClasses[$cell] ?? '';
                @endphp

                <td class="map-cell {{ $bgClass }}{{ $isCurrent ? ' current' : '' }}">
                  {{ $cell }}
                  @if($isCurrent)
                    <i class="bi bi-geo-alt-fill text-dark"></i>
                  @endif
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- 3. Tabs Aspek Dimensi & Aspek Kinerja --}}
  <ul class="nav nav-tabs mb-3" id="showTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#dimensi">Aspek Dimensi</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#kinerja">Aspek Kinerja</button></li>
  </ul>
  <div class="tab-content">

    {{-- 3a. Aspek Dimensi --}}
    <div class="tab-pane fade show active" id="dimensi">
      @include('penilaian-rmi.partials.dimensi') {{-- your existing dimensi partial --}}
    </div>

    {{-- 3b. Aspek Kinerja --}}
    <div class="tab-pane fade" id="kinerja">

      {{-- Bagian 1: Jawaban User --}}
      <div class="row mb-4">
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">Penilaian Capaian Kinerja</div>
            <div class="card-body p-0">
              <table class="table mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No</th>
                    <th>Parameter</th>
                    <th>Jawaban</th>
                    <th>Skala</th>
                    <th>Keterangan</th>
                    <th>Bukti Dukung</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($paramsC as $i => $param)
                    @if($param->children->isEmpty())
                      @php
                        $d     = $details->get($param->id);
                        $jawab = $d?->pilihan->code  ?? '-';
                        $skala = $d?->pilihan->scale ?? '-';
                        $ket   = $d?->comment        ?? '-';
                        $paramDocs = collect($evidenceMap->get($param->id, []));
                      @endphp
                      <tr>
                        <td>{{ $i+1 }}</td>
                        <td>
                          <div class="fw-medium mb-1">{{ $param->name }}</div>
                          <div class="small">
                            @foreach($param->options as $opt)
                              @php $isSelected = strtolower((string) $opt->code) === strtolower((string) $jawab); @endphp
                              <div class="{{ $isSelected ? 'fw-bold text-dark' : 'text-muted' }}">
                                {{ strtolower($opt->code) }}. {{ $opt->description }}
                              </div>
                            @endforeach
                          </div>
                        </td>
                        <td class="text-center fw-bold">{{ strtoupper($jawab) }}</td>
                        <td class="text-center">{{ $skala }}</td>
                        <td>{{ $ket }}</td>
                        <td>
                          @forelse($paramDocs as $doc)
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="d-flex align-items-center gap-1 text-decoration-none small mb-1" data-bs-toggle="tooltip" title="Lihat {{ $doc->filename }}">
                              <i class="bx bx-file"></i>
                              <span class="text-truncate" style="max-width: 160px;">{{ $doc->filename }}</span>
                            </a>
                          @empty
                            <span class="text-muted">-</span>
                          @endforelse
                        </td>
                      </tr>
                    @else
                      @foreach($param->children as $j => $child)
                        @php
                          $d     = $details->get($child->id);
                          $jawab = $d?->pilihan->code  ?? '-';
                          $skala = $d?->pilihan->scale ?? '-';
                          $ket   = ($d && !empty($d->comment)) ? $d->comment : '-';
                          $no    = $child->code;
                          $childDocs = collect($evidenceMap->get($child->id, []));
                        @endphp
                        <tr>
                          <td>{{ $no }}</td>
                          <td class="ps-4">
                            <div class="fw-medium mb-1">{{ $child->name }}</div>
                            <div class="small">
                              @foreach($child->options as $opt)
                                @php $isSelected = strtolower((string) $opt->code) === strtolower((string) $jawab); @endphp
                                <div class="{{ $isSelected ? 'fw-bold text-dark' : 'text-muted' }}">
                                  {{ strtolower($opt->code) }}. {{ $opt->description }}
                                </div>
                              @endforeach
                            </div>
                          </td>
                          <td class="text-center fw-bold">{{ strtoupper($jawab) }}</td>
                          <td class="text-center">{{ $skala }}</td>
                          <td>{{ $ket }}</td>
                          <td>
                            @forelse($childDocs as $doc)
                              <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="d-flex align-items-center gap-1 text-decoration-none small mb-1" data-bs-toggle="tooltip" title="Lihat {{ $doc->filename }}">
                                <i class="bx bx-file"></i>
                                <span class="text-truncate" style="max-width: 160px;">{{ $doc->filename }}</span>
                              </a>
                            @empty
                              <span class="text-muted">-</span>
                            @endforelse
                          </td>
                        </tr>
                      @endforeach
                    @endif
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">Penilaian KPMR</div>
            <div class="card-body p-0">
              <table class="table mb-0 table-hover">
                <thead class="table-light">
                  <tr>
                    <th>No</th>
                    <th>Parameter</th>
                    <th>Jawaban</th>
                    <th>Skala</th>
                    <th>Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($paramsK as $i => $param)
                    @if($param->children->isEmpty())
                      @php
                        $d     = $details->get($param->id);
                        $jawab = $d?->pilihan->code  ?? '-';
                        $skala = $d?->pilihan->scale ?? '-';
                        $ket   = !empty($d?->comment) ? $d->comment : '-';
                      @endphp
                      <tr>
                        <td>{{ $i+1 }}</td>
                        <td>
                          <div class="fw-medium mb-1">{{ $param->name }}</div>
                          <div class="small">
                            @foreach($param->options as $opt)
                              @php $isSelected = strtolower((string) $opt->code) === strtolower((string) $jawab); @endphp
                              <div class="{{ $isSelected ? 'fw-bold text-dark' : 'text-muted' }}">
                                {{ strtolower($opt->code) }}. {{ $opt->description }}
                              </div>
                            @endforeach
                          </div>
                        </td>
                        <td>{{ strtoupper($jawab) }}</td>
                        <td>{{ $skala }}</td>
                        <td>{{ $ket }}</td>
                      </tr>
                    @else
                      @foreach($param->children as $child)
                        @php
                          $d     = $details->get($child->id);
                          $jawab = $d?->pilihan->code  ?? '-';
                          $skala = $d?->pilihan->scale ?? '-';
                          $ket   = !empty($d?->comment) ? $d->comment : '-';
                        @endphp
                        <tr>
                          <td>{{ $child->code }}</td>
                          <td class="ps-4">
                            <div class="fw-medium mb-1">{{ $child->name }}</div>
                            <div class="small">
                              @foreach($child->options as $opt)
                                @php $isSelected = strtolower((string) $opt->code) === strtolower((string) $jawab); @endphp
                                <div class="{{ $isSelected ? 'fw-bold text-dark' : 'text-muted' }}">
                                  {{ strtolower($opt->code) }}. {{ $opt->description }}
                                </div>
                              @endforeach
                            </div>
                          </td>
                          <td>{{ strtoupper($jawab) }}</td>
                          <td>{{ $skala }}</td>
                          <td>{{ $ket }}</td>
                        </tr>
                      @endforeach
                    @endif
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- Bagian 2: Indikator Rekap --}}
      <div class="row">
        {{-- Capaian Kinerja --}}
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">Indikator Pencapaian Kinerja</div>
            <div class="card-body p-0">
              <table class="table mb-0">
                <thead class="table-light">
                  <tr><th>No</th><th>Parameter</th><th>Bobot</th><th>Skala</th><th>Hasil</th><th>Skor</th></tr>
                </thead>
                <tbody>
                  @php $no=1; $tot=0; @endphp
                  @foreach($paramsC as $param)
                    @if($param->children->isEmpty())
                      @php
                        $d   = $details->get($param->id);
                        $hs  = $d?->pilihan->score ?? 0;
                        $sk  = round($hs * ($param->weight/100),2);
                        $tot += $sk;
                      @endphp
                      <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $param->name }}</td>
                        <td>{{ $param->weight }}%</td>
                        <td>{{ $d?->pilihan->scale }}</td>
                        <td>{{ $hs }}</td>
                        <td>{{ $sk }}</td>
                      </tr>
                    @else
                      @php
                        $sum  = 0;
                        foreach($param->children as $c) {
                          $d   = $details->get($c->id);
                          $sum += ($d?->pilihan->score ?? 0)*($c->weight/100);
                        }
                        $skTop = round($sum * ($param->weight/100),2);
                        $tot  += $skTop;
                      @endphp
                      <tr class="table-secondary">
                        <td>{{ $no++ }}</td>
                        <td><strong>{{ $param->name }}</strong></td>
                        <td>{{ $param->weight }}%</td>
                        <td>-</td>
                        <td>{{ number_format($sum,2) }}</td>
                        <td><strong>{{ $skTop }}</strong></td>
                      </tr>
                      @foreach($param->children as $c)
                        @php
                          $d   = $details->get($c->id);
                          $hs  = $d?->pilihan->score ?? 0;
                          $sk  = round($hs * ($c->weight/100),2);
                        @endphp
                        <tr>
                          <td></td>
                          <td class="ps-4">{{ $c->name }}</td>
                          <td>{{ $c->weight }}%</td>
                          <td>{{ $d?->pilihan->scale }}</td>
                          <td>{{ $hs }}</td>
                          <td>{{ $sk }}</td>
                        </tr>
                      @endforeach
                    @endif
                  @endforeach
                </tbody>
                <tfoot>
                  <tr class="table-primary">
                    <th colspan="5" class="text-end">Total Nilai</th><th>{{ number_format($tot,2) }}</th>
                  </tr>
                  <tr class="table-primary">
                    <th colspan="5" class="text-end">Kinerja</th><th>{{ $period->kinerja }}</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        {{-- KPMR --}}
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">Indikator Kualitas Penerapan Manajemen Risiko</div>
            <div class="card-body p-0">
              <table class="table mb-0">
                <thead class="table-light">
                  <tr><th>No</th><th>Parameter</th><th>Bobot</th><th>Skala</th><th>Hasil</th><th>Skor</th></tr>
                </thead>
                <tbody>
                  @php $no2=1; $tot2=0; @endphp
                  @foreach($paramsK as $param)
                    @if($param->children->isEmpty())
                      @php
                        $d   = $details->get($param->id);
                        $hs  = $d?->pilihan->score ?? 0;
                        $sk  = round($hs * ($param->weight/100),2);
                        $tot2+= $sk;
                      @endphp
                      <tr>
                        <td>{{ $no2++ }}</td>
                        <td>{{ $param->name }}</td>
                        <td>{{ $param->weight }}%</td>
                        <td>{{ $d?->pilihan->scale }}</td>
                        <td>{{ $hs }}</td>
                        <td>{{ $sk }}</td>
                      </tr>
                    @else
                      @php
                        $sum=0;
                        foreach($param->children as $c) {
                          $d= $details->get($c->id);
                          $sum+= ($d?->pilihan->score ?? 0)*($c->weight/100);
                        }
                        $skTop= round($sum * ($param->weight/100),2);
                        $tot2+= $skTop;
                      @endphp
                      <tr class="table-secondary">
                        <td>{{ $no2++ }}</td>
                        <td><strong>{{ $param->name }}</strong></td>
                        <td>{{ $param->weight }}%</td>
                        <td>-</td>
                        <td>{{ number_format($sum,2) }}</td>
                        <td><strong>{{ $skTop }}</strong></td>
                      </tr>
                      @foreach($param->children as $c)
                        @php
                          $d  = $details->get($c->id);
                          $hs = $d?->pilihan->score ?? 0;
                          $sk = round($hs * ($c->weight/100),2);
                        @endphp
                        <tr>
                          <td></td>
                          <td class="ps-4">{{ $c->name }}</td>
                          <td>{{ $c->weight }}%</td>
                          <td>{{ $d?->pilihan->scale }}</td>
                          <td>{{ $hs }}</td>
                          <td>{{ $sk }}</td>
                        </tr>
                      @endforeach
                    @endif
                  @endforeach
                </tbody>
                <tfoot>
                  <tr class="table-warning">
                    <th colspan="5" class="text-end">Total Nilai</th><th>{{ number_format($tot2,2) }}</th>
                  </tr>
                  <tr class="table-warning">
                    <th colspan="5" class="text-end">Kualitas Penerapan Manajemen Risiko</th><th>{{ $period->kpmr }}</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

      </div><!-- /.row -->

    </div><!-- /.tab-pane #kinerja -->
  </div><!-- /.tab-content -->
</div><!-- /.container -->

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipList.forEach(function (tooltipTriggerEl) {
      if (!bootstrap.Tooltip.getInstance(tooltipTriggerEl)) {
        new bootstrap.Tooltip(tooltipTriggerEl);
      }
    });

    const tableBody = document.getElementById('document-table-body');
    const docCountBadge = document.getElementById('doc-count-badge');

    tableBody.addEventListener('click', function (event) {
        const deleteButton = event.target.closest('.btn-delete-document');

        if (!deleteButton) {
            return;
        }

        const docId = deleteButton.dataset.docId;
        const periodId = deleteButton.dataset.periodId;
        const url = `/penilaian-rmi/${periodId}/aspek-kinerja/delete-document/${docId}`;

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Dokumen yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const csrfToken = document?.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.message || 'Gagal menghapus dokumen.') });
                    }
                    return response.json();
                })
                .then(data => {
                    const rowToRemove = document.getElementById(`doc-row-${docId}`);
                    if (rowToRemove) {
                        rowToRemove.remove();
                    }

                    const currentCount = parseInt(docCountBadge.innerText) || 0;
                    const newCount = Math.max(0, currentCount - 1);
                    docCountBadge.innerText = `${newCount} File`;

                    const remainingRows = tableBody.querySelectorAll('tr[id^="doc-row-"]').length;
                    if (remainingRows === 0) {
                        const emptyRowHtml = `
                            <tr id="empty-doc-row">
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Belum ada dokumen pendukung peringkat akhir.
                                </td>
                            </tr>`;
                        tableBody.innerHTML = emptyRowHtml;
                    }

                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message || 'Dokumen telah dihapus.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Gagal!',
                        error.message || 'Terjadi kesalahan saat menghapus dokumen.',
                        'error'
                    );
                });
            }
        });
    });

    $(document).on('click', '.btn-delete-dim-doc', function(e) {
      e.preventDefault();

      const formId = $(this).data('form-id'); // Mengambil ID form yang disembunyikan

      Swal.fire({
        title: 'Hapus Dokumen?',
        text: "Dokumen yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        // confirmButtonColor: '#dc3545', // Warna merah danger
        // cancelButtonColor: '#6c757d',  // Warna abu-abu cancel
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {

          Swal.fire({
            title: 'Menghapus...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

          document.getElementById(formId).submit();
        }
      });
    });
  });
</script>
@endpush
