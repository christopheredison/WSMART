@push('styles')
<style>
  .xd-related-divisions .select2-container .select2-selection--multiple {
    min-height: 62px;
    padding-top: 1.625rem;
    padding-bottom: 0;
    padding-inline: 0.5rem;
  }
</style>
@endpush

<div class="row g-2">
    {{ Form::hidden('xd_perlakuan_id', '', ['id' => 'xdPerlakuanId']) }}
    {{ Form::hidden('xd_risiko_id', $projectRisk->id, ['id' => 'xdRisikoId']) }}

    <div class="col-12">
        <div class="form-floating">
            {{ Form::text('xd_deskripsi_dampak', $projectRisk->deskripsi_dampak, ['class' => 'form-control', 'disabled' => 'disabled']) }}
            <label for="xd_deskripsi_dampak">Dampak Risiko</label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-floating">
            {{ Form::textarea('xd_rencana_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'required']) }}
            <label for="xd_rencana_perlakuan_risiko">Rencana Perlakuan Risiko</label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-floating">
            {{ Form::textarea('xd_output_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'required']) }}
            <label for="xd_output_perlakuan_risiko">Output Perlakuan Risiko</label>
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
            <select name="xd_opsi_perlakuan_risiko" id="xdOpsiPerlakuan" class="form-select" required>
                @foreach ($allOpsi as $opsi)
                    @if (in_array(trim($opsi->opsi_perlakuan_risiko), $allowedOpsiNames))
                        <option value="{{ $opsi->id }}">{{ $opsi->opsi_perlakuan_risiko }}</option>
                    @endif
                @endforeach
            </select>
            <label for="xdOpsiPerlakuan">Opsi Perlakuan Risiko</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::text('xd_biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'required', 'id' => 'xdBiayaPerlakuan']) }}
            <label for="xdBiayaPerlakuan">Biaya Perlakuan Risiko</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::select('xd_pic', \App\Models\Jabatan::where('jabatan_type', 2)->pluck('name', 'id'), '', ['id' => 'picEditDampak', 'class' => 'form-select select2', 'required']) }}
            <label for="picEditDampak">PIC</label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-floating xd-related-divisions">
            {{ Form::select('xd_divisi_terkait[]', \App\Models\Unit::where('unit_type_id', 1)->pluck('name', 'id'), '', ['id' => 'divisiTerkaitEditDampak', 'class' => 'form-select select2', 'multiple' => 'multiple', 'style' => 'width: 100%']) }}
            <label for="divisiTerkaitEditDampak">Divisi Terkait</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="xtimelineDampak1" name="xd_timeline_mulai_perlakuan_risiko" required>
            <label for="xtimelineDampak1">Waktu Mulai Perlakuan</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="xtimelineDampak2" name="xd_timeline_selesai_perlakuan_risiko" required>
            <label for="xtimelineDampak2">Waktu Selesai Perlakuan</label>
        </div>
    </div>
</div>
