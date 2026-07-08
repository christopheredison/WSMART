@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')

    @php
        $context = $riskContexts->first();
        $status = $context ? $context->status : 'Draft';

        $user = auth()->user();
        $isRiskOfficer = ($user->level_id == 1);
        $isRiskOwner = ($user->level_id == 2);
        $canManageAsRiskOwner = $isRiskOwner && (
            (int) $user->unit_id === (int) $unit->id ||
            ($user->can('verification_mr') && optional($user->unit)->unit_mr == 1)
        );

        // Menyamakan styling dengan Project & Unit Context
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
                        <h3 class="m-0">Risk Context Anak Perusahaan</h3>
                        <div class="ff-preheading mb-0 mt-1 text-muted">
                            {{ $unit->name }}
                        </div>
                    </div>

                    <div class="ms-auto d-flex align-items-center gap-3">
                        <div class="d-none d-md-block text-end">
                            @if($status == 'Draft')
                                <span class="badge bg-warning">Draft</span>
                            @elseif($status == 'Submitted' && $isRiskOwner)
                                <span class="badge bg-info">Menunggu Verifikasi</span>
                            @elseif($status == 'Revision')
                                <span class="badge bg-danger">Perlu Perbaikan</span>
                            @elseif($status == 'Verified')
                                <span class="badge bg-success">Terverifikasi</span>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            {{-- RISK OFFICER (Level 1) --}}
                            @if($isRiskOfficer)
                                @if($status == 'Draft' || $status == 'Revision')
                                    <a href="{{ route('risk-context-anper.update-or-create', ['unit_id' => $unit->id, 'periode_id' => $periode->id]) }}" class="btn btn-sm d-flex align-items-center gap-2 btn-primary">
                                        <i class="bx bx-edit"></i> {{ $context ? 'Edit Data' : 'Isi Data' }}
                                    </a>

                                    @if($context)
                                    <form id="form-submit-context" action="{{ route('risk-context-anper.submit', $context->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm d-flex align-items-center gap-2 btn-success">
                                            <i class="bx bx-send"></i> Ajukan
                                        </button>
                                    </form>
                                    @endif
                                @elseif($status == 'Submitted')
                                    <button class="btn btn-sm d-flex align-items-center gap-2 btn-outline-info" disabled>
                                        <i class="bx bx-time me-1"></i> Menunggu Verifikasi
                                    </button>
                                @endif
                            @endif

                            {{-- RISK OWNER (Level 2) --}}
                            @if($canManageAsRiskOwner && $context)
                                @if($status == 'Submitted' || $status == 'Verified')
                                    <button type="button" class="btn btn-sm d-flex align-items-center gap-2 btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject">
                                        <i class="bx bx-x"></i> Revisi
                                    </button>
                                @endif

                                @if($status == 'Submitted')
                                    <form id="form-verify-context" action="{{ route('risk-context-anper.verify', $context->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm d-flex align-items-center gap-2 btn-success">
                                            <i class="bx bx-check-double"></i> Setujui
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-lg-4">
                    {{-- Alert Revisi --}}
                    @if($context && $status == 'Revision' && $context->catatan_perbaikan)
                    <div class="alert alert-danger d-flex align-items-center mt-0 mb-4 border-dashed" role="alert">
                        <div class="svg-icon svg-icon-danger me-3">
                            @include('partials.icon-alert')
                        </div>
                        <div class="flex-1 text-break">
                            <strong>Catatan Perbaikan:</strong><br> {{ $context->catatan_perbaikan }}
                        </div>
                    </div>
                    @endif

                    {{-- Alert Verifikasi --}}
                    @if($context && $status == 'Verified' && $context->verified_at)
                        <div class="alert alert-success d-flex align-items-center mt-0 mb-4 border-success border-dashed bg-light-success" role="alert">
                            <i class="bx bx-check-circle fs-3 text-success me-3"></i>
                            <div class="flex-1">
                                <strong>Dokumen Terverifikasi</strong><br> oleh <span class="fw-bold">{{ $context->verifier->name ?? 'Risk Owner' }}</span> pada
                                {{ \Carbon\Carbon::parse($context->verified_at)->translatedFormat('d F Y, H:i') }}
                            </div>
                        </div>
                    @endif

                    @if(!$context)
                        <div class="text-center my-5">
                            <div class="fs-5 fw-bold text-gray-800">Data Belum Tersedia</div>
                            <p class="text-muted">Risk Context belum dibuat.</p>
                            @if($isRiskOfficer)
                                <a href="{{ route('risk-context-anper.update-or-create', ['unit_id' => $unit->id, 'periode_id' => $periode->id]) }}" class="btn btn-primary mt-3">Mulai Isi Data</a>
                            @endif
                        </div>
                    @else

                        {{-- I. INFORMASI UMUM --}}
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-3">I. Informasi Umum</h5>
                            <div class="table-responsive rounded border">
                                <table class="table table-bordered mb-0 align-middle">
                                    <tbody>
                                        <tr>
                                            <td class="{{ $thClass }} text-center" style="width: 5%;">1</td>
                                            <td class="{{ $thClass }}" style="width: 30%;">Nama Anak Perusahaan</td>
                                            <td class="{{ $tdClass }} fw-bold">{{ $unit->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">2</td>
                                            <td class="{{ $thClass }}">Nilai</td>
                                            <td class="{{ $tdClass }}">{{ $context->nilai ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">3</td>
                                            <td class="{{ $thClass }}">Pimpinan Tertinggi</td>
                                            <td class="{{ $tdClass }}">{{ $context->pimpinanTertinggi->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="{{ $thClass }} text-center">4</td>
                                            <td class="{{ $thClass }}">Anggota</td>
                                            {{-- Hapus $tdClass di sini agar list tidak rata tengah --}}
                                            <td class="align-middle text-gray-700">
                                                @if($context->members->count() > 0)
                                                    <ul class="mb-0 ps-3 text-gray-700">
                                                        @foreach($context->members as $member)
                                                            <li>
                                                                {{ $member->nama }}
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

                        {{-- II. RUANG LINGKUP --}}
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-3">II. Ruang Lingkup</h5>
                            <div class="table-responsive rounded border">
                                <table class="table table-bordered mb-0 align-middle">
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
                                            <td class="{{ $thClass }}">Pekerjaan Luar Lingkup</td>
                                            <td class="{{ $tdClass }}">{{ $context->pekerjaan_luar_lingkup ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- III. KONTEKS --}}
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-3">III. Konteks</h5>
                            <div class="table-responsive rounded border">
                                <table class="table table-bordered mb-0 align-middle">
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

                        {{-- IV. STAKEHOLDER --}}
                        <div class="mb-5">
                            <h5 class="text-dark fw-bold mb-3">IV. Stakeholder</h5>

                            <div class="mb-4">
                                <h6 class="fw-semibold mb-2">Stakeholder Internal</h6>
                                <div class="table-responsive rounded border">
                                    <table class="table table-bordered mb-0 align-middle">
                                        <thead class="table-light text-dark fw-semibold">
                                            <tr>
                                                <th style="width: 30%">Nama</th>
                                                <th style="width: 30%">Peran</th>
                                                <th style="width: 40%">Komunikasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($context->stakeholderInternals as $internal)
                                                <tr>
                                                    <td class="{{ $tdClass }}">{{ $internal->stakeholder }}</td>
                                                    <td class="{{ $tdClass }}">{{ $internal->peran }}</td>
                                                    <td class="{{ $tdClass }}">{{ $internal->komunikasi }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted py-3">- Belum ada data -</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="fw-semibold mb-2">Stakeholder Eksternal</h6>
                                <div class="table-responsive rounded border">
                                    <table class="table table-bordered mb-0 align-middle">
                                        <thead class="table-light text-dark fw-semibold">
                                            <tr>
                                                <th style="width: 30%">Nama</th>
                                                <th style="width: 30%">Peran</th>
                                                <th style="width: 40%">Komunikasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($context->stakeholderExternals as $external)
                                                <tr>
                                                    <td class="{{ $tdClass }}">{{ $external->stakeholder }}</td>
                                                    <td class="{{ $tdClass }}">{{ $external->peran }}</td>
                                                    <td class="{{ $tdClass }}">{{ $external->komunikasi }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center text-muted py-3">- Belum ada data -</td></tr>
                                            @endforelse
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
    @if($context && $canManageAsRiskOwner)
    <div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="form-revisi-context" action="{{ route('risk-context-anper.reject', $context->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Revisi Dokumen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-4">
                        <div class="alert alert-warning p-3 mb-4 d-flex align-items-center border-dashed">
                            <i class="bx bx-error-circle fs-3 text-warning me-3"></i>
                            <div>Status dokumen akan berubah menjadi <strong>Perlu Perbaikan</strong>.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold required">Catatan Perbaikan</label>
                            <textarea name="catatan_perbaikan" class="form-control" rows="4" required placeholder="Tuliskan detail perbaikan yang diperlukan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btn-submit-revisi" class="btn btn-danger">
                            <i class="bx bx-send me-1"></i> Kirim Revisi
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
        // 1. Submit
        $('#form-submit-context').on('submit', function(e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: 'Ajukan Verifikasi?',
                text: "Data akan dikirim ke Risk Owner.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });

        // 2. Verify
        $('#form-verify-context').on('submit', function(e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: 'Setujui Dokumen?',
                text: "Dokumen akan berstatus Verified.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });

        // 3. Loading Revisi
        $('#form-revisi-context').on('submit', function() {
            let btn = $('#btn-submit-revisi');
            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm me-2" style="width: 0.75rem; height: 0.75rem;" role="status" aria-hidden="true"></span> Mengirim...');
            return true;
        });
    });
</script>
@endpush
