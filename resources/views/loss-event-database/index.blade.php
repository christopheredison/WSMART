@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header">
        <div class="row justify-content-between">
          <div class="col d-flex align-items-center gap-3">
            <div class="bg-info-subtle p-2 rounded-4">
              <div class="lead__icon">
                <div class="svg-icon svg-icon-2x svg-icon-info">
                  @include('partials.icon-abs05')
                </div>
              </div>
            </div>
            <div class="d-block">
              <div class="ff-preheading">Input Data</div>
              <h2>Loss Event Database</h2>
            </div>
          </div>
          <div class="col-auto">
            <div id="bulk-select-replace-element">
              @can('lost_event_create')
              <a class="btn btn-outline-info btn-sm p-2" href="{{ route('loss-event-database.create') }}" type="button">
                <span class="bx bx-plus"></span>
                <span class="ms-1">Tambah Data Loss Event</span>
              </a>
              @endcan
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="tableExample3" class="dt-header-base"
          data-list='{"valueNames":["tanggal_kejadian","peristiwa_kerugian","kategori_jenis_risiko","nilai_kerugian_finansial","nilai_kerugian_non_fungsional","rekomendasi_perbaikan","unit_penanggung_jawab"],"page":10,"filter":{"key":"kategori_jenis_risiko","peristiwa_risiko","level_risiko"},"pagination":true}'>
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
          <table id="example" class="table table-bulk-select dataTable" data-paging="true" data-info="true"
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
                <th class="sort white-space-nowrap" data-sort="tanggal_kejadian">Tanggal Kejadian</th>
                <th class="sort mw-10r" data-sort="peristiwa_kerugian">Peristiwa Kerugian</th>
                <th class="sort white-space-nowrap" data-sort="kategori_jenis_risiko">Kategori dan Jenis Risiko</th>
                <th class="sort mw-10r" data-sort="nilai_kerugian_finansial">Kerugian Finansial (IDR)</th>
                <th class="sort mw-10r" data-sort="nilai_kerugian_non_fungsional">Kerugian Non Finansial (IDR)</th>
                <th class="sort mw-20r" data-sort="rekomendasi_perbaikan">Rekomendasi Perbaikan yang Perlu
                  Ditindaklanjuti</th>
                <th class="sort" data-sort="unit_penanggung_jawab">Unit Penanggung Jawab</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($lossEvent as $index => $item)
              <tr>
                <td class="white-space-nowrap">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="checkbox-{{ $index + 1 }}"
                      data-bulk-select-row="data-bulk-select-row" />
                  </div>
                </td>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="tanggal_kejadian white-space-nowrap">
                  {{ \Carbon\Carbon::parse($item->tanggal_kejadian)->format('d M Y') }}
                </td>
                <td class="peristiwa_kerugian">{{ $item->peristiwa_kerugian }}</td>
                <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title }} -
                  {{ $item->jenisRisiko->title }}</td>
                <td class="nilai_kerugian_finansial">{{ number_format($item->nilai_kerugian_finansial, 0, ',', '.') }}</td>
                <td class="nilai_kerugian_non_fungsional">{{ $item->nilai_kerugian_non_fungsional }}</td>
                <td class="rekomendasi_perbaikan">
                  <ol>
                    @foreach ($item->child as $child)
                    <li>{{ $child->rekomendasi_perbaikan }}</li>
                    @endforeach
                  </ol>
                </td>
                <td class="unit_penanggung_jawab">{{ $item->unit_penanggung_jawab }}</td>
                <td class="white-space-nowrap">
                  @can('lost_event_create')
                  {{-- <button class="btn-input-icon edit-btn" data-id="{{ $item->id }}" data-bs-toggle="tooltip"
                  title="Rekomendasi Perbaikan">
                  <i class='bx bx-bulb'></i>
                  </button> --}}
                  <a href="{{ route('loss-event-database.edit', $item) }}" class="btn-input-icon"
                    data-bs-toggle="tooltip" title="Edit">
                    <i class='bx bx-message-square-edit'></i>
                  </a>
                  @endcan
                  @can('lost_event_delete')
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item->id }}">
                    <span class="bx bx-trash" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->kategoriRisiko->title . ' - ' . $item->jenisRisiko->title;
                  $formAction = route('loss-event-database.destroy', $item->id);
                  @endphp
                  @include('partials.modal-delete-alert')
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#uploadModal"
                    data-id="{{ $item->id }}">
                    <span class="bx bx-upload" data-bs-toggle="tooltip" title="Upload"></span>
                  </button>
                  @endcan
                  <!-- Button trigger modal -->
                  @if ($item->files && $item->files->isNotEmpty())
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#fileModal-{{ $index }}">
                    <span class="bx bx-file-find" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                  @endif

                  <!-- Modal -->
                  <div class="modal fade" id="fileModal-{{ $index }}" tabindex="-1"
                    aria-labelledby="fileModalLabel-{{ $index }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                      <div class="modal-content">
                        <div class="modal-header pb-0 d-block">
                          <h4 id="fileModalLabel-{{ $index }}">
                            View Files
                          </h4>
                          <span class="text-wrap">Files for {{ $item->peristiwa_kerugian }}</span>
                          <hr class="my-5">
                        </div>
                        <div class="modal-body pt-0">
                          <ul class="list-unstyled">
                            @foreach ($item->files as $file)
                            <li class="mb-3">
                              <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ asset('storage/' . $file->file_path) }}"
                                  target="_blank">{{ basename($file->file_path) }}</a>
                                <form action="{{ route('loss-event-files.destroy', $file) }}" method="POST">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="btn-input-icon text-danger" data-bs-toggle="tooltip"
                                    title="Delete"
                                    onclick="return confirm('Are you sure you want to delete this file?')">
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

                  <!-- <div class="modal fade" id="fileModal-{{ $index }}" tabindex="-1"
                    aria-labelledby="fileModalLabel-{{ $index }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="fileModalLabel-{{ $index }}">Files for
                            {{ $item->peristiwa_kerugian }}</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <ul>
                            @foreach ($item->files as $file)
                            <li class="mb-3">
                              <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ asset('storage/' . $file->file_path) }}"
                                  target="_blank">{{ basename($file->file_path) }}</a>
                                <form action="{{ route('loss-event-files.destroy', $file) }}" method="POST">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="btn btn-sm btn-danger ms-2 delete-file-btn"
                                    onclick="return confirm('Are you sure you want to delete this file?')">Delete</button>
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
                  </div> -->
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <!-- <div class="d-flex justify-content-center mt-3">
            <button class="btn btn-sm btn-falcon-default me-1" type="button" title="Previous"
              data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
            <ul class="pagination mb-0"></ul>
            <button class="btn btn-sm btn-falcon-default ms-1" type="button" title="Next"
              data-list-pagination="next"><span class="fas fa-chevron-right"> </span></button>
          </div> -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Upload Files -->
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
      <form action="{{ route('loss-event-files.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body pb-0">
          <input type="hidden" name="id" id="loss-event-id">
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

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Tambah Rekomendasi Perbaikan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editForm" action="{{ route('loss-event-database.updateRekomendasi', ['id' => ':id']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <input type="hidden" name="id" id="editId">
          <div class="form-group">
            <label for="keterangan">Rekomendasi Perbaikan</label>
            <div id="keteranganInputs">
              <!-- Inputan keterangan dinamis akan ditambahkan di sini -->
            </div>
            <button type="button" class="btn btn-sm btn-success mt-2" id="addKeterangan">Tambah Rekomendasi
              Baru</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script>
$(document).ready(function() {
  // Delete file confirmation
  $('.delete-file-btn').click(function() {
    return confirm('Anda yakin ingin menghapus file ini?');
  });

  // Handle dynamic addition of rekomendasi perbaikan input fields
  $('#addKeterangan').click(function() {
    var inputGroup =
      '<div class="input-group mb-2"><input type="text" class="form-control" name="rekomendasi_perbaikan[]"></div>';
    $('#keteranganInputs').append(inputGroup);
  });

  // AJAX setup for delete operation of loss events
  $('.delete-btn').click(function() {
    if (confirm('Are you sure you want to delete?')) {
      var form = $(this).closest('form');
      $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: {
          _method: 'DELETE',
          _token: '{{ csrf_token() }}'
        },
        success: function(data) {
          // Handle success response
          form.closest('tr').remove();
        },
        error: function(err) {
          // Handle error response
          console.error('Error:', err);
        }
      });
    }
  });

  // Set monitoring ID when modal is shown
  $('#uploadModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id'); // Extract info from data-* attributes
    console.log("Selected ID: " + id); // Log the monitoring ID
    var modal = $(this);
    modal.find('.modal-body #loss-event-id').val(id);
  });

});
</script>

@endsection