<div class="row g-2">
    <!-- Hidden Input for penyebab_risiko_id -->
    {{ Form::hidden('xpenyebab_risiko_id', '', ['id' => 'xpenyebabRisikoId']) }}
    {{ Form::hidden('xperlakuan_id', '', ['id' => 'xperlakuanId']) }}
    <div class="col-12">
        <div class="form-floating">
            {{ Form::text('xpenyebab_risiko', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
            <label for="xpenyebab_risiko">Penyebab Risiko</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::textarea('xrencana_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'required']) }}
            <label for="xrencana_perlakuan_risiko">Rencana Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::textarea('xoutput_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'required']) }}
            <label for="xoutput_perlakuan_risiko">Output Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::select('xopsi_perlakuan_risiko', \App\Models\OpsiPerlakuanRisiko::pluck('opsi_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'required']) }}
            <label for="xopsi_perlakuan_risiko">Opsi Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12">
        <div class="form-floating">
            {{ Form::select('xjenis_rencana_perlakuan_risiko', \App\Models\JenisRencanaPerlakuanRisiko::pluck('jenis_rencana_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'required']) }}
            <label for="xjenis_rencana_perlakuan_risiko">Jenis Rencana Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::text('xbiaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'required']) }}
            <label for="xbiaya_perlakuan_risiko">Biaya Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::text('xpic', null, ['class' => 'form-control', 'required']) }}
            <label for="xpic">PIC</label>
        </div>
    </div>
    {{-- <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="xtimelineRange" name="xtimeline_perlakuan_risiko" required>
            <label for="xtimelineRange">Timeline Perlakuan Risiko</label>
        </div>
    </div> --}}

    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="xtimelineRange1" name="xtimeline_mulai_perlakuan_risiko" required>
            <label for="xtimelineRange1">Waktu Mulai Perlakuan Risiko</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="xtimelineRange2" name="xtimeline_selesai_perlakuan_risiko" required>
            <label for="xtimelineRange2">Waktu Selesai Perlakuan Risiko</label>
        </div>
    </div>
</div>
