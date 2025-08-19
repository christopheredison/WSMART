
@if($item->status == 1 || $item->status == null || $item->status == 5)
  @can('risk_register_edit')
    <a href="{{ route('risk-register-unit.edit', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
      title="Edit"><span class="bx bx-message-square-edit"></span></a>
  @endcan
  <!-- Tambahkan tombol Analisa Risiko di sini -->
  @can('risk_register_edit')
    <a href="{{ route('risk-register-unit.analisa', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
      title="Analisa Risiko">
      <span class="bx bx-analyse text-warning"></span>
    </a>
    <a href="{{ route('risk-register-unit.perencanaan', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
      title="Rencana Perlakuan Risiko">
      <span class="bx bx-task text-primary"></span>
    </a>
  @endcan
@endif

@can('risk_register_verification')
@php
    // Cek apakah level user terdaftar di ApprovalStep unit terkait
    $userLevelId = auth()->user()->level_id;
    $unitId = $item->unit_id;
    $approvalFlow = \App\Models\ApprovalFlow::where('unit_id', $unitId)->first();
    $canVerify = false;
    
    if ($approvalFlow) {
        $approvalStep = \App\Models\ApprovalStep::where('approval_flow_id', $approvalFlow->id)
            ->where('level_id', $userLevelId)
            ->first();
        
        // Cek apakah step_verification risiko = step_order level terkait
        if ($approvalStep && $item->step_verification == $approvalStep->step_order && $item->status_progress == 1) {
            $canVerify = true;
        }
    }
@endphp
@if($canVerify)
<button type="button" class="btn-input-icon" onclick="showVerifikasiModal({{ $item->id }}, '{{ addslashes($item->peristiwa_risiko) }}', '{{ addslashes($item->deskripsi_peristiwa_risiko) }}')">
  <span class="bx bx-check-shield text-success" data-bs-toggle="tooltip" title="Verifikasi Risiko"></span>
</button>
@endif
@endcan

@if($item->status == 1 || $item->status == null || $item->status == 5)
@can('risk_register_delete')
  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
    data-bs-target="#modalDelete{{ $item->id }}">
    <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
  </button>
  @php
  $itemId = $item->id;
  $innerItemText = $item->peristiwa_risiko ?: '-';
  $formAction = route('risk-register-unit.destroy', $item->id);
  @endphp
  @include('partials.modal-delete-alert')
@endcan
@endif