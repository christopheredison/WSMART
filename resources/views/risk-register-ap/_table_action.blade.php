@php
    $canEdit = false;
    $canAnalisa = false;
    
    // Logic untuk Drafter (Officer Divisi / AP)
    if (auth()->user()->level_id == 1 && auth()->user()->unit_id == $item->unit_id) {
        if ($item->status == 1 || $item->status == null || $item->status == 5) {
            $canEdit = true;
            // Jika BUKAN sedang dalam fase Unlocked Request Edit, tombol Analisa muncul
            if ($item->request_edit != 2) {
                $canAnalisa = true; 
            }
        }
    }
@endphp

@if(!$unitExpired)

  @can('risk_register_reopen')
    @if($item->is_closed)
      <button type="button" class="btn-input-icon" onclick='reopenRiskRegisterAp({{ $item->id }}, @json($item->peristiwa_risiko ?? "Risiko"))'>
        <span class="bx bx-reset text-warning" data-bs-toggle="tooltip" title="Re-open Risiko"></span>
      </button>
    @endif
  @endcan

  @if($canEdit)
    @can('risk_register_edit')
      <a href="{{ route('risk-register-ap.edit', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Edit Data Risiko">
          <span class="bx bx-message-square-edit"></span>
      </a>
    @endcan

    @can('risk_register_edit')
      @if($canAnalisa)
      <a href="{{ route('risk-register-ap.analisa', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Analisa Risiko">
        <span class="bx bx-analyse text-warning"></span>
      </a>
      @endif

      <a href="{{ route('risk-register-ap.perencanaan', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Rencana Perlakuan Risiko">
        <span class="bx bx-task text-primary"></span>
      </a>
    @endcan
  @endif

  @if(auth()->user()->level_id == 1 && auth()->user()->unit_id == $item->unit_id && $item->status == 6 && $item->request_edit != 1)
      <button type="button" class="btn-input-icon" onclick="showRequestEditModal({{ $item->id }})">
          <span class="bx bx-message-square-edit text-info" data-bs-toggle="tooltip" title="Request Edit Risiko"></span>
      </button>
  @endif

  @if(\App\Models\IdentifikasiRisiko::canVerifyRequestEdit(auth()->user(), 'ap') && $item->status == 6 && $item->request_edit == 1)
      <button type="button" class="btn-input-icon" onclick='approveRequestEdit({{ $item->id }}, @json($item->request_edit_reason), @json($item->peristiwa_risiko), @json($item->unit->name ?? "Unit Tidak Diketahui"))'>
          <span class="bx bx-check-double text-success" data-bs-toggle="tooltip" title="Setujui Request Edit"></span>
      </button>
  @endif

  @if(\App\Models\IdentifikasiRisiko::canVerifyRequestEdit(auth()->user(), 'ap') && (int) $item->close_request === 1 && !$item->is_closed)
      <button type="button" class="btn-input-icon" onclick='verifyCloseRequest({{ $item->id }}, @json($item->close_request_reason), @json($item->peristiwa_risiko), @json(optional($item->closeRequestedBy)->name ?? "-"), @json($item->close_requested_at_formatted ?? "-"))'>
          <span class="bx bx-lock-alt text-warning" data-bs-toggle="tooltip" title="Verifikasi Penutupan Risiko"></span>
      </button>
  @endif



  @can('risk_register_verification')
  @php
      $canVerify = false;
      if ($item->step_verification == $step_order && $dataBatch->step_verification == $step_order && ($item->status == 2 || $item->status == 3 )) {
          $canVerify = true;
      }
  @endphp
  @if($canVerify)
  <button type="button" class="btn-input-icon" onclick='showVerifikasiModal({{ $item->id }}, @json($item->peristiwa_risiko), @json($item->deskripsi_peristiwa_risiko))'>
    <span class="bx bx-check-shield text-success" data-bs-toggle="tooltip" title="Verifikasi Risiko"></span>
  </button>
  @endif
  @endcan

@endif

<a href="javascript:void(0)" class="btn-input-icon" data-id="{{ $item->id }}" onclick="showCatatanRisiko({{ $item->id }})" data-bs-toggle="tooltip" title="Lihat Catatan">
  <span class="bx bx-comment-dots"></span>
</a>

@if(!$unitExpired)
  @if((($item->status == 1 || $item->status == null || $item->status == 5) && (auth()->user()->level_id == 1 && auth()->user()->unit_id == $item->unit_id)) || auth()->user()->can('risk_register_delete_admin'))
  @can('risk_register_delete')
    <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $item->id }}">
      <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
    </button>
    @php
    $itemId = $item->id;
    $innerItemText = $item->peristiwa_risiko ?: '-';
    $formAction = route('risk-register-ap.destroy', $item->id);
    @endphp
    @include('partials.modal-delete-alert')
  @endcan
  @endif
@endif