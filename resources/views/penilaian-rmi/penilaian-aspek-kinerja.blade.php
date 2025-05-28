@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')

<div class="container my-4">
  <div class="row justify-content-center">
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
          {{-- Langkah form wizard --}}
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

          <form action="{{ route('penilaian-rmi.aspek-kinerja.store', $period->id) }}" method="POST">
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
                    Simpan Capaian & Lanjut <i class="bx bx-chevron-right ms-1"></i>
                  </button>
                </div>
              </div>

              {{-- STEP 2: Penilaian KPMR --}}
              <div class="tab-pane fade {{ session('active_tab')=='kpmr' ? 'show active' : '' }}" id="kpmr">
                <h4 class="mb-3">Penilaian Kualitas Penerapan Manajemen Risiko (KPMR)</h4>

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
                            <div class="card-header bg-info text-dark">
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
                    <i class="bx bx-chevron-left me-1"></i> Kembali ke Capaian
                  </button>
                  <button type="submit" name="action" value="save_kpmr" formnovalidate class="btn btn-primary">
                    Simpan KPMR & Lanjut <i class="bx bx-chevron-right ms-1"></i>
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
                  </div>
                </div>
                
                <div class="d-flex justify-content-between">
                  <button type="submit" name="action" value="back_to_kpmr" formnovalidate class="btn btn-outline-secondary">
                    <i class="bx bx-chevron-left me-1"></i> Kembali ke KPMR
                  </button>
                  <button type="submit" name="action" value="finish_final_rating" class="btn btn-success">
                    Selesai & Simpan <i class="bx bx-save ms-1"></i>
                  </button>
                </div>
              </div>
            </div>
          </form>

        </div>
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
</script>
@endpush

@endsection
