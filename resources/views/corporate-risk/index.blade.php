@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-info-subtle p-2 rounded-4">
          <div class="lead__icon">
            <div class="svg-icon svg-icon-2x svg-icon-info">
              @include('partials.icon-pyramid')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Input Data</div>
          <h2>Risk Register Korporat</h2>
          @if(isset($selectedPeriode))
          <div class="ff-preheading">Periode: {{ $selectedPeriode->tahun }}</div>
          @endif
        </div>
      </div>
      <div class="card-header border-bottom">
          <div class="d-flex align-items-center gap-3">
              <h6 class="mb-0">Keterangan :</h6>
              <div class="d-flex gap-3">
                  @foreach($tableLegend as $legend)
                  <div class="d-flex align-items-center gap-1">
                      {!! $legend['icon'] !!}
                      <span>{{ $legend['label'] }}</span>
                  </div>
                  @endforeach
              </div>
          </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample3">
          <div class="row g-2 mb-1">
            <div class="col-12 col-sm-4" style="display:none;">
              <label for="filter-risk-event" class="form-label d-none">Peristiwa Risiko</label>
              <select id="filter-risk-event" class="form-select select2">
                <option value="" selected>Peristiwa Risiko</option>
                @foreach($peristiwaRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>

            {{-- <div class="col-12 col-sm-4">
              <label for="filter-risk-t2t3" class="form-label d-none">T2 & T3 KBUMN</label>
              <select id="filter-risk-t2t3" class="form-select select2">
                <option value="" selected>T2 & T3 KBUMN</option>
                @foreach($jenisRisiko as $id => $title)
                    @php
                        $kategori = \App\Models\JenisRisiko::find($id)->kategoriRisiko;
                        $kategoriTitle = $kategori ? $kategori->title : '';
                    @endphp
                    <option value="{{ $kategoriTitle }} - {{ $title }}" {{ old('jenis_risiko_id') == $id ? 'selected' : '' }}>
                        {{ $kategoriTitle }} - {{ $title }}
                    </option>
                @endforeach
              </select>
            </div> --}}

            <div class="col-5 col-sm-2" style="display:none;">
              <label for="filter-risk-level" class="form-label d-none">Level Risiko</label>
              <select id="filter-risk-level" class="form-select js-select-hide-search">
                <option value="" selected>Level Risiko</option>
                <option value="High">High</option>
                <option value="Moderate To High">Moderate To High</option>
                <option value="Moderate">Moderate</option>
                <option value="Low To Moderate">Low To Moderate</option>
                <option value="Low">Low</option>
              </select>
            </div>
            <div class="col-auto ms-auto d-flex gap-2 align-items-center">
              {{-- <div class="col-auto ms-auto">
                <a href="{{ route('kamus-risiko-unit.index') }}" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-title="Kamus Risiko">
                  <span class="bx bx-book-bookmark"></span>
                  <span class="ms-1">Kamus Risiko</span>
                </a>
              </div> --}}
              <div class="col-auto ms-auto">
              @php
                  // diasumsikan di view Anda ada $selectedPeriode
                  $pid = $selectedPeriode->id;
              @endphp
              @can('risk_register_create')
                @if($status == null || $status == 1 || $status == 5)
                <a id="add-risk-button" href="{{ route('corporate-risk.create', ['pid' => $pid]) }}" type="button"
                  class="btn btn-outline-info btn-sm d-flex flex-center" data-bs-toggle="tooltip"
                  data-bs-title="Tambah Risiko">
                  <span class="bx bx-plus"></span>
                  <span class="ms-1">Tambah Risiko</span>
                </a>
                @endif
              @endcan
              </div>
            </div>
          </div>
          <table class="table ajax-datatable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="no-sort white-space-nowrap">
                  @if($status == \App\Models\DataBatch::STATUS_RANKING && isset($avgQuantitativeExposure))
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="select-all" />
                  </div>
                  @endif
                </th>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="unit">Unit</th>
                <th class="sort" data-sort="unit_type">Sasaran</th>
                {{-- <th class="sort mw-20r" data-sort="kategori_jenis_risiko">T2 & T3 KBUMN</th> --}}
                <th class="sort mw-10r" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                <th class="sort mw-10r" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                {{-- <th class="sort mw-10r" data-sort="kontrol_eksisting">Jenis Kontrol Eksisting</th> --}}
                <th class="sort mw-10r" data-sort="kategori dampak">Kategori Dampak</th>
                <th class="sort mw-10r" data-sort="nilai_risiko_inherent">Nilai Risiko</th>
                <th class="sort mw-10r" data-sort="eksposure_risiko_inherent">Eksposure Risiko</th>
                <th class="sort mw-10r" data-sort="total_biaya_rencana_perlakuan">Total Biaya Rencana Perlakuan</th>
                <th class="sort mw-15r" data-sort="waktu_terpapar">Waktu Terpapar</th>
                <th class="sort" data-sort="status">Status</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($risiko as $index => $item)
              <tr>
                <td class="white-space-nowrap">
                  @if($status == \App\Models\DataBatch::STATUS_RANKING && isset($avgQuantitativeExposure))
                  <div class="form-check mb-0">
                    <input class="form-check-input select-item" type="checkbox" name="selected_items[]"
                      value="{{ $item->id }}" />
                  </div>
                  @endif
                </td>
                <td class="index-number">
                  @if($item->status_risiko== 2)
                    <span class="badge bg-primary">Rekomendasi</span>
                  @elseif($item->status_risiko == 3 || $item->status_risiko == 4 || $item->status_risiko == 5)
                    <span class="badge bg-danger">Risiko Utama</span>
                  @endif
                  {{ $index + 1 }}
                </td>
                <td class="unit">
                  {{ $item->unit->name ?? '-' }}
                </td>
                <td class="unit_type">{{ $item->target_capaian_kinerja ?? '-' }}</td>
                {{-- <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title ?? '-' }} - {{ $item->jenisRisiko->title ?? '-' }}</td> --}}
                <td class="peristiwa_risiko">
                  @php
                    $add = '';
                    if ($item->riskAnalysis && $item->riskAnalysis->kategori_dampak === 'Kuantitatif' &&
                        isset($avgQuantitativeExposure) && $item->riskAnalysis->eksposur_risiko >= $avgQuantitativeExposure) {
                        $add = '<span class="badge bg-primary" data-bs-toggle="tooltip" title="Rekomendasi Risiko di atas rata-rata IRE">!</span> ';
                    } else if ($item->riskAnalysis && $item->riskAnalysis->kategori_dampak === 'Kualitatif' &&
                              $item->riskAnalysis->skala_risiko >= 20) {
                        $add = '<span class="badge bg-primary" data-bs-toggle="tooltip" title="Rekomendasi Risiko di atas rata-rata IRE">!</span> ';
                    }
                  @endphp
                  {!! $add !!}{{ $item->peristiwa_risiko ?? '-' }}
                </td>
                <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
                {{-- <td class="kontrol_eksisting">{{ $item->jenisKontrolEksisting->jenis_kontrol ?? '-' }}</td> --}}
                <td class="kategori_dampak">{{ $item->riskAnalysis->kategori_dampak ?? '-' }}</td>
                <td class="nilai_risiko" @if($item->riskAnalysis && $item->riskAnalysis->level_risiko)
                    style="background-color:
                    @switch(strtolower($item->riskAnalysis->level_risiko))
                        @case('low')
                            #14A20E
                            @break
                        @case('low to moderate')
                            #7CD020
                            @break
                        @case('moderate')
                            #FCDB2C
                            @break
                        @case('moderate to high')
                            #FEAC17
                            @break
                        @case('high')
                            #EF6345
                            @break
                        @default
                            transparent
                    @endswitch
                    ; color: @if(in_array(strtolower($item->riskAnalysis->level_risiko), ['low', 'low to moderate', 'moderate'])) #000000 @else #FFFFFF @endif;"
                @endif
                >{{ $item->riskAnalysis->skala_risiko ?? '-' }}</td>
                <td class="eksposure_risiko">{{ $item->riskAnalysis && $item->riskAnalysis->eksposur_risiko ? 'Rp ' . number_format($item->riskAnalysis->eksposur_risiko, 0, ',', '.') : '-' }}</td>
                <td class="total_biaya_rencana_perlakuan">
                  @php
                    $totalBiaya = 0;
                    if ($item->penyebabRisiko->count() > 0) {
                      foreach ($item->penyebabRisiko as $penyebab) {
                        foreach ($penyebab->perlakuanPenyebabRisikoUnit as $perlakuan) {
                          $totalBiaya += $perlakuan->biaya_perlakuan_risiko ?? 0;
                        }
                      }
                    }
                  @endphp
                  {{ $totalBiaya > 0 ? 'Rp ' . number_format($totalBiaya, 0, ',', '.') : '-' }}
                </td>
                <td class="waktu_terpapar">
                  {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') }} -
                  {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') }}
                </td>
                <td class="status">
                  @switch($item->status ?? 0)
                  @case(1)
                  Draft
                  @break
                  @case(2)
                  On Review
                  @break
                  @case(3)
                    @php
                      $acceptedText = 'Accepted';
                      if ($item->step_verification == 2) {
                        $acceptedText = 'Accepted by Risk Owner Divisi';
                      } elseif ($item->step_verification == 3) {
                        $acceptedText = 'Accepted by Risk Officer MR';
                      } elseif ($item->step_verification == 3) {
                        $acceptedText = 'Accepted by Risk Owner MR';
                      }
                    @endphp
                    {{ $acceptedText }}
                  @break
                  @case(4)
                  Accepted
                  @break
                  @case(5)
                  Need Revision or Rejected
                  @break
                  @default
                  Draft
                  @endswitch
                </td>
                <td class="white-space-nowrap">
                  @can('risk_register_view')
                  <a href="{{ route('corporate-risk.view', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                  @endcan
                  @include('corporate-risk._table_action', ['item' => $item])
                </td>
              </tr>
              @endforeach
              @php
              if (!isset($index)) {
              $index = 0;
              }
              @endphp
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
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
    //var unitId = $(this).val(); // Mendapatkan nilai unit yang dipilih
    // Memfilter baris tabel berdasarkan nilai unit yang dipilih pada kolom 'Unit'
    //table.column(1).search(unitId).draw();
    const selectedUnitId = $(this).val();
    const currentUrl = new URL(window.location.href);

    // Hapus parameter unit_id jika "Semua Unit" dipilih
    if (selectedUnitId === '') {
        currentUrl.searchParams.delete('unit_id');
    } else {
        currentUrl.searchParams.set('unit_id', selectedUnitId);
    }

    // Refresh halaman dengan parameter baru
    window.location.href = currentUrl.toString();
  });

  $('#filter-risk-event').on('change', function() {
    var riskEvent = $(this).val();
    table.column(4).search(riskEvent).draw();
  });

  $('#filter-risk-t2t3').on('change', function() {
    var riskEvent = $(this).val();
    table.column(4).search(riskEvent).draw();
  });

  // Function to handle changes in the risk level filter
  $('#filter-risk-level').on('change', function() {
    var riskLevel = $(this).val();
    table.column(8).search(riskLevel).draw();
  });

  document.querySelector('#send-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Mencegah form submission otomatis

    const status = {{ $status ?? 'null' }};
    const stepOrder = {{ $step_order ?? 'null' }};

    if(status === 6){
      //redirect ke halaman risk corporate
      window.location.href = "{{ route('corporate-risk.index') }}";
    }
    else if(status===3){
      const selectedRisks = [];
      document.querySelectorAll('.select-item:checked').forEach(function(checkbox) {
        selectedRisks.push(checkbox.value);
      });

      // Jika tidak ada risiko yang dipilih, tampilkan peringatan
      if(selectedRisks.length === 0) {
        Swal.fire({
          title: "Peringatan",
          text: "Silakan pilih minimal satu risiko untuk dijadikan risiko utama",
          icon: "warning",
        });
        return;
      }

      // Hapus input hidden yang mungkin sudah ada sebelumnya
      document.querySelectorAll('input[name="selected_risks[]"]').forEach(function(input) {
        input.remove();
      });

      // Tambahkan input hidden untuk setiap risiko yang dipilih
      selectedRisks.forEach(function(riskId) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_risks[]';
        input.value = riskId;
        this.appendChild(input);
      }, this);

      Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Terima Risiko Terpilih sebagai Risiko Utama?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, terima risiko!",
        cancelButtonText: "Tidak, batal",
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit(); // Kirim form jika dikonfirmasi
        }
      });
    }
    else if (status === 5 && (stepOrder === 0 || stepOrder === null)) {// Jika status adalah 5 (revisi) dan step_order adalah 0 atau null, tampilkan modal kirim perbaikan
      const modal = new bootstrap.Modal(document.getElementById('modalKirimPerbaikanRisiko'));
      modal.show();
    } else {
      // Jika tidak, tampilkan konfirmasi SweetAlert seperti biasa
      Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Semua Data Risiko akan dikirim untuk dilakukan verifikasi selanjutnya dan anda tidak dapat melakukan penambahan risiko dan edit risiko sementara waktu",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, kirim risiko!",
        cancelButtonText: "Tidak, batal",
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit(); // Kirim form jika dikonfirmasi
        }
      });
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
</script>
@endsection
