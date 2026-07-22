@csrf
<div class="row">
    <div class="col-12 mb-3">
        <label for="code" class="form-label">Kode WBS <span class="text-danger">*</span></label>
        <input type="text" name="code" id="code" class="form-control" required placeholder="Contoh: IP-0001">
    </div>

    <div class="col-12 mb-3">
        <label for="name" class="form-label">Nama / Deskripsi WBS <span class="text-danger">*</span></label>
        <textarea name="name" id="name" class="form-control" rows="3" required placeholder="Deskripsi WBS..."></textarea>
    </div>

    <div class="col-12 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
            <label class="form-check-label" for="is_active">Aktif (Tampilkan di Dropdown)</label>
        </div>
        <small class="text-muted">Jika dinonaktifkan, WBS ini tidak akan muncul saat pengisian form identifikasi risiko.</small>
    </div>
</div>
