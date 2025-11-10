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
            @php
                $allOpsi = \App\Models\OpsiPerlakuanRisiko::all();

                $opsiMapping = [
                    'low'               => ['Accept/monitor'],
                    'low to moderate'   => ['Reduce/mitigate', 'Accept/monitor'],
                    'moderate'          => ['Reduce/mitigate'],
                    'moderate to high'  => ['Reduce/mitigate', 'Transfer/sharing'],
                    'high'              => ['Reduce/mitigate', 'Avoid/hindari'],
                ];

                $currentLevel = strtolower(optional($analisa)->level_risiko ?? '');
                $allowedOpsiNames = $opsiMapping[$currentLevel] ?? [];
            @endphp
            <select name="xopsi_perlakuan_risiko" id="xopsi_perlakuan_risiko" class="form-select" required>
                  @foreach ($allOpsi as $opsi)
                      @if (in_array(trim($opsi->opsi_perlakuan_risiko), $allowedOpsiNames))
                          <option value="{{ $opsi->id }}">{{ $opsi->opsi_perlakuan_risiko }}</option>
                      @endif
                  @endforeach
            </select>
            <label for="xopsi_perlakuan_risiko">Opsi Perlakuan Risiko</label>
        </div>
    </div>
    {{-- <div class="col-12">
        <div class="form-floating">
            {{ Form::select('xjenis_rencana_perlakuan_risiko', \App\Models\JenisRencanaPerlakuanRisiko::pluck('jenis_rencana_perlakuan_risiko', 'id'), '', ['class' => 'form-select', 'required', 'id' => 'jenis_rencana_perlakuan_risiko']) }}
            <label for="xjenis_rencana_perlakuan_risiko">Jenis Rencana Perlakuan Risiko</label>
        </div>
    </div> --}}
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::text('xbiaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'required']) }}
            <label for="xbiaya_perlakuan_risiko">Biaya Perlakuan Risiko</label>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::select('xpic', \App\Models\Jabatan::where('jabatan_type', 1)->pluck('name', 'id'), '', ['id' => 'picEdit', 'class' => 'form-select select2', 'required']) }}
            <label for="pic">PIC</label>
        </div>
    </div>
    {{--
    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::text('xpic', null, ['class' => 'form-control', 'required']) }}
            <label for="xpic">PIC</label>
        </div>
    </div>
    <div class="col-12 col-md-6">
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
