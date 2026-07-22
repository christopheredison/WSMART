<div class="modal fade" id="modalVerifikasiRisiko" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="verifikasiRisikoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifikasiRisikoLabel">Verifikasi Monitoring</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 id="modal-peristiwa-risiko">Peristiwa Risiko: </h6>
                    <p id="modal-deskripsi-risiko"></p>
                </div>
                <form id="form-verifikasi" action="" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="catatan-verifikasi" class="form-label">Catatan Verifikasi</label>
                        <textarea class="form-control" id="catatan-verifikasi" name="notes" rows="4" placeholder="Masukkan catatan verifikasi..."></textarea>
                    </div>
                    <input type="hidden" name="status_verifikasi" id="status-verifikasi" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btn-terima-risiko">
                    <span class="indicator-label">Terima Monitoring</span>
                    <span class="indicator-progress d-none">
                        Memproses... <span class="spinner-border spinner-border-sm align-middle ms-2" style="width: 0.75rem; height: 0.75rem;"></span>
                    </span>
                </button>
                <button type="button" class="btn btn-danger" id="btn-tolak-risiko">
                    <span class="indicator-label">Tolak Monitoring</span>
                    <span class="indicator-progress d-none">
                        Memproses... <span class="spinner-border spinner-border-sm align-middle ms-2" style="width: 0.75rem; height: 0.75rem;"></span>
                    </span>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btn-batal-verifikasi">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
function showVerifikasiModal(monitoringId, peristiwaRisiko, deskripsiRisiko) {
    const modalElement = document.getElementById('modalVerifikasiRisiko');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    
    // Reset tampilan tombol setiap kali modal dibuka
    document.querySelectorAll('.modal-footer .btn').forEach(btn => {
        btn.disabled = false;
        
        const label = btn.querySelector('.indicator-label');
        const progress = btn.querySelector('.indicator-progress');

        if (label && progress) {
            label.classList.remove('d-none');
            progress.classList.add('d-none');
        }
    });

    const btnClose = document.querySelector('#modalVerifikasiRisiko .btn-close');
    if (btnClose) {
        btnClose.disabled = false;
    }

    document.getElementById('modal-peristiwa-risiko').textContent = 'Peristiwa Risiko: ' + peristiwaRisiko;
    document.getElementById('modal-deskripsi-risiko').textContent = deskripsiRisiko;

    const form = document.getElementById('form-verifikasi');
    const baseUrl = "{{ route('projects.monitorings.verify', ['project' => request()->route('project'), 'monitoring' => ':id']) }}";
    form.action = baseUrl.replace(':id', monitoringId);
    form.reset();

    document.getElementById('status-verifikasi').value = '';

    modal.show();
}

// Event listener untuk tombol di dalam modal (tidak ada perubahan di sini)
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-verifikasi');
    if (!form) return;

    const btnTerima = document.getElementById('btn-terima-risiko');
    const btnTolak = document.getElementById('btn-tolak-risiko');
    const btnBatal = document.getElementById('btn-batal-verifikasi');
    const btnClose = document.querySelector('#modalVerifikasiRisiko .btn-close');

    const showSpinnerAndSubmit = (button, status) => {
        btnTerima.disabled = true;
        btnTolak.disabled = true;
        btnBatal.disabled = true;
        btnClose.disabled = true;

        button.querySelector('.indicator-label').classList.add('d-none');
        button.querySelector('.indicator-progress').classList.remove('d-none');

        document.getElementById('status-verifikasi').value = status;
        form.submit();
    };

    btnTerima.addEventListener('click', function () {
        const button = this;

        Swal.fire({
            title: 'Terima Monitoring?',
            text: "Apakah Anda yakin ingin menerima monitoring ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Terima',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                showSpinnerAndSubmit(button, 'terima');
            }
        });
    });

    btnTolak.addEventListener('click', function () {
        const button = this;

        if (!document.getElementById('catatan-verifikasi').value.trim()) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Catatan wajib diisi untuk menolak monitoring.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        // 2. Tampilkan konfirmasi SweetAlert
        Swal.fire({
            title: 'Tolak Monitoring?',
            text: "Apakah Anda yakin ingin menolak monitoring ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                showSpinnerAndSubmit(button, 'tolak');
            }
        });
    });
});
</script>