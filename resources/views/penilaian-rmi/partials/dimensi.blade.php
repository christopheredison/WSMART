{{-- resources/views/penilaian-rmi/partials/dimensi.blade.php --}}
@php
  // Variabel yang tersedia:
  // $dimensions, $parameterScores, $criteriaScores, $dimensionScores

  // 1. INISIASI COUNTER PARAMETER GLOBAL
  $paramIndex = 1;
@endphp

<div class="card">
  <div class="card-header bg-primary text-white">
    <h4 class="mb-0">Daftar Dimensi</h4>
  </div>
  <div class="card-body">
    {{-- Tab Navigasi Dimensi --}}
    <ul class="nav nav-tabs" id="dimensionTab" role="tablist">
      @foreach($dimensions as $index => $dimension)
        <li class="nav-item" role="presentation">
          <button class="nav-link {{ $index == 0 ? 'active' : '' }}"
                  id="dimension-tab-{{ $dimension->id }}"
                  data-bs-toggle="tab"
                  data-bs-target="#dimension-content-{{ $dimension->id }}"
                  type="button" role="tab"
                  aria-controls="dimension-content-{{ $dimension->id }}"
                  aria-selected="{{ $index == 0 ? 'true' : 'false' }}">
            {{ $dimension->name }}

            <span class="badge bg-primary ms-2">
              @php
                // Cari skor dimensi langsung dari tabel DimensionAspectEvaluation
                $dimScore = null;
                $dimensionEval = $dimensionScores->where('dimension_id', $dimension->id)->first();
                if ($dimensionEval) {
                  $dimScore = $dimensionEval->score_dimension;
                }
                echo $dimScore !== null ? number_format($dimScore, 2) : '-';
              @endphp
            </span>
          </button>
        </li>
      @endforeach
    </ul>

    {{-- Tab Content Dimensi --}}
    <div class="tab-content pt-4" id="dimensionTabContent">
      @foreach($dimensions as $index => $dimension)
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}"
             id="dimension-content-{{ $dimension->id }}"
             role="tabpanel"
             aria-labelledby="dimension-tab-{{ $dimension->id }}">

          {{-- Tab Navigasi Sub-Dimensi --}}
          <ul class="nav nav-pills mb-4" id="subDimensionTab-{{ $dimension->id }}" role="tablist">
            @foreach($dimension->subDimensions as $subIndex => $subDimension)
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $subIndex == 0 ? 'active' : '' }}"
                        id="sub-tab-{{ $subDimension->id }}"
                        data-bs-toggle="pill"
                        data-bs-target="#sub-content-{{ $subDimension->id }}"
                        type="button" role="tab"
                        aria-controls="sub-content-{{ $subDimension->id }}"
                        aria-selected="{{ $subIndex == 0 ? 'true' : 'false' }}">
                  {{ $subDimension->name }}
                </button>
              </li>
            @endforeach
          </ul>

          {{-- Tab Content Sub-Dimensi --}}
          <div class="tab-content" id="subDimensionTabContent-{{ $dimension->id }}">
            @foreach($dimension->subDimensions as $subIndex => $subDimension)
              <div class="tab-pane fade {{ $subIndex == 0 ? 'show active' : '' }}"
                   id="sub-content-{{ $subDimension->id }}"
                   role="tabpanel"
                   aria-labelledby="sub-tab-{{ $subDimension->id }}">

                {{-- Parameter Pengukuran --}}
                @foreach($subDimension->measurementParameters as $parameter)
                  <div class="card mb-3 border-secondary">
                    <div class="card-header bg-light">
                      <div class="d-flex justify-content-between align-items-center">

                        {{-- 2. UBAH HEADER PARAMETER AGAR MENAMPILKAN NOMOR --}}
                        <h6 class="mb-0 fw-bold text-dark">Parameter {{ $paramIndex }}: {{ $parameter->statement }}</h6>

                        <div>
                          <span class="badge bg-success">
                            Score: {{ isset($parameterScores[$parameter->id]) ? number_format($parameterScores[$parameter->id]->score, 2) : '-' }}
                          </span>
                          <span class="badge bg-warning text-dark ms-2">
                            {{ isset($parameterScores[$parameter->id]) ? $parameterScores[$parameter->id]->score_parameter_desc : '-' }}
                          </span>
                          @if(isset($parameterScores[$parameter->id]) && $parameterScores[$parameter->id]->parameter_wawancara)
                            <span class="badge bg-danger ms-2">
                              {{ $parameterScores[$parameter->id]->parameter_wawancara }}
                            </span>
                          @endif
                        </div>
                      </div>
                    </div>
                    <div class="card-body p-0">
                      {{-- Tabel Kriteria --}}
                      <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                          <thead class="table-light">
                            <tr>
                              {{-- 3. TAMBAHKAN KOLOM NOMOR --}}
                              <th class="text-center align-middle" width="5%">No</th>

                              <th class="align-middle">Kriteria</th>
                              <th class="text-center align-middle" width="80">Score</th>
                              <th class="align-middle">Gap Analysis</th>
                              <th class="align-middle" width="250">Dokumen Pendukung</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($parameter->criteria as $criteria)
                              {{-- Menerapkan selang-seling warna menggunakan $loop->even dari Laravel Blade --}}
                              <tr class="{{ $loop->even ? 'table-light' : 'bg-white' }}">

                                {{-- 4. CETAK NOMOR ITERASI UNTUK KRITERIA --}}
                                <td class="text-center align-middle fw-bold text-muted">{{ $loop->iteration }}</td>

                                <td class="align-middle">
                                  @if(isset($criteriaScores[$criteria->id]))
                                    @php
                                      $sel = (int) $criteriaScores[$criteria->id]->score;
                                    @endphp
                                    @if($sel === 0)
                                      <span class="text-muted fst-italic">Skip Penilaian</span>
                                    @else
                                      @php $det = $criteria->details->where('level', $sel)->first(); @endphp
                                      {!! $det?->criteria ? nl2br(e($det->criteria)) : '-' !!}
                                    @endif
                                  @else
                                    <span class="text-muted fst-italic">Belum dinilai</span>
                                  @endif
                                </td>
                                {{-- <td class="text-center align-middle">
                                  @if(isset($criteriaScores[$criteria->id]))
                                    <span class="badge bg-primary fs-6">{{ $criteriaScores[$criteria->id]->score }}</span>
                                  @else
                                    -
                                  @endif
                                </td> --}}
                                <td class="text-center align-middle">
                                  @if(isset($criteriaScores[$criteria->id]))
                                    @php
                                      $scoreVal = (int) $criteriaScores[$criteria->id]->score;
                                      $badgeClass = match($scoreVal) {
                                        1 => 'bg-primary',
                                        2 => 'bg-info',
                                        3 => 'bg-success',
                                        4 => 'bg-warning text-dark',
                                        5 => 'bg-danger',
                                        0 => 'bg-secondary text-dark border border-dark',
                                        default => 'bg-secondary'
                                      };
                                    @endphp
                                    <small class="badge {{ $badgeClass }} px-2 py-1">
                                      {{ $scoreVal }}
                                    </small>
                                  @else
                                    -
                                  @endif
                                </td>
                                <td class="align-middle">
                                  @if(isset($criteriaScores[$criteria->id]))
                                    <div class="text-break">{{ $criteriaScores[$criteria->id]->gap_analysis }}</div>
                                  @else
                                    -
                                  @endif
                                </td>
                                <td class="align-middle">
                                  @if(isset($criteriaScores[$criteria->id]) && $criteriaScores[$criteria->id]->documents->count())
                                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                      @foreach($criteriaScores[$criteria->id]->documents as $doc)
                                        {{-- Kotak dokumen tetap putih agar menonjol (pop-up) di atas warna abu-abu --}}
                                        <li class="d-flex align-items-center justify-content-between p-2 bg-white border border-secondary-subtle rounded shadow-sm">
                                          <div class="d-flex align-items-center text-truncate pe-2">
                                            <i class="bx bxs-file text-primary fs-5 me-2"></i>
                                            <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="text-truncate text-decoration-none small text-dark fw-medium" title="{{ $doc->filename }}">
                                              {{ $doc->filename }}
                                            </a>
                                          </div>

                                          <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-delete-dim-doc" data-form-id="form-delete-doc-{{ $doc->id }}" title="Hapus Dokumen">
                                            <i class="bx bx-trash fs-6"></i>
                                          </button>

                                          <form id="form-delete-doc-{{ $doc->id }}" action="{{ route('penilaian-rmi.delete-document', $doc->id) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                          </form>
                                        </li>
                                      @endforeach
                                    </ul>
                                  @else
                                    <span class="text-muted small fst-italic"><i class="bx bx-info-circle"></i> Tidak ada dokumen</span>
                                  @endif
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  @php $paramIndex++; @endphp

                @endforeach

              </div>
            @endforeach
          </div>

        </div>
      @endforeach
    </div>
  </div>
</div>
