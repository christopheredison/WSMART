@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center gap-3">
            <div class="lead__icon bg-warning-subtle">
              <div class="svg-icon svg-icon-warning">
                @include('partials.icon-layer')
              </div>
            </div>
            <h2 class="h3">Penilaian Aspek Dimensi - Periode {{ $period->year }}</h2>
          </div>
        </div>
        <div class="card-body">
          <form action="{{ route('penilaian-rmi.save-aspek-dinamis', $period->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @foreach($dimensions as $dimension)
            <!-- Dimensi -->
            <div class="card mb-4">
              <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Dimensi : {{ $dimension->name }}</h5>
              </div>
              <div class="card-body">
                
                @foreach($dimension->subDimensions as $subDimension)
                <!-- Sub-Dimensi -->
                <div class="card mb-3">
                  <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Sub-Dimensi : {{ $subDimension->name }}</h6>
                  </div>
                  <div class="card-body">
                    
                    @foreach($subDimension->measurementParameters as $parameter)
                    @if($parameter->criteria && $parameter->criteria->count() > 0)
                    <!-- Parameter -->
                    <div class="card mb-3">
                      <div class="card-header">
                        <strong>{{ $parameter->statement }}</strong>
                      </div>
                      <div class="card-body">
                        
                        @foreach($parameter->criteria as $criteria)
                        <!-- Kriteria -->
                        <div class="mb-4">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Kriteria {{ $loop->iteration }}</strong>
                            <div class="d-flex gap-2">
                              <select class="form-select form-select-sm w-auto" 
                                      name="scores[{{ $criteria->id }}]">
                                <option value="" disabled {{ !isset($scores[$criteria->id]) ? 'selected' : '' }}>Score</option>
                                @for($i = $criteria->min_score; $i <= $criteria->max_score; $i++)
                                  <option value="{{ $i }}" {{ isset($scores[$criteria->id]) && $scores[$criteria->id] == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                  </option>
                                @endfor
                              </select>
                              <button type="button" 
                                      class="btn btn-sm btn-outline-primary"
                                      data-bs-toggle="modal"
                                      data-bs-target="#modalKriteria{{ $criteria->id }}">
                                <i class="bx bx-file"></i>
                              </button>
                            </div>
                          </div>
                          <div class="d-flex gap-2">
                            @foreach($criteria->details->sortBy('level') as $detail)
                            <div class="flex-fill">
                              @php
                                $bgClass = match($detail->level) {
                                  1 => 'bg-primary',
                                  2 => 'bg-info',
                                  3 => 'bg-success',
                                  4 => 'bg-warning',
                                  5 => 'bg-info',
                                  default => 'bg-light'
                                };
                                $levelText = match($detail->level) {
                                  1 => 'Initial Phase',
                                  2 => 'Emerging State',
                                  3 => 'Good Practice',
                                  4 => 'Strong Practice',
                                  5 => 'Best Practice',
                                  default => 'Level ' . $detail->level
                                };
                              @endphp
                              <div class="p-2 text-center {{ $bgClass }} text-white">
                                <strong>{{ $detail->level }} {{ $levelText }}</strong>
                              </div>
                              <div class="p-3 border text-start">
                                <small>{{ $detail->criteria }}</small>
                              </div>
                            </div>
                            @endforeach
                          </div>
                        </div>
                        
                        <!-- Modal untuk masing-masing kriteria -->
                        <div class="modal fade" id="modalKriteria{{ $criteria->id }}" tabindex="-1" aria-labelledby="modalLabelKriteria{{ $criteria->id }}" aria-hidden="true">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="modalLabelKriteria{{ $criteria->id }}">Gap Analysis & Upload Dokumen - Kriteria {{ $loop->iteration }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <div class="modal-body">
                                <div class="mb-3">
                                  <label class="form-label">Gap Analysis</label>
                                  <textarea name="gap_analysis[{{ $criteria->id }}]" class="form-control" rows="4">{{ old('gap_analysis.' . $criteria->id, $gapAnalysis[$criteria->id] ?? '') }}</textarea>
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Upload Dokumen 1</label>
                                  <input type="file" class="form-control" name="files[{{ $criteria->id }}][0]">
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Upload Dokumen 2</label>
                                  <input type="file" class="form-control" name="files[{{ $criteria->id }}][1]">
                                </div>
                                <div class="mb-3">
                                  <label class="form-label">Upload Dokumen 3</label>
                                  <input type="file" class="form-control" name="files[{{ $criteria->id }}][2]">
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                              </div>
                            </div>
                          </div>
                        </div>
                        @endforeach
                        
                      </div>
                    </div>
                    @endif
                    @endforeach
                    
                  </div>
                </div>
                @endforeach
                
              </div>
            </div>
            @endforeach
            
            <div class="d-flex justify-content-between mt-4">
              <a href="{{ route('penilaian-rmi.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
              </a>
              <div>
                <button type="submit" name="action" value="save" class="btn btn-primary me-2">
                  <i class="bx bx-save me-1"></i> Simpan Sementara
                </button>
                <button type="submit" name="action" value="finish" class="btn btn-success" id="finish-btn">
                  <i class="bx bx-check-circle me-1"></i> Selesai
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Floating Action Buttons -->
<div class="floating-buttons" id="floatingButtons">
  <button type="button" class="btn btn-primary me-2" id="floatingSaveBtn">
    <i class="bx bx-save me-1"></i> Simpan Sementara
  </button>
  <button type="button" class="btn btn-success" id="floatingFinishBtn">
    <i class="bx bx-check-circle me-1"></i> Selesai
  </button>
</div>
@endsection

@push('styles')
<style>
  .floating-buttons {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: flex;
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    padding: 10px;
    background-color: rgba(255, 255, 255, 0.9);
    transition: opacity 0.3s ease;
  }
  
  .floating-buttons.hidden {
    opacity: 0;
    pointer-events: none;
  }
</style>
@endpush

@push('scripts')
<script>
  $(document).ready(function() {
    // Validasi sebelum submit form dengan action finish
    $('#finish-btn, #floatingFinishBtn').click(function(e) {
      if (this.id === 'floatingFinishBtn') {
        e.preventDefault();
      }
      
      const emptyScores = $('select[name^="scores"]').filter(function() {
        return $(this).val() === null || $(this).val() === '';
      });
      
      if (emptyScores.length > 0) {
        e.preventDefault();
        alert('Mohon lengkapi semua penilaian parameter sebelum menyelesaikan.');
        $('html, body').animate({
          scrollTop: $(emptyScores[0]).offset().top - 100
        }, 500);
        $(emptyScores[0]).focus();
      } else if (this.id === 'floatingFinishBtn') {
        // Jika tidak ada yang kosong dan tombol yang diklik adalah floating button
        $('button[name="action"][value="finish"]').click();
      }
    });
    
    // Floating Save Button
    $('#floatingSaveBtn').click(function() {
      $('button[name="action"][value="save"]').click();
    });
    
    // Tampilkan/sembunyikan floating buttons berdasarkan scroll
    const formButtons = $('.d-flex.justify-content-between.mt-4');
    const floatingButtons = $('#floatingButtons');
    
    $(window).scroll(function() {
      const formButtonsPosition = formButtons.offset().top;
      const scrollPosition = $(window).scrollTop() + $(window).height();
      
      // Jika tombol form sudah terlihat, sembunyikan floating buttons
      if (scrollPosition > formButtonsPosition) {
        floatingButtons.addClass('hidden');
      } else {
        floatingButtons.removeClass('hidden');
      }
    });
    
    // Trigger scroll event pada awal load untuk set status awal
    $(window).scroll();

  });
</script>
@endpush