{{-- resources/views/penilaian-rmi/partials/dimensi.blade.php --}}
@php
  // Variabel yang tersedia:
  // $dimensions, $parameterScores, $criteriaScores, $dimensionScores
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
            {{--
            <span class="badge bg-primary ms-2">
              @php
                $dimScore = 0;
                $dimCount = 0;
                foreach($dimension->subDimensions as $subDim) {
                  if(isset($dimensionScores[$subDim->id])) {
                    $dimScore += $dimensionScores[$subDim->id]->score_dimension;
                    $dimCount++;
                  }
                }
                echo $dimCount > 0 ? number_format($dimScore / $dimCount, 2) : '-';
              @endphp
            </span>
            --}}
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
                  {{--
                  <span class="badge bg-info ms-2">
                    {{ isset($dimensionScores[$subDimension->id]) ? number_format($dimensionScores[$subDimension->id]->score_dimension, 2) : '-' }}
                  </span>
                  --}}
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
                  <div class="card mb-3">
                    <div class="card-header bg-light">
                      <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ $parameter->statement }}</h6>
                        <div>
                          <span class="badge bg-success">
                            Score: {{ isset($parameterScores[$parameter->id]) ? number_format($parameterScores[$parameter->id]->score, 2) : '-' }}
                          </span>
                          <span class="badge bg-warning ms-2">
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
                    <div class="card-body">
                      {{-- Tabel Kriteria --}}
                      <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                          <thead class="table-light">
                            <tr>
                              <th>Kriteria</th>
                              <th width="80">Score</th>
                              <th>Gap Analysis</th>
                              <th width="150">Dokumen</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($parameter->criteria as $criteria)
                              <tr>
                                <td>
                                  @if(isset($criteriaScores[$criteria->id]))
                                    @php
                                      $sel = $criteriaScores[$criteria->id]->score;
                                      $det = $criteria->details->where('level', $sel)->first();
                                    @endphp
                                    {{ $det?->criteria ?? $criteria->criteria_statement }}
                                  @else
                                    <span class="text-muted">Belum dinilai</span>
                                  @endif
                                </td>
                                <td class="text-center">
                                  @if(isset($criteriaScores[$criteria->id]))
                                    <span class="badge bg-primary">{{ $criteriaScores[$criteria->id]->score }}</span>
                                  @else
                                    -
                                  @endif
                                </td>
                                <td>
                                  @if(isset($criteriaScores[$criteria->id]))
                                    <div class="gap-analysis-text">{{ $criteriaScores[$criteria->id]->gap_analysis }}</div>
                                  @else
                                    -
                                  @endif
                                </td>
                                <td>
                                  @if(isset($criteriaScores[$criteria->id]) && $criteriaScores[$criteria->id]->documents->count())
                                    <div class="d-flex flex-column gap-1">
                                      @foreach($criteriaScores[$criteria->id]->documents as $doc)
                                        <div class="d-flex align-items-center">
                                          <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="btn btn-sm btn-info me-1" title="{{ $doc->filename }}">
                                            <i class="bx bx-file"></i>
                                          </a>
                                          <form action="{{ route('penilaian-rmi.delete-document', $doc->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus dokumen?')">
                                              <i class="bx bx-trash"></i>
                                            </button>
                                          </form>
                                          <small class="text-truncate ms-1" style="max-width: 80px;" title="{{ $doc->filename }}">{{ $doc->filename }}</small>
                                        </div>
                                      @endforeach
                                    </div>
                                  @else
                                    <span class="text-muted">Tidak ada dokumen</span>
                                  @endif
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                @endforeach

              </div>
            @endforeach
          </div>

        </div>
      @endforeach
    </div>
  </div>
</div>
