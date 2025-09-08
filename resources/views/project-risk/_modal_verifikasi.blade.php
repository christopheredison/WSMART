<!-- Modal Verifikasi Risiko -->
<div class="modal fade" id="modalVerifikasiRisiko" tabindex="-1" aria-labelledby="verifikasiRisikoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verifikasiRisikoLabel">Verifikasi Risiko</h5>
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
            <textarea class="form-control" id="catatan-verifikasi" name="catatan_verifikasi" rows="4" placeholder="Masukkan catatan verifikasi..."></textarea>
          </div>
          <input type="hidden" name="status_verifikasi" id="status-verifikasi" value="">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="btn-terima-risiko">Terima Risiko</button>
        <button type="button" class="btn btn-danger" id="btn-tolak-risiko">Tolak Risiko</button>
        <button type="button" class="btn btn-muted" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<script>
// Fungsi untuk menampilkan modal verifikasi dengan data risiko yang sesuai
function showVerifikasiModal(id, peristiwaRisiko, deskripsiRisiko) {
  // Set data risiko ke dalam modal
  document.getElementById('modal-peristiwa-risiko').textContent = 'Peristiwa Risiko: ' + peristiwaRisiko;
  document.getElementById('modal-deskripsi-risiko').textContent = deskripsiRisiko;

  // Set action form dengan ID risiko yang dipilih
  const form = document.getElementById('form-verifikasi');
  form.action = '{{ url("project-risk") }}/' + id + '/verifikasi';

  // Reset form
  form.reset();
  document.getElementById('status-verifikasi').value = '';

  // Tampilkan modal
  const modal = new bootstrap.Modal(document.getElementById('modalVerifikasiRisiko'));
  modal.show();

  // Set event listener untuk tombol terima dan tolak
  document.getElementById('btn-terima-risiko').onclick = function() {
    submitVerifikasi(id, 'terima');
  };

  document.getElementById('btn-tolak-risiko').onclick = function() {
    submitVerifikasi(id, 'tolak');
  };
}

// Fungsi untuk submit verifikasi
function submitVerifikasi(id, status) {
  // Ambil form verifikasi
  const form = document.getElementById('form-verifikasi');
  const statusInput = document.getElementById('status-verifikasi');
  
  // Set status verifikasi
  statusInput.value = status;
  
  // Submit form
  form.submit();
}
</script>