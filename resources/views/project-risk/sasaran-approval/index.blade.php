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
          <div class="ff-preheading">Verifikasi</div>
          <h2>Pengajuan Sasaran dan Peristiwa Risiko</h2>
        </div>
        <div class="ms-auto d-flex align-items-center gap-3">
          <div class="col-auto">
            <a href="{{ route('projects.sasaran-risiko.index') }}" id="btn-list-sasaran"
              class="btn btn-outline-info btn-sm d-flex flex-center {{ $activeTab !== 'sasaran' ? 'd-none' : '' }}"
              data-bs-toggle="tooltip" data-bs-title="Lihat sasaran risiko yang sudah tersedia">
              <i class="bx bx-list-ul"></i>
              <span class="ms-1">List Sasaran Risiko</span>
            </a>
            <a href="{{ route('projects.peristiwa-risiko.index') }}" id="btn-list-peristiwa"
              class="btn btn-outline-info btn-sm d-flex flex-center {{ $activeTab !== 'peristiwa' ? 'd-none' : '' }}"
              data-bs-toggle="tooltip" data-bs-title="Lihat peristiwa risiko yang sudah tersedia">
              <i class="bx bx-list-ul"></i>
              <span class="ms-1">List Peristiwa Risiko</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body dt-header-true">
        <ul class="nav nav-tabs mb-3" id="verifikasiPengajuanTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'sasaran' ? 'active' : '' }}" id="tab-sasaran-btn"
              data-bs-toggle="tab" data-bs-target="#tab-sasaran" type="button" role="tab"
              aria-controls="tab-sasaran" aria-selected="{{ $activeTab === 'sasaran' ? 'true' : 'false' }}">
              Sasaran Risiko
              @if($pendingSasaranCount > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $pendingSasaranCount }}</span>
              @endif
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'peristiwa' ? 'active' : '' }}" id="tab-peristiwa-btn"
              data-bs-toggle="tab" data-bs-target="#tab-peristiwa" type="button" role="tab"
              aria-controls="tab-peristiwa" aria-selected="{{ $activeTab === 'peristiwa' ? 'true' : 'false' }}">
              Peristiwa Risiko
              @if($pendingPeristiwaCount > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $pendingPeristiwaCount }}</span>
              @endif
            </button>
          </li>
        </ul>

        <div class="tab-content" id="verifikasiPengajuanTabContent">
          <div class="tab-pane fade {{ $activeTab === 'sasaran' ? 'show active' : '' }}" id="tab-sasaran" role="tabpanel" aria-labelledby="tab-sasaran-btn">
            <div id="tableSasaranWrapper">
              <table class="table ajax-datatable" id="table-sasaran" data-paging="true" data-info="true" data-filter="true">
                <thead>
                  <tr>
                    <th class="white-space-nowrap">#</th>
                    <th class="sort" data-sort="proyek">Proyek</th>
                    <th class="sort" data-sort="sasaran">Sasaran Diajukan</th>
                    <th class="sort" data-sort="pengaju">Diajukan Oleh</th>
                    <th class="sort" data-sort="status">Status</th>
                    <th class="sort" data-sort="verifikator">Diverifikasi Oleh</th>
                    <th class="sort" data-sort="tanggal">Tanggal Pengajuan</th>
                    <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
                  </tr>
                </thead>
                <tbody class="list" id="bulk-select-body">
                  @foreach ($sasaranItems as $index => $item)
                    @php
                      $statusClass = match((int) $item->approval_status) {
                        \App\Models\SasaranProyek::APPROVAL_PENDING => 'bg-warning',
                        \App\Models\SasaranProyek::APPROVAL_APPROVED => 'bg-success',
                        \App\Models\SasaranProyek::APPROVAL_REJECTED => 'bg-danger',
                        default => 'bg-secondary',
                      };
                      $statusText = match((int) $item->approval_status) {
                        \App\Models\SasaranProyek::APPROVAL_PENDING => 'Menunggu Verifikasi',
                        \App\Models\SasaranProyek::APPROVAL_APPROVED => 'Disetujui',
                        \App\Models\SasaranProyek::APPROVAL_REJECTED => 'Ditolak',
                        default => 'Tidak Diketahui',
                      };
                      $projectName = optional(optional($item->projectPeriodeList)->project)->project_name ?? '-';
                      $verifierName = $item->verifier->name ?? null;
                      $verifiedAt = optional($item->verified_at)->format('d M Y H:i');
                    @endphp
                    <tr>
                      <td class="index-number">{{ $index + 1 }}</td>
                      <td class="proyek">{{ $projectName }}</td>
                      <td class="sasaran">{{ $item->kpi_desc }}</td>
                      <td class="pengaju">{{ $item->requester->name ?? '-' }}</td>
                      <td class="status">
                        @if((int) $item->approval_status === \App\Models\SasaranProyek::APPROVAL_REJECTED && $item->rejected_reason)
                          <button type="button" class="badge bg-danger btn-sm border-0 btn-view-reason"
                            data-reason="{{ $item->rejected_reason }}">
                            Ditolak
                            <span class="bx bx-info-circle ms-1"></span>
                          </button>
                        @else
                          <div class="badge {{ $statusClass }}">{{ $statusText }}</div>
                        @endif
                      </td>
                      <td class="verifikator">
                        @if($verifierName)
                          <div>{{ $verifierName }}</div>
                          <small class="text-muted">{{ $verifiedAt ?? '-' }}</small>
                        @else
                          <span class="text-muted">Belum diverifikasi</span>
                        @endif
                      </td>
                      <td class="tanggal">{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                      <td class="white-space-nowrap">
                        <button type="button" class="btn-input-icon btn-view-detail-sasaran"
                          data-bs-toggle="tooltip" title="View"
                          data-project="{{ $projectName }}"
                          data-sasaran="{{ $item->kpi_desc }}"
                          data-pengaju="{{ $item->requester->name ?? '-' }}"
                          data-tanggal="{{ optional($item->created_at)->format('d M Y H:i') }}"
                          data-status="{{ $statusText }}"
                          data-verifikator="{{ $verifierName ?? '-' }}"
                          data-verified-at="{{ $verifiedAt ?? '-' }}"
                          data-reason="{{ $item->rejected_reason ?? '-' }}">
                          <span class="bx bx-show-alt"></span>
                        </button>

                        @if((int) $item->approval_status === \App\Models\SasaranProyek::APPROVAL_PENDING)
                          <form method="POST" action="{{ route('projects.sasaran-lainnya.approve', $item->id) }}" class="d-inline form-approve-sasaran">
                            @csrf
                            <button type="button" class="btn-input-icon btn-approve-sasaran" data-bs-toggle="tooltip" title="Setujui"
                              data-sasaran="{{ $item->kpi_desc }}">
                              <span class="bx bx-check-circle text-success"></span>
                            </button>
                          </form>
                          <form method="POST" action="{{ route('projects.sasaran-lainnya.reject', $item->id) }}" class="d-inline form-reject-sasaran">
                            @csrf
                            <input type="hidden" name="rejected_reason" class="reject-reason-input">
                            <button type="button" class="btn-input-icon btn-reject-sasaran" data-bs-toggle="tooltip" title="Tolak"
                              data-sasaran="{{ $item->kpi_desc }}">
                              <span class="bx bx-x-circle text-danger"></span>
                            </button>
                          </form>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade {{ $activeTab === 'peristiwa' ? 'show active' : '' }}" id="tab-peristiwa" role="tabpanel" aria-labelledby="tab-peristiwa-btn">
            <div id="tablePeristiwaWrapper">
              <table class="table ajax-datatable" id="table-peristiwa" data-paging="true" data-info="true" data-filter="true">
                <thead>
                  <tr>
                    <th class="white-space-nowrap">#</th>
                    <th class="sort" data-sort="proyek">Proyek</th>
                    <th class="sort" data-sort="peristiwa">Peristiwa Diajukan</th>
                    <th class="sort" data-sort="pengaju">Diajukan Oleh</th>
                    <th class="sort" data-sort="status">Status</th>
                    <th class="sort" data-sort="verifikator">Diverifikasi Oleh</th>
                    <th class="sort" data-sort="tanggal">Tanggal Pengajuan</th>
                    <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
                  </tr>
                </thead>
                <tbody class="list">
                  @foreach ($peristiwaItems as $index => $item)
                    @php
                      $statusClass = match((int) $item->approval_status) {
                        \App\Models\PeristiwaRisiko::APPROVAL_PENDING => 'bg-warning',
                        \App\Models\PeristiwaRisiko::APPROVAL_APPROVED => 'bg-success',
                        \App\Models\PeristiwaRisiko::APPROVAL_REJECTED => 'bg-danger',
                        default => 'bg-secondary',
                      };
                      $statusText = match((int) $item->approval_status) {
                        \App\Models\PeristiwaRisiko::APPROVAL_PENDING => 'Menunggu Verifikasi',
                        \App\Models\PeristiwaRisiko::APPROVAL_APPROVED => 'Disetujui',
                        \App\Models\PeristiwaRisiko::APPROVAL_REJECTED => 'Ditolak',
                        default => 'Tidak Diketahui',
                      };
                      $projectName = optional($item->project)->project_name
                        ?? optional(optional($item->projectPeriodeList)->project)->project_name
                        ?? '-';
                      $verifierName = $item->verifier->name ?? null;
                      $verifiedAt = optional($item->verified_at)->format('d M Y H:i');
                    @endphp
                    <tr>
                      <td class="index-number">{{ $index + 1 }}</td>
                      <td class="proyek">{{ $projectName }}</td>
                      <td class="peristiwa">{{ $item->title }}</td>
                      <td class="pengaju">{{ $item->requester->name ?? '-' }}</td>
                      <td class="status">
                        @if((int) $item->approval_status === \App\Models\PeristiwaRisiko::APPROVAL_REJECTED && $item->rejected_reason)
                          <button type="button" class="badge bg-danger btn-sm border-0 btn-view-reason"
                            data-reason="{{ $item->rejected_reason }}">
                            Ditolak
                            <span class="bx bx-info-circle ms-1"></span>
                          </button>
                        @else
                          <div class="badge {{ $statusClass }}">{{ $statusText }}</div>
                        @endif
                      </td>
                      <td class="verifikator">
                        @if($verifierName)
                          <div>{{ $verifierName }}</div>
                          <small class="text-muted">{{ $verifiedAt ?? '-' }}</small>
                        @else
                          <span class="text-muted">Belum diverifikasi</span>
                        @endif
                      </td>
                      <td class="tanggal">{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                      <td class="white-space-nowrap">
                        <button type="button" class="btn-input-icon btn-view-detail-peristiwa"
                          data-bs-toggle="tooltip" title="View"
                          data-project="{{ $projectName }}"
                          data-peristiwa="{{ $item->title }}"
                          data-pengaju="{{ $item->requester->name ?? '-' }}"
                          data-tanggal="{{ optional($item->created_at)->format('d M Y H:i') }}"
                          data-status="{{ $statusText }}"
                          data-verifikator="{{ $verifierName ?? '-' }}"
                          data-verified-at="{{ $verifiedAt ?? '-' }}"
                          data-reason="{{ $item->rejected_reason ?? '-' }}">
                          <span class="bx bx-show-alt"></span>
                        </button>

                        @if((int) $item->approval_status === \App\Models\PeristiwaRisiko::APPROVAL_PENDING)
                          <form method="POST" action="{{ route('projects.peristiwa-lainnya.approve', $item->id) }}" class="d-inline form-approve-peristiwa">
                            @csrf
                            <button type="button" class="btn-input-icon btn-approve-peristiwa" data-bs-toggle="tooltip" title="Setujui"
                              data-peristiwa="{{ $item->title }}">
                              <span class="bx bx-check-circle text-success"></span>
                            </button>
                          </form>
                          <form method="POST" action="{{ route('projects.peristiwa-lainnya.reject', $item->id) }}" class="d-inline form-reject-peristiwa">
                            @csrf
                            <input type="hidden" name="rejected_reason" class="reject-reason-input">
                            <button type="button" class="btn-input-icon btn-reject-peristiwa" data-bs-toggle="tooltip" title="Tolak"
                              data-peristiwa="{{ $item->title }}">
                              <span class="bx bx-x-circle text-danger"></span>
                            </button>
                          </form>
                        @endif
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
  </div>
</div>
@endsection

@section('scripts')
<script>
function bindTableHighlight(table) {
  table.on('mouseenter', 'tbody td', function() {
    const cellIndex = table.cell(this).index();
    if (!cellIndex || typeof cellIndex.column === 'undefined') {
      return;
    }
    const colIdx = cellIndex.column;

    table.cells().nodes().each((el) => el.classList.remove('highlight'));
    table.column(colIdx).nodes().each((el) => el.classList.add('highlight'));
  });
}

const tableSasaran = new DataTable('#table-sasaran');
const tablePeristiwa = new DataTable('#table-peristiwa');
bindTableHighlight(tableSasaran);
bindTableHighlight(tablePeristiwa);

$(document).ready(function() {
  function toggleListButtons(tab) {
    const isPeristiwa = tab === 'peristiwa';
    $('#btn-list-sasaran').toggleClass('d-none', isPeristiwa);
    $('#btn-list-peristiwa').toggleClass('d-none', !isPeristiwa);

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url);
  }

  $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();

    const target = $(e.target).attr('data-bs-target');
    toggleListButtons(target === '#tab-peristiwa' ? 'peristiwa' : 'sasaran');
  });

  $(document).on('click', '.btn-view-detail-sasaran', function() {
    const project = $(this).data('project') || '-';
    const sasaran = $(this).data('sasaran') || '-';
    const pengaju = $(this).data('pengaju') || '-';
    const tanggal = $(this).data('tanggal') || '-';
    const status = $(this).data('status') || '-';
    const verifikator = $(this).data('verifikator') || '-';
    const verifiedAt = $(this).data('verified-at') || '-';
    const reason = $(this).data('reason') || '-';

    Swal.fire({
      title: 'Detail Pengajuan Sasaran',
      html: `
        <div class="text-start">
          <p class="mb-2"><strong>Proyek:</strong><br>${project}</p>
          <p class="mb-2"><strong>Sasaran Diajukan:</strong><br>${sasaran}</p>
          <p class="mb-2"><strong>Diajukan Oleh:</strong><br>${pengaju}</p>
          <p class="mb-2"><strong>Tanggal Pengajuan:</strong><br>${tanggal}</p>
          <p class="mb-2"><strong>Status:</strong><br>${status}</p>
          <p class="mb-2"><strong>Diverifikasi Oleh:</strong><br>${verifikator}</p>
          <p class="mb-2"><strong>Tanggal Verifikasi:</strong><br>${verifiedAt}</p>
          <p class="mb-0"><strong>Alasan Penolakan:</strong><br>${reason}</p>
        </div>
      `,
      icon: 'info',
      confirmButtonText: 'Tutup',
      width: 640
    });
  });

  $(document).on('click', '.btn-view-detail-peristiwa', function() {
    const project = $(this).data('project') || '-';
    const peristiwa = $(this).data('peristiwa') || '-';
    const pengaju = $(this).data('pengaju') || '-';
    const tanggal = $(this).data('tanggal') || '-';
    const status = $(this).data('status') || '-';
    const verifikator = $(this).data('verifikator') || '-';
    const verifiedAt = $(this).data('verified-at') || '-';
    const reason = $(this).data('reason') || '-';

    Swal.fire({
      title: 'Detail Pengajuan Peristiwa Risiko',
      html: `
        <div class="text-start">
          <p class="mb-2"><strong>Proyek:</strong><br>${project}</p>
          <p class="mb-2"><strong>Peristiwa Diajukan:</strong><br>${peristiwa}</p>
          <p class="mb-2"><strong>Diajukan Oleh:</strong><br>${pengaju}</p>
          <p class="mb-2"><strong>Tanggal Pengajuan:</strong><br>${tanggal}</p>
          <p class="mb-2"><strong>Status:</strong><br>${status}</p>
          <p class="mb-2"><strong>Diverifikasi Oleh:</strong><br>${verifikator}</p>
          <p class="mb-2"><strong>Tanggal Verifikasi:</strong><br>${verifiedAt}</p>
          <p class="mb-0"><strong>Alasan Penolakan:</strong><br>${reason}</p>
        </div>
      `,
      icon: 'info',
      confirmButtonText: 'Tutup',
      width: 640
    });
  });

  $(document).on('click', '.btn-view-reason', function() {
    Swal.fire({
      title: 'Alasan Penolakan',
      text: $(this).data('reason') || '-',
      icon: 'error',
      confirmButtonText: 'Tutup'
    });
  });

  $(document).on('click', '.btn-approve-sasaran', function() {
    const form = $(this).closest('form');
    const sasaran = $(this).data('sasaran') || '';

    Swal.fire({
      title: 'Setujui pengajuan ini?',
      html: `Sasaran <strong>${sasaran}</strong> akan disetujui dan dapat dipilih pada form risiko proyek.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Setujui',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  $(document).on('click', '.btn-reject-sasaran', function() {
    const form = $(this).closest('form');
    const sasaran = $(this).data('sasaran') || '';
    const reasonInput = form.find('.reject-reason-input');

    Swal.fire({
      title: 'Tolak pengajuan ini?',
      html: `Sasaran <strong>${sasaran}</strong> akan ditolak. Mohon isi alasan penolakan.`,
      input: 'textarea',
      inputPlaceholder: 'Masukkan alasan penolakan...',
      inputAttributes: {
        'aria-label': 'Alasan penolakan'
      },
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Tolak',
      cancelButtonText: 'Batal',
      inputValidator: (value) => {
        if (!value || !value.trim()) {
          return 'Alasan penolakan wajib diisi.';
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        reasonInput.val(result.value.trim());
        form.submit();
      }
    });
  });

  $(document).on('click', '.btn-approve-peristiwa', function() {
    const form = $(this).closest('form');
    const peristiwa = $(this).data('peristiwa') || '';

    Swal.fire({
      title: 'Setujui pengajuan ini?',
      html: `Peristiwa <strong>${peristiwa}</strong> akan disetujui dan dapat dipilih pada form risiko / loss event proyek.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Setujui',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  $(document).on('click', '.btn-reject-peristiwa', function() {
    const form = $(this).closest('form');
    const peristiwa = $(this).data('peristiwa') || '';
    const reasonInput = form.find('.reject-reason-input');

    Swal.fire({
      title: 'Tolak pengajuan ini?',
      html: `Peristiwa <strong>${peristiwa}</strong> akan ditolak. Mohon isi alasan penolakan.`,
      input: 'textarea',
      inputPlaceholder: 'Masukkan alasan penolakan...',
      inputAttributes: {
        'aria-label': 'Alasan penolakan'
      },
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Tolak',
      cancelButtonText: 'Batal',
      inputValidator: (value) => {
        if (!value || !value.trim()) {
          return 'Alasan penolakan wajib diisi.';
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        reasonInput.val(result.value.trim());
        form.submit();
      }
    });
  });
});
</script>
@endsection
