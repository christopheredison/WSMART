<!-- Modal Verifikasi Risiko -->
<div class="modal fade" id="modalVerifikasiRisiko" tabindex="-1" aria-labelledby="verifikasiRisikoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-0">
      <div class="modal-header">
        <h5 class="modal-title" id="verifikasiRisikoLabel">Verifikasi Risiko</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="info-single-risk" class="mb-4">
          <h6 id="modal-peristiwa-risiko">Peristiwa Risiko: </h6>
          <p id="modal-deskripsi-risiko" class="text-muted"></p>
        </div>

        <div id="info-bulk-risk" class="mt-0 mb-4 d-none">
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class='bx bx-info-circle fs-4 me-2'></i>
                <div>
                    Anda akan memverifikasi secara massal sebanyak <strong id="bulk-count">0</strong> data risiko yang telah dipilih.
                </div>
            </div>
        </div>

        <form id="form-verifikasi" action="" method="POST">
          @csrf
          <div id="bulk-ids-container"></div>
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
        <button type="button" class="btn btn-danger" id="btn-kembalikan-risiko">
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
let currentIds = [];
function handleVerifikasiClick(id) {
    const rowData = fetchedData[id];
    const peristiwaRisiko = rowData.peristiwa_risiko?.title || rowData.peristiwa_risiko || 'Tidak Ada Judul';
    const deskripsiRisiko = rowData.deskripsi_peristiwa_risiko || 'Tidak Ada Deskripsi';

    currentIds = [id];
    showVerifikasiModal(currentIds, peristiwaRisiko, deskripsiRisiko);
}

function handleBulkVerifikasiClick() {
    const selectedIds = [];
    $('.row-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu risiko terlebih dahulu', 'warning');
        return;
    }

    currentIds = selectedIds;
    showVerifikasiModal(currentIds);
}

function showVerifikasiModal(ids, peristiwaRisiko = '', deskripsiRisiko = '') {
    const isBulk = ids.length > 1;
    const form = document.getElementById('form-verifikasi');
    const bulkIdsContainer = document.getElementById('bulk-ids-container');
    const singleInfo = document.getElementById('info-single-risk');
    const bulkInfo = document.getElementById('info-bulk-risk');

    form.reset();
    bulkIdsContainer.innerHTML = '';

    if (isBulk) {
        singleInfo.classList.add('d-none');
        bulkInfo.classList.remove('d-none');
        document.getElementById('bulk-count').textContent = ids.length;

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            bulkIdsContainer.appendChild(input);
        });

        form.action = "{{ route('projects.risks.bulk-verifikasi') }}";
    } else {
        singleInfo.classList.remove('d-none');
        bulkInfo.classList.add('d-none');
        document.getElementById('modal-peristiwa-risiko').textContent = 'Peristiwa Risiko: ' + peristiwaRisiko;
        document.getElementById('modal-deskripsi-risiko').textContent = deskripsiRisiko;

        form.action = '{{ url("project-risk") }}/' + ids[0] + '/verifikasi';
    }

    $('.indicator-label').removeClass('d-none');
    $('.indicator-progress').addClass('d-none');
    $('#btn-terima-risiko, #btn-kembalikan-risiko').prop('disabled', false);

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerifikasiRisiko'));
    modal.show();

    document.getElementById('btn-terima-risiko').onclick = () => submitVerifikasi('terima', isBulk);
    document.getElementById('btn-kembalikan-risiko').onclick = () => submitVerifikasi('tolak', isBulk);
}

function submitVerifikasi(status, isBulk) {
    const form = document.getElementById('form-verifikasi');
    const catatan = document.getElementById('catatan-verifikasi').value.trim();
    document.getElementById('status-verifikasi').value = status;

    if (!catatan) {
        Swal.fire('Peringatan', 'Catatan verifikasi tidak boleh kosong', 'warning');
        return;
    }

    const config = {
        title: status === 'terima' ? 'Terima Risiko?' : 'Kembalikan Risiko?',
        text: isBulk ? `Sebanyak ${currentIds.length} data akan diproses.` : 'Data akan diperbarui sesuai status verifikasi.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: status === 'terima' ? 'Ya, Terima' : 'Ya, Kembalikan',
        cancelButtonText: 'Batal'
    };

    Swal.fire(config).then((result) => {
        if (result.isConfirmed) {
            const btnId = status === 'terima' ? 'btn-terima-risiko' : 'btn-kembalikan-risiko';
            const btn = document.getElementById(btnId);

            $('#btn-terima-risiko, #btn-kembalikan-risiko').prop('disabled', true);
            btn.querySelector('.indicator-label').classList.add('d-none');
            btn.querySelector('.indicator-progress').classList.remove('d-none');

            if (isBulk) {
                $.ajax({
                    url: form.action,
                    type: 'POST',
                    data: $(form).serialize(),
                    success: function(res) {
                        bootstrap.Modal.getInstance(document.getElementById('modalVerifikasiRisiko')).hide();
                        Swal.fire('Berhasil', res.message, 'success').then(() => {
                            $('.ajax-datatable').DataTable().ajax.reload();
                            $('#check-all-risiko').prop('checked', false);
                            $('#bulk-verify-container').addClass('d-none');
                        });
                    },
                    error: function(err) {
                        Swal.fire('Gagal', 'Terjadi kesalahan sistem', 'error');
                        $('#btn-terima-risiko, #btn-kembalikan-risiko').prop('disabled', false);
                        btn.querySelector('.indicator-label').classList.remove('d-none');
                        btn.querySelector('.indicator-progress').classList.add('d-none');
                    }
                });
            } else {
                form.submit();
            }
        }
    });
}
</script>
