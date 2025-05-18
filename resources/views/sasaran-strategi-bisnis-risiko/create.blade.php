@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-info-subtle p-2 rounded-4">
            <div class="lead__icon">
              <div class="svg-icon svg-icon-2x svg-icon-info">
                @include('partials.icon-abs05')
              </div>
            </div>
          </div>
          <div>
            <div class="ff-preheading">Tambah Data</div>
            <h2>Tambah Sasaran & Strategi</h2>
          </div>
        </div>
        <a href="{{ route('sasaran-strategi.index') }}" class="btn btn-outline-secondary">
          <i class="bx bx-arrow-back"></i>
          <span class="ms-1">Kembali</span>
        </a>
      </div>

      <div class="card-body">
        <form action="{{ route('sasaran-strategi.store') }}" method="POST">
          @csrf
          
          <div class="mb-4">
            <label class="form-label">Periode</label>
            <select name="periode_id" class="form-select" required>
              <option value="">-- Pilih Periode --</option>
              @foreach($periodes as $periode)
                <option value="{{ $periode->id }}">{{ $periode->tahun }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label">Sasaran</label>
            <textarea name="sasaran" class="form-control" rows="3" placeholder="Masukkan sasaran..." required></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label">Risk Appetite Statement</label>
            <div class="input-group">
              <input type="text" class="form-control" name="risk_appetite_statement" readonly>
              <input type="hidden" name="metrik_strategi_risiko_id">
              <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalRiskAppetite">
                Pilih
              </button>
              
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Parameter Batasan</label>
            <div id="parameterList" class="border rounded-2 p-3 bg-light-subtle">
              <!-- Parameter akan di-render disini via JavaScript -->
            </div>
          </div>

          <div class="mb-4">
            
            <div class="form-label d-flex justify-content-between"> 
                <span>Strategi Bisnis</span>
                <button type="button" class="btn btn-outline-success btn-sm" id="btnTambahStrategi">
                <i class="bx bx-plus"></i> Tambah Strategi
                </button>
            </div>
            <div id="strategiContainer">
              <div class="strategi-item mb-3">
                <div class="input-group">
                  <input type="text" class="form-control" name="strategi[]" placeholder="Masukkan strategi..." required>
                  <button type="button" class="btn btn-outline-danger btn-hapus-strategi">
                    <i class="bx bx-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Expected Result (Rp)</label>
            <input type="text" name="expected_result" class="form-control inputmask-rupiah" required>
          </div>

          <div class="mb-4">
            <label class="form-label">Nilai Risiko (Rp)</label>
            <input type="text" name="risk_value" class="form-control inputmask-rupiah" required>
          </div>
          <div class="text-end">
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Risk Appetite -->
<div class="modal fade" id="modalRiskAppetite" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pilih Risk Appetite Statement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th style="width: 50px">#</th>
              <th>Statement</th>
              <th>Parameter</th>
              <th>Ukuran</th>
              <th>Batas</th>
              <th style="width: 100px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($metrikStrategiRisikos as $idx => $metrik)
              @php
                $rowspan = $metrik->parameterMetriks->count();
                $firstParam = true;
              @endphp
              
              @foreach($metrik->parameterMetriks as $param)
                <tr>
                  @if($firstParam)
                    <td rowspan="{{ $rowspan }}" class="align-middle text-center">{{ $idx + 1 }}</td>
                    <td rowspan="{{ $rowspan }}" class="align-middle">{{ $metrik->risk_appetite_statement }}</td>
                    @php $firstParam = false; @endphp
                  @endif
                  <td>{{ $param->parameter }}</td>
                  <td>{{ $param->satuan_ukuran }}</td>
                  <td>{{ $param->nilai_batasan }}</td>
                  @if($loop->first)
                    <td rowspan="{{ $rowspan }}" class="align-middle text-center">
                      <button type="button" 
                              class="btn btn-sm btn-primary btn-pilih-metrik"
                              data-id="{{ $metrik->id }}"
                              data-statement="{{ $metrik->risk_appetite_statement }}"
                              data-parameter="{{ json_encode($metrik->parameterMetriks) }}">
                        Pilih
                      </button>
                    </td>
                  @endif
                </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
    $('#btnTambahStrategi').on('click', function(e) {
      e.preventDefault();
      console.log('Tambah Strategi diklik');
      const newItem = $(`
        <div class="strategi-item mb-3">
          <div class="input-group">
            <input type="text" class="form-control" name="strategi[]" placeholder="Masukkan strategi..." required>
            <button type="button" class="btn btn-outline-danger btn-delete-strategi">
              <i class="bx bx-trash"></i>
            </button>
          </div>
        </div>
      `);
      $('#strategiContainer').append(newItem);
    });

    // Event delegation untuk tombol hapus strategi
    $(document).on('click', '.btn-delete-strategi', function() {
      $(this).closest('.strategi-item').remove();
    });

    document.addEventListener('DOMContentLoaded', function() {  
        // Pilih Risk Appetite Statement
        const btnPilihMetrik = document.querySelectorAll('.btn-pilih-metrik');
        const parameterList = document.getElementById('parameterList');
        
        btnPilihMetrik.forEach(btn => {
            btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const statement = this.dataset.statement;
            const parameters = JSON.parse(this.dataset.parameter);
            
            document.querySelector('input[name="metrik_strategi_risiko_id"]').value = id;
            document.querySelector('input[name="risk_appetite_statement"]').value = statement;
            
            // Render parameter
            parameterList.innerHTML = parameters.map(param => `
                <div class="mb-2">
                - ${param.parameter} (${param.satuan_ukuran}): ${param.nilai_batasan}
                </div>
            `).join('');
            
            bootstrap.Modal.getInstance(document.getElementById('modalRiskAppetite')).hide();
            });
        });
    });

    //set inputmask
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
        min: 0,
        allowMinus: false,
        onKeyDown: function(e) {
        if (e.key === 'Backspace' || e.keyCode === 8) {
            // tunda eksekusi sampai mask selesai di-apply
            setTimeout(() => {
                const unmasked = this.inputmask.unmaskedvalue();
                // kalau masih ada angka tersisa
                if (unmasked.length > 0) {
                // cek posisi cursor
                const pos = this.selectionStart;
                if (pos === 0) {
                    // pindahkan ke paling kanan
                    const end = this.value.length;
                    this.setSelectionRange(end, end);
                }
                }
            }, 0);
            }
        }
    });
</script>
@endpush