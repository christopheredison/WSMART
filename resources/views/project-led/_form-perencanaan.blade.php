<div class="row g-2">
    <!-- Hidden Input for penyebab_risiko_id -->
    {{-- {{ Form::hidden('penyebab_risiko_id', '', ['id' => 'penyebabRisikoId']) }} --}}

    <div class="col-12">
        <div class="form-floating">
            <input type="text" name="penyebab_risiko_text" class="form-control" id="penyebab_risiko_text" disabled>
            <label for="penyebab_risiko_text">Penyebab Kejadian</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::textarea('rencana_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'required']) }}
            <label for="rencana_perlakuan_risiko">Penanganan Saat Kejadian <span class="text-danger">*</span></label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::textarea('output_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'required']) }}
            <label for="output_perlakuan_risiko">Output Penanganan Saat Kejadian <span class="text-danger">*</span></label>
        </div>
    </div>
    {{-- <div class="col-12">
        <div class="form-floating">
            {{ Form::select('opsi_perlakuan_risiko', \App\Models\OpsiPerlakuanRisiko::pluck('opsi_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'required', 'id' => 'opsi_perlakuan_risiko']) }}
            <label for="opsi_perlakuan_risiko">Opsi Penanganan Saat Kejadian <span class="text-danger">*</span></label>
        </div>
    </div> --}}
    {{-- <div class="col-12">
        <div class="form-floating">
            {{ Form::select('jenis_rencana_perlakuan_risiko', \App\Models\JenisRencanaPerlakuanRisiko::pluck('jenis_rencana_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'required', 'id' => 'jenis_rencana_perlakuan_risiko']) }}
            <label for="jenis_rencana_perlakuan_risiko">Jenis Rencana Perlakuan Risiko <span class="text-danger">*</span></label>
        </div>
    </div> --}}
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::text('biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'required']) }}
            <label for="biaya_perlakuan_risiko">Biaya Penanganan <span class="text-danger">*</span></label>
        </div>
        @if(isset($analisa) && $analisa?->nilai_dampak > 0)
        <div class="invalid-feedback">
            Biaya penanganan tidak boleh melebihi nilai dampak (Rp. {{ number_format($analisa->nilai_dampak, 0, ',', '.') }})
        </div>
        @endif
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::select('pic', \App\Models\Jabatan::where('jabatan_type', 2)->pluck('name', 'id'), '', ['class' => 'form-select', 'required', 'id' => 'pic']) }}
            <label for="pic">PIC <span class="text-danger">*</span></label>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control flatpickr-date-modal" id="timelineRange1" name="timeline_mulai_perlakuan_risiko" required>
            <label for="timelineRange1">Waktu Mulai Penanganan <span class="text-danger">*</span></label>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control flatpickr-date-modal" id="timelineRange2" name="timeline_selesai_perlakuan_risiko" required>
            <label for="timelineRange2">Waktu Selesai Penanganan <span class="text-danger">*</span></label>
        </div>
    </div>
</div>
