@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row justify-content-center g-3 g-xl-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Peristiwa Risiko</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a href="{{ route('peristiwa-risiko.create') }}" class="btn btn-outline-info btn-sm">
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
        <table class="table table-bulk-select table-hover dataTable" data-paging="true" data-filter="true">
          <thead>
            <tr>
              <th class="white-space-nowrap no-sort">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox"
                    data-bulk-select='{"body":"bulk-select-body","actions":"bulk-select-actions","replacedElement":"bulk-select-replace-element"}' />
                </div>
              </th>
              <th class="white-space-nowrap">#</th>
              <th class="mw-10r">Peristiwa Risiko</th>
              <th class="mw-10r">Kategori Risiko</th>
              <th>Jenis Risiko</th>
              <th class="no-sort white-space-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="list" id="bulk-select-body">
            @foreach ($peristiwaRisiko as $index => $item)
            <tr>
              <td class="white-space-nowrap">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="checkbox-1"
                    data-bulk-select-row="data-bulk-select-row" />
                </div>
              </td>
              <td class="index-number">{{ $index + 1 }}</td>
              <td>{{ $item->title }}</td>
              <td>{{ $item?->kategoriRisiko?->title ?? '-' }}</td>
              <td>{{ $item?->jenisRisiko?->title ?? '-' }}</td>
              <td class="white-space-nowrap no-sort">
                @if ($item->trashed())
                <button type="submit" class="btn-input-icon ps-0" data-bs-toggle="modal"
                  data-bs-target="#modalRestore{{ $item->id }}">
                  <span class="bx bx-undo" data-bs-toggle="tooltip" title="Undo"></span>
                </button>
                @php
                $itemId = $item->id;
                $innerItemText = $item->title;
                $formAction = route('peristiwa-risiko.restore', $item->id);
                @endphp
                @include('partials.modal-restore-alert')
                @else
                <a href="{{ route('peristiwa-risiko.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
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
                $formAction = route('peristiwa-risiko.destroy', $item->id);
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
  @endsection
