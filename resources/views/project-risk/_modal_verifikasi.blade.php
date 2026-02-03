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
            <textarea class="form-control" id="catatan-verifikasi" name="catatan_verifikasi" rows="4" placeholder="Masukkan catatan verifikasi..." required></textarea>
          </div>
          <input type="hidden" name="status_verifikasi" id="status-verifikasi" value="">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="btn-terima-risiko">
          <span class="indicator-label">Terima Risiko</span>
          <span class="indicator-progress d-none">
            Memproses... <span class="spinner-border spinner-border-sm align-middle ms-2" style="width: 0.75rem; height: 0.75rem;"></span>
          </span>
        </button>
        <button type="button" class="btn btn-danger" id="btn-tolak-risiko">
          <span class="indicator-label">Kembalikan Risiko</span>
          <span class="indicator-progress d-none">
            Memproses... <span class="spinner-border spinner-border-sm align-middle ms-2" style="width: 0.75rem; height: 0.75rem;"></span>
          </span>
        </button>
        <button type="button" class="btn btn-muted" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<script>
function handleVerifikasiClick(id) {
  const rowData = fetchedData[id];

  // if (!rowData) {
  //     console.error('Data not found for ID:', id);
  //     alert('Data tidak ditemukan!');
  //     return;
  // }

  const peristiwaRisiko = rowData.peristiwa_risiko?.title || rowData.rencana_kegiatan || 'Tidak Ada Judul';
  const deskripsiRisiko = rowData.deskripsi_peristiwa_risiko || 'Tidak Ada Deskripsi';
  showVerifikasiModal(id, peristiwaRisiko, deskripsiRisiko);
}

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

  const btnTerima = document.getElementById('btn-terima-risiko');
  const btnKembalikan = document.getElementById('btn-tolak-risiko');

  // 1. Aktifkan kembali tombol
  btnTerima.disabled = false;
  btnKembalikan.disabled = false;

  // 2. Tampilkan label, sembunyikan spinner (Tombol Terima)
  btnTerima.querySelector('.indicator-label').classList.remove('d-none');
  btnTerima.querySelector('.indicator-progress').classList.add('d-none');

  // 3. Tampilkan label, sembunyikan spinner (Tombol Kembalikan)
  btnKembalikan.querySelector('.indicator-label').classList.remove('d-none');
  btnKembalikan.querySelector('.indicator-progress').classList.add('d-none');

  // Tampilkan modal
  const modal = new bootstrap.Modal(document.getElementById('modalVerifikasiRisiko'));
  modal.show();

  // Set event listener untuk tombol terima dan tolak
  document.getElementById('btn-terima-risiko').onclick = function() {
    submitVerifikasi('terima');
  };

  document.getElementById('btn-tolak-risiko').onclick = function() {
    submitVerifikasi('tolak');
  };
}

function submitVerifikasi(status) {
  // Ambil form verifikasi
  const form = document.getElementById('form-verifikasi');
  const statusInput = document.getElementById('status-verifikasi');
  const catatanInput = document.getElementById('catatan-verifikasi');

  // Set status verifikasi
  statusInput.value = status;

  if (!form.checkValidity() || !catatanInput.value.trim()) {
    Swal.fire({
      title: 'Peringatan',
      text: 'Catatan verifikasi tidak boleh kosong',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    form.reportValidity();
    return;
  }

  const title = status === 'terima' ? 'Terima Risiko?' : 'Kembalikan Risiko?';
  const text = status === 'terima' ? 'Risiko akan diverifikasi dan diterima' : 'Risiko akan dikembalikan untuk revisi';
  const confirmButtonText = status === 'terima' ? 'Ya, Terima' : 'Ya, Kembalikan';

  Swal.fire({
    title: title,
    text: text,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: confirmButtonText,
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      const btnTerima = document.getElementById('btn-terima-risiko');
      const btnKembalikan = document.getElementById('btn-tolak-risiko');

      btnTerima.disabled = true;
      btnKembalikan.disabled = true;

      let clickedButton;
      if (status === 'terima') {
        clickedButton = btnTerima;
      } else {
        clickedButton = btnKembalikan;
      }

      clickedButton.querySelector('.indicator-label').classList.add('d-none');
      clickedButton.querySelector('.indicator-progress').classList.remove('d-none');

      form.submit();
    }
  });
}
</script>
