@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center g-3 g-xl-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Tambah Metrik Strategi Risiko</h2>
        </div>
      </div>
      <div class="card-body">
        <form action="{{ route('metrik-strategi-risiko.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label required">Periode</label>
              <select name="periode_id" class="form-select select2" required>
                <option value="">Pilih Periode</option>
                @foreach($periodes as $periode)
                  <option value="{{ $periode->id }}" {{ old('periode_id') == $periode->id ? 'selected' : '' }}>
                    {{ $periode->tahun }}
                  </option>
                @endforeach
              </select>
              @error('periode_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label required">T2 & T3 KBUMN</label>
              <select name="jenis_risiko" id="jenis_risiko" class="form-select select2" required>
                <option value="">Pilih T2 & T3</option>
                @foreach($jenisRisikos as $jenisRisiko)
                    @php
                        $kategoriTitle = $jenisRisiko->kategoriRisiko ? $jenisRisiko->kategoriRisiko->title : '';
                    @endphp
                    <option value="{{ $jenisRisiko->id }}" {{ old('jenis_risiko_id') == $jenisRisiko->id ? 'selected' : '' }}>
                        {{ $kategoriTitle }} - {{ $jenisRisiko->title }}
                    </option>
                @endforeach
              </select>
              @error('jenis_risikos')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <!-- <div class="col-md-6">
              <label class="form-label">Jenis Risiko</label>
              <input type="text" class="form-control" id="jenis_risiko" readonly>
            </div>

            <div class="col-md-6">
              <label class="form-label">Kategori Risiko</label>
              <input type="text" class="form-control" id="kategori_risiko" readonly>
            </div> -->

            <div class="col-12">
              <label class="form-label required">Risk Appetite Statement</label>
              <textarea name="risk_appetite_statement" class="form-control" rows="3" required>{{ old('risk_appetite_statement') }}</textarea>
              @error('risk_appetite_statement')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label required">Sikap Risiko</label>
              <select name="sikap_risiko_id" class="form-select select2" required>
                <option value="">Pilih Sikap Risiko</option>
                @foreach($sikapRisikos as $sikapRisiko)
                  <option value="{{ $sikapRisiko->id }}" {{ old('sikap_risiko_id') == $sikapRisiko->id ? 'selected' : '' }}>
                    {{ $sikapRisiko->jenis_sikap }}
                  </option>
                @endforeach
              </select>
              @error('sikap_risiko_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mt-4">
            <a href="{{ route('metrik-strategi-risiko.index') }}" class="btn btn-outline-secondary me-2">
              <i class="bx bx-arrow-back"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Event handler untuk form submit
    $('form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menyimpan data ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Event handler untuk peristiwa risiko
    // $('select[name="peristiwa_risiko_id"]').on('change', function() {
    //     const peristiwaId = $(this).val();
    //     if(peristiwaId) {
    //         // Ambil data jenis dan kategori risiko
    //         $.ajax({
    //             url: `/api/peristiwa-risiko/${peristiwaId}/relations`,
    //             method: 'GET',
    //             headers: {
    //                 'Accept': 'application/json'
    //             },
    //             success: function(response) {
    //                 console.log('Response:', response); // untuk debugging
    //                 if(response.data) {
    //                     $('#jenis_risiko').val(response.data.jenis_risiko.title || '-');
    //                     $('#kategori_risiko').val(response.data.kategori_risiko.title || '-');
    //                 }
    //             },
    //             error: function(xhr, status, error) {
    //                 console.error('Error:', error);
    //                 $('#jenis_risiko').val('-');
    //                 $('#kategori_risiko').val('-');
    //             }
    //         });
    //     } else {
    //         $('#jenis_risiko').val('-');
    //         $('#kategori_risiko').val('-');
    //     }
    // });
});
</script>
@endpush
@endsection