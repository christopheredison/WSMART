@csrf
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="unit_id" class="form-label">Divisi</label>
        <select name="unit_id" id="unit_id" class="form-select" required>
            <option value="" disabled selected>Pilih Divisi</option>
            @foreach(\App\Models\Unit::orderBy('name')->get() as $unit)
                <option value="{{ $unit->id }}" data-cost-center="{{ $unit->cost_center }}">{{ $unit->name }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="col-md-4 mb-3">
        <label for="cost_center" class="form-label">Cost Center</label>
        <input type="text" name="cost_center" id="cost_center" class="form-control" required placeholder="Cost Center Divisi">
    </div>

    <div class="col-md-4 mb-3">
        <label for="period" class="form-label">Periode (YYYYMM)</label>
        <input type="text" name="period" id="period" class="form-control" required pattern="\d{6}" placeholder="Contoh: {{ now()->format('Ym') }}">
    </div>
</div>

<div class="mb-3 mt-2">
    <h5 class="border-bottom pb-2">Detail Hasil Usaha</h5>
</div>

{{-- Sisa form tetap sama --}}
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="kontrak_review" class="form-label">Kontrak Review</label>
        <input type="text" name="kontrak_review" class="form-control inputmask-general">
    </div>
    <div class="col-md-4 mb-3">
        <label for="penjualan_ra" class="form-label">Penjualan Rencana (RA)</label>
        <input type="text" name="penjualan_ra" class="form-control inputmask-general">
    </div>
    <div class="col-md-4 mb-3">
        <label for="penjualan_ri" class="form-label">Penjualan Realisasi (RI)</label>
        <input type="text" name="penjualan_ri" class="form-control inputmask-general">
    </div>
    <div class="col-md-6 mb-3">
        <label for="progress_fisik_ra" class="form-label">Progress Fisik (RA)</label>
        <input type="text" name="progress_fisik_ra" class="form-control" readonly placeholder="0.00 %">
    </div>
    <div class="col-md-6 mb-3">
        <label for="progress_fisik_ri" class="form-label">Progress Fisik (RI)</label>
        <input type="text" name="progress_fisik_ri" class="form-control" readonly placeholder="0.00 %">
    </div>
    <div class="col-md-6 mb-3">
        <label for="lsp_review" class="form-label">LSP Review</label>
        <input type="text" name="lsp_review" class="form-control inputmask-general">
    </div>
    <div class="col-md-6 mb-3">
        <label for="lsp_ra" class="form-label">LSP Rencana (RA)</label>
        <input type="text" name="lsp_ra" class="form-control inputmask-general">
    </div>
    <div class="col-md-6 mb-3">
        <label for="lsp_ri" class="form-label">LSP Realisasi (RI)</label>
        <input type="text" name="lsp_ri" class="form-control inputmask-general">
    </div>
    <div class="col-md-6 mb-3">
        <label for="lsp_proyeksi" class="form-label">LSP Proyeksi</label>
        <input type="text" name="lsp_proyeksi" class="form-control inputmask-general">
    </div>
</div>