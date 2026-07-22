@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')

    @php
        $context = $riskContexts->first();
        $status = $context ? $context->status : 'Draft';

        $user = auth()->user();
        $isRiskOfficer = ($user->level_id == 6 || is_null($user->level_id));
        $isRiskOwner = ($user->level_id == 7);

        $thClass = "table-light text-dark fw-semibold align-middle";
        $tdClass = "align-middle text-pre-wrap text-gray-700";
    @endphp

    <div class="row g-5 mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                <div class="card-header d-flex align-items-center gap-3 py-4">
                    <div class="bg-info-subtle p-2 rounded-4">
                        <div class="lead__icon">
                            <div class="svg-icon svg-icon-2x svg-icon-info">
                                @include('partials.icon-process')
                            </div>
                        </div>
                    </div>

                    <div class="d-block">
                        <h3 class="m-0">Proyek Risk Context</h3>
                        <div class="ff-preheading mb-0 mt-1 text-muted">
                            {{ $project->project_name }}
                        </div>
                    </div>

                    <div class="ms-auto d-flex align-items-center gap-3">
                        <div class="d-none d-md-block text-end">
                            @if($status == 'Draft')
                                <span class="badge bg-warning">Draft (Belum Diajukan)</span>
                            @elseif($status == 'Submitted' && $isRiskOwner)
                                <span class="badge bg-info">Menunggu Verifikasi</span>
                            @elseif($status == 'Revision')
                                <span class="badge bg-danger">Perlu Perbaikan</span>
                            @elseif($status == 'Verified')
                                <span class="badge bg-success">Terverifikasi</span>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            @if($isRiskOfficer)
                                @if($status == 'Draft' || $status == 'Revision')
                                    <a href="{{ route('project-risk-context.update-or-create', ['project_id' => $project->id]) }}" class="btn btn-sm d-flex justify-content-center align-items-center gap-2 btn-primary">
                                        <i class="bx bx-edit"></i> {{ $context ? 'Edit Data' : 'Isi Data' }}
                                    </a>

                                    @if($context)
                                    <form id="form-submit-context" action="{{ route('project-risk-context.submit', $context->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm d-flex justify-content-center align-items-center gap-2 btn-success">
                                            <i class="bx bx-send"></i> Ajukan
                                        </button>
                                    </form>
                                    @endif
                                @elseif($status == 'Submitted')
                                    <button class="btn btn-sm d-flex justify-content-center align-items-center gap-2 btn-outline-info" disabled>
                                        <i class="bx bx-time me-1"></i> Menunggu Verifikasi
                                    </button>
                                @endif
                            @endif

                            @if($isRiskOwner && $context)
                                @if($status == 'Submitted' || $status == 'Verified')
                                    <button type="button" class="btn btn-sm d-flex justify-content-center align-items-center gap-2 btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject">
                                        <i class="bx bx-x"></i> Revisi
                                    </button>
                                @endif

                                @if($status == 'Submitted')
                                    <form id="form-verify-context" action="{{ route('project-risk-context.verify', $context->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm d-flex justify-content-center align-items-center gap-2 btn-success">
                                            <i class="bx bx-check-double"></i> Setujui
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-lg-4">
                    {{-- ALERT REVISI --}}
                    @if($context && ($status == 'Revision' || $status == 'Draft') && $context->catatan_perbaikan)
                    <div class="alert alert-danger d-flex align-items-center mt-0 mb-4 border-dashed" role="alert">
                        <div class="svg-icon svg-icon-danger me-3">
                            @include('partials.icon-alert')
                        </div>
                        <div class="flex-1 text-break">
                            <strong>Catatan Perbaikan:</strong><br> {{ $context->catatan_perbaikan }}
                        </div>
                    </div>
                    @endif

                    {{-- INFO VERIFIKASI --}}
                    @if($context && $status == 'Verified' && $context->verified_at)
                        <div class="alert alert-success d-flex align-items-center mt-0 mb-4 border-success border-dashed bg-light-success" role="alert">
                            <i class="bx bx-check-circle fs-3 text-success me-3"></i>
                            <div class="flex-1">
                                <strong>Dokumen Terverifikasi</strong><br> oleh <span class="fw-bold">{{ $context->verifier->name ?? 'Risk Owner' }}</span> pada
                                {{ \Carbon\Carbon::parse($context->verified_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
                            </div>
                        </div>
                    @endif

                    @if(!$context)
                        <div class="text-center my-5">
                            <div class="fs-5 fw-bold text-gray-800">Data Belum Tersedia</div>
                            <p class="text-muted">Risk Context untuk proyek ini belum dibuat.</p>
                            @if($isRiskOfficer)
                                <a href="{{ route('project-risk-context.update-or-create', ['project_id' => $project->id]) }}" class="btn btn-primary mt-3">Mulai Isi Data</a>
                            @endif
                        </div>
                    @else

                        {{-- SECTION I --}}
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-3">I. Informasi Umum</h5>
                            <div class="table-responsive rounded border">
                                <table class="table table-bordered mb-0 align-middle">
                                    <tbody>
                                        <tr>
                                            <td class="{{ $thClass }} text-center" style="width: 5%;">1</td>
                                            <td class="{{ $thClass }}" style="width: 30%;">Nama Proyek</td>
                                            <td class="{{ $tdClass }} fw-bold">{{ $project->project_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">2</td>
                                            <td class="{{ $thClass }}">Nilai</td>
                                            <td class="{{ $tdClass }}">{{ $context->nilai ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">3</td>
                                            <td class="{{ $thClass }}">Pimpinan Tertinggi Proyek</td>
                                            <td class="{{ $tdClass }}">{{ $context->pimpinanTertinggi->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">4</td>
                                            <td class="{{ $thClass }}">Anggota</td>
                                            <td class="align-middle text-gray-700"> @if($context->members->count() > 0)
                                                    <ul class="mb-0 ps-3 text-gray-700">
                                                        @foreach($context->members as $member)
                                                            <li>
                                                                {{ $member->nama ?? $member->user->name ?? '-' }}
                                                                <span class="text-muted small fst-italic">({{ $member->jabatan->name ?? '-' }})</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">5</td>
                                            <td class="{{ $thClass }}">Sponsor</td>
                                            <td class="{{ $tdClass }}">{{ $context->sponsor ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- SECTION II --}}
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-3">II. Ruang Lingkup</h5>
                            <div class="table-responsive rounded border">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="{{ $thClass }} text-center" style="width: 5%;">6</td>
                                            <td class="{{ $thClass }}" style="width: 30%;">Deskripsi</td>
                                            <td class="{{ $tdClass }}">{{ $context->deskripsi ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">7</td>
                                            <td class="{{ $thClass }}">Tujuan</td>
                                            <td class="{{ $tdClass }}">{{ $context->tujuan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">8</td>
                                            <td class="{{ $thClass }}">Lingkup Pekerjaan</td>
                                            <td class="{{ $tdClass }}">{{ $context->lingkup_pekerjaan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">9</td>
                                            <td class="{{ $thClass }}">Pekerjaan di luar lingkup</td>
                                            <td class="{{ $tdClass }}">{{ $context->pekerjaan_luar_lingkup ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- SECTION III --}}
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-3">III. Konteks</h5>
                            <div class="table-responsive rounded border">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="{{ $thClass }} text-center" style="width: 5%;">10</td>
                                            <td class="{{ $thClass }}" style="width: 30%;">Sasaran</td>
                                            <td class="{{ $tdClass }}">{{ $context->sasaran ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">11</td>
                                            <td class="{{ $thClass }}">Batasan</td>
                                            <td class="{{ $tdClass }}">{{ $context->batasan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">12</td>
                                            <td class="{{ $thClass }}">Asumsi Dasar</td>
                                            <td class="{{ $tdClass }}">{{ $context->asumsi_dasar ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- SECTION IV --}}
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-3">IV. Stakeholder</h5>

                            <div class="mb-4">
                                <h6 class="fw-semibold mb-2">Stakeholder Internal</h6>
                                <div class="table-responsive rounded border">
                                    <table class="table table-bordered mb-0 align-middle">
                                        <thead class="table-light text-dark">
                                            <tr>
                                                <th>Nama Stakeholder</th>
                                                <th>Peran / Fungsi</th>
                                                <th>Komunikasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($riskContexts->first() && $riskContexts->first()->stakeholderInternals->count() > 0)
                                                @foreach($riskContexts->first()->stakeholderInternals as $internal)
                                                <tr>
                                                    <td class="{{ $tdClass }}">{{ $internal->stakeholder ?? '-' }}</td>
                                                    <td class="{{ $tdClass }}">{{ $internal->peran ?? '-' }}</td>
                                                    <td class="{{ $tdClass }}">{{ $internal->komunikasi ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">- Belum ada data -</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="fw-semibold mb-2">Stakeholder Eksternal</h6>
                                <div class="table-responsive rounded border">
                                    <table class="table table-bordered mb-0 align-middle">
                                        <thead class="table-light text-dark">
                                            <tr>
                                                <th>Nama Stakeholder</th>
                                                <th>Peran / Fungsi</th>
                                                <th>Komunikasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($riskContexts->first() && $riskContexts->first()->stakeholderExternals->count() > 0)
                                                @foreach($riskContexts->first()->stakeholderExternals as $external)
                                                <tr>
                                                    <td class="{{ $tdClass }}">{{ $external->stakeholder ?? '-' }}</td>
                                                    <td class="{{ $tdClass }}">{{ $external->peran ?? '-' }}</td>
                                                    <td class="{{ $tdClass }}">{{ $external->komunikasi ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">- Belum ada data -</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REVISI --}}
    @if($context && $isRiskOwner)
    <div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-revisi-context" action="{{ route('project-risk-context.reject', $context->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Revisi Dokumen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-4">
                        <div class="alert alert-warning d-flex align-items-center p-3 mb-3 mt-0">
                            <i class="bx bx-error-circle fs-3 text-warning me-3"></i>
                            <div class="text-dark">Status dokumen akan berubah menjadi <strong>Perlu Perbaikan</strong>.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold required">Catatan Perbaikan</label>
                            <textarea name="catatan_perbaikan" class="form-control" rows="4" required placeholder="Tuliskan instruksi perbaikan secara jelas..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btn-submit-revisi" class="btn btn-danger">
                            Kirim Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Handle Submit Risk Officer (Ajukan Verifikasi)
        $('#form-submit-context').on('submit', function(e) {
            e.preventDefault();
            let form = this;

            Swal.fire({
                title: 'Ajukan Verifikasi?',
                text: "Data akan dikirim ke Risk Owner untuk ditinjau.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // 2. Handle Verify Risk Owner (Setujui)
        $('#form-verify-context').on('submit', function(e) {
            e.preventDefault();
            let form = this;

            Swal.fire({
                title: 'Verifikasi Dokumen?',
                text: "Dokumen yang sudah disetujui akan berstatus Verified.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // 3. Handle Revisi Form Loading State
        $('#form-revisi-context').on('submit', function() {
            let btn = $('#btn-submit-revisi');
            let originalText = btn.html();

            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm me-2" style="width: 0.75rem; height: 0.75rem;"" role="status" aria-hidden="true"></span> Mengirim...');

            return true;
        });
    });
</script>
@endpush
