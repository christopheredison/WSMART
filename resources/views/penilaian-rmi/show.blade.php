@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')

@php
  // Prepare data
  $period    = $period;      // RMIPeriod instance passed from controller
  $penilaian = $period->penilaianCapaianKinerja; // with details/pilihan eager-loaded
  //$details   = $penilaian->details->keyBy('parameter_id');
  $details   = $penilaian ? $penilaian->details->keyBy('parameter_id') : collect();
  //$paramsDim = $paramsDimensi;   // Aspek Dimensi, passed from controller
  $paramsDim = $dimensions;
  $paramsC   = $paramsCapaian;   // Aspek Capaian Kinerja
  $paramsK   = $paramsKpmr;      // Aspek KPMR
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
<div class="container my-5">

  {{-- 1. Informasi Periode & Ringkasan --}}
  <div class="row mb-4">
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
          </dl>
        </div>
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
            $pen  = $period->penilaianCapaianKinerja;
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
                  <tr><th>No</th><th>Parameter</th><th>Jawaban</th><th>Skala</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                  @foreach($paramsC as $i => $param)
                    @if($param->children->isEmpty())
                      @php
                        $d     = $details->get($param->id);
                        $jawab = $d?->pilihan->code  ?? '-';
                        $skala = $d?->pilihan->scale ?? '-';
                        $ket   = $d?->comment        ?? '-';
                      @endphp
                      <tr>
                        <td>{{ $i+1 }}</td>
                        <td>
                          {{ $param->name }}<br>
                          <small>
                            @foreach($param->options as $opt)
                              {{ $opt->code }}. {{ $opt->description }}; 
                            @endforeach
                          </small>
                        </td>
                        <td>{{ strtoupper($jawab) }}</td>
                        <td>{{ $skala }}</td>
                        <td>{{ $ket }}</td>
                      </tr>
                    @else
                      @foreach($param->children as $j => $child)
                        @php
                          $d     = $details->get($child->id);
                          $jawab = $d?->pilihan->code  ?? '-';
                          $skala = $d?->pilihan->scale ?? '-';
                          $ket   = $d?->comment        ?? '-';
                          $no    = $child->code;
                        @endphp
                        <tr>
                          <td>{{ $no }}</td>
                          <td class="ps-4">
                            {{ $child->name }}<br>
                            <small>
                              @foreach($child->options as $opt)
                                {{ $opt->code }}. {{ $opt->description }}; 
                              @endforeach
                            </small>
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

        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">Penilaian KPMR</div>
            <div class="card-body p-0">
              <table class="table mb-0">
                <thead class="table-light">
                  <tr><th>No</th><th>Parameter</th><th>Jawaban</th><th>Skala</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                  @foreach($paramsK as $i => $param)
                    @if($param->children->isEmpty())
                      @php
                        $d     = $details->get($param->id);
                        $jawab = $d?->pilihan->code  ?? '-';
                        $skala = $d?->pilihan->scale ?? '-';
                        $ket   = $d?->comment        ?? '-';
                      @endphp
                      <tr>
                        <td>{{ $i+1 }}</td>
                        <td>
                          {{ $param->name }}<br>
                          <small>
                            @foreach($param->options as $opt)
                              {{ $opt->code }}. {{ $opt->description }}; 
                            @endforeach
                          </small>
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
                          $ket   = $d?->comment        ?? '-';
                        @endphp
                        <tr>
                          <td>{{ $child->code }}</td>
                          <td class="ps-4">
                            {{ $child->name }}<br>
                            <small>
                              @foreach($child->options as $opt)
                                {{ $opt->code }}. {{ $opt->description }}; 
                              @endforeach
                            </small>
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
                        <td>{{ number_format($sum,2) }}</td>
                        <td>—</td><td><strong>{{ $skTop }}</strong></td>
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
                        <td>{{ number_format($sum,2) }}</td>
                        <td>—</td><td><strong>{{ $skTop }}</strong></td>
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
