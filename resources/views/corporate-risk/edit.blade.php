@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Edit Identifikasi Risiko Korporat</h3>
        </div>
    </div>

    <form class="row g-3" method="POST" action="{{ route('corporate-risk.update', $identifikasiRisiko->id) }}" id="main-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="periode_id" value="{{ $selectedPeriode->id }}">

        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">1</span></span>
                        <span class="h3 mb-0">Data Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="divider mb-3 mb-md-5 mt-0">
                                <div class="divider-text"><h5 class="mb-0 ff-heading-sm">Periode Tahun {{ $selectedPeriode->tahun }}</h5></div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 gx-md-5">
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="target_capaian_kinerja" name="target_capaian_kinerja" rows="3" placeholder="Sasaran" required>{{ old('target_capaian_kinerja', $identifikasiRisiko->target_capaian_kinerja) }}</textarea>
                                <label for="target_capaian_kinerja">Sasaran</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group d-lg-flex">
                                <div class="form-floating flex-grow-1">
                                    <select class="form-select select2" id="jenis_risiko_id" name="jenis_risiko_id" required>
                                        <option value="" disabled>Pilih Jenis Risiko</option>
                                        @foreach($jenisRisiko as $id => $title)
                                            @php
                                                $kategori = \App\Models\JenisRisiko::find($id)->kategoriRisiko;
                                                $kategoriTitle = $kategori ? $kategori->title : '';
                                            @endphp
                                            <option value="{{ $id }}" {{ old('jenis_risiko_id', $identifikasiRisiko->jenis_risiko_id) == $id ? 'selected' : '' }}>
                                                {{ $kategoriTitle }} - {{ $title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="jenis_risiko_id">Jenis Risiko T2 & T3 KBUMN</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="peristiwa_risiko" name="peristiwa_risiko" rows="3" placeholder="Peristiwa Risiko" required>{{ old('peristiwa_risiko', $identifikasiRisiko->peristiwa_risiko) }}</textarea>
                                <label for="peristiwa_risiko">Peristiwa Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko" rows="3" placeholder="Deskripsi Peristiwa Risiko" required>{{ old('deskripsi_peristiwa_risiko', $identifikasiRisiko->deskripsi_peristiwa_risiko) }}</textarea>
                                <label for="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">2</span></span>
                        <span class="h3 mb-0">Penyebab Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="penyebab-risiko-body">
                        @forelse($identifikasiRisiko->penyebabRisiko as $penyebab)
                        <div class="row g-2">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="penyebab_risiko[]" value="{{ $penyebab->penyebab_risiko }}" placeholder="Masukkan Penyebab Risiko">
                                    <label>Penyebab Risiko</label>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)" {{ $identifikasiRisiko->penyebabRisiko->count() <= 1 ? 'disabled' : '' }}>
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                            <div class="col-12 mt-0"><hr></div>
                        </div>
                        @empty
                        <div class="row g-2">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko">
                                    <label>Penyebab Risiko</label>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)" disabled><i class="bx bx-trash"></i></button>
                            </div>
                            <div class="col-12 mt-0"><hr></div>
                        </div>
                        @endforelse
                    </div>
                    <div class="row"><div class="col-auto ms-auto"><button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-column" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah Penyebab Risiko"><i class='bx bx-plus fs-5'></i></button></div></div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">3</span></span>
                        <span class="h3 mb-0">Key Risk Indicator</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="kri-body">
                        @forelse($identifikasiRisiko->kris as $kri)
                        <div class="row g-2">
                            <div class="col-12 col-lg-11">
                                <div class="row g-2">
                                    <div class="col-12"><div class="form-group form-floating"><input type="text" class="form-control" name="key_risk_indicator[]" value="{{ $kri->kri }}"><label>Key Risk Indicator</label></div></div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating"><input type="text" class="form-control" name="satuan_kri[]" value="{{ $kri->satuan_kri }}"><label>Satuan KRI</label></div></div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-success" name="batas_aman[]" value="{{ $kri->batas_aman }}"><label>Batas Aman</label></div></div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-warning" name="batas_waspada[]" value="{{ $kri->batas_waspada }}"><label>Batas Waspada</label></div></div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-danger" name="batas_bahaya[]" value="{{ $kri->batas_bahaya }}"><label>Batas Bahaya</label></div></div>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center ms-auto"><button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)" {{ $identifikasiRisiko->kris->count() <= 1 ? 'disabled' : '' }}><i class="bx bx-trash"></i></button></div>
                            <div class="col-12 mt-0"><hr></div>
                        </div>
                        @empty
                        <div class="row g-2">
                            <div class="col-12 col-lg-11">
                                <div class="row g-2">
                                    <div class="col-12"><div class="form-group form-floating"><input type="text" class="form-control" name="key_risk_indicator[]"><label>Key Risk Indicator</label></div></div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating"><input type="text" class="form-control" name="satuan_kri[]"><label>Satuan KRI</label></div></div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-success" name="batas_aman[]"><label>Batas Aman</label></div></div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-warning" name="batas_waspada[]"><label>Batas Waspada</label></div></div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-danger" name="batas_bahaya[]"><label>Batas Bahaya</label></div></div>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center ms-auto"><button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)" disabled><i class="bx bx-trash"></i></button></div>
                            <div class="col-12 mt-0"><hr></div>
                        </div>
                        @endforelse
                    </div>
                    <div class="row"><div class="col-auto ms-auto d-flex"><button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-column-kri" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah KRI"><i class='bx bx-plus fs-5'></i></button></div></div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                 <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">4</span></span>
                        <span class="h3 mb-0">Kontrol</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3 gx-xxl-6 mb-3">
                        <div class="col-md-6 col-lg-5 col-xxl-6">
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Jenis Kontrol Eksisting</label>
                                <select name="jenis_kontrol_eksisting_id" class="form-select select2">
                                    <option value="" selected disabled>Jenis Kontrol Eksisting</option>
                                    @foreach ($jenisKontrolEksistings as $jenisKontrolEksisting)
                                        <option value="{{ $jenisKontrolEksisting->id }}" {{ $identifikasiRisiko->jenis_kontrol_eksisting_id == $jenisKontrolEksisting->id ? 'selected' : '' }}>
                                            {{ $jenisKontrolEksisting->jenis_kontrol }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Kontrol Eksisting</label>
                                <div class="w-100">
                                    <div id="kontrol-eksisting-body">
                                        @forelse($identifikasiRisiko->kontrolEksistings as $index => $kontrol)
                                            <div class="row g-2 mb-2">
                                                <div class="col">
                                                    <textarea class="form-control" name="kontrol_eksisting[]" rows="3" placeholder="Masukkan kontrol eksisting">{{ $kontrol->kontrol_eksisting }}</textarea>
                                                </div>
                                                <div class="col-auto d-flex align-items-center">
                                                    <button type="button" class="btn btn-icon-danger h-100" onclick="removeKontrolRow(event)" {{ count($identifikasiRisiko->kontrolEksistings) <= 1 ? 'disabled' : '' }}>
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="row g-2 mb-2">
                                                <div class="col">
                                                    <textarea class="form-control" name="kontrol_eksisting[]" rows="3" placeholder="Masukkan kontrol eksisting">{{ $identifikasiRisiko->kontrol_eksisting }}</textarea>
                                                </div>
                                                <div class="col-auto d-flex align-items-center">
                                                    <button type="button" class="btn btn-icon-danger h-100" onclick="removeKontrolRow(event)" disabled>
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-auto ms-auto">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-kontrol-eksisting"
                                                data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah Kontrol Eksisting">
                                                <i class='bx bx-plus fs-5'></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-7 col-xxl-6">
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Penilaian Efektivitas
                                    Kontrol</label>
                                <select class="form-select select2" name="penilaian_efektifitas_kontrol">
                                    <option selected disabled>Penilaian Efektivitas Kontrol</option>
                                    @foreach ($penilaianEfektifitasKontrols as $efektivitasKontrol)
                                        <option value="{{ $efektivitasKontrol->id }}" {{ $identifikasiRisiko->penilaian_efektifitas_kontrol == $efektivitasKontrol->id ? 'selected' : '' }}>
                                            {{ $efektivitasKontrol->efektivitas_kontrol }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker2">Perkiraan Waktu Mulai Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_mulai_terpapar_risiko"
                                    id="timepicker2" type="text" placeholder="d/m/y"
                                    value="{{ $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai ? \Carbon\Carbon::parse($identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') : '' }}" />
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker3">Perkiraan Waktu Selesai Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_selesai_terpapar_risiko"
                                    id="timepicker3" type="text" placeholder="d/m/y"
                                    value="{{ $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir ? \Carbon\Carbon::parse($identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') : '' }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                     <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">5</span></span>
                        <span class="h3 mb-0">Pilih Risiko Divisi Terkait</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>Pilih risiko divisi yang terkait dengan risiko korporat ini:</p>
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPilihRisikoDivisi"><span class="bx bx-plus"></span> Pilih Risiko Divisi</button>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tabelRisikoDivisiTerpilih">
                            <thead><tr><th>Divisi</th><th>Peristiwa Risiko</th><th>Level Risiko</th><th>Aksi</th></tr></thead>
                            <tbody>{{-- Diisi oleh JavaScript --}}</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalPilihRisikoDivisi" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Pilih Risiko Divisi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="divisi_filter" class="form-label">Filter Berdasarkan Divisi:</label>
                            <select id="divisi_filter" class="form-select select2" style="width: 100%;">
                                <option value="">Pilih Divisi</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tabelRisikoDivisi">
                                <thead><tr><th>Pilih</th><th>Divisi</th><th>Peristiwa Risiko</th><th>Level Risiko</th></tr></thead>
                                <tbody><tr><td colspan="4" class="text-center">Pilih divisi terlebih dahulu.</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button><button type="button" class="btn btn-primary" id="btnPilihRisiko">Pilih</button></div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-5">
            <div class="row g-2">
                <div class="col-auto order-1">
                    <a href="{{ route('corporate-risk.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="save" class="btn btn-warning bg-warning ms-auto btn-action">Simpan dan Keluar</button>
                </div>
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="savenext" class="btn btn-primary ms-auto btn-action">Simpan dan Lanjutkan Analisa</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
{{-- Salin semua script dari create.blade.php --}}
<script>
$(document).ready(function() {
    let selectedRisks = [];

    // **PERUBAHAN UTAMA**: Inisialisasi 'selectedRisks' dengan data yang sudah ada
    @foreach($identifikasiRisiko->divisiRisks as $risk)
        selectedRisks.push({
            id: '{{ $risk->id }}',
            divisi: '{{ optional($risk->unit)->name ?? 'N/A' }}',
            peristiwa: `{!! addslashes($risk->peristiwa_risiko) !!}`,
            level: '{{ optional($risk->riskAnalysis)->level_risiko ?? 'N/A' }}'
        });
    @endforeach
    
    // Panggil fungsi ini saat halaman dimuat untuk menampilkan data awal
    updateSelectedRisksTable();

    $('#modalPilihRisikoDivisi').on('shown.bs.modal', function () {
        $('#divisi_filter').select2({
            dropdownParent: $('#modalPilihRisikoDivisi')
        });
    });

    $('#divisi_filter').on('change', function() {
        const unitId = $(this).val();
        const tbody = $('#tabelRisikoDivisi tbody');
        
        if (!unitId) {
            tbody.html('<tr><td colspan="4" class="text-center">Pilih divisi terlebih dahulu.</td></tr>');
            return;
        }

        tbody.html('<tr><td colspan="4" class="text-center">Memuat data...</td></tr>');
        
        $.ajax({
            url: `{{ route('corporate-risk.get-division-risks', ['unit' => ':unitId']) }}`.replace(':unitId', unitId),
            type: 'GET',
            success: function(response) {
                tbody.html(response.html);
                selectedRisks.forEach(function(risk) {
                    $(`#modalPilihRisikoDivisi #risk-${risk.id}`).prop('checked', true);
                });
            },
            error: function() {
                tbody.html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    });
    
    $('#btnPilihRisiko').on('click', function() {
        const currentlyChecked = new Set();
        $('.pilih-risiko:checked').each(function() {
            const riskId = $(this).val();
            currentlyChecked.add(riskId);
            if (!selectedRisks.some(risk => risk.id === riskId)) {
                selectedRisks.push({
                    id: riskId,
                    divisi: $(this).data('divisi'),
                    peristiwa: $(this).data('peristiwa'),
                    level: $(this).data('level')
                });
            }
        });

        // Hapus dari selectedRisks jika checkbox tidak lagi dicentang di modal yang sedang aktif
        selectedRisks = selectedRisks.filter(risk => {
            const checkboxExistsInModal = $(`#modalPilihRisikoDivisi #risk-${risk.id}`).length > 0;
            if (checkboxExistsInModal) {
                return currentlyChecked.has(risk.id);
            }
            return true; // Keep risk if its checkbox is not in the current modal view
        });

        updateSelectedRisksTable();
        $('#modalPilihRisikoDivisi').modal('hide');
    });
    
    function updateSelectedRisksTable() {
        const tbody = $('#tabelRisikoDivisiTerpilih tbody');
        tbody.empty();
        
        if (selectedRisks.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">Belum ada risiko divisi yang dipilih.</td></tr>');
        } else {
            selectedRisks.forEach(function(risk, index) {
                const row = `
                    <tr>
                        <td>${risk.divisi}</td>
                        <td>${risk.peristiwa}</td>
                        <td>${risk.level}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger hapus-risiko" data-index="${index}">
                                <i class="bx bx-trash"></i>
                            </button>
                            <input type="hidden" name="divisi_risk_ids[]" value="${risk.id}">
                        </td>
                    </tr>`;
                tbody.append(row);
            });
        }
    }

    // Event delegation untuk tombol hapus
    $('#tabelRisikoDivisiTerpilih').on('click', '.hapus-risiko', function() {
        const index = $(this).data('index');
        selectedRisks.splice(index, 1);
        updateSelectedRisksTable();
    });
});
</script>
@endpush