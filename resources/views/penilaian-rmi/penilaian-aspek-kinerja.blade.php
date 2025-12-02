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
                      <button type="button" class="btn btn-info text-white py-2" onclick="openRiskModal('eksposur')">
                          <span class="bx bx-table me-1"></span> Data Eksposur Risiko
                      </button>
                      <button type="button" class="btn btn-primary text-white py-2" data-bs-toggle="modal" onclick="openRiskModal('progress')">
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
                      <select name="final_rating_id" id="final_rating_id" class="form-select select2" required>
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
                <h5 class="modal-title">Pencapaian Nilai Eksposur Risiko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 mb-3">
                {{-- Filter Unit --}}
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <label class="col-auto col-form-label fw-bold">Pilih Divisi:</label>
                            <div class="col-md-4">
                                <select class="form-select risk-unit-selector">
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ $u->id == $defaultUnitId ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <span id="loading-spinner-1" class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                  <div class="card-body p-0">
                    <div class="table-responsive p-0">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="align-middle text-center" style="width: 5%">No</th>
                                    <th class="align-middle" style="width: 25%">Risiko</th>
                                    <th class="align-middle" style="width: 25%">Penyebab Risiko</th>
                                    <th class="align-middle text-center" style="width: 15%">Eksposur Inheren</th>
                                    <th class="align-middle text-center" style="width: 15%">Target Residual (Q4)</th>
                                    <th class="align-middle text-center" style="width: 15%">Realisasi Terkini</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-eksposur">
                            </tbody>
                        </table>
                    </div>
                  </div>
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
                <h5 class="modal-title">Pencapaian Output & Progress Perlakuan Risiko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 mb-3">
                {{-- Filter Unit --}}
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <label class="col-auto col-form-label fw-bold">Pilih Divisi:</label>
                            <div class="col-md-4">
                                <select class="form-select risk-unit-selector">
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ $u->id == $defaultUnitId ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <span id="loading-spinner-2" class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                  <div class="card-body p-0">
                    <div class="table-responsive p-0">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="align-middle text-center" style="width: 5%">No</th>
                                    <th class="align-middle" style="width: 25%">Risiko</th>
                                    <th class="align-middle" style="width: 50%">Penyebab & Perlakuan</th>
                                    <th class="align-middle text-center" style="width: 20%">Progress (%)</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-progress">
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Rata-rata Progress Perlakuan:</td>
                                    <td class="text-center fw-bold fs-5" id="total-average-progress">0%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                  </div>
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

<script>
    // Global variable untuk menyimpan data cache sementara (opsional)
    let currentPeriodId = "{{ $period->id }}";
    
    // Sinkronisasi kedua dropdown (agar jika ubah di modal 1, modal 2 ikut berubah)
    const unitSelectors = document.querySelectorAll('.risk-unit-selector');
    unitSelectors.forEach(sel => {
        sel.addEventListener('change', function() {
            const val = this.value;
            // Set value for all selectors
            unitSelectors.forEach(s => s.value = val);
            // Fetch data baru
            fetchRiskData(val);
        });
    });

    // Function membuka modal
    function openRiskModal(type) {
        const modalId = type === 'eksposur' ? '#modalEksposurRisiko' : '#modalProgressPerlakuan';
        const myModal = new bootstrap.Modal(document.querySelector(modalId));
        myModal.show();
        
        // Load data pertama kali jika belum ada isi atau refresh
        const unitId = unitSelectors[0].value;
        fetchRiskData(unitId);
    }

    // Function utama fetch data API
    function fetchRiskData(unitId) {
        // Tampilkan spinner
        document.getElementById('loading-spinner-1').classList.remove('d-none');
        document.getElementById('loading-spinner-2').classList.remove('d-none');
        
        // URL API
        const url = `{{ route('penilaian-rmi.get-risk-data', ':pid') }}?unit_id=${unitId}`.replace(':pid', currentPeriodId);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                renderTableEksposur(data.risks);
                renderTableProgress(data.risks, data.average_progress);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data risiko.');
            })
            .finally(() => {
                document.getElementById('loading-spinner-1').classList.add('d-none');
                document.getElementById('loading-spinner-2').classList.add('d-none');
            });
    }

    function formatCurrency(value) {
        if (value === null || value === undefined || value === '') return '-';
        
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            // minimumFractionDigits: 2,
            // maximumFractionDigits: 2
        }).format(value);
    }

    // Render Tabel 1: Eksposur
    function renderTableEksposur(risks) {
        const tbody = document.getElementById('tbody-eksposur');
        tbody.innerHTML = '';

        if (risks.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center p-4">Tidak ada data risiko untuk unit ini.</td></tr>';
            return;
        }

        risks.forEach((risk, index) => {
            let penyebabList = '<ul class="ps-3 mb-0">';
            risk.penyebab.forEach(p => { penyebabList += `<li>${p}</li>`; });
            penyebabList += '</ul>';

            let realisasiBadge = risk.realisasi !== null 
                ? `<span class="badge bg-primary mb-1">Q${risk.realisasi_quarter}</span><br>${formatCurrency(risk.realisasi)}` 
                : '<span class="text-muted">-</span>';

            const row = `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>
                        <a href="${risk.detail_url}" target="_blank" class="fw-bold text-decoration-none" title="Klik untuk lihat detail">
                            ${risk.peristiwa_risiko} <i class='bx bx-link-external small ms-1'></i>
                        </a>
                        <div class="text-muted small mt-1">${risk.deskripsi}</div>
                    </td>
                    <td>${penyebabList}</td>
                    <td class="text-center align-middle fw-bold">${formatCurrency(risk.inheren)}</td>
                    <td class="text-center align-middle">${formatCurrency(risk.residual_target)}</td>
                    <td class="text-center align-middle">${realisasiBadge}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    // Render Tabel 2: Progress
    function renderTableProgress(risks, avgProgress) {
        const tbody = document.getElementById('tbody-progress');
        tbody.innerHTML = '';

        // Set Total Average
        const avgEl = document.getElementById('total-average-progress');
        avgEl.innerText = avgProgress + '%';
        avgEl.className = `text-center fw-bold fs-5 ${avgProgress >= 100 ? 'text-success' : 'text-primary'}`;

        if (risks.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center p-4">Tidak ada data risiko untuk unit ini.</td></tr>';
            return;
        }

        let no = 1;
        risks.forEach(risk => {
            if(risk.perlakuans.length === 0) {
                // Jika tidak ada perlakuan, tampilkan baris kosong/info
                const row = `
                    <tr>
                        <td class="text-center">${no++}</td>
                        <td>
                            <a href="${risk.detail_url}" target="_blank" class="fw-bold text-decoration-none">
                                ${risk.peristiwa_risiko}
                            </a>
                            <div class="text-muted small mt-1">${risk.deskripsi}</div>
                        </td>
                        <td colspan="2" class="text-center text-muted fst-italic">Belum ada rencana perlakuan</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            } else {
                risk.perlakuans.forEach((perlakuan, pIndex) => {
                    const row = `
                        <tr>
                            ${pIndex === 0 ? `<td class="text-center align-middle bg-white" rowspan="${risk.perlakuans.length}">${no++}</td>` : ''}
                            ${pIndex === 0 ? `<td class="align-middle bg-white" rowspan="${risk.perlakuans.length}">
                                <a href="${risk.detail_url}" target="_blank" class="fw-bold text-decoration-none">
                                    ${risk.peristiwa_risiko} <i class='bx bx-link-external small ms-1'></i>
                                </a>
                                <div class="text-muted small mt-1">${risk.deskripsi}</div>
                            </td>` : ''}
                            <td>
                                <div class="mb-1"><strong>Penyebab:</strong> ${perlakuan.penyebab}</div>
                                <div class="p-2 bg-light border rounded">
                                    <small class="text-muted">Perlakuan:</small><br>
                                    ${perlakuan.rencana}
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <p class="fw-bold mb-2">
                                  ${perlakuan.progress}%
                                </p>
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="progress w-100" style="height: 20px;">
                                        <div class="progress-bar bg-${perlakuan.progress >= 100 ? 'success' : 'info'}" 
                                            role="progressbar" 
                                            style="width: ${perlakuan.progress}%;" 
                                            aria-valuenow="${perlakuan.progress}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            ${perlakuan.progress}%
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        });
    }
</script>
@endpush

@endsection
