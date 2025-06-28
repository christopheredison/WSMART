@extends('layouts.default')
@section('dashboard')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <h5 class="mb-0">Pelaksanaan Pengujian ICT Plan</h5>
      </div>
      <div class="card-body">
        <form id="ictDoForm" action="{{ route('ict.store-testing', $ictPlan->id) }}" method="POST">
          @csrf
          <!-- Informasi ICT Plan -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Sasaran BUMN</label>
                <input type="text" class="form-control" value="{{ $ictPlan->sasaran_bumn }}" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Peristiwa Risiko</label>
                <input type="text" class="form-control" value="{{ $peristiwaRisiko }}" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Lokasi Risiko</label>
                <input type="text" class="form-control" value="{{ $lokasiRisiko }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Business Process</label>
                <input type="text" class="form-control" value="{{ $ictPlan->business_process }}" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Metode Pengujian</label>
                <input type="text" class="form-control" value="{{ $ictPlan->metode_pengujian }}" readonly>
              </div>
            </div>
          </div>

          <!-- Key Controls -->
          @foreach($ictPlan->planControls as $index => $planControl)
          <div class="key-control-section mb-4" data-index="{{ $index }}">
            <div class="card">
              <div class="card-header bg-light">
                <h5 class="mb-0">Key Control #{{ $index + 1 }}</h5>
              </div>
              <div class="card-body">
                <input type="hidden" name="plan_control_id[]" value="{{ $planControl->id }}">
                
                <!-- Key Control Info -->
                <div class="mb-4">
                  <label class="form-label fw-bold">Key Control</label>
                  <input type="text" class="form-control" value="{{ $planControl->key_control }}" readonly>
                </div>
                
                <!-- Jenis, Bentuk, Level Kontrol -->
                <div class="row mb-4">
                  <div class="col-md-4">
                    <label class="form-label">Jenis Kontrol</label>
                    <select name="jenis_kontrol[]" class="form-select" required>
                      <option value="">Pilih Jenis Kontrol</option>
                      <option value="1">Kontrol Operasi</option>
                      <option value="2">Kontrol Kepatuhan</option>
                      <option value="3">Kontrol Pelaporan</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Bentuk Kontrol</label>
                    <select name="bentuk_kontrol[]" class="form-select" required>
                      <option value="">Pilih Bentuk Kontrol</option>
                      <option value="1">SOP</option>
                      <option value="2">Kebijakan</option>
                      <option value="3">Sistem Informasi dan Komunikasi</option>
                      <option value="4">Sistem Lainnya</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Level Pengendalian</label>
                    <select name="level_pengendalian[]" class="form-select" required>
                      <option value="">Pilih Level Pengendalian</option>
                      <option value="1">Entitas</option>
                      <option value="2">Operasional</option>
                    </select>
                  </div>
                </div>
                
                <!-- Kecukupan Desain Pengendalian -->
                <div class="mb-4">
                  <h6 class="mb-3 border-bottom pb-2">Kecukupan Desain Pengendalian</h6>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Efektivitas pelaksanaan pengendalian dalam mencapai tujuan pengendalian intern</label>
                      <select name="kecukupan_desain_pengendalian_1[]" class="form-select" required>
                        <option value="">Pilih Kecukupan</option>
                        <option value="1">Cukup</option>
                        <option value="2">Tidak Cukup</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Kecukupan desain pengendalian dalam memitigasi risiko</label>
                      <select name="kecukupan_desain_pengendalian_2[]" class="form-select" required>
                        <option value="">Pilih Kecukupan</option>
                        <option value="1">Cukup</option>
                        <option value="2">Tidak Cukup</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Kecukupan dokumentasi yang memadai untuk pengendalian</label>
                      <select name="kecukupan_desain_pengendalian_3[]" class="form-select" required>
                        <option value="">Pilih Kecukupan</option>
                        <option value="1">Cukup</option>
                        <option value="2">Tidak Cukup</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Keberadaan risiko perusahaan yang belum termitigasi</label>
                      <select name="kecukupan_desain_pengendalian_4[]" class="form-select" required>
                        <option value="">Pilih Kecukupan</option>
                        <option value="1">Cukup</option>
                        <option value="2">Tidak Cukup</option>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label class="form-label">Kesimpulan Kecukupan Desain Pengendalian</label>
                      <select name="kecukupan_desain_pengendalian_akhir[]" class="form-select" required>
                        <option value="">Pilih Kesimpulan</option>
                        <option value="1">Cukup</option>
                        <option value="2">Tidak Cukup</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <!-- Efektivitas Desain Pengendalian -->
                <div class="mb-4">
                  <h6 class="mb-3 border-bottom pb-2">Efektivitas Desain Pengendalian</h6>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Efektivitas pelaksanaan pengendalian dalam mencapai tujuan</label>
                      <select name="efektivitas_desain_pengendalian_1[]" class="form-select" required>
                        <option value="">Pilih Efektivitas</option>
                        <option value="1">Efektif</option>
                        <option value="2">Efektif Sebagian</option>
                        <option value="3">Tidak Efektif</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Konsistensi pelaksanaan pengendalian di lapangan</label>
                      <select name="efektivitas_desain_pengendalian_2[]" class="form-select" required>
                        <option value="">Pilih Efektivitas</option>
                        <option value="1">Efektif</option>
                        <option value="2">Efektif Sebagian</option>
                        <option value="3">Tidak Efektif</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Kesesuaian pelaksanaan tugas manajemen dan karyawan</label>
                      <select name="efektivitas_desain_pengendalian_3[]" class="form-select" required>
                        <option value="">Pilih Efektivitas</option>
                        <option value="1">Efektif</option>
                        <option value="2">Efektif Sebagian</option>
                        <option value="3">Tidak Efektif</option>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label class="form-label">Kesimpulan Efektivitas Desain Pengendalian</label>
                      <select name="efektivitas_desain_pengendalian_akhir[]" class="form-select kesimpulan-efektivitas" data-index="{{ $index }}" required>
                        <option value="">Pilih Kesimpulan</option>
                        <option value="1">Efektif</option>
                        <option value="2">Efektif Sebagian</option>
                        <option value="3">Tidak Efektif</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <!-- Kesimpulan dan Tindak Lanjut -->
                <div class="mb-4">
                  <h6 class="mb-3 border-bottom pb-2">Kesimpulan dan Tindak Lanjut</h6>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Kesimpulan Akhir</label>
                      <input type="text" class="form-control kesimpulan-akhir-{{ $index }}" name="kesimpulan_akhir[]" readonly>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Hasil Temuan</label>
                      <textarea class="form-control" name="hasil_temuan[]" rows="3" required></textarea>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Rencana Tindak Lanjut</label>
                      <textarea class="form-control" name="rencana_tindak_lanjut[]" rows="3" required></textarea>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label class="form-label">Batas Waktu Penyelesaian Tindak Lanjut</label>
                      <input type="date" class="form-control" name="batas_waktu_penyelesaian[]" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Penanggung Jawab Tindak Lanjut</label>
                      <input type="text" class="form-control" name="penanggung_jawab[]" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('ict.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    
    // Fungsi untuk menghasilkan kesimpulan akhir berdasarkan kecukupan dan efektivitas
    function generateKesimpulanAkhir(index) {
      // Dapatkan section berdasarkan index
      const section = $('.key-control-section').eq(index);
      
      // Dapatkan nilai dari dropdown di dalam section yang spesifik
      // Gunakan selector yang sesuai dengan struktur HTML
      const kecukupanSelect = section.find('select[name="kecukupan_desain_pengendalian_akhir[]"]');
      const efektivitasSelect = section.find('select[name="efektivitas_desain_pengendalian_akhir[]"]');
      const kesimpulanInput = section.find('.kesimpulan-akhir-' + index);
      
      // Ambil nilai terlebih dahulu
      const kecukupanVal = kecukupanSelect.val();
      const efektivitasVal = efektivitasSelect.val();
      
      // Tambahkan debugging
      console.log('Generating kesimpulan for index:', index);
      console.log('Kecukupan value:', kecukupanVal);
      console.log('Efektivitas value:', efektivitasVal);
      
      let kecukupanText = '';
      let efektivitasText = '';
      
      if (kecukupanVal === '1') {
        kecukupanText = 'cukup';
      } else if (kecukupanVal === '2') {
        kecukupanText = 'tidak cukup';
      }
      
      if (efektivitasVal === '1') {
        efektivitasText = 'efektif';
      } else if (efektivitasVal === '2') {
        efektivitasText = 'efektif sebagian';
      } else if (efektivitasVal === '3') {
        efektivitasText = 'tidak efektif';
      }
      
      // Tambahkan pengecekan nilai sebelum menggabungkan
      if (kecukupanText && efektivitasText) {
        kesimpulanInput.val(kecukupanText + ' dan ' + efektivitasText);
      } else {
        kesimpulanInput.val('');
      }
    }
    
    // Gunakan event delegation untuk menangani perubahan pada dropdown
    $(document).on('change', 'select[name="kecukupan_desain_pengendalian_akhir[]"]', function() {
      const index = $(this).closest('.key-control-section').data('index');
      console.log('Kecukupan changed, index:', index);
      generateKesimpulanAkhir(index);
    });

    
    
    // Gunakan event delegation untuk menangani perubahan pada dropdown efektivitas
    $(document).on('change', 'select[name="efektivitas_desain_pengendalian_akhir[]"]', function() {
      const index = $(this).closest('.key-control-section').data('index');
      console.log('Efektivitas changed, index:', index);
      generateKesimpulanAkhir(index);
    });
    
    // Inisialisasi kesimpulan akhir untuk semua key control saat halaman dimuat
    $('.key-control-section').each(function(index) {
      const kecukupanVal = $(this).find('select[name="kecukupan_desain_pengendalian_akhir[]"]').val();
      const efektivitasVal = $(this).find('select[name="efektivitas_desain_pengendalian_akhir[]"]').val();
      
      if (kecukupanVal && efektivitasVal) {
        generateKesimpulanAkhir(index);
      }
    });
    
    // Konfirmasi sebelum submit form
    $('#ictDoForm').on('submit', function(e) {
      e.preventDefault();
      
      Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menyimpan data pengujian ICT Plan ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit();
        }
      });
    });
  });
</script>
@endpush