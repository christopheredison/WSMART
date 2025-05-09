@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header">
        <div class="row justify-content-between">
          <div class="col-12 col-sm-auto mb-0 mb-sm-4">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-info-subtle p-2 rounded-2">
                <div class="lead__icon">
                  <div class="svg-icon svg-icon-2x svg-icon-info">
                    @include('partials.icon-abs01')
                  </div>
                </div>
              </div>
              <div class="d-block">
                <div class="ff-preheading">Ranking Risiko Unit</div>
                <h2>Risiko Risk Champion</h2>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-auto">
            <div class="row g-2">
              <!-- Average Skala Risiko -->
              <div class="col-12 col-md-auto">
                <div class="card card-sm border-primary bg-primary-subtle">
                  <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="flex-shrink-0 me-3">
                      <span class="svg-icon svg-icon-3x svg-icon-primary">
                        @include('partials.icon-process')
                      </span>
                    </div>
                    <div class="level_risiko flex-shrink-0">
                      <p class="ff-heading-med mb-1">Average Skala Risiko</p>
                      @php
                      $badge_class = '';
                      $lvRisk = strtolower($levelRisiko);

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
                      <span class="badge {{ $badge_class }}">{{ $levelRisiko }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Average Nilai Risiko -->
              <div class="col-12 col-md-auto">
                <div class="card card-sm border-primary bg-primary-subtle">
                  <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="flex-shrink-0 me-3">
                      <span class="svg-icon svg-icon-3x svg-icon-primary">
                        @include('partials.icon-process')
                      </span>
                    </div>
                    <div class="flex-shrink-0">
                      <p class="ff-heading-med mb-1">Average Nilai Risiko</p>
                      <h5 class="ff-heading-sm mb-0">{{ $averageSkalaRisiko }}</h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="tableExample3"
          data-list='{"valueNames":["kategori_jenis_risiko","peristiwa_risiko","deskripsi_peristiwa_risiko","skala_dampak","skala_probabilitas","level_risiko"],"page":10,"filter":{"key":"kategori_jenis_risiko","peristiwa_risiko","level_risiko"},"pagination":true}'>
          <div class="row g-2 mb-4 label-hidden">
            @can('risk_register_all_unit')
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-unit" class="form-label">Unit</label>
              <select id="filter-unit" class="form-select select2">
                <option value="" selected>Unit</option>
                @foreach($unit as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            @can('risk_register_child_unit')
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-unit" class="form-label">Unit</label>
              <select id="filter-unit" class="form-select select2">
                <option value="" selected>Unit</option>
                @foreach($unitChild as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-kategori-jenis" class="form-label">Kategori dan Jenis Risiko</label>
              <select id="filter-kategori-jenis" class="form-select select2">
                <option value="" selected>Kategori dan Jenis Risiko</option>
                @foreach($kategoriJenisRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-8 col-xl-4">
              <label for="filter-risk-event" class="form-label">Peristiwa Risiko</label>
              <select id="filter-risk-event" class="form-select select2">
                <option value="" selected>Peristiwa Risiko</option>
                @foreach($peristiwaRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
              <label for="filter-risk-level" class="form-label">Level Risiko</label>
              <select id="filter-risk-level" class="form-select select2 js-select-hide-search">
                <option value="" selected>Level Risiko</option>
                <option value="High">High</option>
                <option value="Moderate To High">Moderate to High</option>
                <option value="Moderate">Moderate</option>
                <option value="Low To Moderate">Low to Moderate</option>
                <option value="Low">Low</option>
              </select>
            </div>
          </div>
          <form id="table-form" action="{{ route('risk-champion.send') }}" method="POST">
            @csrf
            <table class="table dataTable" id="example" data-info="true" data-paging="true" data-filter="true">
              <thead>
                <tr>
                  @if ($status !== null && $status >= 3)
                  <th class="no-sort white-space-nowrap">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" id="select-all" />
                    </div>
                  </th>
                  @endif
                  <th class="sort white-space-nowrap" data-sort="no">#</th>
                  <th class="sort" data-sort="unit">Unit</th>
                  <th class="sort" data-sort="kategori_jenis_risiko">Kategori dan Jenis Risiko</th>
                  <th class="sort" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                  <th class="sort" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                  <th class="sort" data-sort="skala_dampak">Skala Dampak</th>
                  <th class="sort" data-sort="skala_probabilitas">Skala Probabilitas</th>
                  <th class="sort" data-sort="skala_risiko">Nilai Risiko</th>
                  <th class="sort" data-sort="level_risiko">Level Risiko</th>
                  <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
                </tr>
              </thead>
              <tbody class="list" id="bulk-select-body">
                @foreach ($risiko as $index => $item)
                <tr>
                  @if ($status !== null && $status >= 3)
                  <td class="white-space-nowrap">
                    @if ($item->status_progress !== 'risk_owner')
                    <div class="form-check mb-0">
                      <input class="form-check-input select-item" type="checkbox" name="selected_items[]"
                        value="{{ $item->id }}" />
                    </div>
                    @endif
                  </td>
                  @endif
                  <td class="index-number w-auto">
                    <div class="d-flex align-items-center gap-1">
                    @if ($item->status_progress === 'risk_owner')
                        {{ $index + 1 }} <span class="badge-ranking ranking-up-red bg-danger-subtle"></span>
                    @elseif ($item->status_risiko === 'ranking_risk_champion')
                      {{ $index + 1 }} <span class="badge-ranking ranking-up bg-danger-subtle"></span>
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
                    <span class="badge rounded-pill {{ $badge_class }}">{{ $level_risiko }}</span>
                  </td>
                  <td class="white-space-nowrap">
                  @can('risk_register_view')
                  <a href="{{ route('risk-register.view', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                  @endcan
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            
            @if ($status !== null && $status != 3)
              <div class="alert alert-warning mb-2">
                  Tidak bisa mengirim risiko karena risiko belum diranking atau sedang dalam proses konfirmasi Risk Owner.
              </div>
            @else
              <div class="alert alert-warning mb-2">
                  Silahkan pilih risiko yang hendak dikirim ke Risk Owner.
              </div>
            @endif
            <div class="d-flex justify-content-start mt-3">
              <button id="send-button" type="submit" class="btn btn-success me-2" disabled>Kirim Risiko</button>
          </form>
          @can('ranking_risiko_calculate')
          @if($status !== null && ($status == 2 || $status == 3))
          <form id="ranking-form" action="{{ route('risk-champion.ranking') }}" method="POST" class="d-inline-block">
            @csrf
            <button class="btn btn-submit">Ranking</button>
          </form>
          @endif
          @endcan
          @if($status !== null && ($status == 2))
          <form id="return-form" action="{{ route('risk-champion.return') }}" method="POST" class="d-inline-block" style="margin-left:10px;">
            @csrf
            <button class="btn btn-danger">Kembalikan Risiko Ke Risk Officer</button>
          </form>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
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

  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Risiko terpilih akan dikirimkan sebagai calon prioritas risiko ke risk owner",
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

document.querySelector('#ranking-form').addEventListener('submit', function(event) {
  event.preventDefault(); // Mencegah form submission otomatis

  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Risiko akan diranking berdasarkan nilai risiko",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, ranking risiko!",
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
    text: "Semua Risiko akan dikembalikan ke Risk Officer untuk dicek ulang kembali",
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
<script>
  
    document.addEventListener('DOMContentLoaded', function () {
      const status = {{ $status ?? 'null' }}; // Menggunakan nilai status dari PHP
      const sendButton = document.getElementById('send-button');

      if (status === 3) {
          sendButton.style.display = 'block'; // Tampilkan tombol
          sendButton.disabled = false;
      } else {
          sendButton.style.display = 'none';  // Sembunyikan tombol
          sendButton.disabled = true;
      }
    });
</script>
@endsection
