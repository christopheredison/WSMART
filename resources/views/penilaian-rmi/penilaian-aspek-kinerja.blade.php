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
                  <button type="submit" name="action" value="save_capaian" class="btn btn-primary">
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
                  <button type="submit" name="action" value="back_to_capaian" class="btn btn-outline-secondary">
                    <i class="bx bx-chevron-left me-1"></i> Kembali ke Capaian
                  </button>
                  <button type="submit" name="action" value="finish" class="btn btn-success">
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

  document.querySelector('#tab-kpmr').classList.toggle('disabled', sessionStorage.getItem('active_tab') !== 'kpmr');
</script>
@endpush

@endsection
