<div class="modal fade" id="importDataModal" tabindex="-1" aria-labelledby="importDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importDataModalLabel">{{ $importConfig['title'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="import-form" action="{{ $importConfig['route'] }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Langkah 1: Download Template --}}
                    <div class="">
                        <h6 class="mb-2">Langkah 1: Unduh Template</h6>
                        <p class="mb-2">
                            Unduh template Excel yang sudah disiapkan untuk memastikan data Anda sesuai format. Template ini berisi sheet
                            <strong>Standarisasi Risiko</strong> dan <strong>Taksonomi</strong> sebagai acuan.
                        </p>
                        @if ($importConfig['templateUrl'])
                            <button type="button" id="download-template-btn" class="btn btn-sm btn-primary d-flex align-items-center" data-url="{{ $importConfig['templateUrl'] }}">
                                <span id="download-spinner" class="spinner-border spinner-border-sm me-2 d-none" style="width: 0.75rem; height: 0.75rem;" role="status" aria-hidden="true"></span>
                                <span id="download-icon" class="bx bx-download me-1"></span>
                                <span id="download-text">Download Template</span>
                            </button>
                        @endif
                    </div>
                    <div class="alert alert-info text-info py-2 px-3 small d-flex align-items-center mt-3" role="alert">
                        <span class="bx bx-info-circle me-2 fs-5"></span>
                        <div>
                            <strong>Penting:</strong> Pengisian data pada setiap sheet dimulai dari <strong>baris ke-3</strong>. Baris 1 & 2 adalah header dan tidak boleh diubah.
                        </div>
                    </div>

                    {{-- Langkah 2: Upload File --}}
                    <div class="mt-5">
                        <h6 class="mb-2">Langkah 2: Unggah File</h6>
                        <label for="file" class="form-label">Pilih file Excel yang sudah Anda isi:</label>
                        <input class="form-control" type="file" id="file" name="file" required accept=".xlsx,.xls,.csv">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="submit-import-btn" class="btn btn-success">
                        <span id="import-loading" class="spinner-border spinner-border-sm me-2 d-none" style="width: 0.75rem; height: 0.75rem;" role="status" aria-hidden="true"></span>
                        <span class="bx bx-upload me-1"></span>
                        <span id="import-text">Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>