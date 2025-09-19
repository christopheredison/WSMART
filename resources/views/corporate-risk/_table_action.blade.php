
@can('risk_register_edit')
  <a href="{{ route('corporate-risk.edit', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
    title="Edit"><span class="bx bx-message-square-edit"></span></a>
@endcan
<!-- Tambahkan tombol Analisa Risiko di sini -->
@can('risk_register_edit')
  <a href="{{ route('corporate-risk.analisa', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
    title="Analisa Risiko">
    <span class="bx bx-analyse text-warning"></span>
  </a>
  <a href="{{ route('corporate-risk.perencanaan', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
    title="Rencana Perlakuan Risiko">
    <span class="bx bx-task text-primary"></span>
  </a>
@endcan

{{-- @can('risk_register_verification')
<button type="button" class="btn-input-icon" onclick="showVerifikasiModal({{ $item->id }}, '{{ addslashes($item->peristiwa_risiko) }}', '{{ addslashes($item->deskripsi_peristiwa_risiko) }}')">
  <span class="bx bx-check-shield text-success" data-bs-toggle="tooltip" title="Verifikasi Risiko"></span>
</button>
@endcan --}}

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