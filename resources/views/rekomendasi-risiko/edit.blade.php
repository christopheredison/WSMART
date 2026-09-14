@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">@include('partials.icon-tool')</div>
            <h3 class="mb-0">Edit Rekomendasi Risiko</h3>
        </div>
    </div>

    <form class="row g-3" method="POST" action="{{ route('rekomendasi-risiko.update', $rekomendasi->id) }}" id="main-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="periode_id" value="{{ $periode->id }}">
        <input type="hidden" name="unit_id" value="{{ $unit->id }}">
        
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">1</span></span>
                        <span class="h3 mb-0">Data Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="divider mb-3 mb-md-5 mt-0">
                        <div class="divider-text"><h5 class="mb-0 ff-heading-sm">Periode Tahun {{ $periode->tahun }} untuk Divisi {{ $unit->name }}</h5></div>
                    </div>
                    <div class="row g-3 gx-md-5">
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" name="target_capaian_kinerja" rows="3" placeholder="Sasaran" required>{{ old('target_capaian_kinerja', $rekomendasi->target_capaian_kinerja) }}</textarea>
                                <label>Sasaran</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <select class="form-select select2" name="jenis_risiko_id" required>
                                    <option value="" disabled>Pilih Jenis Risiko</option>
                                    @foreach($jenisRisiko as $id => $title)
                                        @php
                                            $kategori = \App\Models\JenisRisiko::find($id)->kategoriRisiko;
                                            $kategoriTitle = $kategori ? $kategori->title : '';
                                        @endphp
                                        <option value="{{ $id }}" {{ old('jenis_risiko_id', $rekomendasi->jenis_risiko_id) == $id ? 'selected' : '' }}>
                                            {{ $kategoriTitle }} - {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Jenis Risiko T2 & T3 KBUMN</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" name="peristiwa_risiko" rows="3" placeholder="Peristiwa Risiko" required>{{ old('peristiwa_risiko', $rekomendasi->peristiwa_risiko) }}</textarea>
                                <label>Peristiwa Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" name="deskripsi_peristiwa_risiko" rows="3" placeholder="Deskripsi Peristiwa Risiko" required>{{ old('deskripsi_peristiwa_risiko', $rekomendasi->deskripsi_peristiwa_risiko) }}</textarea>
                                <label>Deskripsi Peristiwa Risiko</label>
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
                        @forelse($rekomendasi->penyebabRisikos as $penyebab)
                            <div class="row g-2 @if(!$loop->first) mt-2 @endif">
                                <div class="col">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko" value="{{ $penyebab->penyebab_risiko }}" required>
                                        <label>Penyebab Risiko</label>
                                    </div>
                                </div>
                                <div class="col-auto d-flex align-items-center">
                                    <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(this)" {{ $loop->count <= 1 ? 'disabled' : '' }}><i class="bx bx-trash"></i></button>
                                </div>
                            </div>
                        @empty
                            <div class="row g-2">
                                <div class="col"><div class="form-floating"><input type="text" class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko" required><label>Penyebab Risiko</label></div></div>
                                <div class="col-auto d-flex align-items-center"><button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(this)" disabled><i class="bx bx-trash"></i></button></div>
                            </div>
                        @endforelse
                    </div>
                    <div class="row mt-2">
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-penyebab" data-bs-toggle="tooltip" title="Tambah Penyebab Risiko"><i class='bx bx-plus fs-5'></i></button>
                        </div>
                    </div>
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
                        @forelse ($rekomendasi->kris as $kri)
                            <div class="row g-2 @if(!$loop->first) mt-2 @endif">
                                <div class="col-12 col-lg-11">
                                    <div class="row g-2">
                                        <div class="col-12"><div class="form-group form-floating"><input type="text" class="form-control" name="key_risk_indicator[]" value="{{ $kri->kri }}" placeholder="Key Risk Indicator"><label>Key Risk Indicator</label></div></div>
                                        <div class="col-6 col-md-3"><div class="form-group form-floating"><input type="text" class="form-control" name="satuan_kri[]" value="{{ $kri->satuan_kri }}" placeholder="Satuan KRI"><label>Satuan KRI</label></div></div>
                                        <div class="col-6 col-md-3"><div class="form-group form-floating"><input type="text" class="form-control border-success" name="batas_aman[]" value="{{ $kri->batas_aman }}" placeholder="Batas Aman"><label>Batas Aman</label></div></div>
                                        <div class="col-6 col-md-3"><div class="form-group form-floating"><input type="text" class="form-control border-warning" name="batas_waspada[]" value="{{ $kri->batas_waspada }}" placeholder="Batas Siaga"><label>Batas Siaga</label></div></div>
                                        <div class="col-6 col-md-3"><div class="form-group form-floating"><input type="text" class="form-control border-danger" name="batas_bahaya[]" value="{{ $kri->batas_bahaya }}" placeholder="Batas Bahaya"><label>Batas Bahaya</label></div></div>
                                    </div>
                                </div>
                                <div class="col-auto d-flex align-items-center ms-auto">
                                    <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(this)" {{ $loop->count <= 1 ? 'disabled' : '' }}><i class="bx bx-trash"></i></button>
                                </div>
                                @if(!$loop->last) <div class="col-12 mt-2"><hr></div> @endif
                            </div>
                        @empty
                        @endforelse
                    </div>
                    <div class="row mt-2">
                        <div class="col-auto ms-auto d-flex">
                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-kri" data-bs-toggle="tooltip" title="Tambah KRI"><i class='bx bx-plus fs-5'></i></button>
                        </div>
                    </div>
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
                                    <option value="">Pilih Jenis Kontrol Eksisting</option>
                                    @foreach ($jenisKontrolEksistings as $jenis)
                                        <option value="{{ $jenis->id }}" {{ old('jenis_kontrol_eksisting_id', $rekomendasi->jenis_kontrol_eksisting_id) == $jenis->id ? 'selected' : '' }}>{{ $jenis->jenis_kontrol }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Kontrol Eksisting</label>
                                <div class="w-100">
                                    <div id="kontrol-eksisting-body">
                                        @forelse ($rekomendasi->kontrolEksistings as $kontrol)
                                            <div class="row g-2 @if(!$loop->first) mt-2 @endif">
                                                <div class="col"><textarea class="form-control" name="kontrol_eksisting[]" rows="2" placeholder="Masukkan deskripsi kontrol eksisting..." required>{{ $kontrol->kontrol_eksisting }}</textarea></div>
                                                <div class="col-auto d-flex align-items-center"><button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(this)" {{ $loop->count <= 1 ? 'disabled' : '' }}><i class="bx bx-trash"></i></button></div>
                                            </div>
                                        @empty
                                            <div class="row g-2"><div class="col"><textarea class="form-control" name="kontrol_eksisting[]" rows="2" placeholder="Masukkan deskripsi kontrol eksisting..." required></textarea></div><div class="col-auto d-flex align-items-center"><button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(this)" disabled><i class="bx bx-trash"></i></button></div></div>
                                        @endforelse
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-auto ms-auto">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-kontrol-eksisting" data-bs-toggle="tooltip" title="Tambah Kontrol Eksisting">
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
                                    <option value="" disabled>Pilih Penilaian Efektivitas Kontrol</option>
                                    @foreach ($penilaianEfektifitasKontrols as $item)
                                        <option value="{{ $item->id }}" {{ old('penilaian_efektifitas_kontrol', $rekomendasi->penilaian_efektifitas_kontrol) == $item->id ? 'selected' : '' }}>{{ $item->efektivitas_kontrol }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker_mulai">Perkiraan Waktu Mulai Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_mulai_terpapar_risiko" id="timepicker_mulai" type="text" value="{{ old('perkiraan_waktu_mulai_terpapar_risiko', \Carbon\Carbon::parse($rekomendasi->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y')) }}" placeholder="d/m/y" required/>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker_selesai">Perkiraan Waktu Selesai Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_selesai_terpapar_risiko" id="timepicker_selesai" type="text" value="{{ old('perkiraan_waktu_selesai_terpapar_risiko', \Carbon\Carbon::parse($rekomendasi->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y')) }}" placeholder="d/m/y" required/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mt-5">
            <div class="d-flex gap-2 justify-content-start">
                <a href="{{ route('rekomendasi-risiko.show', ['unit' => $rekomendasi->unit_id, 'periode' => $rekomendasi->periode_id]) }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary btn-action">Update Draft</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function removeRow(element) {
        let parentContainer = $(element).closest('[id$="-body"]');
        $(element).closest('.row.g-2').remove();
        if (parentContainer.find('.row.g-2').length <= 1) {
            parentContainer.find('.btn-icon-danger').prop('disabled', true);
        }
    }

    function enableAllRemoveButtons(container) {
        if (container.find('.row.g-2').length > 1) {
            container.find('.btn-icon-danger').prop('disabled', false);
        }
    }

    $(document).ready(function() {
        $('#add-penyebab').click(function() {
            const container = $('#penyebab-risiko-body');
            const html = `<div class="row g-2 mt-2"><div class="col"><div class="form-floating"><input type="text" class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko" required><label>Penyebab Risiko</label></div></div><div class="col-auto d-flex align-items-center"><button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(this)"><i class="bx bx-trash"></i></button></div></div>`;
            container.append(html);
            enableAllRemoveButtons(container);
        });

        $('#add-kri').click(function() {
            const container = $('#kri-body');
            const html = `<div class="row g-2 mt-2"><div class="col-12 col-lg-11"><div class="row g-2"><div class="col-12"><div class="form-group form-floating"><input type="text" class="form-control" name="key_risk_indicator[]" placeholder="Key Risk Indicator"><label>Key Risk Indicator</label></div></div><div class="col-6 col-md-3"><div class="form-group form-floating"><input type="text" class="form-control" name="satuan_kri[]" placeholder="Satuan KRI"><label>Satuan KRI</label></div></div><div class="col-6 col-md-3"><div class="form-group form-floating"><input type="text" class="form-control border-success" name="batas_aman[]" placeholder="Batas Aman"><label>Batas Aman</label></div></div><div class="col-6 col-md-3"><div class="form-group form-floating"><input type="text" class="form-control border-warning" name="batas_waspada[]" placeholder="Batas Siaga"><label>Batas Siaga</label></div></div><div class="col-6 col-md-3"><div class="form-group form-floating"><input type="text" class="form-control border-danger" name="batas_bahaya[]" placeholder="Batas Bahaya"><label>Batas Bahaya</label></div></div></div></div><div class="col-auto d-flex align-items-center ms-auto"><button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(this)"><i class="bx bx-trash"></i></button></div><div class="col-12 mt-2"><hr></div></div>`;
            container.append(html);
            enableAllRemoveButtons(container);
        });
        
        if ($('#kri-body .row.g-2').length === 0) {
            $('#add-kri').click();
        }

        $('#add-kontrol-eksisting').click(function() {
            const container = $('#kontrol-eksisting-body');
            const html = `<div class="row g-2 mt-2"><div class="col"><textarea class="form-control" name="kontrol_eksisting[]" rows="2" placeholder="Masukkan deskripsi kontrol eksisting..." required></textarea></div><div class="col-auto d-flex align-items-center"><button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(this)"><i class="bx bx-trash"></i></button></div></div>`;
            container.append(html);
            enableAllRemoveButtons(container);
        });

        var periodeYear = {{ $periode->tahun }};
        var startOfYear = new Date(periodeYear, 0, 1);
        var endOfYear = new Date(periodeYear, 11, 31);
        
        flatpickr("#timepicker_mulai", { altInput: true, altFormat: "j F Y", dateFormat: "d/m/Y", minDate: startOfYear, maxDate: endOfYear });
        flatpickr("#timepicker_selesai", { altInput: true, altFormat: "j F Y", dateFormat: "d/m/Y", minDate: startOfYear, maxDate: endOfYear });

        $('#main-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const data = new FormData(this);
            const button = form.find('.btn-action');
            const originalText = button.html();

            button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...').prop('disabled', true);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, showConfirmButton: false, timer: 1500 })
                    .then(() => { window.location.href = response.redirect; });
                },
                error: function(xhr) {
                    button.html(originalText).prop('disabled', false);
                    const response = xhr.responseJSON;
                    let errorMsg = response.message || 'Terjadi kesalahan.';
                    if (response.errors) {
                        errorMsg = '<ul>';
                        $.each(response.errors, function(key, value) { errorMsg += '<li>' + value[0] + '</li>'; });
                        errorMsg += '</ul>';
                    }
                    Swal.fire({ icon: 'error', title: 'Gagal!', html: errorMsg });
                }
            });
        });
    });
</script>
@endpush