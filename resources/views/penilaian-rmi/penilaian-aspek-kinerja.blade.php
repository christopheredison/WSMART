@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-success-subtle">
            <div class="svg-icon svg-icon-success">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Penilaian Aspek Kinerja - Periode {{ $period->year }}</h2>
        </div>
      </div>
      <div class="card-body">
        @php
          // Data jawaban lama atau existing
          $oldResponses = old('responses', []);
          $oldComments  = old('comments', []);
        @endphp

        <ul class="nav nav-tabs mb-4" id="stepTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ session('active_tab','capaian')=='capaian' ? 'active' : '' }}" id="tab-capaian" data-bs-toggle="tab" data-bs-target="#capaian" type="button">1. Capaian Kinerja</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ session('active_tab')=='kpmr' ? 'active' : '' }} {{ session('active_tab')=='capaian' ? 'disabled' : '' }}" id="tab-kpmr" data-bs-toggle="tab" data-bs-target="#kpmr" type="button">2. Penilaian KPMR</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ session('active_tab')=='final_rating' ? 'active' : '' }} {{ session('active_tab')=='capaian' || session('active_tab')=='kpmr' ? 'disabled' : '' }}" id="tab-final-rating" data-bs-toggle="tab" data-bs-target="#final-rating" type="button">3. Penilaian Tingkat Kesehatan Peringkat Akhir</button>
          </li>
        </ul>

        <form action="{{ route('penilaian-rmi.aspek-kinerja.store', $period->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="tab-content">

            {{-- STEP 1: Capaian Kinerja --}}
            <div class="tab-pane fade {{ session('active_tab','capaian')=='capaian' ? 'show active' : '' }}" id="capaian">
              <h4 class="mb-3">Penilaian Capaian Kinerja</h4>

              @foreach($paramsCapaian as $param)
                <div class="card mb-4">
                  <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                      {{ $param->code }}. {{ $param->name }}
                      <span class="badge bg-light text-primary ms-2">Bobot: {{ $param->weight }}%</span>
                    </h5>
                  </div>
                  <div class="card-body">
                    @php
                      // existing jawaban dari DB
                      $existingVal = $existing[$param->id] ?? null;
                      $existingCmt = $existingComments[$param->id] ?? '';
                    @endphp

                    @if($param->children->isEmpty())
                      @foreach($param->options as $opt)
                        @php
                          $sel = $oldResponses[$param->id] ?? $existingVal;
                        @endphp
                        <div class="form-check">
                          <input class="form-check-input" type="radio"
                                 name="responses[{{ $param->id }}]"
                                 id="opt-{{ $opt->id }}"
                                 value="{{ $opt->id }}"
                                 {{ $sel == $opt->id ? 'checked' : '' }}>
                          <label class="form-check-label" for="opt-{{ $opt->id }}">
                            ({{ strtoupper($opt->code) }}) {{ $opt->description }} — <strong>Skala: {{ $opt->scale }}</strong>
                          </label>
                        </div>
                      @endforeach
                      <div class="mt-3">
                        <label for="comment-{{ $param->id }}" class="form-label">Keterangan (opsional)</label>
                        <textarea name="comments[{{ $param->id }}]" id="comment-{{ $param->id }}" class="form-control" rows="2">{{ $oldComments[$param->id] ?? $existingCmt }}</textarea>
                      </div>
                    @else
                      @foreach($param->children as $child)
                        @php
                          $exVal = $existing[$child->id] ?? null;
                          $exCmt = $existingComments[$child->id] ?? '';
                          $selVal = $oldResponses[$child->id] ?? $exVal;
                        @endphp
                        <div class="card mb-3">
                          <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                              {{ $child->code }}. {{ $child->name }}
                              <span class="badge bg-light text-info ms-2">Bobot: {{ $child->weight }}%</span>
                            </h6>
                          </div>
                          <div class="card-body">
                            @foreach($child->options as $opt)
                              <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="responses[{{ $child->id }}]"
                                       id="opt-{{ $opt->id }}"
                                       value="{{ $opt->id }}"
                                       {{ $selVal == $opt->id ? 'checked' : '' }}>
                                <label class="form-check-label" for="opt-{{ $opt->id }}">
                                  ({{ strtoupper($opt->code) }}) {{ $opt->description }} — <strong>Skala: {{ $opt->scale }}</strong>
                                </label>
                              </div>
                            @endforeach
                            <div class="mt-2">
                              <label for="comment-{{ $child->id }}" class="form-label">Keterangan (opsional)</label>
                              <textarea name="comments[{{ $child->id }}]" id="comment-{{ $child->id }}" class="form-control" rows="1">{{ $oldComments[$child->id] ?? $exCmt }}</textarea>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    @endif
                  </div>
                </div>
              @endforeach

              <div class="d-flex justify-content-end">
                <button type="submit" name="action" value="save_capaian" formnovalidate class="btn btn-primary">
                  Simpan Capaian & Lanjut <span class="bx bx-chevron-right ms-1"></span>
                </button>
              </div>
            </div>

            {{-- STEP 2: Penilaian KPMR --}}
            <div class="tab-pane fade {{ session('active_tab')=='kpmr' ? 'show active' : '' }}" id="kpmr">
              <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="mb-0">Penilaian Kualitas Penerapan Manajemen Risiko (KPMR)</h4>
                  
                  <div class="btn-group">
                      <button type="button" class="btn btn-info text-white py-2" data-bs-toggle="modal" data-bs-target="#modalEksposurRisiko">
                          <span class="bx bx-table me-1"></span> Data Eksposur Risiko
                      </button>
                      <button type="button" class="btn btn-primary text-white py-2" data-bs-toggle="modal" data-bs-target="#modalProgressPerlakuan">
                          <span class="bx bx-task me-1"></span> Progress Perlakuan
                      </button>
                  </div>
              </div>
              {{-- <h4 class="mb-3">Penilaian Kualitas Penerapan Manajemen Risiko (KPMR)</h4> --}}

              @foreach($paramsKpmr as $param)
                <div class="card mb-4">
                  <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                      {{ $param->code }}. {{ $param->name }}
                      <span class="badge bg-light text-warning ms-2">Bobot: {{ $param->weight }}%</span>
                    </h5>
                  </div>
                  <div class="card-body">
                    @php
                      $existingVal = $existing[$param->id] ?? null;
                      $existingCmt = $existingComments[$param->id] ?? '';
                      $sel = $oldResponses[$param->id] ?? $existingVal;
                    @endphp

                    @if($param->children->isEmpty())
                      @foreach($param->options as $opt)
                        <div class="form-check">
                          <input class="form-check-input" type="radio"
                                name="responses[{{ $param->id }}]"
                                id="kopt-{{ $opt->id }}"
                                value="{{ $opt->id }}"
                                {{ $sel == $opt->id ? 'checked' : '' }}>
                          <label class="form-check-label" for="kopt-{{ $opt->id }}">
                            ({{ strtoupper($opt->code) }}) {{ $opt->description }} — <strong>Skala: {{ $opt->scale }}</strong>
                          </label>
                        </div>
                      @endforeach
                      <div class="mt-3">
                        <label for="kcomment-{{ $param->id }}" class="form-label">Keterangan (opsional)</label>
                        <textarea name="comments[{{ $param->id }}]" id="kcomment-{{ $param->id }}" class="form-control" rows="2">{{ $oldComments[$param->id] ?? $existingCmt }}</textarea>
                      </div>
                    @else
                      @foreach($param->children as $child)
                        @php
                          $exVal = $existing[$child->id] ?? null;
                          $exCmt = $existingComments[$child->id] ?? '';
                          $selVal = $oldResponses[$child->id] ?? $exVal;
                        @endphp
                        <div class="card mb-3">
                          <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                              {{ $child->code }}. {{ $child->name }}
                              <span class="badge bg-light text-info ms-2">Bobot: {{ $child->weight }}%</span>
                            </h6>
                          </div>
                          <div class="card-body">
                            @foreach($child->options as $opt)
                              <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="responses[{{ $child->id }}]"
                                       id="kopt-{{ $opt->id }}"
                                       value="{{ $opt->id }}"
                                       {{ $selVal == $opt->id ? 'checked' : '' }}>
                                <label class="form-check-label" for="kopt-{{ $opt->id }}">
                                  ({{ strtoupper($opt->code) }}) {{ $opt->description }} — <strong>Skala: {{ $opt->scale }}</strong>
                                </label>
                              </div>
                            @endforeach
                            <div class="mt-2">
                              <label for="kcomment-{{ $child->id }}" class="form-label">Keterangan (opsional)</label>
                              <textarea name="comments[{{ $child->id }}]" id="kcomment-{{ $child->id }}" class="form-control" rows="1">{{ $oldComments[$child->id] ?? $exCmt }}</textarea>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    @endif
                  </div>
                </div>
              @endforeach

              <div class="d-flex justify-content-between">
                <button type="submit" name="action" value="back_to_capaian" formnovalidate class="btn btn-outline-secondary">
                  <span class="bx bx-chevron-left me-1"></span> Kembali ke Capaian
                </button>
                <button type="submit" name="action" value="save_kpmr" formnovalidate class="btn btn-primary">
                  Simpan KPMR & Lanjut <span class="bx bx-chevron-right ms-1"></span>
                </button>
              </div>
            </div>

            {{-- STEP 3: Penilaian Tingkat Kesehatan Peringkat Akhir --}}
            <div class="tab-pane fade {{ session('active_tab')=='final_rating' ? 'show active' : '' }}" id="final-rating">
              <h4 class="mb-3">Penilaian Tingkat Kesehatan Peringkat Akhir</h4>

              <div class="card mb-4">
                <div class="card-header bg-success text-white">
                  <h5 class="mb-0">Pilih Final Rating</h5>
                </div>
                <div class="card-body">
                  @php
                    $existingFinalRatingId = $finalRatingPeriod->final_rating_id ?? old('final_rating_id');
                    $existingConversionScore = null;

                    if ($existingFinalRatingId) {
                      $selectedRating = $finalRatings->firstWhere('id', $existingFinalRatingId);
                      $existingConversionScore = $selectedRating ? $selectedRating->conversion_score : null;
                    }
                  @endphp

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="final_rating_id" class="form-label">Final Rating</label>
                      <select name="final_rating_id" id="final_rating_id" class="form-select" required>
                        <option value="">-- Pilih Final Rating --</option>
                        @foreach($finalRatings as $rating)
                          <option value="{{ $rating->id }}" data-conversion-score="{{ $rating->conversion_score }}" {{ $existingFinalRatingId == $rating->id ? 'selected' : '' }}>
                            {{ $rating->rating }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label for="conversion_score" class="form-label">Nilai Konversi</label>
                      <input type="text" id="conversion_score" class="form-control" value="{{ $existingConversionScore }}" readonly>
                    </div>
                  </div>

                  @if($period->nilai_konversi)
                  <div class="alert alert-info">
                    <p class="mb-0"><strong>Informasi Perhitungan:</strong></p>
                    <ul class="mb-0">
                      <li>Nilai Konversi Final Rating: <span id="display-conversion-score">{{ $existingConversionScore ?: '0' }}</span> × 50% = <span id="display-bobot-konversi">{{ $finalRatingPeriod->score_bobot_konversi ?? '0' }}</span></li>
                      <li>Nilai Konversi KPMR: {{ $period->nilai_konversi }} × 50% = {{ $period->nilai_konversi * 0.5 }}</li>
                      <li>Total Score Kinerja: <span id="display-total-score">{{ $finalRatingPeriod->total_score_kinerja ?? '0' }}</span></li>
                    </ul>
                  </div>
                  @endif

                  <div class="mt-4">
                    <h6 class="form-label">Upload dokumen pendukung (opsional)</h6>
                    <div id="document-uploads-container" style="display: none;">
                      {{-- Tempat untuk input file yang disembunyikan --}}
                    </div>
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">Nama Dokumen</th>
                          <th scope="col">Deskripsi (Opsional)</th>
                          <th scope="col" style="width: 80px;">Aksi</th>
                        </tr>
                      </thead>
                      <tbody id="final-rating-documents-tbody">
                        {{-- Baris dokumen akan ditambahkan di sini oleh JavaScript --}}
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="3" class="text-center">
                            <button type="button" class="btn btn-link btn-sm py-1" id="btnAddFinalRatingDocument">
                              <span class="bx bx-plus me-1"></span>Tambah Dokumen
                            </button>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between">
                <button type="submit" name="action" value="back_to_kpmr" formnovalidate class="btn btn-outline-secondary">
                  <span class="bx bx-chevron-left me-1"></span> Kembali ke KPMR
                </button>
                <button type="submit" name="action" value="finish_final_rating" class="btn btn-success">
                  Selesai & Simpan <span class="bx bx-save ms-1"></span>
                </button>
              </div>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

{{-- MODAL 1: Pencapaian Nilai Eksposur Risiko --}}
<div class="modal fade" id="modalEksposurRisiko" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header p-0 mb-4">
                <h5 class="modal-title">Pencapaian Nilai Eksposur Risiko (Risiko Korporat)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="align-middle">Risiko</th>
                                <th class="align-middle">Penyebab Risiko</th>
                                <th class="align-middle text-center">Eksposur Risiko Inheren</th>
                                <th class="align-middle text-center">Target Risiko Residual (Q4)</th>
                                <th class="align-middle text-center">Realisasi Eksposur Risiko (Terkini)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($corporateRisks as $risk)
                                <tr>
                                    <td>
                                        <strong>{{ $risk->peristiwa_risiko }}</strong>
                                        <div class="text-muted small">{{ $risk->deskripsi_peristiwa_risiko }}</div>
                                    </td>
                                    <td>
                                        <ul class="ps-3 mb-0">
                                            @foreach($risk->penyebabRisiko as $penyebab)
                                                <li>{{ $penyebab->penyebab_risiko }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="text-center align-middle">
                                        {{-- Inheren --}}
                                        Rp {{ number_format($risk->riskAnalysis->eksposur_risiko ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="text-center align-middle">
                                        {{-- Residual Q4 (Target) --}}
                                        Rp {{ number_format($risk->riskAnalysis->eksposur_risiko_residual_q4 ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="text-center align-middle">
                                        {{-- Realisasi Terkini --}}
                                        @php
                                            $lastMonitoring = $risk->monitoringRisikos->first();
                                        @endphp
                                        @if($lastMonitoring)
                                            <span class="badge bg-primary">Q{{ $lastMonitoring->quarter }}</span><br>
                                            Rp {{ number_format($lastMonitoring->eksposure_risiko ?? 0, 2, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data risiko korporat pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer p-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: Progress Perlakuan Risiko --}}
<div class="modal fade" id="modalProgressPerlakuan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header p-0 mb-4">
                <h5 class="modal-title">Pencapaian Output & Progress Perlakuan Risiko (Risiko Korporat)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="align-middle">Risiko</th>
                                <th class="align-middle">Penyebab & Perlakuan</th>
                                <th class="align-middle text-center" style="width: 15%;">Progress (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($corporateRisks as $risk)
                                @php
                                    // Hitung total baris yang dibutuhkan untuk rowspan 'Risiko'
                                    // Kita hitung jumlah penyebab risiko yang punya perlakuan
                                    // Jika 1 penyebab punya 2 perlakuan, itu akan merender 2 baris (opsional, disini saya buat simple per penyebab)
                                    $rowspan = $risk->penyebabRisiko->count();
                                @endphp

                                @foreach($risk->penyebabRisiko as $index => $penyebab)
                                    <tr>
                                        {{-- Kolom Risiko (Rowspan hanya di perulangan pertama) --}}
                                        @if($index === 0)
                                            <td rowspan="{{ $rowspan > 0 ? $rowspan : 1 }}" class="align-middle bg-white">
                                                <strong>{{ $risk->peristiwa_risiko }}</strong>
                                            </td>
                                        @endif

                                        {{-- Kolom Penyebab & Perlakuan --}}
                                        <td>
                                            <div class="mb-2"><strong>Penyebab:</strong> {{ $penyebab->penyebab_risiko }}</div>
                                            
                                            {{-- PERBAIKAN: Looping Perlakuan --}}
                                            @if($penyebab->perlakuanPenyebabRisikoUnit->count() > 0)
                                                @foreach($penyebab->perlakuanPenyebabRisikoUnit as $perlakuan)
                                                    @php
                                                        $lastProgress = $perlakuan->perlakuanPenyebabUnitMonitorings->first();
                                                        $progressVal = $lastProgress ? $lastProgress->progress_rencana_perlakuan_risiko : 0;
                                                    @endphp
                                                    
                                                    <div class="border rounded p-2 mb-2 bg-light">
                                                        <div class="text-muted small mb-1">Perlakuan:</div>
                                                        <div class="mb-2">{{ $perlakuan->rencana_perlakuan_risiko }}</div>
                                                        
                                                        {{-- Progress Bar per Perlakuan --}}
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-info me-2">Progress: {{ $progressVal }}%</span>
                                                            <div class="progress flex-grow-1" style="height: 10px;">
                                                                <div class="progress-bar bg-{{ $progressVal >= 100 ? 'success' : 'info' }}" 
                                                                    role="progressbar" 
                                                                    style="width: {{ $progressVal }}%;" 
                                                                    aria-valuenow="{{ $progressVal }}" 
                                                                    aria-valuemin="0" 
                                                                    aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted fst-italic">Belum ada rencana perlakuan.</span>
                                            @endif
                                        </td>

                                        {{-- Kolom Progress (Summary per Penyebab jika ada banyak perlakuan, atau kosongkan jika detail sudah di kolom tengah) --}}
                                        <td class="text-center align-middle">
                                            {{-- Opsional: Menampilkan rata-rata progress untuk penyebab ini --}}
                                            @php
                                                $avgPenyebab = 0;
                                                $countP = 0;
                                                foreach($penyebab->perlakuanPenyebabRisikoUnit as $p) {
                                                    $m = $p->perlakuanPenyebabUnitMonitorings->first();
                                                    $avgPenyebab += $m ? $m->progress_rencana_perlakuan_risiko : 0;
                                                    $countP++;
                                                }
                                                $finalAvg = $countP > 0 ? round($avgPenyebab / $countP) : 0;
                                            @endphp

                                            @if($countP > 0)
                                                <div class="fw-bold {{ $finalAvg >= 100 ? 'text-success' : 'text-primary' }}">
                                                    {{ $finalAvg }}%
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Tidak ada data risiko korporat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Rata-rata Progress Perlakuan:</td>
                                <td class="text-center fw-bold fs-5 text-{{ $averageProgress >= 100 ? 'success' : 'primary' }}">
                                    {{ $averageProgress }}%
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer p-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
  const tabCapaian = new bootstrap.Tab(document.querySelector('#tab-capaian'));
  const tabKpmr    = new bootstrap.Tab(document.querySelector('#tab-kpmr'));
  const tabFinalRating = new bootstrap.Tab(document.querySelector('#tab-final-rating'));

  document.querySelector('#tab-kpmr').classList.toggle('disabled', sessionStorage.getItem('active_tab') !== 'kpmr' && sessionStorage.getItem('active_tab') !== 'final_rating');
  document.querySelector('#tab-final-rating').classList.toggle('disabled', sessionStorage.getItem('active_tab') !== 'final_rating');

  // Handle final rating selection
  const finalRatingSelect = document.getElementById('final_rating_id');
  const conversionScoreInput = document.getElementById('conversion_score');
  const displayConversionScore = document.getElementById('display-conversion-score');
  const displayBobotKonversi = document.getElementById('display-bobot-konversi');
  const displayTotalScore = document.getElementById('display-total-score');

  if (finalRatingSelect) {
    finalRatingSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const conversionScore = selectedOption.getAttribute('data-conversion-score') || '0';

      conversionScoreInput.value = conversionScore;

      if (displayConversionScore) {
        displayConversionScore.textContent = conversionScore;
      }

      if (displayBobotKonversi) {
        const bobotKonversi = Math.round(parseFloat(conversionScore) * 0.5 * 100) / 100;
        displayBobotKonversi.textContent = bobotKonversi;
      }

      if (displayTotalScore) {
        const nilaiKonversi = {{ $period->nilai_konversi ?? 0 }};
        const bobotKonversi = Math.round(parseFloat(conversionScore) * 0.5 * 100) / 100;
        const totalScore = Math.round((bobotKonversi + (nilaiKonversi * 0.5)) * 100) / 100;
        displayTotalScore.textContent = totalScore;
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    const addDocumentButton = document.getElementById('btnAddFinalRatingDocument');
    const documentsTbody = document.getElementById('final-rating-documents-tbody');
    const uploadsContainer = document.getElementById('document-uploads-container');
    const maxFiles = 10;

    addDocumentButton.addEventListener('click', function() {
      if (documentsTbody.rows.length >= maxFiles) {
        alert(`Anda hanya dapat mengunggah maksimal ${maxFiles} dokumen.`);
        return;
      }

      const index = Date.now(); // Indeks unik untuk setiap baris

      // Buat input file
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.name = `documents[${index}]`;
      fileInput.id = `doc-file-${index}`;
      fileInput.style.display = 'none';
      uploadsContainer.appendChild(fileInput);

      // Buat baris tabel baru
      const newRow = documentsTbody.insertRow();
      newRow.setAttribute('data-index', index);

      newRow.innerHTML = `
        <td><span class="filename">Pilih file...</span></td>
        <td>
          <input type="text" name="document_descriptions[${index}]" class="form-control form-control-sm" placeholder="Deskripsi singkat dokumen">
        </td>
        <td>
          <button type="button" class="btn btn-link btn-sm text-danger btn-remove-doc">Hapus</button>
        </td>
      `;

      // Event listener untuk input file
      fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
          const fileName = this.files[0].name;
          // Batasi ukuran file (contoh: 5MB)
          const fileSize = this.files[0].size / 1024 / 1024; // dalam MB
          if (fileSize > 5) {
              alert('Ukuran file tidak boleh lebih dari 5MB.');
              this.value = ''; // Reset input file
              return;
          }
          newRow.querySelector('.filename').textContent = fileName;
        }
      });

      // Klik input file secara programatik
      fileInput.click();

      // Sembunyikan tombol tambah jika sudah mencapai batas
      if (documentsTbody.rows.length >= maxFiles) {
        addDocumentButton.style.display = 'none';
      }
    });

    // Event delegation untuk tombol hapus
    documentsTbody.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('btn-remove-doc')) {
        const row = e.target.closest('tr');
        const index = row.getAttribute('data-index');
        const fileInputToRemove = document.getElementById(`doc-file-${index}`);

        if (fileInputToRemove) {
          fileInputToRemove.remove();
        }
        row.remove();

        // Tampilkan kembali tombol tambah
        if (documentsTbody.rows.length < maxFiles) {
          addDocumentButton.style.display = 'inline-block';
        }
      }
    });
  });
</script>
@endpush

@endsection
