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
                        <h2 class="h3">Tambah Loss Event Unit</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('unit-led.store') }}" method="post" id="form-unit-led">
                        @csrf
                        <div class="row">
                            <!-- Periode -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Periode <span class="text-danger">*</span></label>
                                @if ($periode)
                                    <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                                @endif
                                <select class="form-select @error('periode_id') is-invalid @enderror" name="periode_id" required {{ $periode ? 'disabled' : '' }}>
                                    <option value="">Pilih Periode</option>
                                    @foreach($periodes as $periodeItem)
                                        <option value="{{ $periodeItem->id }}" {{ old('periode_id', $periode?->id) == $periodeItem->id ? 'selected' : '' }}>
                                            {{ $periodeItem->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('periode_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                            </div>

                            <!-- Status dalam Risk Register -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status dalam Risk Register <span class="text-danger">*</span></label>
                                <select class="form-select @error('status_risk_register') is-invalid @enderror" name="status_risk_register" id="status_risk_register" required>
                                    <option value="">Pilih Status</option>
                                    <option value="1" {{ old('status_risk_register') == '1' ? 'selected' : '' }}>Ya</option>
                                    <option value="0" {{ old('status_risk_register') == '0' ? 'selected' : '' }}>Tidak</option>
                                </select>
                                @error('status_risk_register')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- No Urut Risiko -->
                            <div class="col-md-6 mb-3" id="no_urut_container" style="display: none;">
                                <label class="form-label">Risk Register <span class="text-danger">*</span></label>
                                <select class="form-select @error('no_urut_risiko') is-invalid @enderror" name="no_urut_risiko" id="risk_register_id" required>
                                </select>
                                @error('no_urut_risiko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Kejadian -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Nama Kejadian <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('nama_kejadian') is-invalid @enderror" 
                                    name="nama_kejadian" rows="3" required>{{ old('nama_kejadian') }}</textarea>
                                @error('nama_kejadian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal Kejadian -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Kejadian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('tanggal_kejadian') is-invalid @enderror" 
                                    id="tanggal_kejadian" name="tanggal_kejadian" 
                                    value="{{ old('tanggal_kejadian') }}" required>
                                @error('tanggal_kejadian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Identifikasi Kejadian (Free Text) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Identifikasi Kejadian <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('identifikasi_kejadian') is-invalid @enderror" 
                                    name="identifikasi_kejadian" rows="3" required>{{ old('identifikasi_kejadian') }}</textarea>
                                @error('identifikasi_kejadian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kategori Kejadian -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select @error('kategori_kejadian_id') is-invalid @enderror" name="kategori_kejadian_id" required>
                                    <option value="">Pilih Kategori Kejadian</option>
                                    @foreach($kategoriKejadians as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_kejadian_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->kategori_kejadian }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_kejadian_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sumber Penyebab Kejadian -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sumber Penyebab Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select @error('sumber_penyebab_kejadian') is-invalid @enderror" name="sumber_penyebab_kejadian" required>
                                    <option value="">Pilih Sumber Penyebab</option>
                                    <option value="1" {{ old('sumber_penyebab_kejadian') == '1' ? 'selected' : '' }}>Internal</option>
                                    <option value="2" {{ old('sumber_penyebab_kejadian') == '2' ? 'selected' : '' }}>Eksternal</option>
                                </select>
                                @error('sumber_penyebab_kejadian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penyebab Masalah -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Penyebab Masalah <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('penyebab_masalah') is-invalid @enderror" 
                                    name="penyebab_masalah" rows="3" required>{{ old('penyebab_masalah') }}</textarea>
                                @error('penyebab_masalah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penanganan saat Kejadian -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Penanganan saat Kejadian <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('penanganan_kejadian') is-invalid @enderror" 
                                    name="penanganan_kejadian" rows="3" required>{{ old('penanganan_kejadian') }}</textarea>
                                @error('penanganan_kejadian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Deskripsi Kejadian - Risk Event -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Deskripsi Kejadian - Risk Event <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('deskripsi_kejadian') is-invalid @enderror" 
                                    name="deskripsi_kejadian" rows="4" required>{{ old('deskripsi_kejadian') }}</textarea>
                                @error('deskripsi_kejadian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kategori Risiko BUMN -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Risiko BUMN <span class="text-danger">*</span></label>
                                <select class="form-select @error('kategori_risiko_bumn') is-invalid @enderror" name="kategori_risiko_bumn" required>
                                    <option value="">Pilih Kategori Risiko BUMN</option>
                                    <option value="1" {{ old('kategori_risiko_bumn') == '1' ? 'selected' : '' }}>Financial</option>
                                    <option value="2" {{ old('kategori_risiko_bumn') == '2' ? 'selected' : '' }}>Operational</option>
                                    <option value="3" {{ old('kategori_risiko_bumn') == '3' ? 'selected' : '' }}>Public & Legal</option>
                                </select>
                                @error('kategori_risiko_bumn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kategori Risiko T2 & T3 BUMN -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Risiko T2 & T3 BUMN <span class="text-danger">*</span></label>
                                <input type="hidden" name="kategori_risiko_id" value="{{ old('kategori_risiko_id') }}" id="kategori_risiko_id">
                                <select class="form-select @error('jenis_risiko_id') is-invalid @enderror" name="jenis_risiko_id" id="jenis_risiko_id" required onchange="handleJenisRisikoChange(this)">
                                    <option value="">Pilih Jenis Risiko</option>
                                    @foreach($jenisRisikos as $jenis)
                                        <option value="{{ $jenis->id }}" 
                                            data-kategori="{{ $jenis->kategori_risiko_id }}"
                                            {{ old('jenis_risiko_id') == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->kategoriRisiko->title ?? '' }} – {{ $jenis->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jenis_risiko_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penjelasan Kerugian -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Penjelasan Kerugian <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('penjelasan_kerugian') is-invalid @enderror" 
                                    name="penjelasan_kerugian" rows="3" required>{{ old('penjelasan_kerugian') }}</textarea>
                                @error('penjelasan_kerugian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nilai Kerugian -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nilai Kerugian (IDR)</label>
                                <input type="text" class="form-control inputmask-rupiah @error('nilai_kerugian_finansial') is-invalid @enderror" 
                                    name="nilai_kerugian_finansial" value="{{ old('nilai_kerugian_finansial') }}">
                                @error('nilai_kerugian_finansial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Bisa tidak diisi atau diisi 0 bila tidak ada kerugian finansial</small>
                            </div>

                            <!-- Kejadian Berulang -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kejadian Berulang <span class="text-danger">*</span></label>
                                <select class="form-select @error('kejadian_berulang') is-invalid @enderror" name="kejadian_berulang" id="kejadian_berulang" required>
                                    <option value="">Pilih</option>
                                    <option value="1" {{ old('kejadian_berulang') == '1' ? 'selected' : '' }}>Ya</option>
                                    <option value="0" {{ old('kejadian_berulang') == '0' ? 'selected' : '' }}>Tidak</option>
                                </select>
                                @error('kejadian_berulang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Frekuensi Kejadian -->
                            <div class="col-md-6 mb-3" id="frekuensi_container" style="display: none;">
                                <label class="form-label">Frekuensi Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select @error('frekuensi_kejadian') is-invalid @enderror" name="frekuensi_kejadian">
                                    <option value="">Pilih Frekuensi</option>
                                    <option value="1" {{ old('frekuensi_kejadian') == '1' ? 'selected' : '' }}>1 kali</option>
                                    <option value="2" {{ old('frekuensi_kejadian') == '2' ? 'selected' : '' }}>2 kali</option>
                                    <option value="3" {{ old('frekuensi_kejadian') == '3' ? 'selected' : '' }}>3 kali</option>
                                    <option value="4" {{ old('frekuensi_kejadian') == '4' ? 'selected' : '' }}>4 kali</option>
                                    <option value="5" {{ old('frekuensi_kejadian') == '5' ? 'selected' : '' }}>5 kali</option>
                                    <option value="6" {{ old('frekuensi_kejadian') == '6' ? 'selected' : '' }}>6 kali atau lebih</option>
                                </select>
                                @error('frekuensi_kejadian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Mitigasi yang Direncanakan -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Mitigasi yang Direncanakan <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('rencana_mitigasi') is-invalid @enderror" 
                                    name="rencana_mitigasi" rows="3" required>{{ old('rencana_mitigasi') }}</textarea>
                                @error('rencana_mitigasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Realisasi Mitigasi -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Realisasi Mitigasi <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('realisasi_mitigasi') is-invalid @enderror" 
                                    name="realisasi_mitigasi" rows="3" required>{{ old('realisasi_mitigasi') }}</textarea>
                                @error('realisasi_mitigasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Perbaikan Mendatang -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Perbaikan Mendatang <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('perbaikan_mendatang') is-invalid @enderror" 
                                    name="perbaikan_mendatang" rows="3" required>{{ old('perbaikan_mendatang') }}</textarea>
                                @error('perbaikan_mendatang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Pihak Terkait -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pihak Terkait <span class="text-danger">*</span></label>
                                {{--
                                <input type="text" class="form-control @error('unit_penanggung_jawab') is-invalid @enderror" 
                                    name="unit_penanggung_jawab" value="{{ old('unit_penanggung_jawab') }}" required>
                                --}}
                                <select class="form-select @error('unit_penanggung_jawab') is-invalid @enderror" 
                                    name="unit_penanggung_jawab" id="unit_penanggung_jawab" required>
                                    <option value="">Pilih Pihak Terkait</option>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}" {{ old('unit_penanggung_jawab') == $jabatan->id ? 'selected' : '' }}>
                                            {{ $jabatan->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_penanggung_jawab')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status Asuransi -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Asuransi <span class="text-danger">*</span></label>
                                <select class="form-select @error('status_asuransi') is-invalid @enderror" name="status_asuransi" id="status_asuransi" required>
                                    <option value="">Pilih Status</option>
                                    <option value="1" {{ old('status_asuransi') == '1' ? 'selected' : '' }}>Ya</option>
                                    <option value="0" {{ old('status_asuransi') == '0' ? 'selected' : '' }}>Tidak</option>
                                </select>
                                @error('status_asuransi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nilai Premi -->
                            <div class="col-md-6 mb-3" id="premi_container" style="display: none;">
                                <label class="form-label">Nilai Premi (IDR) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inputmask-rupiah @error('nilai_premi') is-invalid @enderror" 
                                    name="nilai_premi" value="{{ old('nilai_premi') }}">
                                @error('nilai_premi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nilai Klaim -->
                            <div class="col-md-6 mb-3" id="klaim_container" style="display: none;">
                                <label class="form-label">Nilai Klaim (IDR) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control inputmask-rupiah @error('nilai_klaim') is-invalid @enderror" 
                                    name="nilai_klaim" value="{{ old('nilai_klaim') }}">
                                @error('nilai_klaim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Biaya Risiko Inheren -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Biaya Risiko Inheren (IDR)</label>
                                <input type="text" class="form-control inputmask-rupiah @error('biaya_risiko_inheren') is-invalid @enderror" 
                                    name="biaya_risiko_inheren" value="{{ old('biaya_risiko_inheren') }}">
                                @error('biaya_risiko_inheren')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Biaya Upaya Perbaikan -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Biaya Upaya Perbaikan (IDR)</label>
                                <input type="text" class="form-control inputmask-rupiah @error('biaya_upaya_perbaikan') is-invalid @enderror" 
                                    name="biaya_upaya_perbaikan" value="{{ old('biaya_upaya_perbaikan') }}">
                                @error('biaya_upaya_perbaikan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Hasil dari Perbaikan -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hasil dari Perbaikan (IDR)</label>
                                <input type="text" class="form-control inputmask-rupiah @error('hasil_perbaikan') is-invalid @enderror" 
                                    name="hasil_perbaikan" value="{{ old('hasil_perbaikan') }}">
                                @error('hasil_perbaikan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="{{ route('unit-led.index-by-periode', ['periode' => $periode->id]) }}" class="btn btn-secondary">Kembali</a>
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
const dataPeriodes = @json($periodes);

function handleJenisRisikoChange(select) {
    var kategoriId = $(select).find('option:selected').data('kategori');
    $('#kategori_risiko_id').val(kategoriId);
}

$(document).ready(function() {
    // Form submit dengan konfirmasi SweetAlert
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        // Validasi form terlebih dahulu
        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }
        
        // Simpan referensi ke form
        var form = this;
        
        // Tampilkan konfirmasi SweetAlert
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menyimpan data Loss Event Unit ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Hapus format rupiah dari input sebelum submit
                $('.inputmask-rupiah').each(function() {
                    $(this).inputmask('remove');
                });
                
                // Jika user mengkonfirmasi, submit form
                form.submit();
            }
        });
    });
    
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

    // Handle kejadian berulang change
    $('#kejadian_berulang').change(function() {
        if ($(this).val() == '1') {
            $('#frekuensi_container').show();
            $('select[name="frekuensi_kejadian"]').prop('required', true);
        } else {
            $('#frekuensi_container').hide();
            $('select[name="frekuensi_kejadian"]').prop('required', false);
        }
    });

    // Handle status asuransi change
    $('#status_asuransi').change(function() {
        if ($(this).val() == '1') {
            $('#premi_container, #klaim_container').show();
            $('input[name="nilai_premi"], input[name="nilai_klaim"]').prop('required', true);
        } else {
            $('#premi_container, #klaim_container').hide();
            $('input[name="nilai_premi"], input[name="nilai_klaim"]').prop('required', false);
        }
    });

    // Handle status risk register change
    $('#status_risk_register').change(function() {
        if ($(this).val() == '1') {
            $('#no_urut_container').show();
            $('input[name="no_urut_risiko"]').prop('required', true);
        } else {
            $('#no_urut_container').hide();
            $('input[name="no_urut_risiko"]').prop('required', false);
        }
    });

    
    $(':input[name="periode_id"]').change(function() {
        var periodeId = $(this).val();
        var periode = dataPeriodes.find(periode => periode.id == periodeId);
        if (periode) {
            $('#risk_register_id').html('<option value="">Pilih Risk Register</option>' + periode.identifikasi_risikos.map(risk => `<option value="${risk.id}">${risk.deskripsi_peristiwa_risiko}</option>`).join(''));
        } else {
            $('#risk_register_id').html('<option value="">Tidak ada Risk Register</option>');
        }
    }).trigger('change');

    $('#risk_register_id').change(function() {
        let riskRegisterId = $(this).val();

        const namaKejadianDom = $('#form-unit-led :input[name="nama_kejadian"]');
        const tanggalKejadianDom = $('#form-unit-led :input[name="tanggal_kejadian"]');
        const peristiwaRisikoDom = $('#form-unit-led :input[name="peristiwa_risiko_id"]');
        const penyebabMasalahDom = $('#form-unit-led :input[name="penyebab_masalah"]');
        const penangananKejadianDom = $('#form-unit-led :input[name="penanganan_kejadian"]');
        const deskripsiKejadianDom = $('#form-unit-led :input[name="deskripsi_kejadian"]');

        if (riskRegisterId) {
            let periodeId = $(':input[name="periode_id"]').val();
            let periode = dataPeriodes.find(periode => periode.id == periodeId);
            if (periode) {
                let riskRegister = periode.identifikasi_risikos.find(risk => risk.id == riskRegisterId);
                if (riskRegister) {
                    console.log(riskRegister);

                    namaKejadianDom.val(riskRegister.deskripsi_peristiwa_risiko).prop('readonly', true).change();
                    tanggalKejadianDom.val(riskRegister.perkiraan_waktu_terpapar_risiko_mulai).prop('readonly', true).change();
                    peristiwaRisikoDom.val(riskRegister.peristiwa_risiko_id).prop('readonly', true).change();
                    penyebabMasalahDom.val(riskRegister.penyebab_risikos?.map(penyebab => penyebab.penyebab_risiko).join('; ')).prop('readonly', true).change();
                    penangananKejadianDom.val(riskRegister.penyebab_risikos?.map(penyebab => penyebab.perlakuan_penyebab_risiko?.map(perlakuan => perlakuan.rencana_perlakuan_risiko).filter(Boolean).join('; ')).filter(Boolean).join('; ')).prop('readonly', true).change();
                    deskripsiKejadianDom.val(riskRegister.deskripsi_peristiwa_risiko).prop('readonly', true).change();
                } else {
                    namaKejadianDom.val('').prop('readonly', false).change();
                    tanggalKejadianDom.val('').prop('readonly', false).change();
                    peristiwaRisikoDom.val('').prop('readonly', false).change();
                    penyebabMasalahDom.val('').prop('readonly', false).change();
                    penangananKejadianDom.val('').prop('readonly', false).change();
                    deskripsiKejadianDom.val('').prop('readonly', false).change();
                }
            }
        } else {
            namaKejadianDom.val('').prop('readonly', false).change();
            tanggalKejadianDom.val('').prop('readonly', false).change();
            peristiwaRisikoDom.val('').prop('readonly', false).change();
            penyebabMasalahDom.val('').prop('readonly', false).change();
            penangananKejadianDom.val('').prop('readonly', false).change();
            deskripsiKejadianDom.val('').prop('readonly', false).change();
        }
    }).trigger('change');


    // Trigger initial state
    $('#kejadian_berulang').trigger('change');
    $('#status_asuransi').trigger('change');
    $('#status_risk_register').trigger('change');
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