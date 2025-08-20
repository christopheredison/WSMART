<div class="modal fade" id="modalPerencanaan" tabindex="-1" role="dialog" aria-labelledby="modalPerencanaan"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg " role="document">
        <div class="modal-content">
            <form method="POST" id="formPerencanaan">
                @csrf
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="newSkalaDampakLabel">Rencana Perlakuan Risiko</h3>
                    <div class="lead__icon lead__icon_sm">
                        <div class="svg-icon svg-icon-secondary">
                            @include('partials.icon-tool')
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    {{ Form::hidden('id', '') }}
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="form-floating">
                                {{ Form::text('penyebab_risiko', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                <label for="penyebab_risiko">Penyebab Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                {{ Form::textarea('rencana_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Rencana Perlakuan Risiko', 'required' => 'required']) }}
                                <label for="rencana_perlakuan_risiko" class="form-label">Rencana Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                {{ Form::textarea('output_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Output Perlakuan Risiko', 'required' => 'required']) }}
                                <label for="output_perlakuan_risiko" class="form-label">Output Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                {{ Form::select('opsi_perlakuan_risiko', \App\Models\OpsiPerlakuanRisiko::pluck('opsi_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'placeholder' => 'Pilih Opsi Perlakuan Risiko', 'required' => true]) }}
                                <label for="output_perlakuan_risiko" class="form-label">Opsi Perlakuan Risiko</label>
                            </div>
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-floating">
                                {{ Form::select('jenis_rencana_perlakuan_risiko', \App\Models\JenisRencanaPerlakuanRisiko::pluck('jenis_rencana_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'placeholder' => 'Pilih Jenis Rencana Perlakuan Risiko', 'required' => true]) }}
                                <label for="output_perlakuan_risiko" class="form-label">Jenis Rencana Perlakuan Risiko</label>
                            </div>
                        </div> --}}
                        <div class="col-12 col-md-6">
                            <div class="form-floating">
                                {{ Form::text('biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'placeholder' => 'Masukkan biaya dalam rupiah', 'required' => 'required']) }}
                                <label for="biaya_perlakuan_risiko" class="form-label">Biaya Perlakuan Risiko (dalam rupiah)</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-floating">
                                {{ Form::text('pic', null, ['class' => 'form-control', 'placeholder' => 'PIC', 'required' => 'required']) }}
                                <label for="pic" class="form-label">PIC</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 mt-3">
                            <label class="form-label">Timeline Perlakuan Risiko</label>
                            <div class="row general-checkbox timeline-container">
                                <div class="col-12">
                                    <label class="form-check" for="selectAllMonths">
                                        <input class="form-check-input" type="checkbox" value="" id="selectAllMonths">
                                        Pilih Semua
                                    </label>
                                </div>
                                @php $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; @endphp
                                @foreach ($months as $idx => $month)
                                    <div class="col-md-3">
                                        {{ Form::hidden("timeline_perlakuan_risiko[$idx]", '0') }}
                                        <label for="timeline_perlakuan_risiko_{{ $idx }}" class="form-check">
                                            {{ Form::checkbox("timeline_perlakuan_risiko[$idx]", '1', false, ['id' => "timeline_perlakuan_risiko_$idx", 'class' => 'form-check-input']) }}
                                            {{ $month }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-submit" data-action="save-perencanaan">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .general-checkbox label.form-check {
        pointer-events: auto !important;
    }

    .general-checkbox .form-check input {
        pointer-events: auto !important; 
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('button[data-action="save-perencanaan"]').on('click', function() {
        var form = $('#formPerencanaan')[0];
        var formData = new FormData(form);

        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const inputmaskFields = document.querySelectorAll('.inputmask-rupiah');
        inputmaskFields.forEach(function(input) {
            const value = input.inputmask.unmaskedvalue();
            formData.set(input.name, value);
        });

        $.ajax({
            url: "{{ route('projects.risks.do-rencana', ['project' => $projectPeriodeList->id, 'risk' => $projectRisk->id]) }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#modalPerencanaan').modal('hide');
                Swal.fire({
                    title: 'Berhasil',
                    text: response.message || 'Data berhasil disimpan',
                    icon: 'success',
                    confirmButtonText: 'OK',
                }).then(function() {
                    window.location.reload();
                })
            },
            error: function(response) {
                Swal.fire({
                    title: 'Gagal',
                    text: response.responseJSON.message || 'Terjadi kesalahan saat menyimpan data',
                    icon: 'error',
                    confirmButtonText: 'OK',
                });
            }
        });
    });

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

    $('#selectAllMonths').on('change', function() {
        const checkboxes = $('.timeline-container .form-check-input');
        checkboxes.prop('checked', this.checked);
    });
})
</script>
@endpush