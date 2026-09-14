@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-info-subtle p-2 rounded-4">
          <div class="lead__icon">
            <div class="svg-icon svg-icon-2x svg-icon-info">
              @include('partials.icon-pyramid')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Master Data</div>
          <h2>List Peristiwa Risiko</h2>
        </div>
        <div class="ms-auto d-flex align-items-center gap-3">
          <div class="col-auto">
            <a href="{{ route('projects.verifikasi-pengajuan.index', ['tab' => 'peristiwa']) }}" type="button"
              class="btn btn-outline-info btn-sm d-flex flex-center" data-bs-toggle="tooltip"
              data-bs-title="Kembali ke verifikasi pengajuan">
              <i class="bx bx-check-shield"></i>
              <span class="ms-1">Verifikasi Pengajuan</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample3">
          <table class="table ajax-datatable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="peristiwa">Peristiwa Risiko</th>
                <th class="sort" data-sort="sumber">Sumber</th>
                <th class="sort" data-sort="proyek">Proyek</th>
                <th class="sort" data-sort="pengaju">Diajukan Oleh</th>
                <th class="sort" data-sort="verifikator">Diverifikasi Oleh</th>
                <th class="sort" data-sort="tanggal">Tanggal</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list">
              @foreach ($items as $index => $item)
                @php
                  $isCustom = (int) $item->status === \App\Models\PeristiwaRisiko::STATUS_CUSTOM;
                  $projectName = optional($item->project)->project_name
                    ?? optional(optional($item->projectPeriodeList)->project)->project_name
                    ?? '-';
                  $verifierName = $item->verifier->name ?? null;
                  $verifiedAt = optional($item->verified_at)->format('d M Y H:i');
                @endphp
                <tr>
                  <td class="index-number">{{ $index + 1 }}</td>
                  <td class="peristiwa">{{ $item->title }}</td>
                  <td class="sumber">
                    @if($isCustom)
                      <div class="badge bg-primary">Pengajuan Proyek</div>
                    @else
                      <div class="badge bg-info">Master</div>
                    @endif
                  </td>
                  <td class="proyek">{{ $isCustom ? $projectName : '-' }}</td>
                  <td class="pengaju">{{ $isCustom ? ($item->requester->name ?? '-') : '-' }}</td>
                  <td class="verifikator">
                    @if($isCustom && $verifierName)
                      <div>{{ $verifierName }}</div>
                      <small class="text-muted">{{ $verifiedAt ?? '-' }}</small>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="tanggal">{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                  <td class="white-space-nowrap">
                    <button type="button" class="btn-input-icon btn-view-detail"
                      data-bs-toggle="tooltip" title="View"
                      data-peristiwa="{{ $item->title }}"
                      data-sumber="{{ $isCustom ? 'Pengajuan Proyek' : 'Master' }}"
                      data-proyek="{{ $isCustom ? $projectName : '-' }}"
                      data-pengaju="{{ $isCustom ? ($item->requester->name ?? '-') : '-' }}"
                      data-verifikator="{{ $isCustom ? ($verifierName ?? '-') : '-' }}"
                      data-verified-at="{{ $isCustom ? ($verifiedAt ?? '-') : '-' }}"
                      data-tanggal="{{ optional($item->created_at)->format('d M Y H:i') }}">
                      <span class="bx bx-show-alt"></span>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const table = new DataTable('#example');
table.on('mouseenter', 'tbody td', function() {
  const cellIndex = table.cell(this).index();
  if (!cellIndex || typeof cellIndex.column === 'undefined') {
    return;
  }
  const colIdx = cellIndex.column;

  table.cells().nodes().each((el) => el.classList.remove('highlight'));
  table.column(colIdx).nodes().each((el) => el.classList.add('highlight'));
});

$(document).ready(function() {
  $(document).on('click', '.btn-view-detail', function() {
    Swal.fire({
      title: 'Detail Peristiwa Risiko',
      html: `
        <div class="text-start">
          <p class="mb-2"><strong>Peristiwa Risiko:</strong><br>${$(this).data('peristiwa') || '-'}</p>
          <p class="mb-2"><strong>Sumber:</strong><br>${$(this).data('sumber') || '-'}</p>
          <p class="mb-2"><strong>Proyek:</strong><br>${$(this).data('proyek') || '-'}</p>
          <p class="mb-2"><strong>Diajukan Oleh:</strong><br>${$(this).data('pengaju') || '-'}</p>
          <p class="mb-2"><strong>Diverifikasi Oleh:</strong><br>${$(this).data('verifikator') || '-'}</p>
          <p class="mb-2"><strong>Tanggal Verifikasi:</strong><br>${$(this).data('verified-at') || '-'}</p>
          <p class="mb-0"><strong>Tanggal:</strong><br>${$(this).data('tanggal') || '-'}</p>
        </div>
      `,
      icon: 'info',
      confirmButtonText: 'Tutup',
      width: 640
    });
  });
});
</script>
@endsection
