@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
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
                <!-- Row 1 -->
                <div class="setting-wrapper row row-cols-xl-5 flex-nowrap g-2 mb-2">
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[0]->id }}]">
                        <option disabled selected>Level Risiko</option>
                        <option value="Low" {{ $riskMap[0]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[0]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[0]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[0]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[0]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[0]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[0]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="medium">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[1]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[1]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[1]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[1]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[1]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[1]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[1]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[1]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[2]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[2]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[2]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[2]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[2]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[2]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[2]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[2]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[3]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[3]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[3]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[3]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[3]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[3]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[3]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[3]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[4]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[4]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[4]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[4]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[4]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[4]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[4]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[4]->nilai_risiko }}" />
                    </div>
                  </div>
                </div>
                <!-- Row 2 -->
                <div class="setting-wrapper row row-cols-xl-5 flex-nowrap g-2 mb-2">
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[5]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[5]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[5]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[5]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[5]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[5]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[5]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[5]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[6]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[6]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[6]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[6]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[6]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[6]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[6]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[6]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="medium">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[7]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[7]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[7]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[7]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[7]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[7]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[7]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[7]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[8]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[8]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[8]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[8]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[8]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[8]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[8]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[8]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[9]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[9]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[9]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[9]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[9]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[9]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[9]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[9]->nilai_risiko }}" />
                    </div>
                  </div>
                </div>
                <!-- Row 3 -->
                <div class="setting-wrapper row row-cols-xl-5 flex-nowrap g-2 mb-2">
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[10]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[10]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[10]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[10]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[10]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[10]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[10]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[10]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[11]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[11]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[11]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[11]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[11]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[11]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[11]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[11]->nilai_risiko }}">
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="medium">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[12]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[12]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[12]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[12]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[12]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[12]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[12]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[12]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[13]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[13]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[13]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[13]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[13]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[13]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[13]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[13]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[14]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[14]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[14]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[14]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[14]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[14]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[14]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[14]->nilai_risiko }}" />
                    </div>
                  </div>
                </div>
                <!-- Row 4 -->
                <div class="setting-wrapper row row-cols-xl-5 flex-nowrap g-2 mb-2">
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[15]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[15]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[15]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[15]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[15]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[15]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[15]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[15]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[16]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[16]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[16]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[16]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[16]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[16]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[16]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[16]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[17]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[17]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[17]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[17]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[17]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[17]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[17]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[17]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="medium">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[18]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[18]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[18]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[18]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[18]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[18]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[18]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[18]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[19]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[19]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[19]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[19]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[19]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[19]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[19]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[19]->nilai_risiko }}" />
                    </div>
                  </div>
                </div>
                <!-- Row 5 -->
                <div class="setting-wrapper row row-cols-xl-5 flex-nowrap g-2">
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[20]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[20]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[20]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[20]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[20]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[20]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[20]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[20]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[21]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[21]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[21]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[21]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[21]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[21]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[21]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[21]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="med-low">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[22]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[22]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[22]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[22]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[22]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[22]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[22]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[22]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="medium">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[23]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[23]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[23]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[23]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[23]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[23]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[23]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[23]->nilai_risiko }}" />
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="d-flex flex-column gap-2 p-2 p-md-3 rounded-3" data-level="high">
                      <select class="form-select js-select-hide-search" name="level_risiko[{{ $riskMap[24]->id }}]">
                        <option selected disabled>Level Risiko</option>
                        <option value="Low" {{ $riskMap[24]->level_risiko == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Low to Moderate"
                          {{ $riskMap[24]->level_risiko == 'Low to Moderate' ? 'selected' : '' }}>Low to Moderate
                        </option>
                        <option value="Moderate" {{ $riskMap[24]->level_risiko == 'Moderate' ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="Moderate to High"
                          {{ $riskMap[24]->level_risiko == 'Moderate to High' ? 'selected' : '' }}>Moderate to High
                        </option>
                        <option value="High" {{ $riskMap[24]->level_risiko == 'High' ? 'selected' : '' }}>High
                        </option>
                      </select>
                      <input class="form-control" name="nilai_risiko[{{ $riskMap[24]->id }}]" id="nilai_risiko"
                        type="number" placeholder="Nilai Risiko" value="{{ $riskMap[24]->nilai_risiko }}" />
                    </div>
                  </div>
                </div>
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