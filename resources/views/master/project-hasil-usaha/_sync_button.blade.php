<button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#syncModal">
    <span class="bx bx-sync"></span>
    <span class="ms-1">Sinkronisasi Hasil Usaha</span>
</button>

<div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="syncModalLabel">Sinkronisasi Hasil Usaha Proyek</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="syncForm" action="{{ route('project-hasil-usaha.sync-all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>
                        Masukkan periode (format YYYYMM) untuk mengambil data dari API WIKA. Proses ini akan memperbarui data hasil usaha proyek, termasuk data LSP yang masih kosong.
                    </p>
                    <div class="mb-3">
                        <label for="period" class="form-label">Periode</label>
                        <input 
                        type="text" class="form-control" id="period" name="period" 
                            placeholder="Contoh: {{ now()->format('Ym') }}" 
                            value="{{ now()->format('Ym') }}"
                            required pattern="\d{6}" title="Masukkan 6 digit angka, contoh: 202510">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="force" name="force">
                        <label class="form-check-label" for="force">
                            Paksa ambil ulang semua data (overwrite), meskipun data sudah ada
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitSyncBtn">
                        <span id="sync-text">Mulai Sinkronisasi</span>
                        <span id="sync-loading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
