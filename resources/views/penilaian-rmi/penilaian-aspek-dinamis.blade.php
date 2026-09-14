@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
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
        <form action="{{ route('penilaian-rmi.save-aspek-dinamis', $period->id) }}" method="POST" enctype="multipart/form-data" id="formPenilaian">
          @csrf
          <input type="hidden" name="action" id="formAction" value="save">

          @php
            // Inisiasi variabel untuk penomoran Parameter yang tidak ter-reset
            $paramIndex = 1;
          @endphp

          <div class="accordion" id="accordionDimensi">
            @foreach($dimensions as $dimension)
            <div class="accordion-item mb-4 border-0 shadow-sm">
              <h6 class="accordion-header" id="headingDimensi{{ $dimension->id }}">
                <button class="accordion-button bg-primary text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDimensi{{ $dimension->id }}" aria-expanded="true" aria-controls="collapseDimensi{{ $dimension->id }}">
                  <h6 class="mb-0">Dimensi : {{ $dimension->name }}</h6>
                </button>
              </h6>
              <div id="collapseDimensi{{ $dimension->id }}" class="accordion-collapse collapse show" aria-labelledby="headingDimensi{{ $dimension->id }}">
                <div class="accordion-body border border-top-0 border-primary rounded-bottom p-4">

                  <div class="accordion" id="accordionSubDimensi{{ $dimension->id }}">
                    @foreach($dimension->subDimensions as $subDimension)
                    <div class="accordion-item mb-3 border-0">
                      <h6 class="accordion-header" id="headingSubDimensi{{ $subDimension->id }}">
                        <button class="accordion-button bg-info text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubDimensi{{ $subDimension->id }}" aria-expanded="true" aria-controls="collapseSubDimensi{{ $subDimension->id }}">
                          <h6 class="mb-0">Sub-Dimensi : {{ $subDimension->name }}</h6>
                        </button>
                      </h6>
                      <div id="collapseSubDimensi{{ $subDimension->id }}" class="accordion-collapse collapse show" aria-labelledby="headingSubDimensi{{ $subDimension->id }}">
                        <div class="accordion-body border border-top-0 border-info rounded-bottom p-3">

                          <div class="accordion" id="accordionParameter{{ $subDimension->id }}">
                            @foreach($subDimension->measurementParameters as $parameter)
                            @if($parameter->criteria && $parameter->criteria->count() > 0)
                            <div class="accordion-item mb-3">
                              <h6 class="accordion-header" id="headingParameter{{ $parameter->id }}">
                                <button class="accordion-button bg-light text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParameter{{ $parameter->id }}" aria-expanded="true" aria-controls="collapseParameter{{ $parameter->id }}">
                                  <!-- Menggunakan $paramIndex agar nomor tidak reset -->
                                  Parameter {{ $paramIndex }}: {{ $parameter->statement }}
                                </button>
                              </h6>
                              <div id="collapseParameter{{ $parameter->id }}" class="accordion-collapse collapse show" aria-labelledby="headingParameter{{ $parameter->id }}">
                                <div class="accordion-body">

                                  @foreach($parameter->criteria as $criteria)
                                  <div class="mb-4 border-bottom pb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                      <strong class="text-primary">Kriteria {{ $loop->iteration }}</strong>
                                      <div class="d-flex gap-2">
                                        <!-- Menggunakan $paramIndex pada data-fieldname untuk validasi js -->
                                        @php $selectedScore = $scores[$criteria->id] ?? null; @endphp
                                        <select class="form-select form-select-sm w-auto" name="scores[{{ $criteria->id }}]" data-fieldname="Score: Parameter {{ $paramIndex }}, Kriteria {{ $loop->iteration }}" data-criteria-id="{{ $criteria->id }}">
                                          <option value="" disabled {{ $selectedScore === null ? 'selected' : '' }}>Pilih Score</option>
                                          <option value="0" {{ $selectedScore !== null && (int) $selectedScore === 0 ? 'selected' : '' }}>0 - Skip Penilaian</option>
                                          @for($i = max(1, $criteria->min_score); $i <= $criteria->max_score; $i++)
                                            <option value="{{ $i }}" {{ $selectedScore !== null && (int) $selectedScore === $i ? 'selected' : '' }}>
                                              {{ $i }}
                                            </option>
                                          @endfor
                                        </select>

                                        @php
                                          $isGapFilled = !empty($gapAnalysis[$criteria->id]);
                                          $btnClass = $isGapFilled ? 'btn-primary' : 'btn-outline-primary';
                                          $btnIcon = $isGapFilled ? 'bx-check-circle' : 'bx-file';
                                        @endphp
                                        <button type="button"
                                                class="btn btn-sm {{ $btnClass }} btn-gap-modal"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalKriteria{{ $criteria->id }}">
                                          <span class="bx {{ $btnIcon }} me-1"></span> Gap Analysis
                                        </button>
                                      </div>
                                    </div>

                                    <div class="row g-2 align-items-stretch">
                                      @foreach($criteria->details->sortBy('level') as $detail)
                                      @php
                                        $bgClass = match($detail->level) {
                                          1 => 'bg-primary',
                                          2 => 'bg-info',
                                          3 => 'bg-success',
                                          4 => 'bg-warning text-dark',
                                          5 => 'bg-danger',
                                          default => 'bg-secondary'
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
                                      <div class="col-md">
                                        <div class="card h-100 border shadow-none">
                                          <div class="card-header p-2 text-center {{ $bgClass }} {{ $detail->level == 4 ? '' : 'text-white' }}" style="font-size: 12px;">
                                            <span class="fw-bold d-block">{{ $detail->level }}</span>
                                            <span>{{ $levelText }}</span>
                                          </div>
                                          <div class="card-body p-2 text-start bg-white">
                                            <div class="text-dark" style="font-size: 12px;">
                                              {!! $detail?->criteria ? nl2br(e($detail->criteria)) : '-' !!}
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      @endforeach
                                    </div>
                                  </div>

                                  <!-- Modal Gap Analysis -->
                                  <div class="modal fade" id="modalKriteria{{ $criteria->id }}" tabindex="-1" aria-labelledby="modalLabelKriteria{{ $criteria->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                      <div class="modal-content">
                                        <div class="modal-header">
                                          <h5 class="modal-title" id="modalLabelKriteria{{ $criteria->id }}">Gap Analysis & Upload Dokumen - Kriteria {{ $loop->iteration }}</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                          <div class="mb-3">
                                            <label class="form-label">Gap Analysis <span class="text-danger">*</span></label>
                                            <!-- Menggunakan $paramIndex pada data-fieldname -->
                                            <textarea name="gap_analysis[{{ $criteria->id }}]" class="form-control" rows="4" data-fieldname="Gap Analysis: Parameter {{ $paramIndex }}, Kriteria {{ $loop->iteration }}">{{ old('gap_analysis.' . $criteria->id, $gapAnalysis[$criteria->id] ?? '') }}</textarea>
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
                            </div>

                            <!-- Increment variabel setelah tag parameter selesai -->
                            @php
                              $paramIndex++;
                            @endphp

                            @endif
                            @endforeach
                          </div>

                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>

                </div>
              </div>
            </div>
            @endforeach
          </div>

          <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('penilaian-rmi.index') }}" class="btn btn-outline-secondary">
              <span class="bx bx-arrow-back me-1"></span> Kembali
            </a>
            <div>
              <button type="button" id="btn-save" class="btn btn-primary me-2">
                <span class="bx bx-save me-1"></span> Simpan Sementara
              </button>
              <button type="button" id="btn-finish" class="btn btn-success">
                <span class="bx bx-check-circle me-1"></span> Selesai
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Floating Action Buttons -->
<div class="floating-buttons" id="floatingButtons">
  <button type="button" class="btn btn-primary me-2" id="floatingSaveBtn">
    <span class="bx bx-save me-1"></span> Simpan Sementara
  </button>
  <button type="button" class="btn btn-success" id="floatingFinishBtn">
    <span class="bx bx-check-circle me-1"></span> Selesai
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

  .accordion-button.bg-primary::after,
  .accordion-button.bg-info::after {
      filter: invert(1) grayscale(100%) brightness(200%);
  }
</style>
@endpush

@push('scripts')
<script>
  $(document).ready(function() {
    const form = $('#formPenilaian');
    const formEl = document.getElementById('formPenilaian');
    const originalScores = {};
    const originalGaps = {};
    const saveUrl = form.attr('action');

    // Snapshot nilai awal saat halaman dibuka (untuk partial save multi-user)
    $('select[name^="scores"]').each(function() {
      const id = String($(this).data('criteria-id'));
      originalScores[id] = $(this).val() ?? '';
    });

    $('textarea[name^="gap_analysis"]').each(function() {
      const match = ($(this).attr('name') || '').match(/\[(\d+)\]/);
      if (!match) return;
      originalGaps[match[1]] = $(this).val() ?? '';
    });

    function getDirtyCriteriaIds() {
      const dirty = new Set();

      $('select[name^="scores"]').each(function() {
        const id = String($(this).data('criteria-id'));
        const current = $(this).val() ?? '';
        if (String(current) !== String(originalScores[id] ?? '')) {
          dirty.add(id);
        }
      });

      $('textarea[name^="gap_analysis"]').each(function() {
        const match = ($(this).attr('name') || '').match(/\[(\d+)\]/);
        if (!match) return;
        const id = match[1];
        if (String($(this).val() ?? '') !== String(originalGaps[id] ?? '')) {
          dirty.add(id);
        }
      });

      $('input[type="file"][name^="files"]').each(function() {
        if (this.files && this.files.length > 0) {
          const match = ($(this).attr('name') || '').match(/files\[(\d+)\]/);
          if (match) dirty.add(match[1]);
        }
      });

      return dirty;
    }

    // Bangun FormData kecil: hanya CSRF + action + kriteria yang berubah + file yang benar-benar dipilih.
    // Ini menghindari error PHP "Multipart body parts limit exceeded".
    function buildPartialFormData(actionValue, dirtyIds) {
      const formData = new FormData();
      const token = form.find('input[name="_token"]').val();

      formData.append('_token', token);
      formData.append('action', actionValue);

      dirtyIds.forEach(function(criteriaId) {
        const scoreSelect = formEl.querySelector('select[name="scores[' + criteriaId + ']"]');
        const gapTextarea = formEl.querySelector('textarea[name="gap_analysis[' + criteriaId + ']"]');

        if (scoreSelect) {
          formData.append('scores[' + criteriaId + ']', scoreSelect.value ?? '');
        }

        if (gapTextarea) {
          formData.append('gap_analysis[' + criteriaId + ']', gapTextarea.value ?? '');
        }

        // Hanya append file yang benar-benar dipilih (bukan input kosong)
        for (let i = 0; i < 3; i++) {
          const fileInput = formEl.querySelector('input[type="file"][name="files[' + criteriaId + '][' + i + ']"]');
          if (fileInput && fileInput.files && fileInput.files[0]) {
            formData.append('files[' + criteriaId + '][' + i + ']', fileInput.files[0]);
          }
        }
      });

      return formData;
    }

    function submitPartialForm(actionValue, dirtyIds) {
      const formData = buildPartialFormData(actionValue, dirtyIds);

      $.ajax({
        url: saveUrl,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response, textStatus, xhr) {
          // Redirect response (Laravel redirect) biasanya 200 + HTML, atau ikut Location
          const redirectUrl = xhr.getResponseHeader('X-Redirect') || null;

          if (response && response.success === false) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal Menyimpan',
              text: response.message || 'Terjadi kesalahan saat menyimpan data.',
              confirmButtonColor: '#0d6efd'
            });
            return;
          }

          const message = (response && response.message)
            ? response.message
            : (actionValue === 'finish'
              ? 'Penilaian Aspek Dimensi berhasil diselesaikan.'
              : 'Penilaian Aspek Dimensi berhasil disimpan sementara.');

          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: message,
            confirmButtonColor: '#0d6efd'
          }).then(function() {
            window.location.href = @json(route('penilaian-rmi.show', $period->id));
          });
        },
        error: function(xhr) {
          let message = 'Terjadi kesalahan saat menyimpan data.';
          let html = null;

          if (xhr.status === 422) {
            const res = xhr.responseJSON || {};
            message = res.message || 'Validasi gagal. Mohon periksa kembali data Anda.';

            if (res.errors) {
              const list = Array.isArray(res.errors)
                ? res.errors
                : Object.values(res.errors).flat();

              if (list.length) {
                html = '<div class="text-start" style="max-height:250px;overflow-y:auto;"><ul class="text-danger ps-3 mb-0">' +
                  list.map(function(item) { return '<li>' + item + '</li>'; }).join('') +
                  '</ul></div>';
              }
            }
          } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          } else if (typeof xhr.responseText === 'string' && xhr.responseText.indexOf('Multipart body parts limit') !== -1) {
            message = 'Form terlalu besar untuk dikirim. Silakan refresh halaman lalu simpan ulang (hanya perubahan Anda yang dikirim).';
          }

          Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan',
            text: html ? undefined : message,
            html: html || undefined,
            confirmButtonColor: '#0d6efd'
          });
        }
      });
    }

    function submitWithLoadingAndConfirmation(actionValue, title, text, confirmText, confirmColor) {
      const dirtyIds = getDirtyCriteriaIds();

      if (actionValue === 'save' && dirtyIds.size === 0) {
        Swal.fire({
          icon: 'info',
          title: 'Tidak ada perubahan',
          text: 'Belum ada data yang diubah untuk disimpan sementara.',
          confirmButtonColor: '#0d6efd'
        });
        return;
      }

      Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (!result.isConfirmed) return;

        Swal.fire({
          title: 'Memproses Data...',
          html: 'Mohon tunggu sebentar, data sedang disimpan.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        submitPartialForm(actionValue, dirtyIds);
      });
    }

    // Action SELESAI: kelengkapan final divalidasi server dari DB (hasil gabungan multi-user)
    $('#btn-finish, #floatingFinishBtn').click(function(e) {
      e.preventDefault();

      let isValid = true;
      let firstInvalidElement = null;
      let errorMessages = [];
      const dirtyIds = getDirtyCriteriaIds();

      $('select[name^="scores"]').removeClass('is-invalid');
      $('textarea[name^="gap_analysis"]').removeClass('is-invalid');
      $('.btn-gap-modal').removeClass('btn-danger text-white');

      dirtyIds.forEach(function(criteriaId) {
        const scoreSelect = $('select[name="scores[' + criteriaId + ']"]');
        const gapTextarea = $('textarea[name="gap_analysis[' + criteriaId + ']"]');
        const scoreVal = scoreSelect.val();

        if (scoreVal === null || scoreVal === '') {
          isValid = false;
          scoreSelect.addClass('is-invalid');
          errorMessages.push('<li>' + (scoreSelect.data('fieldname') || ('Score kriteria ' + criteriaId)) + '</li>');
          if (!firstInvalidElement) firstInvalidElement = scoreSelect;
          return;
        }

        if (scoreVal === '0' || scoreVal === 0) {
          return;
        }

        if (gapTextarea.length && gapTextarea.val().trim() === '') {
          isValid = false;
          gapTextarea.addClass('is-invalid');

          let modalId = gapTextarea.closest('.modal').attr('id');
          let triggerBtn = $('button[data-bs-target="#' + modalId + '"]');
          triggerBtn.removeClass('btn-primary btn-outline-primary').addClass('btn-danger text-white');
          errorMessages.push('<li>' + (gapTextarea.data('fieldname') || ('Gap Analysis kriteria ' + criteriaId)) + '</li>');

          if (!firstInvalidElement) firstInvalidElement = triggerBtn;
        }
      });

      if (!isValid) {
        let errorHtml = '<div class="text-start" style="max-height: 250px; overflow-y: auto;">' +
                          '<p class="mb-2 text-dark">Mohon lengkapi bagian yang Anda ubah:</p>' +
                          '<ul class="text-danger ps-3 mb-0" style="font-size: 0.95rem;">' +
                            errorMessages.join('') +
                          '</ul>' +
                        '</div>';

        Swal.fire({
          icon: 'error',
          title: 'Data Perubahan Belum Lengkap!',
          html: errorHtml,
          confirmButtonColor: '#0d6efd'
        }).then(() => {
          if (firstInvalidElement && firstInvalidElement.length) {
            $('html, body').animate({
              scrollTop: firstInvalidElement.offset().top - 150
            }, 500);

            if (firstInvalidElement.is('select')) {
              firstInvalidElement.focus();
            }
          }
        });
        return;
      }

      submitWithLoadingAndConfirmation(
        'finish',
        'Selesaikan Penilaian?',
        'Sistem akan mengecek kelengkapan dari database (termasuk data yang diisi user lain). Hanya perubahan Anda yang akan disimpan.',
        'Ya, Selesaikan!',
        '#198754'
      );
    });

    $(document).on('change', 'select[name^="scores"]', function() {
      $(this).removeClass('is-invalid');

      const criteriaId = $(this).data('criteria-id');
      const gapTextarea = $('textarea[name="gap_analysis[' + criteriaId + ']"]');
      if ($(this).val() === '0') {
        gapTextarea.removeClass('is-invalid');
        let modalId = gapTextarea.closest('.modal').attr('id');
        let triggerBtn = $('button[data-bs-target="#' + modalId + '"]');
        triggerBtn.removeClass('btn-danger').addClass('btn-outline-primary');
      }
    });

    $(document).on('input', 'textarea[name^="gap_analysis"]', function() {
      $(this).removeClass('is-invalid');

      let modalId = $(this).closest('.modal').attr('id');
      let triggerBtn = $('button[data-bs-target="#' + modalId + '"]');
      let icon = triggerBtn.find('.bx');

      if ($(this).val().trim() !== '') {
        triggerBtn.removeClass('btn-danger btn-outline-primary').addClass('btn-primary text-white');
        icon.removeClass('bx-file').addClass('bx-check-circle');
      } else {
        triggerBtn.removeClass('btn-danger btn-primary text-white').addClass('btn-outline-primary');
        icon.removeClass('bx-check-circle').addClass('bx-file');
      }
    });

    $('#btn-save, #floatingSaveBtn').click(function(e) {
      e.preventDefault();

      submitWithLoadingAndConfirmation(
        'save',
        'Simpan Sementara?',
        'Hanya bagian yang Anda ubah yang akan disimpan, sehingga tidak menimpa data user lain.',
        'Ya, Simpan!',
        '#0d6efd'
      );
    });

    // Cegah native submit penuh (Enter key) yang bisa memicu multipart limit
    form.on('submit', function(e) {
      e.preventDefault();
    });

    const formButtons = $('.d-flex.justify-content-between.mt-4');
    const floatingButtons = $('#floatingButtons');

    $(window).scroll(function() {
      if (formButtons.length === 0) return;

      const formButtonsPosition = formButtons.offset().top;
      const scrollPosition = $(window).scrollTop() + $(window).height();

      if (scrollPosition > formButtonsPosition) {
        floatingButtons.addClass('hidden');
      } else {
        floatingButtons.removeClass('hidden');
      }
    });

    $(window).scroll();
  });
</script>
@endpush
