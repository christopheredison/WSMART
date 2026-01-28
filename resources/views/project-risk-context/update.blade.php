@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bx bx-edit mr-2"></i>
                        {{ $isEdit ? 'Update Proyek Risk Context' : 'Buat Proyek Risk Context' }}
                    </h3>
                </div>
                <div class="card-body">
                    @include('partials.success-message')

                    <form action="{{ route('project-risk-context.store-or-update') }}" method="POST">
                        @csrf

                        <!-- Informasi Project -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Proyek</label>
                                <input type="text" class="form-control" value="{{ $selectedProject->project_name }}" readonly>
                                <input type="hidden" name="project_id" class="form-control" value="{{ $selectedProject->id }}">
                            </div>
                        </div>

                        <!-- Informasi Umum -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Umum</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nilai</label>
                                        <input type="text" name="nilai" class="form-control"
                                              value="{{ old('nilai', $projectRiskContext->nilai ?? '') }}">
                                        <small class="form-text text-muted">
                                            <strong>Proyek:</strong> Diisikan besaran nilai omzet kontrak yang akan dikerjakan.<br>
                                            <strong>Divisi dan Anak Perusahaan:</strong> diisikan besarnya Biaya Usaha dalam RKAP.
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pimpinan Tertinggi</label>
                                        <select name="pimpinan_tertinggi_jabatan_id" class="form-control select2">
                                            <option value="">Pilih Jabatan</option>
                                            @foreach($jabatans as $jabatan)
                                                <option value="{{ $jabatan->id }}"
                                                    {{ (old('pimpinan_tertinggi_jabatan_id') == $jabatan->id) || ($projectRiskContext && $projectRiskContext->pimpinan_tertinggi_jabatan_id == $jabatan->id) ? 'selected' : '' }}>
                                                    {{ $jabatan->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            Adalah nama dan jabatan pimpinan tertinggi proyek tersebut.
                                        </small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Sponsor</label>
                                        <input type="text" name="sponsor" class="form-control"
                                              value="{{ old('sponsor', $projectRiskContext->sponsor ?? '') }}">
                                        <small class="form-text text-muted">
                                            Adalah pihak yang memiliki proyek, individu atau entitas yang menyediakan sumber daya keuangan dalam bentuk tunai atau yang setara untuk proyek.
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $projectRiskContext->deskripsi ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Gambaran umum proyek.
                                        </small>
                                    </div>
                                </div>
                                {{-- <div class="mb-3">
                                    <label class="form-label">Anggota Proyek</label>
                                    <small class="form-text text-muted d-block mb-2">
                                        Keseluruhan anggota tim inti (nama dan jabatan) yang terlibat dalam proyek yang bersangkutan.
                                    </small>
                                </div> --}}
                            </div>
                        </div>

                        <!-- Ruang Lingkup -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Ruang Lingkup</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tujuan</label>
                                        <textarea name="tujuan" class="form-control" rows="3">{{ old('tujuan', $projectRiskContext->tujuan ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Menerangkan latar belakang dan tujuan proyek (Risk Owner).
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Lingkup Pekerjaan</label>
                                        <textarea name="lingkup_pekerjaan" class="form-control" rows="3">{{ old('lingkup_pekerjaan', $projectRiskContext->lingkup_pekerjaan ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Adalah semua pekerjaan yang menjadi bagian dari proyek yang bersangkutan.
                                        </small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pekerjaan Luar Lingkup</label>
                                    <textarea name="pekerjaan_luar_lingkup" class="form-control" rows="3">{{ old('pekerjaan_luar_lingkup', $projectRiskContext->pekerjaan_luar_lingkup ?? '') }}</textarea>
                                    <small class="form-text text-muted">
                                        Adalah pekerjaan yang bukan menjadi bagian dari team atau tetapi harus diselesaikan oleh proyek tersebut (contoh: pembebesan lahan, pemindahan utilities yang tidak masuk dalam kontrak, dll).
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Konteks -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Konteks</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Sasaran</label>
                                        <textarea name="sasaran" class="form-control" rows="3">{{ old('sasaran', $projectRiskContext->sasaran ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Kondisi atau capaian yang harus diselesaikan sesuai target kinerja proyek.
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Batasan</label>
                                        <textarea name="batasan" class="form-control" rows="3">{{ old('batasan', $projectRiskContext->batasan ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Kondor yang membatasi pekerjaan suatu proyek dengan mempertimbangkan isu internal dan eksternal:<br>
                                            1. Ada tidaknya milestone<br>
                                            2. Ada tidaknya batasan penggunaan produk impor<br>
                                            3. Ada tidaknya syarat pemakaian tenaga lokal
                                        </small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Asumsi Dasar</label>
                                    <textarea name="asumsi_dasar" class="form-control" rows="3">{{ old('asumsi_dasar', $projectRiskContext->asumsi_dasar ?? '') }}</textarea>
                                    <small class="form-text text-muted">
                                        Asumsi atau landasan pikiran yang menjadi dasar dalam pencapaian target oleh tim proyek.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Members -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Tim Anggota</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addMember()">
                                    <span class="bx bx-plus"></span> Tambah Anggota
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="members-container">
                                    @if($projectRiskContext && $projectRiskContext->members->count() > 0)
                                        @foreach($projectRiskContext->members as $index => $member)
                                            <div class="member-row row mb-2">
                                                <div class="col-md-5">
                                                    <input type="text" name="member_nama[]" class="form-control" placeholder="Nama Anggota" value="{{ $member->nama }}">
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="member_jabatan_id[]" class="form-control select2">
                                                        <option value="">Pilih Jabatan</option>
                                                        @foreach($jabatans as $jabatan)
                                                            <option value="{{ $jabatan->id }}" {{ $member->jabatan_id == $jabatan->id ? 'selected' : '' }}>
                                                                {{ $jabatan->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeMember(this)">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="member-row row mb-2">
                                            <div class="col-md-5">
                                                <input type="text" name="member_nama[]" class="form-control" placeholder="Nama Anggota">
                                            </div>
                                            <div class="col-md-5">
                                                <select name="member_jabatan_id[]" class="form-control select2">
                                                    <option value="">Pilih Jabatan</option>
                                                    @foreach($jabatans as $jabatan)
                                                        <option value="{{ $jabatan->id }}">{{ $jabatan->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeMember(this)">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Stakeholder Internal -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Stakeholder Internal</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addStakeholderInternal()">
                                    <span class="bx bx-plus"></span> Tambah Stakeholder
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="form-text text-muted">
                                        <strong>Pihak yang berkepentingan dan berhubungan dengan Proyek/Divisi/Anak Perusahaan yang berada di lingkungannya (pihak yang ikut terlibat dan dapat dikontrol oleh risk owner) meliputi:</strong><br>
                                        1. Direksi<br>
                                        2. Divisi Operasi<br>
                                        3. Divisi Korporasi<br>
                                        4. Anak Perusahaan<br>
                                        5. Proyek, dll
                                    </small>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-3"><strong>Nama Stakeholder</strong></div>
                                    <div class="col-md-3"><strong>Peran / Fungsi</strong></div>
                                    <div class="col-md-4"><strong>Komunikasi</strong></div>
                                    <div class="col-md-2"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <small class="form-text text-muted">Diisikan peran dan fungsi dari masing-masing stakeholder pada kolom pertama</small>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="form-text text-muted">Diisikan peran dan fungsi dari masing-masing stakeholder pada kolom pertama</small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="form-text text-muted">Komunikasi yang digunakan untuk masing-masing stakeholder, bisa melalui kegiatan formal maupun informal</small>
                                    </div>
                                    <div class="col-md-2"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <small class="form-text text-muted">Diisikan peran dan fungsi dari masing-masing stakeholder pada kolom pertama</small>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="form-text text-muted">Diisikan peran dan fungsi dari masing-masing stakeholder pada kolom pertama</small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="form-text text-muted">Komunikasi yang digunakan untuk masing-masing stakeholder, bisa melalui kegiatan formal maupun informal</small>
                                    </div>
                                    <div class="col-md-2"></div>
                                </div>
                                <div id="stakeholder-internal-container">
                                    @if($projectRiskContext && $projectRiskContext->stakeholderInternals->count() > 0)
                                        @foreach($projectRiskContext->stakeholderInternals as $index => $stakeholder)
                                            <div class="stakeholder-internal-row row mb-2">
                                                <div class="col-md-3">
                                                    <input type="text" name="stakeholder_internal_stakeholder[]" class="form-control" placeholder="Stakeholder" value="{{ $stakeholder->stakeholder }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" name="stakeholder_internal_peran[]" class="form-control" placeholder="Peran" value="{{ $stakeholder->peran }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="stakeholder_internal_komunikasi[]" class="form-control" placeholder="Komunikasi" value="{{ $stakeholder->komunikasi }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeStakeholderInternal(this)">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="stakeholder-internal-row row mb-2">
                                            <div class="col-md-3">
                                                <input type="text" name="stakeholder_internal_stakeholder[]" class="form-control" placeholder="Stakeholder">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="stakeholder_internal_peran[]" class="form-control" placeholder="Peran">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="stakeholder_internal_komunikasi[]" class="form-control" placeholder="Komunikasi">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeStakeholderInternal(this)">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Stakeholder External -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Stakeholder External</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addStakeholderExternal()">
                                    <span class="bx bx-plus"></span> Tambah Stakeholder
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="form-text text-muted">
                                        <strong>Pihak yang berkepentingan dan berhubungan dengan Proyek/Divisi/Anak Perusahaan yang berada di luar lingkungannya (pihak yang tidak dapat dikontrol oleh risk owner) meliputi:</strong><br>
                                        1. Konsultan<br>
                                        2. Kementrian<br>
                                        3. Lembaga Sekuritas, dll
                                    </small>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-3"><strong>Nama Stakeholder</strong></div>
                                    <div class="col-md-3"><strong>Peran / Fungsi</strong></div>
                                    <div class="col-md-4"><strong>Komunikasi</strong></div>
                                    <div class="col-md-2"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <small class="form-text text-muted">Diisikan peran dan fungsi dari masing-masing stakeholder pada kolom pertama</small>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="form-text text-muted">Diisikan peran dan fungsi dari masing-masing stakeholder pada kolom pertama</small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="form-text text-muted">Komunikasi yang digunakan untuk masing-masing stakeholder, bisa melalui kegiatan formal maupun informal</small>
                                    </div>
                                    <div class="col-md-2"></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <small class="form-text text-muted">Diisikan peran dan fungsi dari masing-masing stakeholder pada kolom pertama</small>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="form-text text-muted">Diisikan peran dan fungsi dari masing-masing stakeholder pada kolom pertama</small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="form-text text-muted">Komunikasi yang digunakan untuk masing-masing stakeholder, bisa melalui kegiatan formal maupun informal</small>
                                    </div>
                                    <div class="col-md-2"></div>
                                </div>
                                <div id="stakeholder-external-container">
                                    @if($projectRiskContext && $projectRiskContext->stakeholderExternals->count() > 0)
                                        @foreach($projectRiskContext->stakeholderExternals as $index => $stakeholder)
                                            <div class="stakeholder-external-row row mb-2">
                                                <div class="col-md-3">
                                                    <input type="text" name="stakeholder_external_stakeholder[]" class="form-control" placeholder="Stakeholder" value="{{ $stakeholder->stakeholder }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" name="stakeholder_external_peran[]" class="form-control" placeholder="Peran" value="{{ $stakeholder->peran }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="stakeholder_external_komunikasi[]" class="form-control" placeholder="Komunikasi" value="{{ $stakeholder->komunikasi }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeStakeholderExternal(this)">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="stakeholder-external-row row mb-2">
                                            <div class="col-md-3">
                                                <input type="text" name="stakeholder_external_stakeholder[]" class="form-control" placeholder="Stakeholder">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="stakeholder_external_peran[]" class="form-control" placeholder="Peran">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="stakeholder_external_komunikasi[]" class="form-control" placeholder="Komunikasi">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeStakeholderExternal(this)">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('project-risk-context.index-by-project-periode', ['projectId' => $projectPeriodeList->id]) }}" class="btn btn-secondary">
                                <span class="bx bx-arrow-back"></span> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <span class="bx bx-save"></span> {{ $isEdit ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addMember() {
    const container = document.getElementById('members-container');
    const memberRow = document.createElement('div');
    memberRow.className = 'member-row row mb-2';
    memberRow.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="member_nama[]" class="form-control" placeholder="Nama Anggota">
        </div>
        <div class="col-md-5">
            <select name="member_jabatan_id[]" class="form-control select2">
                <option value="">Pilih Jabatan</option>
                @foreach($jabatans as $jabatan)
                    <option value="{{ $jabatan->id }}">{{ $jabatan->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeMember(this)">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(memberRow);
}

function removeMember(button) {
    button.closest('.member-row').remove();
}

function addStakeholderInternal() {
    const container = document.getElementById('stakeholder-internal-container');
    const stakeholderRow = document.createElement('div');
    stakeholderRow.className = 'stakeholder-internal-row row mb-2';
    stakeholderRow.innerHTML = `
        <div class="col-md-3">
            <input type="text" name="stakeholder_internal_stakeholder[]" class="form-control" placeholder="Stakeholder">
        </div>
        <div class="col-md-3">
            <input type="text" name="stakeholder_internal_peran[]" class="form-control" placeholder="Peran">
        </div>
        <div class="col-md-4">
            <input type="text" name="stakeholder_internal_komunikasi[]" class="form-control" placeholder="Komunikasi">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeStakeholderInternal(this)">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(stakeholderRow);
}

function removeStakeholderInternal(button) {
    button.closest('.stakeholder-internal-row').remove();
}

function addStakeholderExternal() {
    const container = document.getElementById('stakeholder-external-container');
    const stakeholderRow = document.createElement('div');
    stakeholderRow.className = 'stakeholder-external-row row mb-2';
    stakeholderRow.innerHTML = `
        <div class="col-md-3">
            <input type="text" name="stakeholder_external_stakeholder[]" class="form-control" placeholder="Stakeholder">
        </div>
        <div class="col-md-3">
            <input type="text" name="stakeholder_external_peran[]" class="form-control" placeholder="Peran">
        </div>
        <div class="col-md-4">
            <input type="text" name="stakeholder_external_komunikasi[]" class="form-control" placeholder="Komunikasi">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeStakeholderExternal(this)">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(stakeholderRow); // Ubah dari stakeholderExternal ke stakeholderRow
}

function removeStakeholderExternal(button) {
    button.closest('.stakeholder-external-row').remove();
}
</script>
@endsection
