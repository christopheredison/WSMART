{{-- MODAL PENDING ITEMS PROJECT --}}
<div class="modal fade" id="modalPendingProject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow p-0">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title fw-bold text-dark"><i class="bx bx-time-five me-2"></i>Menunggu Persetujuan (Proyek)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
                @if(isset($pendingItemsProject) && count($pendingItemsProject) > 0)
                    @include('tasks._list_items', ['items' => $pendingItemsProject])
                @else
                    <div class="p-5 text-center text-black">
                        <i class="bx bx-check-circle fs-1 text-success mb-2"></i>
                        <p class="mb-0">Tidak ada proyek yang menunggu persetujuan Anda.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PENDING ITEMS DIVISI --}}
<div class="modal fade" id="modalPendingDivisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow p-0">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title fw-bold text-dark"><i class="bx bx-time-five me-2"></i>Menunggu Persetujuan (Divisi)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
                @if(isset($pendingItemsDivisi) && count($pendingItemsDivisi) > 0)
                    @include('tasks._list_items', ['items' => $pendingItemsDivisi])
                @else
                    <div class="p-5 text-center text-black">
                        <i class="bx bx-check-circle fs-1 text-success mb-2"></i>
                        <p class="mb-0">Tidak ada divisi yang menunggu persetujuan Anda.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL TASK --}}
<div class="modal fade" id="modalTaskDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalProjectName">Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Section Risk Register --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-primary mb-0">Identifikasi & Penilaian Risiko</h6>
                        <a href="#" id="linkRiskRegister" class="btn btn-primary btn-sm">Buka Halaman Risiko</a>
                    </div>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-success-subtle rounded text-center">
                                <h4 class="mb-0 fw-bold text-success" id="countPublish">0</h4>
                                <small class="text-black">Published</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-warning-subtle rounded text-center">
                                <h4 class="mb-0 fw-bold text-warning" id="countPending">0</h4>
                                <small class="text-black">Verifikasi</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-danger-subtle rounded text-center">
                                <h4 class="mb-0 fw-bold text-danger" id="countRevisi">0</h4>
                                <small class="text-black">Revisi</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <h4 class="mb-0 fw-bold text-dark" id="countDraft">0</h4>
                                <small class="text-black">Draft</small>
                            </div>
                        </div>
                    </div>
                    <div id="riskAlertBox" class="alert alert-warning mt-3 d-none">
                        <i class="bx bx-bell"></i> <span id="riskAlertText"></span>
                    </div>
                </div>

                <hr class="text-black">

                {{-- Section Monitoring --}}
                <div>
                    <h6 class="fw-bold text-info mb-3">Monitoring Risiko (Per Quarter)</h6>
                    <div class="list-group" id="monitoringList"></div>
                </div>
            </div>
        </div>
    </div>
</div>
