@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
@php
  $levelToDataAttr = [
    'Low' => 'low',
    'Low to Moderate' => 'med-low',
    'Moderate' => 'medium',
    'Moderate to High' => 'med-high',
    'High' => 'high',
  ];
  $levelOptions = array_keys($levelToDataAttr);
@endphp
<div class="row">
  <div class="col-12">
    <form action="{{ route('risk-map-setting.update') }}" method="POST">
      @csrf
      @method('PUT')
      <div class="card">
        <div class="card-header d-flex align-items-center gap-3">
          <div class="bg-info-subtle rounded-4 p-1">
            <div class="lead__icon">
              <span class="svg-icon svg-icon-2x svg-icon-info">
                @include('partials.icon-layout-rounded')
              </span>
            </div>
          </div>
          <h2>Risk Map Setting</h2>
        </div>
        <div class="card-body">
          <div class="risk-map table-responsive-xl pb-3">
            <div class="row g-2">
              <div class="col-1">
                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center h-100">
                  <span class="map-x-axis">Probabilitas</span>
                </div>
              </div>
              <div class="col-11">
                @for ($row = 0; $row < 5; $row++)
                  <div class="setting-wrapper row row-cols-xl-5 flex-nowrap g-2 {{ $row < 4 ? 'mb-2' : '' }}">
                    @for ($col = 0; $col < 5; $col++)
                      @php
                        $cell = $riskMap[$row * 5 + $col];
                        $dataLevel = $levelToDataAttr[$cell->level_risiko] ?? 'low';
                      @endphp
                      <div class="col-6 col-md-4 col-lg-3">
                        <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3 risk-map-cell"
                          data-level="{{ $dataLevel }}">
                          <select class="form-select js-select-hide-search risk-level-select"
                            name="level_risiko[{{ $cell->id }}]">
                            <option disabled {{ empty($cell->level_risiko) ? 'selected' : '' }}>Level Risiko</option>
                            @foreach ($levelOptions as $level)
                              <option value="{{ $level }}"
                                {{ $cell->level_risiko == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                          </select>
                          <input class="form-control" name="nilai_risiko[{{ $cell->id }}]" type="number"
                            placeholder="Nilai Risiko" value="{{ $cell->nilai_risiko }}" />
                        </div>
                      </div>
                    @endfor
                  </div>
                @endfor
              </div>
              <div class="col-11 ms-auto">
                <div class="divider">
                  <div class="divider-text">
                    <span class="map-y-axis">Dampak</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-submit">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    const levelToDataAttr = {
      'Low': 'low',
      'Low to Moderate': 'med-low',
      'Moderate': 'medium',
      'Moderate to High': 'med-high',
      'High': 'high',
    };

    function updateCellLevel($select) {
      const level = $select.val();
      const dataLevel = levelToDataAttr[level] || 'low';
      $select.closest('.risk-map-cell').attr('data-level', dataLevel);
    }

    $(document).ready(function () {
      $('.risk-level-select').each(function () {
        updateCellLevel($(this));
      });

      $(document).on('change', '.risk-level-select', function () {
        updateCellLevel($(this));
      });
    });
  })();
</script>
@endpush
