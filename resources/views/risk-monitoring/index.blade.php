@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col mb-4 mb-sm-0 d-flex align-items-center gap-3">
            <div class="bg-info-subtle p-2 rounded-4">
              <div class="lead__icon">
                <div class="svg-icon svg-icon-2x svg-icon-info">
                  @include('partials.icon-search-alt')
                </div>
              </div>
            </div>
            <div class="d-block">
              <div class="ff-preheading">Input Data</div>
              <h2>Risk Monitoring</h2>
            </div>
          </div>
          <div class="col-12 col-sm-auto col-lg-3 col-xl-4 col-xxl-3 ms-auto ps-xl-6 px-xxl-0">
            <div class="d-md-flex">
              <label for="filter-quarter" class="form-label label-md-start ms-xl-7">Periode
                Monitoring</label>
              <select id="filter-quarter" class="form-select form-select-sm js-select-hide-search">
                <option value="">Semua</option>
                <option value="Quarter 1">Quarter 1</option>
                <option value="Quarter 2">Quarter 2</option>
                <option value="Quarter 3">Quarter 3</option>
                <option value="Quarter 4">Quarter 4</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="tableExample3" class="dt-header-base">
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
          <table class="table table-bulk-select dataTable" id="example" data-paging="true" data-info="true"
            data-filter="true">
            <thead>
              <tr>
                <th class="sort white-space-nowrap" data-sort="no">#</th>
                <th class="sort" data-sort="area">Periode Monitoring</th>
                <th class="sort mw-10r" data-sort="type">Peristiwa Risiko</th>
                <th class="sort white-space-nowrap" data-sort="action">Deskripsi Peristiwa Risiko</th>
                <th class="sort text-center" data-sort="action">Realisasi Nilai Dampak</th>
                <th class="sort text-center" data-sort="action">Realisasi Skala Dampak</th>
                <th class="sort text-center" data-sort="action">Realisasi Nilai Probabilitas</th>
                <th class="sort" data-sort="action">Realisasi Skala Probabilitas</th>
                <th class="sort text-center" data-sort="action">Realisasi Level Risiko</th>
                <th class="no-sort">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($riskMonitoring as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="area">{{ $item->periode_monitoring }}</td>
                <td class="type">{{ $item->identifikasiRisiko->peristiwaRisiko->title }}</td>
                <td class="type">{{ $item->identifikasiRisiko->deskripsi_peristiwa_risiko }}</td>
                <td class="area">{{ $item->realisasi_nilai_dampak }}</td>
                <td class="area">{{ $item->realisasi_skala_dampak }}</td>
                <td class="area">{{ $item->realisasi_nilai_probabilitas }}</td>
                <td class="area white-space-nowrap">{{ $item->realisasi_skala_probabilitas }}</td>
                <td class="area level_risiko">
                  @php
                  $level_risiko = $item->realisasi_level_risiko;
                  $badge_class = '';

                  if ($level_risiko == "High") {
                  $badge_class = 'high';
                  } elseif ($level_risiko == "high") {
                  $badge_class = 'high';
                  } elseif ($level_risiko == "Moderate to High") {
                  $badge_class = 'medium-high';
                  } elseif ($level_risiko == "moderate to high") {
                  $badge_class = 'medium-high';
                  } elseif ($level_risiko == "Moderate") {
                  $badge_class = 'medium';
                  } elseif ($level_risiko == "moderate") {
                  $badge_class = 'medium';
                  } elseif ($level_risiko == "Low to Moderate") {
                  $badge_class = 'low-medium';
                  } elseif ($level_risiko == "low to moderate") {
                  $badge_class = 'low-medium';
                  } elseif ($level_risiko == "Low") {
                  $badge_class = 'low';
                  } elseif ($level_risiko == "low") {
                  $badge_class = 'low';
                  }
                  @endphp
                  <span class="badge {{ $badge_class }}">{{ $level_risiko }}</span>
                </td>
                <td class="white-space-nowrap">
                  @if ($item->files && $item->files->isNotEmpty())
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#fileModal-{{ $index }}">
                    <span class="bx bx-file-find" data-bs-toggle="tooltip" title="View File"></span>
                  </button>
                  @endif
                  @can('risk_monitoring_input')
                  <a href="{{ route('risk-monitoring.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Edit">
                    <span class="bx bx-message-square-edit"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#uploadModal"
                    data-id="{{ $item->id }}">
                    <span class="bx bx-upload" data-bs-toggle="tooltip" title="Upload"></span>
                  </button>
                  @endcan
                </td>
              </tr>
              <!-- File Modal -->
              @if ($item->files && $item->files->isNotEmpty())
              <div class="modal fade" id="fileModal-{{ $index }}" tabindex="-1"
                aria-labelledby="fileModalLabel-{{ $index }}" aria-hidden="true">
                <div class="modal-dialog modal-md">
                  <div class="modal-content">
                    <div class="modal-header pb-0 d-block">
                      <h4 id="fileModalLabel-{{ $index }}">
                        View Files
                      </h4>
                      <span class="text-wrap">Files for {{ $item->identifikasiRisiko->peristiwaRisiko->title }}</span>
                      <hr class="my-5">
                    </div>
                    <div class="modal-body pt-0">
                      <ul class="list-unstyled">
                        @foreach ($item->files as $file)
                        <li class="mb-3">
                          <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ asset('storage/' . $file->file_path) }}"
                              target="_blank">{{ basename($file->file_name) }}</a>
                            <form action="{{ route('risk-monitoring.delete-file', ['id' => $file->id]) }}"
                              method="POST">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn-input-icon text-danger" data-bs-toggle="tooltip"
                                title="Delete" onclick="return confirm('Are you sure you want to delete this file?')">
                                <span class="bx bx-trash pt-1"></span>
                              </button>
                            </form>
                          </div>
                        </li>
                        @endforeach
                      </ul>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
              @endif
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header d-flex flex-between-center">
        <h4 class="modal-title">Upload Files</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form action="{{ route('risk-monitoring.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body pb-0">
          <input type="hidden" name="id" id="monitoring-id">
          <div class="">
            <input class="upload-form" type="file" id="files" name="files[]" multiple hidden="hidden" />
            <button class="btn btn-outline-danger" type="button" id="upload-button">Choose Files</button>
            <span id="file-title" class="ps-2">No file chosen, yet.</span>
          </div>
          <div class="form-text mt-2">Max size 2MB per file. Allowed types: jpg, png, pdf, zip.</div>
          <hr>
        </div>
        <div class="modal-footer pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-submit">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script>

/*
document.addEventListener('DOMContentLoaded', function() {
    var realFileBtn = document.getElementById("files");
    var uploadButton = document.getElementById("upload-button");

    if (realFileBtn && uploadButton) {
        uploadButton.addEventListener('click', function() {
            realFileBtn.click();
        });
    } else {
        console.error('Element with id "files" or "upload-button" not found.');
    }
});
*/

const table = new DataTable('#example');

table.on('mouseenter', 'td', function() {
  let colIdx = table.cell(this).index().column;

  table
    .cells()
    .nodes()
    .each((el) => el.classList.remove('highlight'));

  table
    .column(colIdx)
    .nodes()
    .each((el) => el.classList.add('highlight'));
});
</script>
<script>
$(document).ready(function() {
  // Initialize DataTable
  const table = $('#example').DataTable();

  // Handle quarter filter change
  $('#filter-quarter').on('change', function() {
    var quarter = $(this).val(); // Get selected quarter
    // Filter rows based on the selected quarter
    table.column(1).search(quarter).draw();
  });

  // Set monitoring ID when modal is shown
  $('#uploadModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id'); // Extract info from data-* attributes
    console.log("Selected Monitoring ID: " + id); // Log the monitoring ID
    var modal = $(this);
    modal.find('.modal-body #monitoring-id').val(id);
  });
});
</script>
@endsection