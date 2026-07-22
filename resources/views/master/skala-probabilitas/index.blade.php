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
          <h2 class="h3">Skala Probabilitas</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#newSkalaProbabilitas">
              <span class="bx bx-plus"></span>
              <span class="ms-1">New</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="tableExample3">
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
          <table class="table table-bulk-select table-hover dataTable" data-filter="true" data-paging="true"
            data-select="true">
            <thead>
              <tr>
                <th class="no-sort white-space-nowrap">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox"
                      data-bulk-select='{"body":"bulk-select-body","actions":"bulk-select-actions","replacedElement":"bulk-select-replace-element"}' />
                  </div>
                </th>
                <th class="white-space-nowrap" data-sort="no">#</th>
                <th class="text-center">Min</th>
                <th class="text-center">Max</th>
                <th class="white-space-nowrap text-center">Type Risiko</th>
                <th class="text-center">Tingkat</th>
                <th>Skala</th>
                <th class="mw-10r">Deskripsi</th>
                <th class="no-sort white-space-nowrap">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($skalaProbabilitas as $index => $item)
              <tr>
                <td class="white-space-nowrap">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="checkbox-1"
                      data-bulk-select-row="data-bulk-select-row" />
                  </div>
                </td>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="min text-center">{{ $item->min }}</td>
                <td class="max text-center">{{ $item->max }}</td>
                <td class="type_risiko text-center">{{ $item->type_risiko }}</td>
                <td class="tingkat text-center">{{ $item->tingkat }}</td>
                <td class="skala">{{ $item->skala }}</td>
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
                  $formAction = route('skala-probabilitas.restore', $item->id);
                  @endphp
                  @include('partials.modal-restore-alert')
                  @else
                  <a href="{{ route('skala-probabilitas.edit', $item) }}" class="btn-input-icon"
                    data-bs-toggle="tooltip" title="Edit">
                    <span class="bx bx-edit"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item->id }}">
                    <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->deskripsi;
                  $formAction = route('skala-probabilitas.destroy', $item->id);
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
<!-- Modal New Skala Probabilitas -->
<div class="modal fade" id="newSkalaProbabilitas" tabindex="-1" role="dialog"
  aria-labelledby="newSkalaProbabilitasLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex flex-between-center">
        <h4 class="modal-title">Buat Skala Probabilitas Baru</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('skala-probabilitas.store') }}">
        @csrf
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-6 col-xl-3">
              <div class="form-floating text-center">
                <input class="form-control" id="min" name="min" type="number" placeholder="Masukkan Min"
                  value="{{ old('min') }}" />
                <label for="min">Minimum</label>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="form-floating text-center">
                <input class="form-control" id="max" name="max" type="number" placeholder="Masukkan Max"
                  value="{{ old('max') }}" />
                <label for="max">Maksimum</label>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="form-floating text-center">
                <input class="form-control" id="tingkat" name="tingkat" type="number" placeholder="Masukkan Tingkat"
                  value="{{ old('tingkat') }}" />
                <label for="tingkat">Tingkat Probabilitas</label>
              </div>
            </div>
            <div class="col-6 col-xl-3">
              <div class="form-floating text-center">
                <input class="form-control" id="skala" name="skala" type="text" placeholder="Masukkan Skala"
                  value="{{ old('skala') }}" placeholder="Masukkan Skala" />
                <label for="skala">Skala</label>
              </div>
            </div>
            <div class="col-12 d-flex align-items-center my-5">
              <label class="form-label mb-0 me-4 pt-1">Type</label>
              <div class="d-flex">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="type_risiko" id="type_umum" value="Umum"
                    {{ old('type_risiko', 'Umum') === 'Umum' ? 'checked' : '' }}>
                  <label class="form-check-label" for="type_umum">Umum</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="type_risiko" id="type_medis" value="Medis"
                    {{ old('type_risiko') === 'Medis' ? 'checked' : '' }}>
                  <label class="form-check-label" for="type_medis">Medis</label>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="form-floating">
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" value="{{ old('deskripsi') }}"
                  placeholder=""></textarea>
                <label for="title">Deskripsi</label>
              </div>
            </div>
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