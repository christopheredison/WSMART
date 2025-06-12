@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Rencana Perlakuan Risiko</h3>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">1</span>
                </span>
                <span class="h3 mb-0">Deskripsi Peristiwa Risiko</span>
            </div>
        </div>
        <div class="card-body">
            <p>{{ $identifikasiRisiko->deskripsi_peristiwa_risiko }}</p>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">2</span>
                </span>
                <span class="h3 mb-0">Perlakuan Risiko</span>
            </div>
        </div>
        <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Penyebab Risiko</th>
                    <th>Rencana Perlakuan</th>
                    <th>Biaya</th>
                    <th>PIC</th>
                    <th>Timeline Perlakuan Risiko</th>
                    <th>Action</th>
                    <th></th> <!-- Kolom untuk "Rencana Perlakuan Risiko" -->
                </tr>
            </thead>
            <tbody>
                @php
                    $totalBiaya = 0;
                @endphp
                @foreach ($identifikasiRisiko->penyebabRisiko as $penyebab)
                    @php
                        // Tentukan kelas striping untuk penyebab risiko
                        $stripingClass = $loop->iteration % 2 === 0 ? 'xtable-light' : 'xtable-light';
                    @endphp
                    <tr class="{{ $stripingClass }}">
                        <td rowspan="{{ max($penyebab->perlakuanPenyebabRisiko?->count(), 1) }}">{{ $loop->iteration }}</td>
                        <td rowspan="{{ max($penyebab->perlakuanPenyebabRisiko?->count(), 1) }}">{{ $penyebab->penyebab_risiko }}</td>
                        @if ($penyebab->perlakuanPenyebabRisiko?->isNotEmpty())
                            @foreach ($penyebab->perlakuanPenyebabRisiko as $index => $perlakuan)
                                @php
                                    $totalBiaya += $perlakuan->biaya_perlakuan_risiko ?? 0;
                                @endphp
                                @if ($index > 0)
                                    <tr class="{{ $stripingClass }}">
                                @endif
                                <td>{{ $perlakuan->rencana_perlakuan_risiko }}</td>
                                <td>{{ $perlakuan->biaya_perlakuan_risiko ? 'Rp' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}</td>
                                <td>{{ $perlakuan->pic }}</td>
                                <td>{{ $perlakuan->waktu_perlakuan_risiko }}</td>
                                <td class="action-cell">
                                    {{-- <div>
                                        <button class="btn btn-link text-primary" type="button" data-action="edit" data-id="{{ $perlakuan->id }}">Edit</button>
                                        <button class="btn btn-link text-danger" type="button" data-action="delete" data-id="{{ $perlakuan->id }}">Hapus</button>   
                                    </div> --}}
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-link text-primary p-0" type="button" data-action="edit" data-id="{{ $perlakuan->id }}" title="Edit">
                                            <i class="bx bx-edit-alt fs-5"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-0" type="button" data-action="delete" data-id="{{ $perlakuan->id }}" title="Hapus">
                                            <i class="bx bx-trash fs-5"></i>
                                        </button>   
                                    </div>
                                </td>
                                @if ($index === 0)
                                    {{-- <td rowspan="{{ $penyebab->perlakuanPenyebabRisiko?->count() }}" class="text-center">
                                        <button class="btn btn-primary" type="button" data-action="add" data-id="{{ $penyebab->id }}" data-penyebab="{{ $penyebab->penyebab_risiko }}">Tambah Rencana Perlakuan</button>
                                    </td> --}}
                                    <td rowspan="{{ $penyebab->perlakuanPenyebabRisiko?->count() }}" class="text-center">
                                        <button class="btn btn-primary btn-sm" type="button" data-action="add" data-id="{{ $penyebab->id }}" data-penyebab="{{ $penyebab->penyebab_risiko }}" title="Tambah Rencana Perlakuan">
                                            <i class="bx bx-plus-circle"></i>
                                        </button>
                                    </td>
                                @endif
                                @if ($index > 0)
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <td colspan="4" class="text-center">Belum ada rencana perlakuan risiko</td>
                            {{-- <td class="text-center">
                                <button class="btn btn-primary" type="button" data-action="add" data-id="{{ $penyebab->id }}" data-penyebab="{{ $penyebab->penyebab_risiko }}">Input Rencana Perlakuan</button>
                            </td> --}}
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm" type="button" data-action="add" data-id="{{ $penyebab->id }}" data-penyebab="{{ $penyebab->penyebab_risiko }}" title="Input Rencana Perlakuan">
                                    <i class="bx bx-plus-circle"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold">Total Biaya Perlakuan:</td>
                    <td class="fw-bold">{{ 'Rp' . number_format($totalBiaya, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">3</span>
                </span>
                <span class="h3 mb-0">Pengukuran Risiko Inheren</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">Nilai Dampak</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->nilai_dampak ? 'Rp' . number_format($analisa->nilai_dampak, 0, ',', '.') : '-' }}</div>
                <div class="col-md-2">Nilai Probabilitas (%)</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->nilai_probabilitas ?: '-' }}</div>
                <div class="col-md-2">Skala Risiko</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->skala_risiko ?: '-' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2">Skala Dampak</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->skala_dampak ?: '-' }}</div>
                <div class="col-md-2">Skala Probabilitas</div>
                <div class="col-md-2 fw-bold">{{ ($analisa?->skalaProbabilitas? '(' . $analisa->skalaProbabilitas->tingkat . ') ' . $analisa->skalaProbabilitas->skala : null) ?: '-' }}</div>
                <div class="col-md-2">Level Risiko</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->level_risiko ?: '-' }}</div>
            </div>
        </div>
    </div>

    @for ($i = 1; $i <= 4; $i++)
    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">{{ $i + 3 }}</span>
                </span>
                <span class="h3 mb-0">Pengukuran Risiko Residual Q{{ $i }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">Nilai Dampak</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->{'nilai_dampak_residual_q' . $i} ? 'Rp' . number_format($analisa->{'nilai_dampak_residual_q' . $i }, 0, ',', '.') : '-' }}</div>
                <div class="col-md-2">Nilai Probabilitas (%)</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->{'nilai_probabilitas_residual_q' . $i} ?: '-' }}</div>
                <div class="col-md-2">Skala Risiko</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->{'skala_risiko_residual_q' . $i} ?: '-' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2">Skala Dampak</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->{'skala_dampak_residual_q' . $i} ?: '-' }}</div>
                <div class="col-md-2">Skala Probabilitas</div>
                <div class="col-md-2 fw-bold">{{ ($analisa?->{'skalaProbabilitasResidualQ' . $i}? '(' . $analisa->{'skalaProbabilitasResidualQ' . $i}->tingkat . ') ' . $analisa->{'skalaProbabilitasResidualQ' . $i}->skala : null) ?: '-' }}</div>
                <div class="col-md-2">Level Risiko</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->{'level_risiko_residual_q' . $i} ?: '-' }}</div>
            </div>
        </div>
    </div>
    @endfor

    <div class="col-12">
        <div class="row g-2">
            <div class="col-auto order-1">
                <a href="{{ route('risk-register-unit.index') }}" class="btn btn-outline-secondary">Selesai</a>
            </div>
            <div class="col-auto order-3 px-0 px-md-1 d-flex">
                <a href="{{ route('risk-register-unit.create') }}" class="btn btn-primary ms-auto btn-action">Lanjut Ke Pengisian Risiko Baru</a>
            </div>
        </div>
    </div>

    @include('risk-register-unit._modal_tambah_rencana_perlakuan_risiko')
    @include('risk-register-unit._modal_edit_rencana_perlakuan_risiko')
@endsection
@push('styles')
<style>
.xtable-light {
    background-color: #F8FAFB !important; /* Warna striping terang */
}

.xtable-white {
    background-color: #ffffff !important; /* Warna putih */
} 

.xtable-light tr, .xtable-white tr {
    background-color: inherit !important; /* Warna pewarisan sama */
    opacity: 1 !important; /* Pastikan tidak ada opacity */
}

.action-cell {
    vertical-align: top !important; /* Konten berada di atas secara vertikal */
}

.action-cell .d-flex {
    align-items: flex-start !important; /* Konten tetap berada di atas */
}

</style>
@endpush
@push('scripts')
<script>
const penyebabRisiko = @json($identifikasiRisiko->penyebabRisiko->keyBy('id'));
$(document).ready(function() {

    const kategoriDampak = '{{ $analisa->kategori_dampak ?? "" }}';
    // Ambil nilai dampak dari data yang ada
    const nilaiDampak = {{ $analisa->nilai_dampak ?? 0 }};
            
    // Fungsi untuk validasi biaya
    function validateBiaya(input) {
            // Skip validasi jika kategori dampak Kualitatif atau nilai dampak 0/null
            if (kategoriDampak === 'Kualitatif' || !nilaiDampak) {
                return true;
            }

            const biayaValue = parseFloat(input.val().replace(/[^0-9.-]+/g, '')) || 0;
            
            if (biayaValue > nilaiDampak) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: `Biaya perlakuan risiko (${biayaValue.toLocaleString('id-ID')}) tidak boleh melebihi nilai dampak (${nilaiDampak.toLocaleString('id-ID')})`,
                    icon: 'warning'
                });
                input.val('');
                return false;
            }
            return true;
        };

    // Event handler untuk form tambah dan edit
    $(document).on('change', 'input[name="biaya_perlakuan_risiko"], input[name="xbiaya_perlakuan_risiko"]', function() {
        validateBiaya($(this));
    });

    // Validasi saat submit form
    $('#formTambahRencana, #formEditRencana').on('submit', function(e) {
        const biayaInput = $(this).find('input[name="biaya_perlakuan_risiko"], input[name="xbiaya_perlakuan_risiko"]');
        if (!validateBiaya(biayaInput)) {
            e.preventDefault();
            return false;
        }
    });

    $(document).on('click', 'button[data-action="add"]', function() {
        const penyebabId = $(this).data('id'); // Ambil ID penyebab risiko dari tombol
        const penyebabNama = $(this).data('penyebab');

        // Reset form
        $('#formTambahRencana')[0].reset();
        
        // Reset select2 jika ada
        $('#formTambahRencana select').each(function() {
            $(this).val('').trigger('change');
        });

        // Reset flatpickr
        // if ($('#timelineRange')[0]._flatpickr) {
        //     $('#timelineRange')[0]._flatpickr.clear();
        // }

        // if ($('#timelineRange1')[0]._flatpickr) {
        //     $('#timelineRange1')[0]._flatpickr.clear();
        // }

        // if ($('#timelineRange2')[0]._flatpickr) {
        //     $('#timelineRange2')[0]._flatpickr.clear();
        // }

        // Reset input rupiah
        $('#formTambahRencana .inputmask-rupiah').val('');

        $('#penyebabRisikoId').val(penyebabId); // Set nilai penyebab risiko di input hidden
        $('input[name="penyebab_risiko"]').val(penyebabNama);

        $('#modalTambahRencana').modal('show'); // Tampilkan modal
    });

    $('button[data-action="save"]').on('click', function() {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Data yang sudah disimpan tidak dapat diubah kembali',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Data berhasil disimpan', '', 'success');
            }
        });
    });
});
</script>
<script>
    $(document).on('click', 'button[data-action="delete"]', function() {
        const rencanaId = $(this).data('id'); // Ambil ID rencana dari tombol

        // Tampilkan SweetAlert untuk konfirmasi
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Rencana perlakuan ini akan dihapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika konfirmasi "Ya", kirimkan permintaan AJAX untuk menghapus
                $.ajax({
                    url: `{{ route('risk-register-unit.hapus-rencana-perlakuan', ['riskRegister' => $identifikasiRisiko->id, 'id' => ':id']) }}`.replace(':id', rencanaId), // Endpoint hapus
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}' // Kirim token CSRF untuk otentikasi
                    },
                    success: function(response) {
                        // Tampilkan pesan berhasil
                        Swal.fire(
                            'Dihapus!',
                            response.message || 'Rencana perlakuan berhasil dihapus.',
                            'success'
                        ).then(() => {
                            // Refresh halaman atau hapus baris tabel
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        // Tampilkan pesan error
                        Swal.fire(
                            'Gagal!',
                            xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus data.',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>
<script>
    $(document).on('click', 'button[data-action="edit"]', function() {
        const rencanaId = $(this).data('id'); // Ambil ID rencana
        var flatpickrInstance = flatpickr("#xtimelineRange", {
            mode: "range",
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            disableMobile: true
        });

        var flatpickrInstance1 = flatpickr("#xtimelineRange1", {
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            disableMobile: true
        });

        var flatpickrInstance2 = flatpickr("#xtimelineRange2", {
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            disableMobile: true
        });
        // Ambil data rencana dari server
        $.ajax({
            url: `{{ route('risk-register-unit.edit-rencana-perlakuan', ['riskRegister' => $identifikasiRisiko->id, 'id' => ':id']) }}`.replace(':id', rencanaId),
            type: 'GET',
            success: function(response) {
                // Isi data ke dalam modal
                $('#formEditRencana #xperlakuanId').val(response.id);
                $('#formEditRencana #xpenyebabRisikoId').val(response.penyebab_risiko_id);
                $('#formEditRencana [name="xpenyebab_risiko"]').val(response.penyebab_risiko);
                $('#formEditRencana [name="xrencana_perlakuan_risiko"]').val(response.rencana_perlakuan_risiko);
                $('#formEditRencana [name="xoutput_perlakuan_risiko"]').val(response.output_perlakuan_risiko);
                $('#formEditRencana [name="xopsi_perlakuan_risiko"]').val(response.opsi_perlakuan_risiko);
                $('#formEditRencana [name="xjenis_rencana_perlakuan_risiko"]').val(response.jenis_rencana_perlakuan_risiko);
                $('#formEditRencana [name="xbiaya_perlakuan_risiko"]').val(response.biaya_perlakuan_risiko);
                $('#formEditRencana [name="xpic"]').val(response.pic);
                
                if (response.timeline_perlakuan_risiko_start && response.timeline_perlakuan_risiko_end) {
                    // flatpickrInstance.setDate([
                    //     response.timeline_perlakuan_risiko_start,
                    //     response.timeline_perlakuan_risiko_end
                    // ]);
                    flatpickrInstance1.setDate(response.timeline_perlakuan_risiko_start);
                    flatpickrInstance2.setDate(response.timeline_perlakuan_risiko_end);

                } else {
                    //flatpickrInstance.clear(); // Kosongkan jika tidak ada timeline
                    flatpickrInstance1.clear();
                    flatpickrInstance2.clear();
                }

                // Tampilkan modal edit
                $('#modalEditRencana').modal('show');
            },
            error: function(xhr) {
                Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan saat mengambil data.', 'error');
            }
        });
    });
</script>
@endpush