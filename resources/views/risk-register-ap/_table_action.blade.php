
@if(!$unitExpired)
  @if((
    $item->status == 1
    || $item->status == null
    || $item->status == 5
  ) && (
    auth()->user()->level_id == 1
    // && auth()->user()->unit_id == $item->unit_id
  ))
    @can('risk_register_edit')
      <a href="{{ route('risk-register-ap.edit', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
        title="Edit"><span class="bx bx-message-square-edit"></span></a>
    @endcan
    <!-- Tambahkan tombol Analisa Risiko di sini -->
    @can('risk_register_edit')
      <a href="{{ route('risk-register-ap.analisa', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
        title="Analisa Risiko">
        <span class="bx bx-analyse text-warning"></span>
      </a>
      <a href="{{ route('risk-register-ap.perencanaan', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
        title="Rencana Perlakuan Risiko">
        <span class="bx bx-task text-primary"></span>
      </a>
    @endcan
  @endif
@endif

@if(!$unitExpired)
  @can('risk_register_verification')
  @php
      $canVerify = false;
      if ($item->step_verification == $step_order && $dataBatch->step_verification == $step_order && ($item->status == 2 || $item->status == 3 )) {
          $canVerify = true;
      }
  @endphp
  @if($canVerify)
  <button type="button" class="btn-input-icon" onclick="showVerifikasiModal({{ $item->id }}, '{{ json_encode($item->peristiwa_risiko) }}', '{{ json_encode($item->deskripsi_peristiwa_risiko) }}')">
    <span class="bx bx-check-shield text-success" data-bs-toggle="tooltip" title="Verifikasi Risiko"></span>
  </button>
  @endif
  @endcan
@endif

<a href="javascript:void(0)" class="btn-input-icon" data-id="{{ $item->id }}" onclick="showCatatanRisiko({{ $item->id }})" data-bs-toggle="tooltip" title="Lihat Catatan">
  <span class="bx bx-comment-dots"></span>
</a>

@if(!$unitExpired)
  @if((
    $item->status == 1
    || $item->status == null
    || $item->status == 5
  ) && (
    auth()->user()->level_id == 1
    // && auth()->user()->unit_id == $item->unit_id
  ))
  @can('risk_register_delete')
    <button type="button" class="btn-input-icon" data-bs-toggle="modal"
      data-bs-target="#modalDelete{{ $item->id }}">
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
