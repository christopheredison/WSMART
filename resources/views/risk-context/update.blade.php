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
                        {{ $isEdit ? 'Update Risk Context' : 'Buat Risk Context' }}
                    </h3>
                </div>
                <div class="card-body">
                    @include('partials.success-message')
                    
                    <form action="{{ route('risk-context.store-or-update') }}" method="POST">
                        @csrf
                        
                        <!-- Informasi Unit -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Divisi</label>
                                <input type="text" class="form-control" value="{{ $unit->name }}" readonly>
                                <input type="hidden" name="unit_id" class="form-control" value="{{ $unit->id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Periode <span class="text-danger">*</span></label>
                                <select name="periode_id" class="form-control" required>
                                    <option value="">Pilih Periode</option>
                                    @foreach($periodes as $periode)
                                        <option value="{{ $periode->id }}" 
                                            {{ ($selectedPeriode && $selectedPeriode->id == $periode->id) || (old('periode_id') == $periode->id) || ($riskContext && $riskContext->periode_id == $periode->id) ? 'selected' : '' }}>
                                            {{ $periode->tahun }}
                                        </option>
                                    @endforeach
                                </select>
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
                                               value="{{ old('nilai', $riskContext->nilai ?? '') }}">
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
                                                    {{ (old('pimpinan_tertinggi_jabatan_id') == $jabatan->id) || ($riskContext && $riskContext->pimpinan_tertinggi_jabatan_id == $jabatan->id) ? 'selected' : '' }}>
                                                    {{ $jabatan->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            Adalah nama dan jabatan pimpinan tertinggi unit kerja tersebut.
                                        </small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Sponsor</label>
                                        <input type="text" name="sponsor" class="form-control" 
                                               value="{{ old('sponsor', $riskContext->sponsor ?? '') }}">
                                        <small class="form-text text-muted">
                                            Adalah pihak yang memiliki unit kerja, individu atau entitas yang menyediakan sumber daya keuangan dalam bentuk tunai atau yang setara untuk proyek.
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $riskContext->deskripsi ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Gambaran umum unit kerja.
                                        </small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Anggota Unit Kerja</label>
                                    <small class="form-text text-muted d-block mb-2">
                                        Keseluruhan anggota tim inti (nama dan jabatan) yang terlibat dalam unit kerja yang bersangkutan.
                                    </small>
                                </div>
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
                                        <textarea name="tujuan" class="form-control" rows="3">{{ old('tujuan', $riskContext->tujuan ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Menerangkan latar belakang dan tujuan unit kerja (Risk Owner).
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Lingkup Pekerjaan</label>
                                        <textarea name="lingkup_pekerjaan" class="form-control" rows="3">{{ old('lingkup_pekerjaan', $riskContext->lingkup_pekerjaan ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Adalah semua pekerjaan yang menjadi bagian dari unit kerja yang bersangkutan.
                                        </small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pekerjaan Luar Lingkup</label>
                                    <textarea name="pekerjaan_luar_lingkup" class="form-control" rows="3">{{ old('pekerjaan_luar_lingkup', $riskContext->pekerjaan_luar_lingkup ?? '') }}</textarea>
                                    <small class="form-text text-muted">
                                        Adalah pekerjaan yang bukan menjadi bagian dari team atau tetapi harus diselesaikan oleh unit kerja tersebut (contoh: pembebesan lahan, pemindahan utilities yang tidak masuk dalam kontrak, dll).
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
                                        <textarea name="sasaran" class="form-control" rows="3">{{ old('sasaran', $riskContext->sasaran ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Kondisi atau capaian yang harus diselesaikan sesuai target kinerja unit kerja.
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Batasan</label>
                                        <textarea name="batasan" class="form-control" rows="3">{{ old('batasan', $riskContext->batasan ?? '') }}</textarea>
                                        <small class="form-text text-muted">
                                            Kondor yang membatasi pekerjaan suatu unit kerja dengan mempertimbangkan isu internal dan eksternal:<br>
                                            1. Ada tidaknya milestone<br>
                                            2. Ada tidaknya batasan penggunaan produk impor<br>
                                            3. Ada tidaknya syarat pemakaian tenaga lokal
                                        </small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Asumsi Dasar</label>
                                    <textarea name="asumsi_dasar" class="form-control" rows="3">{{ old('asumsi_dasar', $riskContext->asumsi_dasar ?? '') }}</textarea>
                                    <small class="form-text text-muted">
                                        Asumsi atau landasan pikiran yang menjadi dasar dalam pencapaian target oleh tim unit kerja.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Members -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Tim Anggota</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addMember()">
                                    <i class="bx bx-plus"></i> Tambah Anggota
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="members-container">
                                    @if($riskContext && $riskContext->members->count() > 0)
                                        @foreach($riskContext->members as $index => $member)
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
                                    <i class="bx bx-plus"></i> Tambah Stakeholder
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
                                    @if($riskContext && $riskContext->stakeholderInternals->count() > 0)
                                        @foreach($riskContext->stakeholderInternals as $index => $stakeholder)
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
                                    <i class="bx bx-plus"></i> Tambah Stakeholder
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
                                    @if($riskContext && $riskContext->stakeholderExternals->count() > 0)
                                        @foreach($riskContext->stakeholderExternals as $index => $stakeholder)
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
                            <a href="{{ route('risk-context.index-by-periode-unit', ['periodeId' => $selectedPeriode->id, 'unitId' => $unit->id]) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> {{ $isEdit ? 'Update' : 'Simpan' }}
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