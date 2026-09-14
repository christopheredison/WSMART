@php
    $unitExpired = $unitExpired ?? false;
    $is_mr = auth()->user()->unit ? (auth()->user()->unit->unit_mr == 1) : false;
    $userLevel = auth()->user()->level_id;
    $step_order = $step_order ?? ($u_step ?? 0);

    $canEdit = false;
    $canAnalisa = false;

    // Risk Officer MR (inputter) — level 1 + unit_mr
    if ($userLevel == 1 && $is_mr) {
        if ($item->status == 1 || $item->status == null || $item->status == 5) {
            $canEdit = true;
            $canAnalisa = true;
        }
    }

    $canVerify = false;
    if (
        isset($dataBatch)
        && $item->step_verification == $step_order
        && $dataBatch->step_verification == $step_order
        && in_array($item->status, [2, 3])
    ) {
        $canVerify = true;
    }
@endphp

@if(!$unitExpired)

  @if($canEdit)
    @can('risk_register_edit')
      <a href="{{ route('corporate-risk.edit', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Edit Data Risiko">
          <span class="bx bx-message-square-edit"></span>
      </a>
    @endcan

    @can('risk_register_edit')
      @if($canAnalisa)
      <a href="{{ route('corporate-risk.analisa', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Analisa Risiko">
        <span class="bx bx-analyse text-warning"></span>
      </a>
      @endif

      <a href="{{ route('corporate-risk.perencanaan', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Rencana Perlakuan Risiko">
        <span class="bx bx-task text-primary"></span>
      </a>
    @endcan
  @endif

  @can('risk_register_verification')
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
  @if(($canEdit) || auth()->user()->can('risk_register_delete_admin'))
  @can('risk_register_delete')
    <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $item->id }}">
      <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
    </button>
    @php
    $itemId = $item->id;
    $innerItemText = $item->peristiwa_risiko ?: '-';
    $formAction = route('corporate-risk.destroy', $item->id);
    @endphp
    @include('partials.modal-delete-alert')
  @endcan
  @endif
@endif
