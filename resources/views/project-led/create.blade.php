@extends('layouts.default')
@section('dashboard')
    @include('partials.success-message')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="lead__icon bg-warning-subtle">
                            <div class="svg-icon svg-icon-warning">
                                @include('partials.icon-tool')
                            </div>
                        </div>
                        <h2 class="h3">Tambah Loss Event Project</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('project-led.store') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Konstruksi Spesifik <span class="text-danger">*</span></label>
                                    <select class="form-select @error('project_sektor_id') is-invalid @enderror" name="project_sektor_id" required>
                                        <option value="">Pilih Konstruksi Spesifik</option>
                                        @foreach($projectSektors as $sektor)
                                            <option value="{{ $sektor->id }}" {{ old('project_sektor_id') == $sektor->id ? 'selected' : '' }}>
                                                {{ $sektor->sektor_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_sektor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Peristiwa Risiko <span class="text-danger">*</span></label>
                                    <select class="form-select @error('peristiwa_risiko_id') is-invalid @enderror" name="peristiwa_risiko_id" required>
                                        <option value="">Pilih Peristiwa Risiko</option>
                                        @foreach($peristiwaRisikos as $risiko)
                                            <option value="{{ $risiko->id }}" {{ old('peristiwa_risiko_id') == $risiko->id ? 'selected' : '' }}>
                                                {{ $risiko->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('peristiwa_risiko_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Project <span class="text-danger">*</span></label>
                                    <select class="form-select @error('project_id') is-invalid @enderror" name="project_id" required>
                                        <option value="">Pilih Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->project_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Kejadian <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('tanggal_kejadian') is-invalid @enderror" 
                                        id="tanggal_kejadian" name="tanggal_kejadian" 
                                        value="{{ old('tanggal_kejadian') }}" required>
                                    @error('tanggal_kejadian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Deskripsi Kejadian <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('deskripsi_kejadian') is-invalid @enderror" 
                                        name="deskripsi_kejadian" rows="4" required>{{ old('deskripsi_kejadian') }}</textarea>
                                    @error('deskripsi_kejadian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nilai Kerugian Finansial</label>
                                    <input type="text" class="form-control inputmask-rupiah @error('nilai_kerugian_finansial') is-invalid @enderror" 
                                        name="nilai_kerugian_finansial" value="{{ old('nilai_kerugian_finansial') }}">
                                    @error('nilai_kerugian_finansial')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Wajib diisi jika Nilai Kerugian Non Finansial kosong</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nilai Kerugian Non Finansial</label>
                                    <input type="text" class="form-control @error('nilai_kerugian_non_finansial') is-invalid @enderror" 
                                        name="nilai_kerugian_non_finansial" value="{{ old('nilai_kerugian_non_finansial') }}">
                                    @error('nilai_kerugian_non_finansial')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Wajib diisi jika Nilai Kerugian Finansial kosong</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">PIC <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('unit_penanggung_jawab') is-invalid @enderror" 
                                        name="unit_penanggung_jawab" value="{{ old('unit_penanggung_jawab') }}" required>
                                    @error('unit_penanggung_jawab')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                    <a href="{{ route('project-led.index') }}" class="btn btn-secondary">Kembali</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize flatpickr for date input
    flatpickr("#tanggal_kejadian", {
        dateFormat: "Y-m-d",
        maxDate: "today",
        allowInput: true,
        monthSelectorType: "static",
        yearSelectorType: "static",
        showMonths: 1
    });

    // Initialize select2 for better dropdown experience
    $('select').select2({
        width: '100%',
        placeholder: 'Pilih...'
    });
});

document.addEventListener('DOMContentLoaded', function() {
    $('.inputmask-rupiah').inputmask({
        alias: 'numeric',
        groupSeparator: '.',
        autoGroup: true,
        digits: 0,
        digitsOptional: false,
        prefix: 'Rp ',
        placeholder: '0',
        rightAlign: false,
        autoUnmask: true,
        removeMaskOnSubmit: true,
    });
});
</script>
@endpush