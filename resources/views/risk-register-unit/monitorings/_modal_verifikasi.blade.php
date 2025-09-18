<div class="modal fade" id="modalVerifikasi" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="verifikasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="verifikasiLabel">Verifikasi Monitoring</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 id="modal-verifikasi-risiko-title">Peristiwa Risiko: </h6>
                    <p id="modal-verifikasi-risiko-desc"></p>
                </div>
                <form id="form-verifikasi" action="" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="catatan-verifikasi" class="form-label">Catatan Verifikasi</label>
                        <textarea class="form-control" id="catatan-verifikasi" name="notes" rows="4" placeholder="Masukkan catatan..."></textarea>
                    </div>
                    <input type="hidden" name="status_verifikasi" id="status-verifikasi" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btn-terima-verifikasi">
                  <span class="indicator-label">Terima Monitoring</span>
                  <span class="indicator-progress d-none">
                    Memproses... <span class="spinner-border spinner-border-sm" style="width: 0.75rem; height: 0.75rem;"></span>
                  </span>
                </button>
                <button type="button" class="btn btn-danger" id="btn-tolak-verifikasi">
                  <span class="indicator-label">Tolak Monitoring</span>
                  <span class="indicator-progress d-none">
                    Memproses... <span class="spinner-border spinner-border-sm" style="width: 0.75rem; height: 0.75rem;"></span>
                  </span>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btn-batal-verifikasi">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
function showVerifikasiModal(monitoringId, peristiwaRisiko, deskripsiRisiko) {
    const modalElement = document.getElementById('modalVerifikasi');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    document.getElementById('modal-verifikasi-risiko-title').textContent = 'Peristiwa Risiko: ' + peristiwaRisiko;
    document.getElementById('modal-verifikasi-risiko-desc').textContent = deskripsiRisiko;

    const form = document.getElementById('form-verifikasi');
    const baseUrl = "{{ route('risk-register-unit.monitorings.verify', ['period' => request()->route('period'), 'monitoring' => ':id']) }}";
    form.action = baseUrl.replace(':id', monitoringId);
    form.reset();
    
    modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-verifikasi');
    if (!form) return;
    const btnTerima = document.getElementById('btn-terima-verifikasi');
    const btnTolak = document.getElementById('btn-tolak-verifikasi');
    const btnBatal = document.getElementById('btn-batal-verifikasi');
    const btnClose = document.querySelector('#modalVerifikasi .btn-close');

    const handleSubmit = (button, status) => {
        btnTerima.disabled = true; btnTolak.disabled = true; btnBatal.disabled = true; btnClose.disabled = true;
        button.querySelector('.indicator-label').classList.add('d-none');
        button.querySelector('.indicator-progress').classList.remove('d-none');
        document.getElementById('status-verifikasi').value = status;
        form.submit();
    };

    btnTerima.addEventListener('click', () => handleSubmit(btnTerima, 'terima'));
    btnTolak.addEventListener('click', () => {
        if (!document.getElementById('catatan-verifikasi').value) {
            alert('Catatan wajib diisi untuk menolak monitoring.');
            return;
        }
        handleSubmit(btnTolak, 'tolak');
    });
});
</script>