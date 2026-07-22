@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-info-subtle p-2 rounded-2">
            <div class="lead__icon">
              <div class="svg-icon svg-icon-2x svg-icon-info">
                @include('partials.icon-users')
              </div>
            </div>
          </div>
          <div class="d-block">
            <div class="ff-preheading">Prioritas Risiko</div>
            <h2>Risiko Risk Owner</h2>
          </div>
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample3"
          data-list='{"valueNames":["kategori_jenis_risiko","peristiwa_risiko","deskripsi_peristiwa_risiko","skala_dampak","skala_probabilitas","level_risiko"],"page":10,"filter":{"key":"kategori_jenis_risiko","peristiwa_risiko","level_risiko"},"pagination":true}'>
          <div class="row g-2">
            @can('risk_register_all_unit')
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-unit" class="form-label d-none">Unit</label>
              <select id="filter-unit" class="form-select select2">
                <option value="">Unit</option>
                @foreach($unit as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            @can('risk_register_child_unit')
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-unit" class="form-label d-none">Unit</label>
              <select id="filter-unit" class="form-select select2">
                <option value="">Unit</option>
                @foreach($unitChild as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-kategori-jenis" class="form-label d-none">Kategori dan Jenis Risiko</label>
              <select id="filter-kategori-jenis" class="form-select select2">
                <option value="">Kategori dan Jenis Risiko</option>
                @foreach($kategoriJenisRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-8 col-xl-4">
              <label for="filter-risk-event" class="form-label d-none">Peristiwa Risiko</label>
              <select id="filter-risk-event" class="form-select select2">
                <option value="">Peristiwa Risiko</option>
                @foreach($peristiwaRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
              <label for="filter-risk-level" class="form-label d-none">Level Risiko</label>
              <select id="filter-risk-level" class="form-select select2 js-select-hide-search">
                <option value="">Level Risiko</option>
                <option value="High">High</option>
                <option value="Moderate To High">Moderate to High</option>
                <option value="Moderate">Moderate</option>
                <option value="Low To Moderate">Low to Moderate</option>
                <option value="Low">Low</option>
              </select>
            </div>
          </div>
          <!-- <div class="d-flex align-items-center justify-content-end mb-3">
            <div class="d-none ms-3" id="bulk-select-actions">
              <div class="d-flex">
                <select class="form-select form-select-sm" aria-label="Bulk actions">
                  <option selected="selected">Bulk actions</option>
                  <option value="Delete">Delete</option>
                  <option value="Archive">Archive</option>
                </select>
                <button class="btn btn-falcon-danger btn-sm ms-2" type="button">Apply</button>
              </div>
            </div>
          </div> -->
          @if($status === 6 && $dataBatch->finish === 0)
          <form id="table-form" action="{{ route('risk-owner.send-universitas') }}" method="POST">
          @else
          <form id="table-form" action="{{ route('risk-owner.konfirmasi') }}" method="POST">
          @endif
            @csrf
            <table class="table dataTable" id="example" data-info="true" data-paging="true" data-filter="true">
              <thead>
                <tr>
                  <th class="no-sort white-space-nowrap">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" id="select-all" />
                    </div>
                  </th>
                  <th class="sort white-space-nowrap" data-sort="no">#</th>
                  <th class="sort" data-sort="unit">Unit</th>
                  <th class="sort mw-10r" data-sort="kategori_jenis_risiko">Kategori dan Jenis Risiko</th>
                  <th class="sort mw-10r" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                  <th class="sort mw-10r" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                  <th class="sort" data-sort="skala_dampak">Skala Dampak</th>
                  <th class="sort" data-sort="skala_probabilitas">Skala Probabilitas</th>
                  <th class="sort" data-sort="skala_risiko">Nilai Risiko</th>
                  <th class="sort" data-sort="level_risiko">Level Risiko</th>
                  <th class="sort" data-sort="is_university">Risiko Universitas</th>
                  <th class="sort mw-10r" data-sort="catatan">Catatan</th>
                  <th class="no-sort" data-sort="action">Action</th>
                </tr>
              </thead>
              <tbody class="list" id="bulk-select-body">
                @foreach ($risiko as $index => $item)
                <tr>
                  <td class="white-space-nowrap">
                  @if($item->status_risiko=="verification" && $item->status_progress=="risk_owner")
                    <!-- nothing -->
                  @else
                    <div class="form-check mb-0"> 
                      <input class="form-check-input select-item" type="checkbox" name="selected_items[]"
                        value="{{ $item->id }}" />
                    </div>
                  @endif
                  </td>
                  <td class="index-number w-auto">
                    <div class="d-flex align-items-center gap-1">
                      @if ($item->status === 4 )
                        {{ $index + 1 }} <span class="badge-ranking star-up bg-warning-subtle"></span>
                      @elseif ($item->status_risiko === 'verification')
                        {{ $index + 1 }} <span class="badge-ranking ranking-up-red bg-danger-subtle"></span>
                      @elseif ($item->status === 3 )
                        {{ $index + 1 }} <span class="badge-ranking ranking-up bg-success-subtle"></span>
                      @else
                        {{ $index + 1 }}
                      @endif
                    </div>
                  </td>
                  <td class="unit">{{ $item->unit->name }}</td>
                  <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title }} -
                    {{ $item->jenisRisiko->title }}</td>
                  <td class="peristiwa_risiko">{{ $item->peristiwaRisiko->title }}</td>
                  <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
                  <td class="skala_dampak">{{ $item->riskAnalysis->skala_dampak }}</td>
                  <td class="skala_probabilitas">{{ $item->riskAnalysis->skalaProbabilitas->tingkat ?? NULL }}
                  </td>
                  <td class="skala_risiko">{{ $item->riskAnalysis->skala_risiko }}</td>
                  <td class="level_risiko">
                    @php
                    $level_risiko = $item->riskAnalysis->level_risiko;
                    $badge_class = '';
                    $lvRisk = strtolower($level_risiko);
                    if ($lvRisk == "high") {
                    $badge_class = 'high';
                    } elseif ($lvRisk == "moderate to high") {
                    $badge_class = 'medium-high';
                    } elseif ($lvRisk == "moderate") {
                    $badge_class = 'medium';
                    } elseif ($lvRisk == "low to moderate") {
                    $badge_class = 'low-medium';
                    } elseif ($lvRisk == "low") {
                    $badge_class = 'low';
                    }
                    @endphp
                    <span class="badge {{ $badge_class }}">{{ $level_risiko }}</span>
                  </td>
                  {{-- 
                  <td class="status white-space-nowrap">{{ ucwords(str_replace('_', ' ', $item->status_progress)) }}
                  </td>
                  --}}
                  <td class="is_university">
                    {{ $item->is_university == 1 ? 'Ya' : 'Tidak' }}
                  </td>
                  <td class="catatan">{{ $item->catatan }}</td>
                  <td class="white-space-nowrap">
                    @can('prioritas_risiko_verification')
                    <a href="{{ route('risk-register.view', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                      title="View">
                      <span class="bx bx-show-alt"></span>
                    </a>
                    @if ($item->status_progress == 'risk_owner')
                    {{-- <button type="button" class="btn-input-icon edit-btn" data-id="{{ $item->id }}">
                      <span class="bx bx-edit-alt" data-bs-toggle="tooltip" title="Edit"></span>
                    </button> --}}
                    @endif
                    @endcan
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
        </div>
        @if ($status !== 3)
            <div class="alert alert-warning mb-2">
                Tidak ada risiko yang bisa dikonfirmasi karena belum ada risiko yang memenuhi syarat verifikasi.
            </div>
        @endif  
      </div>            
      
      <div class="card-footer pt-0 border-0">
        @can('prioritas_risiko_verification')
          @if ($status === 3)
            <button type="submit" class="btn btn-success">Terima Risiko</button>
          @elseif($status === 6 && $dataBatch->finish === 0)
            <button type="submit" class="btn btn-success">Kirim Risiko ke Universitas</button>       
          @endif
        </form>
        @if ($status === 3)
        <form id="confirm-form" action="{{ route('risk-owner.send') }}" method="POST">
          @csrf
          <button class="btn btn-submit">Konfirmasi Risiko Utama</button>
        </form>
        @endif
        @endcan
        
        @if($status !== null && ($status == 3))
        <form id="return-form" action="{{ route('risk-owner.return') }}" method="POST" class="d-inline-block" style="margin-left:10px;">
          @csrf
          <button class="btn btn-danger">Kembalikan Risiko Ke Risk Champion</button>
        </form>
        @endif
      </div>
    </div>
  </div>
</div>
<!--================ Modal Verifikasi Risiko ================-->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex flex-between-center">
        <h4 class="modal-title">Verifikasi Risiko</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form id="editForm" action="{{ route('risk-owner.verif', ['id' => ':id']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <input type="hidden" name="id" id="editId">
          <div class="form-floating">
            <textarea class="form-control" name="catatan" id="catatan" cols="30" rows="5"
              placeholder="Catatan Penolakan Risiko"></textarea>
            <label>Catatan Penolakan Risiko</label>
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
    //console.log("ID yang diambil:", id);
    //alert("{{ url('risk-owner') }}" + '/' + id + '/edit');

    $.get("{{ url('risk-owner') }}" + '/' + id + '/edit', function(data) {
      $('#editId').val(data.id);
      $('#catatan').val(data.catatan);

      $('#editModal').modal('show');
    });

  });

  $('#editForm').submit(function(e) {
    e.preventDefault();

    Swal.fire({
      title: "Apakah Anda yakin?",
      text: "Risiko akan dikembalikan ke risk officer untuk dilakukan revisi",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, simpan!",
      cancelButtonText: "Tidak, batal",
    }).then((result) => {
      if (result.isConfirmed) {
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
      }
    });


  });
});
</script>
<script>
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

function deleteItem(element) {
  if (confirm('Are you sure you want to delete?')) {
    // Ambil form yang berisi tombol hapus
    const form = element.parentNode;
    // Submit form untuk menghapus item
    form.submit();
  }
}
</script>
<script>
$(document).ready(function() {
  // Inisialisasi DataTable
  const table = $('#example').DataTable();

  // Fungsi untuk menangani perubahan nilai di select unit
  $('#filter-unit').on('change', function() {
    var unitId = $(this).val(); // Mendapatkan nilai unit yang dipilih
    // Memfilter baris tabel berdasarkan nilai unit yang dipilih pada kolom 'Unit'
    table.column(2).search(unitId).draw();
  });

  // Fungsi untuk menangani perubahan nilai di select kategori dan jenis risiko
  $('#filter-kategori-jenis').on('change', function() {
    var kategoriJenisId = $(this).val(); // Mendapatkan nilai kategori dan jenis risiko yang dipilih
    // Memfilter baris tabel berdasarkan nilai kategori dan jenis risiko yang dipilih pada kolom 'Kategori dan Jenis Risiko'
    table.column(3).search(kategoriJenisId).draw();
  });

  $('#filter-risk-event').on('change', function() {
    var riskEvent = $(this).val();
    table.column(4).search(riskEvent).draw();
  });

  // Function to handle changes in the risk level filter
  $('#filter-risk-level').on('change', function() {
    var riskLevel = $(this).val();
    if (riskLevel === 'High') {
      table.column(9).search('^High$', true, false).draw();
    } else if (riskLevel === 'Low') {
      table.column(9).search('^Low$', true, false).draw();
    } else if (riskLevel === 'Moderate') {
      table.column(9).search('^Moderate$', true, false).draw();
    } else {
      table.column(9).search(riskLevel).draw();
    }
  });

  // Select/Deselect all checkboxes
  $('#select-all').on('click', function() {
    var rows = table.rows({
      'search': 'applied'
    }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
  });

  // Handle individual row selection
  $('#example tbody').on('change', 'input[type="checkbox"]', function() {
    if (!this.checked) {
      var el = $('#select-all').get(0);
      if (el && el.checked && ('indeterminate' in el)) {
        el.indeterminate = true;
      }
    }
  });
});

document.querySelector('#table-form').addEventListener('submit', function(event) {
  
  event.preventDefault(); // Mencegah form submission otomatis
  const status = {{ $status ?? 'null' }};
  const finish = {{ $dataBatch->finish ?? 'null' }};
  var text = "";
  if(status === 6 && finish === 0){
    text = "Risiko dikirimkan ke Universitas";
  }
  else{
    text = "Risiko diterima sebagai risiko utama";
  }

  Swal.fire({
    title: "Apakah Anda yakin?",
    text: text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, kirim!",
    cancelButtonText: "Tidak, batal",
  }).then((result) => {
    if (result.isConfirmed) {
      this.submit(); // Kirim form jika dikonfirmasi
    }
  });
});

document.getElementById('table-form').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Mencegah submit jika tombol Enter ditekan
    }
});


document.querySelector('#confirm-form').addEventListener('submit', function(event) {
  event.preventDefault(); // Mencegah form submission otomatis

  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Simpan risiko terpilih menjadi risiko utama",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, simpan!",
    cancelButtonText: "Tidak, batal",
  }).then((result) => {
    if (result.isConfirmed) {
      this.submit(); // Kirim form jika dikonfirmasi
    }
  });
});

document.querySelector('#return-form').addEventListener('submit', function(event) {
  event.preventDefault(); // Mencegah form submission otomatis

  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Semua Risiko akan dikembalikan ke Risk Champion untuk dicek ulang kembali",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, kembalikan semua risiko!",
    cancelButtonText: "Tidak, batal",
  }).then((result) => {
    if (result.isConfirmed) {
      this.submit(); // Kirim form jika dikonfirmasi
    }
  });
});
</script>
@endsection