<div class="modal fade" id="modalCatatan" tabindex="-1" aria-labelledby="modalCatatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalCatatanLabel"><i class="fas fa-history me-2"></i>Riwayat Catatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body bg-light"><div id="catatan-content"></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<script>
    function showCatatanModal(riskId) {
        const modalElement = document.getElementById('modalCatatan');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        const contentDiv = $('#catatan-content');
        contentDiv.html('<div class="d-flex justify-content-center my-4"><div class="spinner-border"></div></div>');

        const url = "{{ route('risk-register-unit.monitorings.notes', ['period' => request()->route('period'), 'risk' => ':id']) }}".replace(':id', riskId);
        
        $.ajax({
            url: url,
            type: 'GET',
            data: {
                quarter: $('#table-filter select[name="quarter"]').val(),
                month: $('#table-filter select[name="month"]').val(),
            },
            success: function(notes) {
                if (notes.length === 0) {
                    contentDiv.html(`<div class="text-center my-4"><i class="fas fa-comment-slash fa-2x text-muted mb-2"></i><p>Belum ada catatan.</p></div>`);
                } else {
                    let html = '';
                    notes.forEach(note => {
                        const statusBadge = note.status == 1 ? '<span class="badge bg-success-subtle text-success-emphasis">Diterima</span>' : '<span class="badge bg-danger-subtle text-danger-emphasis">Ditolak</span>';
                        const formattedDate = new Date(note.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                        html += `<div class="card mb-3 shadow-sm"><div class="card-header bg-white d-flex justify-content-between align-items-center py-2"><div class="fw-bold"><i class="fas fa-user-circle text-muted me-2"></i> ${note.user.name}</div><div class="d-flex align-items-center"><small class="text-muted me-3">${formattedDate}</small>${statusBadge}</div></div><div class="card-body"><p class="card-text mb-0">${note.notes || '<i>Tidak ada catatan.</i>'}</p></div></div>`;
                    });
                    contentDiv.html(html);
                }
            },
            error: function() { /* ... error handling ... */ }
        });
        modal.show();
    }
</script>