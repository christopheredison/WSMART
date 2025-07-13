<div class="row g-2">
    <!-- Hidden Input for penyebab_risiko_id -->
    {{ Form::hidden('penyebab_risiko_id', '', ['id' => 'penyebabRisikoId']) }}

    <div class="col-12">
        <div class="form-floating">
            {{ Form::text('penyebab_risiko', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
            <label for="penyebab_risiko">Penyebab Risiko</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::textarea('rencana_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'required']) }}
            <label for="rencana_perlakuan_risiko">Rencana Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::textarea('output_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'required']) }}
            <label for="output_perlakuan_risiko">Output Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::select('opsi_perlakuan_risiko', \App\Models\OpsiPerlakuanRisiko::pluck('opsi_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'required', 'id' => 'opsi_perlakuan_risiko']) }}
            <label for="opsi_perlakuan_risiko">Opsi Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::select('jenis_rencana_perlakuan_risiko', \App\Models\JenisRencanaPerlakuanRisiko::pluck('jenis_rencana_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'required', 'id' => 'jenis_rencana_perlakuan_risiko']) }}
            <label for="jenis_rencana_perlakuan_risiko">Jenis Rencana Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::text('biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'required']) }}
            <label for="biaya_perlakuan_risiko">Biaya Perlakuan Risiko</label>
        </div>
        <div class="invalid-feedback">
            Biaya perlakuan risiko tidak boleh melebihi nilai dampak (Rp. {{ number_format($analisa->nilai_dampak, 0, ',', '.') }})
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::select('pic', \App\Models\Jabatan::pluck('name', 'id'), '', ['class' => 'form-select', 'required']) }}
            <label for="pic">PIC</label>
        </div>
    </div>
    {{-- <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="timelineRange" name="timeline_perlakuan_risiko" required>
            <label for="timelineRange">Timeline Perlakuan Risiko</label>
        </div>
    </div> --}}
    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="timelineRange1" name="timeline_mulai_perlakuan_risiko" required>
            <label for="timelineRange1">Waktu Mulai Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="timelineRange2" name="timeline_selesai_perlakuan_risiko" required>
            <label for="timelineRange2">Waktu Selesai Perlakuan Risiko</label>
        </div>
    </div>
</div>
