@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-3">
                <div class="bg-info-subtle p-2 rounded-4">
                    <div class="lead__icon"><div class="svg-icon svg-icon-2x svg-icon-info">@include('partials.icon-pyramid')</div></div>
                </div>
                <div class="d-block">
                    <h2>Rekomendasi Risiko Divisi</h2>
                    <div class="ff-preheading">Periode: {{ $periode->tahun }}</div>
                </div>
            </div>
            
            <div class="card-header border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <h6 class="mb-0">Keterangan :</h6>
                    <div class="d-flex gap-3">
                        @foreach($tableLegend as $legend)
                        <div class="d-flex align-items-center gap-1">{!! $legend['icon'] !!}<span>{{ $legend['label'] }}</span></div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-2 mb-3 align-items-center">
                    <form id="filter-form" action="{{ route('rekomendasi-risiko.show', ['unit' => $unit->id, 'periode' => $periode->id]) }}" method="GET" class="col-md-7 row g-2">
                        <div class="col-md-6">
                            <select name="jenis_risiko_id" class="form-select select2" onchange="document.getElementById('filter-form').submit();">
                                <option value="">Semua T2 & T3 KBUMN</option>
                                @foreach($jenisRisiko as $id => $title)
                                    @php
                                        $kategori = \App\Models\JenisRisiko::find($id)->kategoriRisiko;
                                        $kategoriTitle = $kategori ? $kategori->title : '';
                                    @endphp
                                    <option value="{{ $id }}" {{ $jenisRisikoFilter == $id ? 'selected' : '' }}>
                                        {{ $kategoriTitle }} - {{ $title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select name="status" class="form-select" onchange="document.getElementById('filter-form').submit();">
                                <option value="">Semua Status</option>
                                <option value="1" {{ $statusFilter == '1' ? 'selected' : '' }}>Draft</option>
                                <option value="2" {{ $statusFilter == '2' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </form>
                    <div class="col-auto ms-auto">
                        <a href="{{ route('rekomendasi-risiko.create', ['unit' => $unit->id, 'periode' => $periode->id]) }}" class="btn btn-outline-info btn-sm d-flex flex-center">
                            <span class="bx bx-plus"></span>
                            <span class="ms-1">Tambah Rekomendasi Risiko</span>
                        </a>
                    </div>
                </div>
                
                @if($rekomendasiRisikos->isNotEmpty())
                    <div class="">
                        <table class="table table-hover" id="rekomendasi-table">
                            <thead>
                                <tr>
                                    <th class="text-nowrap">#</th>
                                    <th class="text-nowrap">Divisi</th>
                                    <th class="text-nowrap">Sasaran</th>
                                    <th class="text-nowrap">T2 & T3 KBUMN</th>
                                    <th class="text-nowrap">Peristiwa Risiko</th>
                                    <th class="text-nowrap">Deskripsi Peristiwa Risiko</th>
                                    <th class="text-nowrap">Jenis Kontrol Eksisting</th>
                                    <th class="text-nowrap">Waktu Terpapar Risiko</th>
                                    <th class="text-nowrap">Status</th>
                                    <th class="no-sort text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rekomendasiRisikos as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->unit->name ?? '-' }}</td>
                                    <td>{{ Str::limit($item->target_capaian_kinerja, 50) }}</td>
                                    <td>{{ optional($item->kategoriRisiko)->title }} - {{ optional($item->jenisRisiko)->title }}</td>
                                    <td>{{ Str::limit($item->peristiwa_risiko, 50) }}</td>
                                    <td>{{ Str::limit($item->deskripsi_peristiwa_risiko, 70) }}</td>
                                    <td>{{ optional($item->jenisKontrolEksisting)->jenis_kontrol }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/y') }}</td>
                                    <td>
                                        @if($item->status == \App\Models\RekomendasiRisiko::STATUS_DRAFT)
                                            <span class="badge bg-info">Draft</span>
                                        @elseif($item->status == \App\Models\RekomendasiRisiko::STATUS_PUBLISHED)
                                            <span class="badge bg-success">Published</span>
                                        @endif
                                    </td>
                                    <td class="white-space-nowrap">
                                        <a href="{{ route('rekomendasi-risiko.view', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="View"><span class="bx bx-show-alt"></span></a>
                                        @if($item->status == \App\Models\RekomendasiRisiko::STATUS_DRAFT)
                                            <a href="{{ route('rekomendasi-risiko.edit', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Edit"><span class="bx bx-message-square-edit"></span></a>
                                            <a href="javascript:void(0)" onclick="deleteConfirmation({{ $item->id }})" class="btn-input-icon" data-bs-toggle="tooltip" title="Delete"><span class="bx bx-trash text-danger"></span></a>
                                            <a href="javascript:void(0)" onclick="publishConfirmation({{ $item->id }})" class="btn-input-icon" data-bs-toggle="tooltip" title="Publish"><span class="bx bx-send text-success"></span></a>
                                            <form id="publish-form-{{ $item->id }}" action="{{ route('rekomendasi-risiko.publish', $item->id) }}" method="POST" class="d-none">@csrf</form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-secondary text-center" role="alert">
                        Tidak ada data rekomendasi risiko untuk ditampilkan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteConfirmation(id) {
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Tidak, batal",
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary ms-2'
            },
        }).then((result) => {
            if (result.isConfirmed) {
                let url = '{{ route("rekomendasi-risiko.destroy", ":id") }}';
                url = url.replace(':id', id);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus data.',
                        });
                    }
                });
            }
        });
    }

    function publishConfirmation(id) {
        Swal.fire({
            title: "Publikasikan Rekomendasi?",
            text: "Data ini akan disalin ke Risk Register Divisi terkait dan tidak dapat diubah lagi.",
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "Ya, publikasikan!",
            cancelButtonText: "Batal",
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-secondary ms-2'
            },
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('publish-form-' + id).submit();
            }
        });
    }

    $(document).ready(function() {
        if ({{ count($rekomendasiRisikos) }} > 0) {
            $('#rekomendasi-table').DataTable({
                "searching": true,
                "columnDefs": [
                    { "orderable": false, "targets": 'no-sort' }
                ]
            });
        } else {
            $('#rekomendasi-table').html('<tr><td colspan="10" class="text-center">Tidak ada data rekomendasi risiko yang sesuai.</td></tr>');
        }
    });
</script>
@endpush