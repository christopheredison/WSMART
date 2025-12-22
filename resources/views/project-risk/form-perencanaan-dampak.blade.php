@push('styles')
<style>
  .related-divisions-dampak .select2-container .select2-selection--multiple {
    min-height: 62px;
    padding-top: 1.625rem;
    padding-bottom: 0;
    padding-inline: 0.5rem;
  }
</style>
@endpush

<div class="row g-2">
    {{ Form::hidden('risiko_id', $projectRisk->id, ['id' => 'risikoIdDampak']) }}

    <div class="col-12">
        <div class="form-floating">
            {{ Form::text('deskripsi_dampak', $projectRisk->deskripsi_dampak, ['class' => 'form-control', 'disabled' => 'disabled']) }}
            <label for="deskripsi_dampak">Dampak Risiko</label>
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
            <select name="opsi_perlakuan_risiko" id="opsiPerlakuanDampak" class="form-select" required>
                @foreach ($allOpsi as $opsi)
                    @if (in_array(trim($opsi->opsi_perlakuan_risiko), $allowedOpsiNames))
                        <option value="{{ $opsi->id }}">{{ $opsi->opsi_perlakuan_risiko }}</option>
                    @endif
                @endforeach
            </select>
            <label for="opsiPerlakuanDampak">Opsi Perlakuan Risiko</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::text('biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'required']) }}
            <label for="biaya_perlakuan_risiko">Biaya Perlakuan Risiko</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            {{ Form::select('pic', \App\Models\Jabatan::where('jabatan_type', 2)->pluck('name', 'id'), '', ['id' => 'picTambahDampak', 'class' => 'form-select select2', 'required']) }}
            <label for="picTambahDampak">PIC</label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-floating related-divisions-dampak">
            {{ Form::select('divisi_terkait[]', \App\Models\Unit::where('unit_type_id', 1)->pluck('name', 'id'), '', ['id' => 'divisiTerkaitTambahDampak', 'class' => 'form-select select2', 'multiple' => 'multiple', 'style' => 'width: 100%']) }}
            <label for="divisiTerkaitTambahDampak">Divisi Terkait</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="timelineDampak1" name="timeline_mulai_perlakuan_risiko" required>
            <label for="timelineDampak1">Waktu Mulai Perlakuan</label>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="timelineDampak2" name="timeline_selesai_perlakuan_risiko" required>
            <label for="timelineDampak2">Waktu Selesai Perlakuan</label>
        </div>
    </div>
</div>
