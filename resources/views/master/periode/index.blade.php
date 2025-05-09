@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Periode</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#newPeriode">
              <span class="bx bx-plus"></span>
              <span class="ms-1">New</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-0 mb-3">
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <form action="{{ route('change-active-period') }}" method="POST">
              @csrf
              <div class="btn-group w-100">
                <select class="form-select js-select-hide-search" name="periode" id="periode">
                  <option selected disabled>Periode aktif</option>
                  @foreach ($periode as $item)
                  <option value="{{ $item->id }}">{{ $item->tahun }}</option>
                  @endforeach
                </select>
                <button type="submit" class="btn btn-secondary">Ganti Status</button>
              </div>
            </form>
          </div>
        </div>
        <div class="table-responsive-sm scrollbar">
          <table class="table table-hover mb-md-0">
            <thead>
              <tr>
                <th class="white-space-nowrap" data-sort="no">#</th>
                <th>Tahun</th>
                <th class="text-center" data-sort="status">Status</th>
                <th class="white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($periode as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="tahun">{{ $item->tahun }}</td>
                <td class="status text-center">
                  <figure class="badge bg-success">
                    @if($item->status == 'active')
                    Aktif
                    @elseif($item->status == 'non-active')
                    Tidak Aktif
                    @endif
                  </figure>
                </td>
                <td class="white-space-nowrap">
                  @if ($item->trashed())
                  <button type="submit" class="btn-input-icon ps-0" data-bs-toggle="modal"
                    data-bs-target="#modalRestore{{ $item->id }}">
                    <span class="bx bx-undo" data-bs-toggle="tooltip" title="Undo"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->tahun;
                  $formAction = route('periode.restore', $item->id);
                  @endphp
                  @include('partials.modal-restore-alert')
                  @else
                  <a href="{{ route('periode.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Edit">
                    <span class="bx bx-edit"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item->id }}">
                    <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->tahun;
                  $formAction = route('periode.destroy', $item->id);
                  @endphp
                  @include('partials.modal-delete-alert')
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
<!-- Modal New Periode -->
<div class="modal fade" id="newPeriode" tabindex="-1" role="dialog" aria-labelledby="newPeriodeLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header flex-between-center">
        <h2 class="h4">Buat Periode Baru</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('periode.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group d-flex">
            <label class="form-label label-start col-4" for="tahun">Tahun</label>
            <input class="form-control" id="tahun" name="tahun" type="number" placeholder="Masukkan Tahun"
              value="{{ old('tahun') }}" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
// Get badge status
var elList = Array.prototype.slice.call(
  document.querySelectorAll("figure"));

elList.forEach(function(el) {
  if (el.textContent.indexOf("Tidak Aktif") > -1) {
    console.log(el);
    el.classList.remove("bg-success");
    el.classList.add("tx-g400");
  }
});
</script>
@endsection
