@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')

<div class="row mb-5">
    <div class="col-12 d-flex align-items-center gap-3 position-relative">
        <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
        </div>
        <h3 class="mb-0">Risk Context - {{ $project->project_name }}</h3>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="bx bx-list-ul mr-2"></i>Risk Context
                </h3>
                <a href="{{ route('risk-context.update-or-create') }}" class="btn btn-primary">
                    <i class="bx bx-edit"></i> Update Risk Context
                </a>
            </div>
            <div class="card-body">
                <!-- I. Informasi Umum -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">I. Informasi Umum</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" style="width: 5%">1</td>
                                        <td class="fw-bold" style="width: 25%">Nama Proyek</td>
                                        <td>{{ $riskContexts->first()->project->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">2</td>
                                        <td class="fw-bold">Nilai</td>
                                        <td>{{ $riskContexts->first()->nilai ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">3</td>
                                        <td class="fw-bold">Pimpinan Tertinggi Proyek</td>
                                        <td>{{ $riskContexts->first()->pimpinanTertinggi->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">4</td>
                                        <td class="fw-bold">Anggota Proyek</td>
                                        <td>
                                            @if($riskContexts->first() && $riskContexts->first()->members->count() > 0)
                                                @foreach($riskContexts->first()->members as $member)
                                                    {{ $member->jabatan->name ?? '-' }}@if(!$loop->last), @endif
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">5</td>
                                        <td class="fw-bold">Sponsor</td>
                                        <td>{{ $riskContexts->first()->sponsor ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- II. Ruang Lingkup -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">II. Ruang Lingkup</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" style="width: 5%">6</td>
                                        <td class="fw-bold" style="width: 25%">Deskripsi</td>
                                        <td>{{ $riskContexts->first()->deskripsi ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">7</td>
                                        <td class="fw-bold">Tujuan</td>
                                        <td class="text-pre-wrap">{{ $riskContexts->first()->tujuan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">8</td>
                                        <td class="fw-bold">Lingkup Pekerjaan</td>
                                        <td>{{ $riskContexts->first()->lingkup_pekerjaan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">9</td>
                                        <td class="fw-bold">Pekerjaan di luar lingkup</td>
                                        <td>{{ $riskContexts->first()->pekerjaan_luar_lingkup ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- III. Konteks -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">III. Konteks</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" style="width: 5%">10</td>
                                        <td class="fw-bold" style="width: 25%">Sasaran</td>
                                        <td>{{ $riskContexts->first()->sasaran ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">11</td>
                                        <td class="fw-bold">Batasan</td>
                                        <td>{{ $riskContexts->first()->batasan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">12</td>
                                        <td class="fw-bold">Asumsi Dasar</td>
                                        <td>{{ $riskContexts->first()->asumsi_dasar ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- IV. Stakeholder -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">IV. Stakeholder</h6>

                        <!-- Stakeholder Internal -->
                        <div class="mb-3">
                            <h6 class="fw-bold">Stakeholder Internal</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nama Stakeholder</th>
                                            <th>Peran / Fungsi</th>
                                            <th>Komunikasi yang digunakan untuk masing-masing stakeholder</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($riskContexts->first() && $riskContexts->first()->stakeholderInternals->count() > 0)
                                            @foreach($riskContexts->first()->stakeholderInternals as $internal)
                                            <tr>
                                                <td>{{ $internal->stakeholder ?? '-' }}</td>
                                                <td>{{ $internal->peran ?? '-' }}</td>
                                                <td>{{ $internal->komunikasi ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Stakeholder Eksternal -->
                        <div class="mb-3">
                            <h6 class="fw-bold">Stakeholder Eksternal</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nama Stakeholder</th>
                                            <th>Peran / Fungsi</th>
                                            <th>Komunikasi yang digunakan untuk masing-masing stakeholder</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($riskContexts->first() && $riskContexts->first()->stakeholderExternals->count() > 0)
                                            @foreach($riskContexts->first()->stakeholderExternals as $external)
                                            <tr>
                                                <td>{{ $external->stakeholder ?? '-' }}</td>
                                                <td>{{ $external->peran ?? '-' }}</td>
                                                <td>{{ $external->komunikasi ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
