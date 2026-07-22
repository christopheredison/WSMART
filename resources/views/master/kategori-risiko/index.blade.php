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
          <h2 class="h3">Kategori Risiko</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#newKategori">
              <span class="bx bx-plus"></span>
              <span class="ms-1">New</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="position-relative">
          <div class="row row-bulk-select g-2">
            <div class="col-6 col-md-4 col-lg-3 col-xxl-2 mb-3 d-none" id="bulk-select-actions">
              <div class="d-flex">
                <select class="form-select js-select-hide-search" aria-label="Bulk actions">
                  <option selected="selected">Bulk actions</option>
                  <option value="Delete">Delete</option>
                  <option value="Archive">Archive</option>
                </select>
                <button class="btn btn-muted btn-sm" type="submit">Apply</button>
              </div>
            </div>
          </div>
        </div>
        <table class="table table-bulk-select table-hover dataTable" data-scroll-y="false" data-paging="true"
          data-filter="true">
          <thead>
            <tr>
              <th class="no-sort white-space-nowrap">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox"
                    data-bulk-select='{"body":"bulk-select-body","actions":"bulk-select-actions","replacedElement":"bulk-select-replace-element"}' />
                </div>
              </th>
              <th class="sort white-space-nowrap" data-sort="no">#</th>
              <th class="sort" data-sort="title">Kategori</th>
              <th class="sort white-space-nowrap" data-sort="action">Action</th>
            </tr>
          </thead>
          <tbody class="list" id="bulk-select-body">
            @foreach ($kategoriRisiko as $index => $item)
            <tr>
              <td class="white-space-nowrap">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="checkbox-1"
                    data-bulk-select-row="data-bulk-select-row" />
                </div>
              </td>
              <td class="index-number">{{ $index + 1 }}</td>
              <td class="title">{{ $item->title }}</td>
              <td class="no-sort white-space-nowrap">
                @if ($item->trashed())
                <button type="submit" class="btn-input-icon ps-0" data-bs-toggle="modal"
                  data-bs-target="#modalRestore{{ $item->id }}">
                  <span class="bx bx-undo" data-bs-toggle="tooltip" title="Undo"></span>
                </button>
                @php
                $itemId = $item->id;
                $innerItemText = $item->title;
                $formAction = route('kategori-risiko.restore', $item->id);
                @endphp
                @include('partials.modal-restore-alert')
                @else
                <a href="{{ route('kategori-risiko.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                  title="Edit">
                  <span class="bx bx-edit"></span>
                </a>
                <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                  data-bs-target="#modalDelete{{ $item->id }}">
                  <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                </button>
                @php
                $itemId = $item->id;
                $innerItemText = $item->title;
                $formAction = route('kategori-risiko.destroy', $item->id);
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
<!-- Modal New Kategori Risiko -->
<div class="modal fade" id="newKategori" tabindex="-1" role="dialog" aria-labelledby="newKategoriLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header flex-between-center">
        <h3 class="modal-title h4" id="newTCKLabel">Buat Kategori Risiko Baru</h3>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('kategori-risiko.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-floating">
            <input class="form-control" id="title" name="title" type="text" placeholder="Masukkan Kategori Risiko"
              value="{{ old('title') }}" />
            <label for="title">Kategori Risiko</label>
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