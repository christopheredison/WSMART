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
          <h2 class="h3">Skala Dampak</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#newSkalaDampak">
              <span class="bx bx-plus"></span>
              <span class="ms-1">New</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive scrollbar">
          <table class="table table-hover">
            <thead>
              <tr>
                <th class="white-space-nowrap text-center" data-sort="tingkat">Tingkat</th>
                <th>Deskripsi</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($skalaDampak as $index => $item)
              <tr>
                <td class="tingkat white-space-nowrap text-center ff-heading-sm">{{ $item->tingkat }}</td>
                <td class="deskripsi">{{ $item->deskripsi }}</td>
                <td class="white-space-nowrap">
                  @if ($item->trashed())
                  <button type="submit" class="btn-input-icon ps-0" data-bs-toggle="modal"
                    data-bs-target="#modalRestore{{ $item->id }}">
                    <span class="bx bx-undo" data-bs-toggle="tooltip" title="Undo"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->deskripsi;
                  $formAction = route('skala-dampak.restore', $item->id);
                  @endphp
                  @include('partials.modal-restore-alert')
                  @else
                  <a href="{{ route('skala-dampak.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Edit">
                    <span class="bx bx-edit"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item->id }}">
                    <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->deskripsi;
                  $formAction = route('skala-dampak.destroy', $item->id);
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
<!-- Modal New Area Dampak -->
<div class="modal fade" id="newSkalaDampak" tabindex="-1" role="dialog" aria-labelledby="newSkalaDampakLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg " role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('skala-dampak.store') }}">
        @csrf
        <div class="modal-header d-flex flex-between-center">
          <h3 class="modal-title h4" id="newSkalaDampakLabel">Buat Skala Dampak Baru</h3>
          <div class="lead__icon lead__icon_sm">
            <div class="svg-icon svg-icon-secondary">
              @include('partials.icon-tool')
            </div>
          </div>
        </div>
        <div class="modal-body">
          <div class="form-group d-md-flex mb-4">
            <label class="form-label label-md-start col-md-2" for="tingkat">Tingkat</label>
            <input class="form-control" id="tingkat" name="tingkat" type="number"
              placeholder="Masukkan Tingkat Skala Dampak" value="{{ old('tingkat') }}" />
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-2" for="deskripsi">Deskripsi</label>
            <input type="text" class="form-control" id="deskripsi" name="deskripsi"
              placeholder="Masukkan Deskripsi Skala Dampak" value="{{ old('deskripsi') }}"></input>
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
