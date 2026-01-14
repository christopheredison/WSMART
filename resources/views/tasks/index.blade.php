@extends('layouts.default')

@section('dashboard')
<div class="container-fluid px-4 mt-4">
    {{-- 1. HEADER HALAMAN --}}
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div class="">
            <h2 class="h3 mb-0 text-gray-800 fw-bold">Task Approval</h2>
            <p class="text-muted small">Ringkasan status persetujuan dan monitoring risiko.</p>
        </div>

        {{-- Summary Badges --}}
        <div class="bg-white p-2 rounded shadow-sm">
            {{-- URGENT TOOLTIP --}}
            <span class="badge bg-danger rounded-pill me-1" id="total-urgent-badge">0</span>
            <span class="text-danger fw-bold me-3" style="cursor: help;"
                  data-bs-toggle="tooltip" data-bs-placement="bottom"
                  title="Prioritas Tinggi: Berisi risiko yang DITOLAK atau REVISI. Wajib segera diperbaiki agar proses bisa berlanjut.">
                Urgent <i class="bx bx-info-circle" style="font-size: 0.8rem;"></i>
            </span>

            {{-- PENDING TOOLTIP --}}
            <span class="badge bg-warning text-dark rounded-pill me-1" id="total-warning-badge">0</span>
            <span class="text-dark fw-bold" style="cursor: help;"
                  data-bs-toggle="tooltip" data-bs-placement="bottom"
                  title="Menunggu Aksi: Berisi risiko DRAFT yang belum dikirim atau risiko masuk yang menunggu VERIFIKASI Anda.">
                Pending <i class="bx bx-info-circle" style="font-size: 0.8rem;"></i>
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 text-white"
                style="background: linear-gradient(135deg, #d35400 0%, #e67e22 100%);">
                <div class="card-body p-4 position-relative overflow-hidden">
                    <h3 class="fw-bold mb-4">Project</h3>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center">
                            <span class="fw-bold me-2">{{ $stats['project']['total'] }}</span>
                            <span class="opacity-75">Total Project</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold me-2">{{ count($pendingItems) }}</span>
                            <a href="#" class="text-white text-decoration-underline {{ count($pendingItems) > 0 ? '' : 'opacity-50 text-decoration-none pe-none' }}"
                              @if(count($pendingItems) > 0) data-bs-toggle="modal" data-bs-target="#modalPendingItems" @endif>
                              Menunggu Persetujuan
                            </a>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold me-2">{{ $stats['project']['approved'] }}</span>
                            <span class="opacity-75">Disetujui</span>
                        </div>
                    </div>
                    <i class="bx bx-briefcase-alt-2 position-absolute" style="bottom: -10px; right: 15px; font-size: 5rem; opacity: 0.15;"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 text-white"
                style="background: linear-gradient(135deg, #0f509e 0%, #136a8a 100%);">
                <div class="card-body p-4 position-relative overflow-hidden">
                    <h3 class="fw-bold mb-4">Divisi</h3>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center">
                            <span class="fw-bold me-2">{{ $divisiStats['total'] }}</span>
                            <span class="opacity-75">Total Divisi</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold me-2">{{ $divisiStats['pending'] }}</span>
                            <span class="opacity-100">Menunggu Tindakan</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold me-2">{{ $divisiStats['approved'] }}</span>
                            <span class="opacity-75">Selesai</span>
                        </div>
                    </div>
                    <i class="bx bx-buildings position-absolute" style="bottom: -10px; right: 15px; font-size: 5rem; opacity: 0.15;"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('tasks.index') }}" method="GET">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-muted fw-bold x-small mb-0">
                        <i class="bx bx-filter-alt"></i> Filter Data
                    </h6>
                </div>

                <div class="row align-items-end g-3">
                    <div class="col-md-auto">
                        <label class="form-label small text-muted mb-1">Scope Tampilan:</label>
                        <div class="bg-light rounded p-2 border d-flex gap-3">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input cursor-pointer" type="radio" name="scope" id="scopeAll" value="all"
                                    {{ request('scope', 'all') == 'all' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small cursor-pointer" for="scopeAll">Semua</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input cursor-pointer" type="radio" name="scope" id="scopeProject" value="project"
                                    {{ request('scope') == 'project' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small cursor-pointer" for="scopeProject">Project</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input cursor-pointer" type="radio" name="scope" id="scopeDivisi" value="divisi"
                                    {{ request('scope') == 'divisi' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small cursor-pointer" for="scopeDivisi">Divisi</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md">
                        <label class="form-label small text-muted mb-1">Pencarian:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama project atau kode..." value="{{ request('q') }}">
                            <button class="btn btn-primary px-4" type="submit">Cari</button>

                            @if(request('q') || request('scope', 'all') != 'all')
                                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Reset Filter">
                                    <i class="bx bx-refresh"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- LIST PROJECT --}}
    <div class="row">
        @forelse($taskList as $task)
            @php
                $cardBorder = 'border-success';
                $iconBg = 'bg-success-subtle text-success';
                $mainIcon = 'bx-check-shield';
                $badgeHtml = '';

                if($task['status_category'] == 'urgent') {
                    $cardBorder = 'border-danger';
                    $iconBg = 'bg-danger-subtle text-danger';
                    $mainIcon = 'bx-error-circle';
                    $badgeHtml = '<span class="badge bg-danger shadow-sm"><i class="bx bx-error"></i> Urgent</span>';
                } elseif($task['status_category'] == 'pending') {
                    $cardBorder = 'border-warning';
                    $iconBg = 'bg-warning-subtle text-warning';
                    $mainIcon = 'bx-time-five';
                    $badgeHtml = '<span class="badge bg-warning text-dark shadow-sm"><i class="bx bx-time"></i> Pending</span>';
                }
            @endphp

        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm hover-shadow transition-all border-start border-4 {{ $cardBorder }}">
                <div class="card-body p-4 position-relative">

                    <div class="position-absolute top-0 end-0 mt-3 me-3">
                        {!! $badgeHtml !!}
                    </div>

                    {{-- HEADER --}}
                    <div class="d-flex justify-content-between align-items-start mb-4 pe-5">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3 flex-shrink-0 {{ $iconBg }}">
                                <i class="bx {{ $mainIcon }} fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $task['project_name'] }}</h5>
                                <small class="text-muted"><i class="bx bx-building"></i> {{ $task['unit_name'] }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- STEPPER --}}
                    <div class="position-relative px-2 px-md-3 mb-4 pt-2 pb-3 border-bottom border-light">
                        <div class="progress" style="height: 2px; position: absolute; top: 15px; left: 5%; right: 5%; z-index: 0;">
                            <div class="progress-bar bg-light" role="progressbar" style="width: 100%;"></div>
                        </div>
                        @php
                            $progressPercent = ($task['current_step_index'] - 1) * 25;
                            if ($progressPercent > 100) $progressPercent = 100;
                            if ($task['is_revision']) $progressPercent -= 25;
                            if ($progressPercent < 0) $progressPercent = 0;
                        @endphp
                        <div class="progress" style="height: 2px; position: absolute; top: 15px; left: 5%; right: 5%; z-index: 0; background: transparent;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between position-relative" style="z-index: 1;">
                            @foreach($task['steps'] as $step)
                                @php
                                    $sIcon = 'bx-circle'; $sClass = 'bg-white border-secondary text-muted';
                                    if ($step['status'] == 'completed') { $sIcon = 'bx-check'; $sClass = 'bg-success text-white border-success'; }
                                    elseif ($step['status'] == 'current') { $sIcon = 'bx-loader-alt bx-spin'; $sClass = 'bg-primary text-white border-primary shadow-sm'; }
                                    elseif ($step['status'] == 'rejected') { $sIcon = 'bx-x'; $sClass = 'bg-danger text-white border-danger shadow-sm'; }
                                @endphp
                                <div class="text-center" style="width: 18%;">
                                    <div class="rounded-circle border border-2 d-flex align-items-center justify-content-center mx-auto mb-1 {{ $sClass }}"
                                        style="width: 24px; height: 24px;" data-bs-toggle="tooltip" title="{{ $step['role'] }}">
                                        <i class="bx {{ $sIcon }}" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div class="d-none d-md-block" style="line-height: 1;">
                                        <small class="fw-bold {{ $step['status'] == 'current' ? 'text-primary' : 'text-muted' }}" style="font-size: 0.65rem;">{{ $step['label'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- DETAIL STATS (DIKEMBALIKAN KE TAMPILAN AWAL) --}}
                    <div class="row align-items-start g-4">

                        {{-- 1. RISK REGISTER DETAIL --}}
                        <div class="col-md-5 border-end-md">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-uppercase text-muted fw-bold x-small">Risk Register</small>
                                <small class="text-muted">{{ $task['risk_stats']['total'] }} Total Risiko</small>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-2">
                                {{-- Status Badges --}}
                                @if($task['risk_stats']['published'] > 0)
                                    <span class="badge bg-success-subtle text-success" title="Published">{{ $task['risk_stats']['published'] }} Publish</span>
                                @endif
                                @if($task['risk_stats']['pending'] > 0)
                                    <span class="badge bg-warning-subtle text-warning" title="Verifikasi">{{ $task['risk_stats']['pending'] }} Verifikasi</span>
                                @endif
                                @if($task['risk_stats']['revision'] > 0)
                                    <span class="badge bg-danger-subtle text-danger" title="Revisi">{{ $task['risk_stats']['revision'] }} Revisi</span>
                                @endif
                                @if($task['risk_stats']['draft'] > 0)
                                    <span class="badge bg-info-subtle text-info" title="Draft">{{ $task['risk_stats']['draft'] }} Draft</span>
                                @endif
                                @if($task['risk_stats']['rejected_mr'] > 0)
                                    <span class="badge bg-danger-subtle text-danger" title="Ditolak">{{ $task['risk_stats']['rejected_mr'] }} Ditolak</span>
                                @endif

                                @if($task['risk_stats']['total'] == 0)
                                    <span class="badge bg-light text-muted">Belum ada data</span>
                                @endif
                            </div>

                            {{-- Progress Bar --}}
                            <div class="progress" style="height: 6px;">
                                @php
                                    $total = $task['risk_stats']['total'] > 0 ? $task['risk_stats']['total'] : 1;
                                    $percent = ($task['risk_stats']['published'] / $total) * 100;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                            </div>

                            @if($task['risk_action']['count'] > 0)
                                <div class="mt-2 text-end">
                                    <small class="text-danger fw-bold animate-pulse">
                                        <i class="bx bx-error-circle"></i> {{ $task['risk_action']['label'] }}
                                    </small>
                                </div>
                            @endif
                        </div>

                        {{-- 2. MONITORING DETAIL (LIST SEMUA BULAN) --}}
                        <div class="col-md-7 ps-md-4">
                            <small class="text-uppercase text-muted fw-bold x-small mb-2 d-block">Monitoring ({{ date('Y') }})</small>

                            <div class="d-flex flex-wrap gap-2">
                                @php $hasMonData = false; @endphp
                                @foreach($task['monitoring_summary'] as $q => $months)
                                    @foreach($months as $mon)
                                        @if($mon['status'] !== 'empty')
                                            @php
                                                $hasMonData = true;
                                                // Default Process (Biru Muda/Abu)
                                                $colorClass = 'bg-info-subtle text-info-emphasis';

                                                if($mon['status'] == 'pending') {
                                                    // Menunggu Verifikasi (Kuning)
                                                    $colorClass = 'bg-warning text-dark border border-warning';
                                                }
                                                elseif($mon['status'] == 'revision') {
                                                    // Revisi (Merah)
                                                    $colorClass = 'bg-danger text-white animate-pulse';
                                                }
                                                elseif($mon['status'] == 'draft') {
                                                    // Draft / Belum Lengkap (Biru Tua / Primary) - Action Needed
                                                    $colorClass = 'bg-primary text-white';
                                                }
                                                elseif($mon['status'] == 'process') {
                                                    // Sudah dikirim / Draft Masa Depan (Info)
                                                    $colorClass = 'bg-info-subtle text-info border border-info-subtle';
                                                }
                                            @endphp
                                            <a href="{{ $mon['link'] }}" class="text-decoration-none" data-bs-toggle="tooltip"
                                              title="Q{{$q}} - {{ $mon['month_name'] }}: {{ ucfirst($mon['status']) }} ({{ $mon['count'] }} Item)">
                                                <span class="badge {{ $colorClass }} p-2">
                                                    {{ $mon['month_name'] }}
                                                    @if($mon['status'] == 'pending' || $mon['status'] == 'revision')
                                                        <span class="bx bxs-circle text-danger ms-1" style="font-size: 6px; vertical-align: middle;"></span>
                                                    @endif
                                                    @if($mon['status'] == 'draft')
                                                        <span class="bx bxs-circle text-warning ms-1" style="font-size: 6px; vertical-align: middle;"></span>
                                                    @endif
                                                </span>
                                            </a>
                                        @endif
                                    @endforeach
                                @endforeach

                                @if(!$hasMonData)
                                    <div class="badge bg-light text-muted fw-normal border border-dashed p-2">Belum ada monitoring</div>
                                @endif
                            </div>

                            @if($task['monitoring_action_count'] > 0)
                                <div class="mt-2">
                                    <small class="text-danger fw-bold">
                                        <i class="bx bx-error"></i> {{ $task['monitoring_action_count'] }} monitoring perlu tindakan
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- TOMBOL ACTION DI BAWAH --}}
                    <div class="text-end mt-3 border-top pt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4"
                            onclick="showProjectDetail({{ json_encode($task) }})">
                            Detail Lengkap <span class="bx bx-chevron-right"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <h5 class="mt-3 text-muted">Tidak ada data ditemukan.</h5>
            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Reset Filter</a>
        </div>
        @endforelse
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalTaskDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalProjectName">Detail Proyek</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Section Risk Register --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-primary mb-0">
                          {{-- <i class="bx bx-shield-quarter"></i> --}}
                          Identifikasi & Penilaian Risiko
                        </h6>
                        <a href="#" id="linkRiskRegister" class="btn btn-primary btn-sm">Buka Halaman Risiko</a>
                    </div>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-success-subtle rounded text-center">
                                <h4 class="mb-0 fw-bold text-success" id="countPublish">0</h4>
                                <small class="text-muted">Published</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-warning-subtle rounded text-center">
                                <h4 class="mb-0 fw-bold text-warning" id="countPending">0</h4>
                                <small class="text-muted">Verifikasi</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-danger-subtle rounded text-center">
                                <h4 class="mb-0 fw-bold text-danger" id="countRevisi">0</h4>
                                <small class="text-muted">Revisi</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <h4 class="mb-0 fw-bold text-dark" id="countDraft">0</h4>
                                <small class="text-muted">Draft</small>
                            </div>
                        </div>
                    </div>
                    <div id="riskAlertBox" class="alert alert-warning mt-3 d-none">
                        <i class="bx bx-bell"></i> <span id="riskAlertText"></span>
                    </div>
                </div>

                <hr class="text-muted">

                {{-- Section Monitoring --}}
                <div>
                    <h6 class="fw-bold text-info mb-3">
                      {{-- <i class="bx bx-bar-chart-alt-2"></i>  --}}
                      Monitoring Risiko (Per Quarter)
                    </h6>
                    <div class="list-group" id="monitoringList">
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPendingItems" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow p-0">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bx bx-time-five me-2"></i>Menunggu Persetujuan Anda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
                @if(count($pendingItems) > 0)
                    @php
                        $riskItems = collect($pendingItems)->where('type', 'Risk Register');
                        $monitoringItems = collect($pendingItems)->filter(function($item) {
                            return \Illuminate\Support\Str::contains($item['type'], 'Monitoring');
                        });
                    @endphp

                    <div class="accordion accordion-flush" id="accordionPendingItems">
                        {{-- 1. RISK REGISTER SECTION --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingRisk">
                                <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRisk" aria-expanded="true" aria-controls="collapseRisk">
                                    <i class="bx bx-shield-quarter me-2 text-primary"></i> Risk Register
                                    <span class="badge bg-danger ms-2">{{ $riskItems->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseRisk" class="accordion-collapse collapse show" aria-labelledby="headingRisk" data-bs-parent="#accordionPendingItems">
                                <div class="accordion-body p-0">
                                    @if($riskItems->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($riskItems as $item)
                                                <a href="{{ $item['link'] }}" class="list-group-item list-group-item-action p-3">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm me-3 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 40px; height: 40px;">
                                                                <i class="bx bx-shield-quarter fs-4"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-bold text-black">{{ $item['project_name'] }}</h6>
                                                                <small class="text-black d-block">
                                                                    <i class="bx bx-building"></i> {{ $item['unit_name'] }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-warning text-dark mb-1">{{ $item['description'] }}</span>
                                                            <small class="text-danger d-block fw-bold">{{ $item['count'] }}</small>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-3 text-center text-black small">Tidak ada item Risk Register yang menunggu.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- 2. MONITORING SECTION --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingMon">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMon" aria-expanded="false" aria-controls="collapseMon">
                                    <i class="bx bx-bar-chart-alt-2 me-2 text-info"></i> Monitoring
                                    <span class="badge bg-danger ms-2">{{ $monitoringItems->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseMon" class="accordion-collapse collapse" aria-labelledby="headingMon" data-bs-parent="#accordionPendingItems">
                                <div class="accordion-body p-0">
                                    @if($monitoringItems->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($monitoringItems as $item)
                                                <a href="{{ $item['link'] }}" class="list-group-item list-group-item-action p-3">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm me-3 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 40px; height: 40px;">
                                                                <i class="bx bx-bar-chart-alt-2 fs-4"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-bold text-black">{{ $item['project_name'] }}</h6>
                                                                <small class="text-black d-block">
                                                                    <i class="bx bx-building"></i> {{ $item['unit_name'] }} &bullet;
                                                                    <span class="text-primary fw-semibold">{{ $item['type'] }}</span>
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-warning text-dark mb-1">{{ $item['description'] }}</span>
                                                            <small class="text-danger d-block fw-bold">{{ $item['count'] }}</small>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-3 text-center text-black small">Tidak ada item Monitoring yang menunggu.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <img src="{{ asset('images/illustrations/empty.svg') }}" alt="Empty" style="height: 100px; opacity: 0.5;" class="mb-3">
                        <h6 class="text-black">Tidak ada item yang menunggu persetujuan Anda saat ini.</h6>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-circle {
        width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    }
    .hover-shadow:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .transition-all { transition: all 0.3s ease; }
    .x-small { font-size: 0.7rem; letter-spacing: 0.5px; }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
    .animate-pulse { animation: pulse 2s infinite; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init Tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // 1. Hitung Total Badge
        let totalUrgent = 0;
        let totalPending = {{ count($pendingItems) }};

        @foreach($taskList as $t)
            @if($t['status_category'] == 'urgent')
                totalUrgent++;
            @endif
        @endforeach

        // 2. Update Elemen HTML
        const uBadge = document.getElementById('total-urgent-badge');
        const pBadge = document.getElementById('total-warning-badge');

        uBadge.innerText = totalUrgent;
        pBadge.innerText = totalPending;
    });

    function showProjectDetail(data) {
        // 1. Set Header & Risk Data
        $('#modalProjectName').text(data.project_name);
        $('#linkRiskRegister').attr('href', data.risk_action.link);

        $('#countPublish').text(data.risk_stats.published);
        $('#countPending').text(data.risk_stats.pending);
        $('#countRevisi').text(data.risk_stats.revision + data.risk_stats.rejected_mr);
        $('#countDraft').text(data.risk_stats.draft);

        const riskAlert = $('#riskAlertBox');
        if(data.risk_action.count > 0) {
            riskAlert.removeClass('d-none');
            $('#riskAlertText').html(`<strong>Perhatian:</strong> Ada ${data.risk_action.count} item di Risk Register yang statusnya: <u>${data.risk_action.label}</u>.`);
        } else {
            riskAlert.addClass('d-none');
        }

        // 2. Set Monitoring List (UPDATED FOR MONTHS)
        const monList = $('#monitoringList');
        monList.empty();

        const summary = data.monitoring_summary;
        let hasMonData = false;

        Object.keys(summary).forEach(q => {
            const months = summary[q];
            const activeMonths = months.filter(m => m.status !== 'empty');

            if (activeMonths.length > 0) {
                hasMonData = true;
                monList.append(`<div class="list-group-item bg-light fw-bold py-1 text-uppercase small text-muted">Quarter ${q}</div>`);

                activeMonths.forEach(item => {
                    let badgeClass = 'bg-info-subtle text-info';
                    let badgeText = 'Proses';
                    let btnClass = 'btn-outline-primary';
                    let btnText = 'Lihat';

                    if(item.status == 'process') { badgeClass = 'bg-info-subtle text-dark'; badgeText = 'Proses/Selesai'; }
                    if(item.status == 'draft') {
                        badgeClass = 'bg-primary'; badgeText = 'Perlu Input';
                        btnClass = 'btn-primary'; btnText = 'Input';
                    }
                    if(item.status == 'pending') {
                        badgeClass = 'bg-warning text-dark'; badgeText = 'Verifikasi';
                        btnClass = 'btn-warning'; btnText = 'Verifikasi';
                    }
                    if(item.status == 'revision') {
                        badgeClass = 'bg-danger'; badgeText = 'Revisi';
                        btnClass = 'btn-danger'; btnText = 'Perbaiki';
                    }

                    const html = `
                        <div class="list-group-item d-flex justify-content-between align-items-center ps-4">
                            <div>
                                <span class="fw-bold text-dark">${item.month_name}</span>
                                <span class="badge ${badgeClass} ms-2">${badgeText}</span>
                                <div class="small text-muted mt-1">${item.count} risiko terkait</div>
                            </div>
                            <a href="${item.link}" class="btn btn-sm ${btnClass} rounded-pill px-3">${btnText}</a>
                        </div>
                    `;
                    monList.append(html);
                });
            }
        });

        if (!hasMonData) {
            monList.append(`<div class="text-center py-3 text-muted">Belum ada data monitoring untuk proyek ini.</div>`);
        }

        // Show Modal
        const modal = new bootstrap.Modal(document.getElementById('modalTaskDetail'));
        modal.show();
    }
</script>
@endpush
