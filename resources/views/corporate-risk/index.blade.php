@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-info-subtle p-2 rounded-4">
            <div class="lead__icon">
              <div class="svg-icon svg-icon-2x svg-icon-info">
                @include('partials.icon-pyramid')
              </div>
            </div>
          </div>
          <div>
            <div class="ff-preheading">Input Data</div>
            <h2>Risk Register Korporat</h2>
            @if(isset($selectedPeriode))
            <div class="ff-preheading">Periode: {{ $selectedPeriode->tahun }}</div>
            @endif
          </div>
          <div class="ms-auto d-flex align-items-center gap-3">
            <div class="col-auto">
              @php
                  // Logic sama seperti risk-register-unit:
                  // Level 1 (Risk Officer MR) boleh menambah risiko.
                  $canAdd = false;
                  if (($is_unit_mr ?? true) && ($levelId ?? null) == 1) {
                      $canAdd = true;
                  } elseif (($levelId ?? null) == 1) {
                      $canAdd = true;
                  }
                  $pid = $selectedPeriode->id ?? null;
              @endphp
              @can('risk_register_create')
                @if(
                    !($unitExpired ?? false)
                    && ($status == null || $status == 1 || $status == 5)
                    && $canAdd
                )
                <a id="add-risk-button" href="{{ route('corporate-risk.create', ['pid' => $pid]) }}" type="button"
                  class="btn btn-outline-info btn-sm d-flex flex-center" data-bs-toggle="tooltip"
                  data-bs-title="Tambah Risiko">
                  <span class="bx bx-plus"></span>
                  <span class="ms-1">Tambah Risiko</span>
                </a>
                @endif
              @endcan
            </div>
          </div>
        </div>
      </div>
      <div class="card-header border-bottom">
          <div class="d-flex align-items-center gap-3">
              <h6 class="mb-0">Keterangan :</h6>
              <div class="d-flex gap-3">
                  @foreach($tableLegend as $legend)
                  <div class="d-flex align-items-center gap-1">
                      {!! $legend['icon'] !!}
                      <span>{{ $legend['label'] }}</span>
                  </div>
                  @endforeach
              </div>
          </div>
      </div>
      <div class="card-body dt-header-true">
        <div class="d-flex align-items-center justify-content-end gap-3">
          @if(!empty($summaryInfo))
          <div class="alert alert-{{ $summaryInfo['type'] }} alert-dismissible fade show d-flex align-items-center mt-0 mb-3 flex-grow-1" role="alert">
              <div class="bg-{{ $summaryInfo['type'] }} text-white rounded-circle p-0 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                  <i class="bx {{ $summaryInfo['icon'] }} text-white fs-4"></i>
              </div>
              <div class="flex-grow-1 pe-4">
                  {!! $summaryInfo['message'] !!}
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          <div class="col-auto ms-auto d-flex gap-2 align-items-center">
              @if(isset($escalationConfig) && $escalationConfig['show'])
                <form id="form-eskalasi-action" action="{{ $escalationConfig['route'] }}" method="POST" class="d-inline-block">
                  @csrf
                  @if(isset($escalationConfig['parameters']) && is_array($escalationConfig['parameters']))
                    @foreach($escalationConfig['parameters'] as $name => $value)
                      <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                  @endif

                  @if(($escalationConfig['parameters']['send_type'] ?? '') == 'rev')
                    <button type="button"
                      class="btn btn-warning btn-arrow-right"
                      onclick="document.getElementById('modalKirimPerbaikanRisiko') && bootstrap.Modal.getOrCreateInstance(document.getElementById('modalKirimPerbaikanRisiko')).show()"
                      {{ ($escalationConfig['disabled'] ?? false) ? 'disabled' : '' }}>
                      {{ $escalationConfig['label'] }}
                    </button>
                  @else
                    <button type="button"
                      class="btn {{ str_contains(strtolower($escalationConfig['label']), 'publish') ? 'btn-success' : 'btn-info' }} btn-arrow-right"
                      onclick="submitEskalasiForm('form-eskalasi-action', '{{ $escalationConfig['label'] }}')"
                      {{ ($escalationConfig['disabled'] ?? false) ? 'disabled' : '' }}>
                      {{ $escalationConfig['label'] }}
                    </button>
                  @endif
                </form>
              @endif
          </div>
        </div>

        @if(!empty($batchNotes))
        <div class="alert alert-warning">
          <strong>Catatan Perbaikan:</strong> {{ $batchNotes->notes }}
        </div>
        @endif

        <div id="tableExample3">
          <div class="row g-2 mb-1">
            <div class="col-12 col-sm-4" style="display:none;">
              <label for="filter-risk-event" class="form-label d-none">Peristiwa Risiko</label>
              <select id="filter-risk-event" class="form-select select2">
                <option value="" selected>Peristiwa Risiko</option>
                @foreach($peristiwaRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-5 col-sm-2" style="display:none;">
              <label for="filter-risk-level" class="form-label d-none">Level Risiko</label>
              <select id="filter-risk-level" class="form-select js-select-hide-search">
                <option value="" selected>Level Risiko</option>
                <option value="High">High</option>
                <option value="Moderate To High">Moderate To High</option>
                <option value="Moderate">Moderate</option>
                <option value="Low To Moderate">Low To Moderate</option>
                <option value="Low">Low</option>
              </select>
            </div>
          </div>
          <div class="col-auto mb-3 d-none" id="bulk-verify-container">
            <button type="button" class="btn btn-success btn-sm align-self-center" onclick="handleBulkVerifikasiClick()">
              <span class="bx bx-check-shield"></span> Verifikasi Risiko (<span id="count-checked">0</span>)
            </button>
          </div>
          <table class="table ajax-datatable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="no-sort white-space-nowrap">
                  @if(isset($step_order) && $step_order > 0 && isset($dataBatch) && $dataBatch->step_verification == $step_order)
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="check-all-risiko" />
                  </div>
                  @endif
                </th>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="unit">Unit</th>
                <th class="sort" data-sort="unit_type">Sasaran</th>
                {{-- <th class="sort mw-20r" data-sort="kategori_jenis_risiko">T2 & T3 KBUMN</th> --}}
                <th class="sort mw-10r" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                <th class="sort mw-10r" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                {{-- <th class="sort mw-10r" data-sort="kontrol_eksisting">Jenis Kontrol Eksisting</th> --}}
                <th class="sort mw-10r" data-sort="kategori dampak">Kategori Dampak</th>
                <th class="sort mw-10r" data-sort="nilai_risiko_inherent">Nilai Risiko</th>
                <th class="sort mw-10r" data-sort="eksposure_risiko_inherent">Eksposure Risiko</th>
                <th class="sort mw-10r" data-sort="total_biaya_rencana_perlakuan">Total Biaya Rencana Perlakuan</th>
                <th class="sort mw-15r" data-sort="waktu_terpapar">Waktu Terpapar</th>
                <th class="sort" data-sort="status">Status Approval</th>
                <th class="sort" data-sort="status_risiko">Status Risiko</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($risiko as $index => $item)
              <tr>
                <td class="white-space-nowrap">
                  @if(isset($step_order) && $step_order > 0 && isset($dataBatch) && $dataBatch->step_verification == $step_order && !in_array($item->status, [\App\Models\IdentifikasiRisiko::STATUS_TERVERIFIKASI, \App\Models\IdentifikasiRisiko::STATUS_PUBLISHED]))
                  <div class="form-check mb-0">
                    <input class="form-check-input select-risiko" type="checkbox" name="selected_items[]"
                      value="{{ $item->id }}"
                      data-peristiwa="{{ $item->peristiwa_risiko }}"
                      data-deskripsi="{{ $item->deskripsi_peristiwa_risiko }}" />
                  </div>
                  @endif
                </td>
                <td class="index-number">
                  @if($item->status_risiko== 2)
                    <span class="badge bg-primary">Rekomendasi</span>
                  @elseif($item->status_risiko == 3 || $item->status_risiko == 4 || $item->status_risiko == 5)
                    <span class="badge bg-danger">Risiko Utama</span>
                  @endif
                  {{ $index + 1 }}
                </td>
                <td class="unit">
                  {{ $item->unit->name ?? '-' }}
                </td>
                <td class="unit_type">{{ $item->target_capaian_kinerja ?? '-' }}</td>
                {{-- <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title ?? '-' }} - {{ $item->jenisRisiko->title ?? '-' }}</td> --}}
                <td class="peristiwa_risiko">
                  @php
                    $add = '';
                    if ($item->riskAnalysis && $item->riskAnalysis->kategori_dampak === 'Kuantitatif' &&
                        isset($avgQuantitativeExposure) && $item->riskAnalysis->eksposur_risiko >= $avgQuantitativeExposure) {
                        $add = '<span class="badge bg-primary" data-bs-toggle="tooltip" title="Rekomendasi Risiko di atas rata-rata IRE">!</span> ';
                    } else if ($item->riskAnalysis && $item->riskAnalysis->kategori_dampak === 'Kualitatif' &&
                              $item->riskAnalysis->skala_risiko >= 20) {
                        $add = '<span class="badge bg-primary" data-bs-toggle="tooltip" title="Rekomendasi Risiko di atas rata-rata IRE">!</span> ';
                    }
                  @endphp
                  {!! $add !!}{{ $item->peristiwa_risiko ?? '-' }}
                </td>
                <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
                {{-- <td class="kontrol_eksisting">{{ $item->jenisKontrolEksisting->jenis_kontrol ?? '-' }}</td> --}}
                <td class="kategori_dampak">{{ $item->riskAnalysis->kategori_dampak ?? '-' }}</td>
                <td class="nilai_risiko" @if($item->riskAnalysis && $item->riskAnalysis->level_risiko)
                    style="background-color:
                    @switch(strtolower($item->riskAnalysis->level_risiko))
                        @case('low')
                            #14A20E
                            @break
                        @case('low to moderate')
                            #7CD020
                            @break
                        @case('moderate')
                            #FCDB2C
                            @break
                        @case('moderate to high')
                            #FEAC17
                            @break
                        @case('high')
                            #EF6345
                            @break
                        @default
                            transparent
                    @endswitch
                    ; color: @if(in_array(strtolower($item->riskAnalysis->level_risiko), ['low', 'low to moderate', 'moderate'])) #000000 @else #FFFFFF @endif;"
                @endif
                >{{ $item->riskAnalysis->skala_risiko ?? '-' }}</td>
                <td class="eksposure_risiko">{{ $item->riskAnalysis && $item->riskAnalysis->eksposur_risiko ? 'Rp ' . number_format($item->riskAnalysis->eksposur_risiko, 0, ',', '.') : '-' }}</td>
                <td class="total_biaya_rencana_perlakuan">
                  @php
                    $totalBiaya = 0;
                    if ($item->penyebabRisiko->count() > 0) {
                      foreach ($item->penyebabRisiko as $penyebab) {
                        foreach ($penyebab->perlakuanPenyebabRisikoUnit as $perlakuan) {
                          $totalBiaya += $perlakuan->biaya_perlakuan_risiko ?? 0;
                        }
                      }
                    }
                  @endphp
                  {{ $totalBiaya > 0 ? 'Rp ' . number_format($totalBiaya, 0, ',', '.') : '-' }}
                </td>
                <td class="waktu_terpapar">
                  {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') }} -
                  {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') }}
                </td>
                <td class="status">
                  @switch($item->status ?? 0)
                  @case(1)
                  Draft
                  @break
                  @case(2)
                  On Review
                  @break
                  @case(3)
                    @php
                      $canVerifyStatus = false;
                      if (isset($dataBatch) && $item->step_verification == $step_order && $dataBatch->step_verification == $step_order && in_array($item->status, [2, 3])) {
                        $canVerifyStatus = true;
                      }
                      $acceptedText = 'Accepted';
                      if ($item->step_verification == 1) {
                        $acceptedText = $canVerifyStatus
                          ? 'Need Verification Risk Owner MR'
                          : 'On Review Risk Owner MR';
                      }
                    @endphp
                    {{ $acceptedText }}
                  @break
                  @case(4)
                      @php
                        $text = 'Accepted';
                        if ($item->step_verification == 1) {
                          $text = 'Accepted by Risk Owner MR';
                        }
                      @endphp
                      {{ $text }}
                  @break
                  @case(5)
                  @php
                    $rejectedText = 'Need Revision or Rejected';
                    if ((int) $item->step_verification === 1 || (int) $item->step_verification === 0) {
                      $rejectedText = 'Rejected by Risk Owner MR';
                    }
                  @endphp
                  {{ $rejectedText }}
                  @break
                  @case(6)
                  Published
                  @break
                  @default
                  Draft
                  @endswitch
                </td>
                <td class="status_risiko text-center">
                  @if($item->is_closed)
                    <div class="badge bg-danger rounded-pill px-2 mt-auto d-inline-flex align-items-center">
                      Closed
                      @if($item->closed_at_formatted)
                        <i class="bx bx-info-circle ms-1" style="cursor:pointer;font-size:0.95em;"
                           data-bs-toggle="popover"
                           data-bs-trigger="hover focus"
                           data-bs-placement="top"
                           data-bs-content="Ditutup pada: {{ $item->closed_at_formatted }}"
                           title=""></i>
                      @endif
                    </div>
                  @else
                    <div class="badge bg-success rounded-pill px-2 mt-auto">Open</div>
                  @endif
                </td>
                <td class="white-space-nowrap">
                  @can('risk_register_view')
                  <a href="{{ route('corporate-risk.view', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                  @endcan
                  @include('corporate-risk._table_action', [
                    'item' => $item,
                    'status' => $status,
                    'step_order' => $step_order ?? 0,
                    'dataBatch' => $dataBatch ?? null,
                    'u_step' => $u_step ?? 0,
                    'unitExpired' => $unitExpired ?? false,
                  ])
                </td>
              </tr>
              @endforeach
              @php
              if (!isset($index)) {
              $index = 0;
              }
              @endphp
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Modal Verifikasi Risiko -->
<div class="modal fade" id="modalVerifikasiRisiko" tabindex="-1" aria-labelledby="verifikasiRisikoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verifikasiRisikoLabel">Verifikasi Risiko</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <h6 id="modal-peristiwa-risiko">Peristiwa Risiko: </h6>
          <p id="modal-deskripsi-risiko"></p>
        </div>
        <form id="form-verifikasi" action="" method="POST">
          @csrf
          <div class="mb-3">
            <label for="catatan-verifikasi" class="form-label">Catatan Verifikasi</label>
            <textarea class="form-control" id="catatan-verifikasi" name="catatan_verifikasi" rows="4" placeholder="Masukkan catatan verifikasi..."></textarea>
          </div>
          <input type="hidden" name="status_verifikasi" id="status-verifikasi" value="">
          <div id="bulk-ids-container"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="btn-terima-risiko">Terima Risiko</button>
        <button type="button" class="btn btn-danger" id="btn-tolak-risiko">Tolak Risiko</button>
        <button type="button" class="btn btn-muted" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Kirim Perbaikan Risiko -->
<div class="modal fade" id="modalKirimPerbaikanRisiko" tabindex="-1" aria-labelledby="kirimPerbaikanRisikoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kirimPerbaikanRisikoLabel">Kirim Perbaikan Risiko</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-kirim-perbaikan" action="{{ route('corporate-risk.send') }}" method="POST">
          @csrf
          <input type="hidden" name="periode_id" value="{{ $selectedPeriode->id ?? '' }}">
          <input type="hidden" name="unit_id" value="{{ $unitId }}">
          <input type="hidden" name="send_type" value="rev">
          <div class="mb-3">
            <label for="catatan-perbaikan" class="form-label">Catatan Perbaikan</label>
            <textarea class="form-control" id="catatan-perbaikan" name="catatan_perbaikan" rows="4" placeholder="Masukkan catatan perbaikan..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-submit" id="btn-kirim-perbaikan">Kirim Perbaikan</button>
        <button type="button" class="btn btn-muted" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Catatan -->
@include('corporate-risk._modal_catatan')
@endsection
@section('scripts')
@include('partials.risk-note-helpers')
<script>
function submitEskalasiForm(formId, actionText) {
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin melakukan "${actionText}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

function showVerifikasiModal(id, peristiwaRisiko, deskripsiRisiko) {
  document.getElementById('modal-peristiwa-risiko').textContent = 'Peristiwa Risiko: ' + peristiwaRisiko;
  document.getElementById('modal-deskripsi-risiko').textContent = deskripsiRisiko;

  const form = document.getElementById('form-verifikasi');
  form.action = '{{ url("corporate-risk") }}/' + id + '/verifikasi';
  form.reset();
  document.getElementById('status-verifikasi').value = '';
  document.getElementById('bulk-ids-container').innerHTML = '';

  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerifikasiRisiko')).show();

  document.getElementById('btn-terima-risiko').onclick = function() {
    submitVerifikasi(id, 'terima');
  };
  document.getElementById('btn-tolak-risiko').onclick = function() {
    submitVerifikasi(id, 'tolak');
  };
}

function submitVerifikasi(id, status) {
  const form = document.getElementById('form-verifikasi');
  const catatanInput = document.getElementById('catatan-verifikasi');
  if (!catatanInput.value.trim()) {
    Swal.fire({ title: 'Peringatan', text: 'Catatan verifikasi tidak boleh kosong', icon: 'warning' });
    return;
  }

  document.getElementById('status-verifikasi').value = status;
  Swal.fire({
    title: status === 'terima' ? 'Terima Risiko?' : 'Kembalikan Risiko?',
    text: status === 'terima' ? 'Risiko akan diverifikasi dan diterima' : 'Risiko akan dikembalikan untuk revisi',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: status === 'terima' ? 'Ya, Terima' : 'Ya, Kembalikan',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) form.submit();
  });
}

function showCatatanRisiko(riskId) {
    const modalElement = document.getElementById('modalCatatan');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const contentDiv = $('#catatan-content');

    contentDiv.html('<div class="d-flex justify-content-center my-4"><div class="spinner-border" role="status"><span class="visually-hidden">Memuat...</span></div></div>');

    const url = "{{ route('corporate-risk.notes', ['riskRegister' => ':id']) }}".replace(':id', riskId);

    $.ajax({
        url: url,
        type: 'GET',
        success: function(notes) {
            if (!notes.length) {
                contentDiv.html('<div class="text-center my-4"><i class="fas fa-comment-slash fa-2x text-muted mb-2"></i><p>Belum ada catatan untuk risiko ini.</p></div>');
            } else {
                let html = '';
                notes.forEach(note => {
                    const statusBadge = window.RiskNoteHelpers.statusBadge(note.status);
                    const formattedDate = window.RiskNoteHelpers.formatDateWib(note.created_at);

                    html += `
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                            <div class="fw-bold">
                                ${note.user ? note.user.name : 'User Tidak Ditemukan'}
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <small class="text-muted">${formattedDate}</small>
                                ${statusBadge}
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <p class="card-text mb-0">${note.notes || '<i>Tidak ada catatan.</i>'}</p>
                        </div>
                    </div>
                    `;
                });
                contentDiv.html(html);
            }
            modal.show();
        },
        error: function() {
            contentDiv.html('<div class="text-center my-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Gagal memuat catatan.</p></div>');
            modal.show();
        }
    });
}

function handleBulkVerifikasiClick() {
    const checked = $('.select-risiko:checked');
    if (!checked.length) return;

    const idsContainer = document.getElementById('bulk-ids-container');
    idsContainer.innerHTML = '';
    checked.each(function() {
        idsContainer.innerHTML += `<input type="hidden" name="ids[]" value="${$(this).val()}">`;
    });

    document.getElementById('modal-peristiwa-risiko').textContent = `Bulk Verifikasi (${checked.length} risiko)`;
    document.getElementById('modal-deskripsi-risiko').textContent = 'Verifikasi massal untuk risiko yang dipilih.';
    const form = document.getElementById('form-verifikasi');
    form.action = '{{ route("corporate-risk.bulk-verifikasi") }}';
    form.reset();
    document.getElementById('status-verifikasi').value = '';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerifikasiRisiko')).show();
    document.getElementById('btn-terima-risiko').onclick = function() { submitVerifikasi(null, 'terima'); };
    document.getElementById('btn-tolak-risiko').onclick = function() { submitVerifikasi(null, 'tolak'); };
}

$(document).ready(function() {
  const table = $('#example').DataTable({
      "paging": true,
      "info": true,
      "searching": true,
      "columnDefs": [
        {
          "searchable": false,
          "orderable": false,
          "targets": 0
        }
      ],
      "order": [[1, 'asc']],
      "layout": {
        "topEnd": {
            "search": {
                "placeholder": 'Search...'
            }
        }
      }
  });

  $('#filter-risk-event').on('change', function() {
    table.column(4).search($(this).val()).draw();
  });

  $('#filter-risk-level').on('change', function() {
    table.column(8).search($(this).val()).draw();
  });

  $('#check-all-risiko').on('change', function() {
    $('.select-risiko').prop('checked', this.checked);
    toggleBulkVerify();
  });

  $(document).on('change', '.select-risiko', toggleBulkVerify);

  function toggleBulkVerify() {
    const count = $('.select-risiko:checked').length;
    $('#count-checked').text(count);
    if (count > 0) $('#bulk-verify-container').removeClass('d-none');
    else $('#bulk-verify-container').addClass('d-none');
  }

  $('#btn-kirim-perbaikan').on('click', function() {
    const catatan = $('#catatan-perbaikan').val().trim();
    if (!catatan) {
      Swal.fire({ title: 'Peringatan', text: 'Catatan perbaikan tidak boleh kosong', icon: 'warning' });
      return;
    }
    $('#form-kirim-perbaikan').submit();
  });
});

document.addEventListener('DOMContentLoaded', function() {
  const status = {{ $status ?? 'null' }};
  const addRiskButton = document.getElementById('add-risk-button');
  if (addRiskButton) {
    addRiskButton.addEventListener('click', function(event) {
      if (status !== null && status != 1 && status != 5) {
        event.preventDefault();
        alert('Belum bisa menambah data risiko karena sedang dalam proses konfirmasi.');
      }
    });
  }
});
</script>
@endsection
