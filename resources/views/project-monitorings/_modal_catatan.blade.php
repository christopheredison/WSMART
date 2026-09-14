@include('partials.risk-note-helpers')
<div class="modal fade" id="modalCatatan" tabindex="-1" aria-labelledby="modalCatatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCatatanLabel">
                    <i class="fas fa-history me-2"></i>Riwayat Catatan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div id="catatan-content">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showCatatanModal(riskId) {
        const modalElement = document.getElementById('modalCatatan');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

        const contentDiv = $('#catatan-content');

        contentDiv.html(`
            <div class="d-flex justify-content-center my-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Memuat...</span>
                </div>
            </div>
        `);

        const url = "{{ route('projects.monitorings.notes', ['project' => request()->route('project'), 'riskId' => ':id']) }}".replace(':id', riskId);

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                quarter: $('#table-filter select[name="quarter"]').val(),
                tahun: $('#table-filter select[name="tahun"]').val(),
                month: $('#table-filter select[name="month"]').val(),
            },
            success: function(notes) {
                if (notes.length === 0) {
                    contentDiv.html(`
                        <div class="text-center my-4">
                            <i class="fas fa-comment-slash fa-2x text-muted mb-2"></i>
                            <p>Belum ada catatan untuk risiko ini pada periode yang dipilih.</p>
                        </div>
                    `);
                } else {
                    let html = '';
                    notes.forEach(note => {
                        const statusBadge = window.RiskNoteHelpers.statusBadge(note.status);
                        const formattedDate = window.RiskNoteHelpers.formatDateWib(note.created_at);

                        html += `
                        <div class="card mb-3 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                <div class="fw-bold">
                                    ${note.user.name}
                                </div>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-3">${formattedDate}</small>
                                    ${statusBadge}
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text mb-0">${note.notes || '<i>Tidak ada catatan.</i>'}</p>
                            </div>
                        </div>
                        `;
                    });
                    contentDiv.html(html);
                }
                modal.show();
            },
            error: function() {
                contentDiv.html(`
                    <div class="text-center my-4 text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Gagal memuat catatan.</p>
                    </div>
                `);
                modal.show();
            }
        });
    }
</script>
