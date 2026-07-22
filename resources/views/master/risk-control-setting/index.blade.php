@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3 p-xxl-5">
        <div class="bg-primary-subtle rounded-4 p-1">
          <div class="lead__icon">
            <span class="svg-icon svg-icon-2x svg-icon-primary">
              @include('partials.icon-cog')
            </span>
          </div>
        </div>
        <h2>Risk Control Setting</h2>
      </div>
      <div class="card-body p-xxl-5">

        <div class="accordion" id="accordionEfektivitas">
          @foreach ($riskSettings as $index => $item)
          <div class="accordion-item">
            <div class="accordion-header" id="heading{{ $index + 1 }}">
              <button class="accordion-button collapsed ff-heading-med d-flex align-items-center gap-2" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapse{{ $index + 1 }}" aria-expanded="false"
                aria-controls="collapse{{ $index + 1 }}">
                <div class="d-block">Skala {{ $index + 1 }}</div>
                <i class='bx bx-chevron-right'></i>
                <div class="d-block">{{ $item->efektivitas_control }}</div>
              </button>
            </div>
            <div class="accordion-collapse collapse" id="collapse{{ $index + 1 }}"
              aria-labelledby="heading{{ $index + 1 }}" data-bs-parent="#accordionEfektivitas">
              <div class="accordion-body p-xxl-5">
                <div class="row g-2 mb-4">
                  @foreach ($item->child as $child)
                  <div class="col-12 d-flex gap-3">
                    <button class="btn btn-icon-danger" type="submit" data-bs-toggle="modal"
                      data-bs-target="#modalDelete{{ $child->id }}">
                      <span class="bx bxs-trash" data-bs-toggle="tooltip" title="Delete"></span>
                    </button>
                    <div class="pt-1">
                      {{ $child->keterangan }}
                    </div>
                    <!-- Modal Delete Exclamation -->
                    <div class="modal fade" id="modalDelete{{ $child->id }}" tabindex="-1"
                      aria-labelledby="{ $child->id }}Label" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered modal-lg exclamation-alert">
                        <div class="modal-content">
                          <div class="modal-body text-center">
                            <div class="exc-icon">
                              <div class="exc-icon-content">!</div>
                            </div>
                            <h5>Anda yakin akan menghapus "{{ $child->keterangan }}"?</h5>
                            <div class="d-flex justify-content-center mt-5">
                              <form action="{{ route('risk-setting-child.destroy', $child->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Ya, hapus!</button>
                              </form>
                              <button type=" button" class="btn btn-muted" data-bs-dismiss="modal">Batal</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
                <div class="d-flex">
                  <button class="btn btn-danger ms-auto edit-btn" data-id="{{ $item->id }}">Edit</button>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal scrollbar fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex flex-between-center">
        <h4 class="modal-title" id="editModalLabel">Edit Risk Setting</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form id="editForm" action="{{ route('risk-control-setting.update', ['id' => ':id']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body pt-0">
          <hr class="mb-5">
          <input type="hidden" name="id" id="editId">
          <div id="select_accessible" class="form-group d-md-flex mb-4">
            <label class="h6 label-md-start col-md-3 col-xl-2 mb-2 mb-md-0" for="efektivitas_control">Efektivitas
              Control</label>
            <select class="form-select col-md-6 col-xl-4" id="efektivitas_control" name="efektivitas_control">
              @foreach ($riskSettings as $item)
              <option value="{{ $item->efektivitas_control }}">{{ $item->efektivitas_control }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="h6 mb-2" for="keterangan">Keterangan</label>
            <ol id="keteranganInputs" class="list-input">
              <!-- Inputan keterangan dinamis akan ditambahkan di sini -->
            </ol>
            <button id="addKeterangan" type="button" class="btn btn-outline-secondary btn-icon ms-auto"
              data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah Keterangan">
              <i class="bx bx-plus"></i>
            </button>
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
$(document).ready(function() {
  $('.edit-btn').click(function() {
    var id = $(this).data('id');
    console.log("ID yang diambil:", id);
    $.get("{{ url('risk-control-setting') }}" + '/' + id + '/edit', function(data) {
      $('#editId').val(data.id);
      $('#efektivitas_control').val(data.efektivitas_control);

      // Kosongkan inputan dinamis sebelum menambahkan kembali
      $('#keteranganInputs').empty();

      // Tambahkan inputan keterangan dinamis jika data memiliki properti 'child'
      if (data.hasOwnProperty('child')) {
        data.child.forEach(function(child) {
          var inputGroup =
            '<li class="input-group"><input type="text" class="form-control"placeholder="Tambahkan keterangan" name="keterangan[]" value="' +
            child.keterangan + '"></li>';
          $('#keteranganInputs').append(inputGroup);
        });
      }

      $('#editModal').modal('show');
    });
  });

  $('#editForm').submit(function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function(response) {
        if (response.status == 200) {
          $('#editModal').modal('hide');
          location.reload();
        } else {
          alert('Terjadi Kesalahan');
        }
      },
      error: function(error) {
        console.log(error);
      }
    });
  });


  // Tambahkan event click untuk tombol "Add Keterangan Baru"
  $('#addKeterangan').click(function() {
    var inputGroup =
      '<li class="input-group"><input type="text" class="form-control" placeholder="Tambahkan keterangan" name="keterangan[]"></li>';
    $('#keteranganInputs').append(inputGroup);
  });
});
</script>
@endsection