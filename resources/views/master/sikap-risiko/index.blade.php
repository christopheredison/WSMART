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
          <h2 class="h3">Sikap Risiko</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#newSikapRisiko">
              <span class="bx bx-plus"></span>
              <span class="ms-1">New</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive scrollbar">
          <table class="table table-hover mb-sm-0">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th>Jenis Sikap</th>
                <th class="no-sort white-space-nowrap">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($sikapRisiko as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="sikap">{{ $item->jenis_sikap }}</td>
                <td class="white-space-nowrap">
                  @if ($item->trashed())
                  <button type="submit" class="btn-input-icon ps-0" data-bs-toggle="modal"
                    data-bs-target="#modalRestore{{ $item->id }}">
                    <span class="bx bx-undo" data-bs-toggle="tooltip" title="Undo"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->jenis_sikap;
                  $formAction = route('sikap-risiko.restore', $item->id);
                  @endphp
                  @include('partials.modal-restore-alert')
                  @else
                  <a href="{{ route('sikap-risiko.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Edit">
                    <span class="bx bx-edit"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item->id }}">
                    <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->jenis_sikap;
                  $formAction = route('sikap-risiko.destroy', $item->id);
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
<!-- Modal New Sikap Risiko -->
<div class="modal fade" id="newSikapRisiko" tabindex="-1" role="dialog" aria-labelledby="newSikapRisikoLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header flex-between-center">
        <h4 class="modal-title">Buat Sikap Risiko Baru</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('sikap-risiko.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-floating">
            <input class="form-control" id="jenis_sikap" name="jenis_sikap" type="text"
              placeholder="Masukkan Jenis Sikap" value="{{ old('jenis_sikap') }}" />
            <label for="jenis_sikap">Jenis Sikap</label>
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
