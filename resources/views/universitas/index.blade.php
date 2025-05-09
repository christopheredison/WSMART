@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row mb-5">
  <div class="col">
    <h2>Ranking Risiko Universitas</h2>
  </div>
</div>
<div class="row g-6">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-info-subtle rounded-3 p-2">
          <div class="lead__icon">
            <span class="svg-icon svg-icon-2x svg-icon-info">
              @include('partials.icon-university')
            </span>
          </div>
        </div>
        <h3 class="h4">Prioritas Risiko Universitas</h3>
      </div>
      <div class="card-body">
        <div id="table-prioritas-risiko"
          data-list='{"valueNames":["kategori_jenis_risiko","peristiwa_risiko","deskripsi_peristiwa_risiko","skala_dampak","skala_probabilitas","level_risiko"],"page":10,"filter":{"key":"kategori_jenis_risiko","peristiwa_risiko","level_risiko"},"pagination":true}'>
          <div class="row g-2 mb-4">
            @can('risk_register_all_unit')
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-unit1" class="form-label d-none">Unit</label>
              <select id="filter-unit1" class="form-select select2">
                <option value="">Unit</option>
                @foreach($unit as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            @can('risk_register_child_unit')
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-unit1" class="form-label d-none">Unit</label>
              <select id="filter-unit1" class="form-select select2">
                <option value="">Unit</option>
                @foreach($unitChild as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-kategori-jenis1" class="form-label d-none">Kategori dan Jenis Risiko</label>
              <select id="filter-kategori-jenis1" class="form-select select2">
                <option value="">Kategori dan Jenis Risiko</option>
                @foreach($kategoriJenisRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-8 col-xl-4">
              <label for="filter-risk-event1" class="form-label d-none">Peristiwa Risiko</label>
              <select id="filter-risk-event1" class="form-select select2">
                <option value="">Peristiwa Risiko</option>
                @foreach($peristiwaRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
              <label for="filter-risk-level1" class="form-label d-none">Level Risiko</label>
              <select id="filter-risk-level1" class="form-select select2 js-select-hide-search">
                <option value="">Level Risiko</option>
                <option value="High">High</option>
                <option value="Moderate to High">Moderate to High</option>
                <option value="Moderate">Moderate</option>
                <option value="Low to Moderate">Low to Moderate</option>
                <option value="Low">Low</option>
              </select>
            </div>
          </div>
          <table class="table dataTable" id="example1" data-info="true" data-paging="true" data-filter="true">
            <thead>
              <tr>
                <th class="sort" data-sort="no">#</th>
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
              @foreach ($risikoPrioritas as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="unit">{{ $item->unit->name }}</td>
                <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title }} -
                  {{ $item->jenisRisiko->title }}</td>
                <td class="peristiwa_risiko">{{ $item->peristiwaRisiko->title }}</td>
                <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
                <td class="skala_dampak">{{ $item->skala_dampak }}</td>
                <td class="skala_probabilitas">{{ $item->skalaProbabilitas->tingkat ?? NULL }}
                </td>
                <td class="skala_risiko">{{ $item->skala_risiko }}</td>
                <td class="level_risiko">
                  @php
                  $level_risiko = $item->level_risiko;
                  $badge_class = '';

                  if ($level_risiko == "High") {
                  $badge_class = 'high';
                  } elseif ($level_risiko == "Moderate To High") {
                  $badge_class = 'medium-high';
                  } elseif ($level_risiko == "Moderate") {
                  $badge_class = 'medium';
                  } elseif ($level_risiko == "Low To Moderate") {
                  $badge_class = 'low-medium';
                  } elseif ($level_risiko == "Low") {
                  $badge_class = 'low';
                  }
                  @endphp
                  <span class="badge {{ $badge_class }}">{{ $level_risiko }}</span>
                </td>
                <td class="white-space-nowrap">
                  <a href="{{ route('risk-register.view', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if(!is_null($status) && $status != 8)
          <form id="confirm-form" action="{{ route('universitas.konfirmasi') }}" method="POST">
            @csrf
            <button class="btn btn-submit">Konfirmasi Risiko Utama</button>
          </form>
        @endif
      </div>
    </div>
  </div>

  <!-- Table 2 -->
  <div class="col-12">
    <div class="card dt-header-true">
      <div class="card-header">
        <div class="row justify-content-between">
          <div class="col-12 col-sm-auto mb-0 mb-sm-4">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-warning-subtle rounded-3 p-2">
                <div class="lead__icon">
                  <span class="svg-icon svg-icon-2x svg-icon-warning">
                    @include('partials.icon-university')
                  </span>
                </div>
              </div>
              <h3 class="h4">Risiko Unit</h3>
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
                      if (strtolower($levelRisiko) == "high") {
                      $badge_class = 'high';
                      } elseif (strtolower($levelRisiko) == "moderate to high") {
                      $badge_class = 'medium-high';
                      } elseif (strtolower($levelRisiko) == "moderate") {
                      $badge_class = 'medium';
                      } elseif (strtolower($levelRisiko) == "low to moderate") {
                      $badge_class = 'low-medium';
                      } elseif (strtolower($levelRisiko) == "low") {
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
                      <h5 class="ff-heading-sm mb-0">{{ number_format((float)($averageSkalaRisiko), 2) }}
                      </h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample2"
          data-list='{"valueNames":["kategori_jenis_risiko","peristiwa_risiko","deskripsi_peristiwa_risiko","skala_dampak","skala_probabilitas","level_risiko"],"page":10,"filter":{"key":"kategori_jenis_risiko","peristiwa_risiko","level_risiko"},"pagination":true}'>
          <div class="row g-2">
            @can('risk_register_all_unit')
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-unit2" class="form-label d-none">Unit</label>
              <select id="filter-unit2" class="form-select select2">
                <option value="">Unit</option>
                @foreach($unit as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            @can('risk_register_child_unit')
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-unit2" class="form-label d-none">Unit</label>
              <select id="filter-unit2" class="form-select select2">
                <option value="">Unit</option>
                @foreach($unitChild as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            <div class="col-12 col-sm-6 col-xl-3">
              <label for="filter-kategori-jenis2" class="form-label d-none">Kategori dan Jenis Risiko</label>
              <select id="filter-kategori-jenis2" class="form-select select2">
                <option value="">Kategori dan Jenis Risiko</option>
                @foreach($kategoriJenisRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-8 col-xl-4">
              <label for="filter-risk-event2" class="form-label d-none">Peristiwa Risiko</label>
              <select id="filter-risk-event2" class="form-select select2">
                <option value="">Peristiwa Risiko</option>
                @foreach($peristiwaRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
              <label for="filter-risk-level2" class="form-label d-none">Level Risiko</label>
              <select id="filter-risk-level2" class="form-select select2 js-select-hide-search">
                <option value="">Level Risiko</option>
                <option value="High">High</option>
                <option value="Moderate To High">Moderate to High</option>
                <option value="Moderate">Moderate</option>
                <option value="Low To Moderate">Low to Moderate</option>
                <option value="Low">Low</option>
              </select>
            </div>
          </div>
          <!-- <div class="position-relative">
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
          </div> -->
          <form id="table-form" class="mt-3" action="{{ route('universitas.send') }}" method="POST">
            @csrf
            <table class="table dataTable" id="tableRisikoUnit" data-info="true" data-paging="true" data-filter="true">
              <thead>
                <tr>
                  <th class="no-sort white-space-nowrap">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" id="select-all" />
                    </div>
                  </th>
                  <th class="sort" data-sort="no">#</th>
                  <th class="sort" data-sort="unit">Unit</th>
                  <th class="sort" data-sort="kategori_jenis_risiko">Kategori dan Jenis Risiko</th>
                  <th class="sort" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                  <th class="sort" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                  <th class="sort" data-sort="skala_dampak">Skala Dampak</th>
                  <th class="sort" data-sort="skala_probabilitas">Skala Probabilitas</th>
                  <th class="sort" data-sort="skala_risiko">Nilai Risiko</th>
                  <th class="sort" data-sort="level_risiko">Level Risiko</th>
                </tr>
              </thead>
              <tbody class="list" id="bulk-select-body">
                @foreach ($risiko as $index => $item)
                <tr>
                  <td class="white-space-nowrap">
                    <div class="form-check mb-0">
                      <input class="form-check-input select-item" type="checkbox" name="selected_items[]"
                        value="{{ $item->id }}" />
                    </div>
                  </td>
                  <td class="index-number w-auto">
                    <div class="d-flex align-items-center gap-1">    
                      @if ($item->status === 2)
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
                  <td class="skala_dampak">{{ $item->skala_dampak }}</td>
                  <td class="skala_probabilitas">{{ $item->skalaProbabilitas->tingkat ?? NULL }}
                  </td>
                  <td class="skala_risiko">{{ $item->skala_risiko }}</td>
                  <td class="level_risiko">
                    @php
                    $level_risiko = $item->level_risiko;
                    $badge_class = '';

                    if ($level_risiko == "High") {
                    $badge_class = 'high';
                    } elseif ($level_risiko == "Moderate To High") {
                    $badge_class = 'medium-high';
                    } elseif ($level_risiko == "Moderate") {
                    $badge_class = 'medium';
                    } elseif ($level_risiko == "Low To Moderate") {
                    $badge_class = 'low-medium';
                    } elseif ($level_risiko == "Low") {
                    $badge_class = 'low';
                    }
                    @endphp
                    <span class="badge {{ $badge_class }}">{{ $level_risiko }}</span>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            <div class="d-flex gap-2 mt-3">
            @if(!is_null($status) && $status != 8)
              <button id="send-button" type="submit" class="btn btn-danger">Terima Risiko</button>
            @else
              <button id="send-button" type="submit" class="btn btn-danger" disabled>Terima Risiko</button>       
            @endif
          </form>
          @if(!is_null($status) && $status != 8)
          <form id="ranking-form" class="m-0" action="{{ route('universitas.ranking') }}" method="POST" class="d-inline-block">
            @csrf
            <button class="btn btn-submit">Ranking</button>
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
  const table = $('#tableRisikoUnit').DataTable();
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
<script>
const table = new DataTable('#example1');

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
<script src="vendors/list.js/list.min.js"></script>
<script>
const table2 = new DataTable('#example2');

table2.on('mouseenter', 'td', function() {
  let colIdx = table2.cell(this).index().column;

  table2
    .cells()
    .nodes()
    .each((el) => el.classList.remove('highlight'));

  table2
    .column(colIdx)
    .nodes()
    .each((el) => el.classList.add('highlight'));
});
</script>
<script>
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
 
document.addEventListener('DOMContentLoaded', function() {
  var tables = [{
      tableId: 'example1',
      filterUnitId: 'filter-unit1',
      filterKategoriJenisId: 'filter-kategori-jenis1',
      filterRiskEventId: 'filter-risk-event1',
      filterRiskLevelId: 'filter-risk-level1',
    },
    {
      tableId: 'example2',
      filterUnitId: 'filter-unit2',
      filterKategoriJenisId: 'filter-kategori-jenis2',
      filterRiskEventId: 'filter-risk-event2',
      filterRiskLevelId: 'filter-risk-level2',
    }
  ];

  tables.forEach(function(table) {
    var tableElement = document.getElementById(table.tableId);
    var filterUnit = document.getElementById(table.filterUnitId);
    var filterKategoriJenis = document.getElementById(table.filterKategoriJenisId);
    var filterRiskEvent = document.getElementById(table.filterRiskEventId);
    var filterRiskLevel = document.getElementById(table.filterRiskLevelId);

    var filterRows = function() {
      var unitFilter = filterUnit.value.toLowerCase();
      var kategoriJenisFilter = filterKategoriJenis.value.toLowerCase();
      var riskEventFilter = filterRiskEvent.value.toLowerCase();
      var riskLevelFilter = filterRiskLevel.value.toLowerCase();

      var rows = tableElement.querySelectorAll('tbody tr');

      rows.forEach(function(row) {
        var unit = row.querySelector('.unit').innerText.toLowerCase();
        var kategoriJenisRisiko = row.querySelector('.kategori_jenis_risiko').innerText.toLowerCase();
        var peristiwaRisiko = row.querySelector('.peristiwa_risiko').innerText.toLowerCase();
        var levelRisiko = row.querySelector('.level_risiko').innerText.toLowerCase();

        var unitMatch = !unitFilter || unit.includes(unitFilter);
        var kategoriJenisMatch = !kategoriJenisFilter || kategoriJenisRisiko.includes(
          kategoriJenisFilter);
        var peristiwaMatch = !riskEventFilter || peristiwaRisiko.includes(riskEventFilter);
        var riskLevelMatch = !riskLevelFilter || levelRisiko.includes(riskLevelFilter);

        if (unitMatch && kategoriJenisMatch && peristiwaMatch && riskLevelMatch) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    };

    filterUnit.addEventListener('change', filterRows);
    filterKategoriJenis.addEventListener('change', filterRows);
    filterRiskEvent.addEventListener('change', filterRows);
    filterRiskLevel.addEventListener('change', filterRows);
  });
});
</script>
@endsection
</body>

</html>
