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
          <div class="ff-preheading">Data</div>
          <h2>Risiko Utama Korporasi</h2>
          @if(isset($selectedPeriode))
          <div class="ff-preheading">Periode: {{ $selectedPeriode->tahun }}</div>
          @endif
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample3">
          <div class="row g-2 mb-1">
            <div class="col-4 col-sm-2">
              <form action="{{ route('corporate-risk.index') }}" method="GET" id="filter-unit-form">
                @if(request()->has('pid'))
                <input type="hidden" name="pid" value="{{ request('pid') }}">
                @endif
                <label for="unit_id" class="form-label">Unit</label>
                <select id="unit_id" name="unit_id" class="form-select select2" onchange="this.form.submit()">
                  <option value="" selected>Semua Unit</option>
                  @foreach($unit as $id => $name)
                  <option value="{{ $id }}" {{ $unitId == $id ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
              </form>
            </div>
            <div class="col-4 col-sm-2">
              <form action="{{ route('corporate-risk.index') }}" method="GET" id="filter-periode-form">
                @if(request()->has('unit_id'))
                <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">
                @endif
                <label for="pid" class="form-label">Periode</label>
                <select id="pid" name="pid" class="form-select select2" onchange="this.form.submit()">
                  @foreach($periodes as $id => $tahun)
                  <option value="{{ $id }}" {{ $selectedPeriode && $selectedPeriode->id == $id ? 'selected' : '' }}>{{ $tahun }}</option>
                  @endforeach
                </select>
              </form>
            </div>
          </div>
          <form id="corporate-risk-form" action="{{ route('corporate-risk.update-to-corporate') }}" method="POST">
            @csrf
            <input type="hidden" name="periode_id" value="{{ $selectedPeriode->id ?? '' }}">
            <table class="table dataTable" id="example" data-paging="true" data-info="true" data-filter="true">
              <thead>
                <tr>
                  <th class="no-sort white-space-nowrap">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" id="select-all-main" />
                    </div>
                  </th>
                  <th class="white-space-nowrap">#</th>
                  <th class="sort" data-sort="unit">Unit</th>
                  <th class="sort" data-sort="unit_type">Sasaran</th>
                  <th class="sort mw-20r" data-sort="kategori_jenis_risiko">T2 & T3 KBUMN</th>
                  <th class="sort mw-10r" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                  <th class="sort mw-10r" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                  <th class="sort mw-10r" data-sort="kontrol_eksisting">Jenis Kontrol Eksisting</th>
                  <th class="sort mw-10r" data-sort="kategori dampak">Kategori Dampak</th>
                  <th class="sort mw-10r" data-sort="nilai_risiko_inherent">Nilai Risiko</th>
                  <th class="sort mw-10r" data-sort="eksposure_risiko_inherent">Eksposure Risiko</th>
                  <th class="sort mw-10r" data-sort="total_biaya_rencana_perlakuan">Total Biaya Rencana Perlakuan</th>
                  <th class="sort mw-15r" data-sort="waktu_terpapar">Waktu Terpapar</th>
                  <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
                </tr>
              </thead>
              <tbody class="list" id="bulk-select-body">
                @foreach ($risikoMain as $index => $item)
                {{--
                <tr @if($item->status_risiko == \App\Models\IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION) class="bg-warning-subtle" @endif>
                --}}
                <tr>
                  <td class="white-space-nowrap">
                    <div class="form-check mb-0"> 
                      <input class="form-check-input select-item-main" type="checkbox" name="selected_risks[]" value="{{ $item->id }}" />
                    </div>
                  </td>
                  <td class="index-number">
                    @if($item->status_risiko == \App\Models\IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION)
                      <span class="badge bg-warning">Rekomendasi Korporat</span>
                    @else
                      <span class="badge bg-danger">Risiko Utama</span>
                    @endif
                    {{ $index + 1 }}
                  </td>
                  <td class="unit">
                    {{ $item->unit->name ?? '-' }}
                  </td>
                  <td class="unit_type">{{ $item->target_capaian_kinerja ?? '-' }}</td>
                  <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title ?? '-' }} - {{ $item->jenisRisiko->title ?? '-' }}</td>
                  <td class="peristiwa_risiko">{{ $item->peristiwa_risiko ?? '-' }}</td>
                  <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
                  <td class="kontrol_eksisting">{{ $item->jenisKontrolEksisting->jenis_kontrol ?? '-' }}</td>
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
                  <td class="eksposure_risiko">{{ $item->riskAnalysis->eksposur_risiko ? 'Rp ' . number_format($item->riskAnalysis->eksposur_risiko, 0, ',', '.') : '-' }}</td>
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
                  <td class="white-space-nowrap">
                    <a href="{{ route('risk-register-unit.view', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                      title="View">
                      <span class="bx bx-show-alt"></span>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            <div class="d-flex justify-content-end mt-3">
                <input type="hidden" name="periode_id" id="periode_id" value="{{ $selectedPeriode->id ?? '' }}">
                @if(isset($showRankingButton) && $showRankingButton)
                <button id="ranking-button" type="button" class="btn btn-primary">Ranking Risiko</button>
                @endif
                
                @if(isset($showCorporateButton) && $showCorporateButton)
                <button id="update-to-corporate-button" type="button" class="btn btn-submit">Jadikan Risiko Corporate</button>
                @endif
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabel Risiko Corporate -->
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-danger-subtle p-2 rounded-4">
          <div class="lead__icon">
            <div class="svg-icon svg-icon-2x svg-icon-danger">
              @include('partials.icon-pyramid')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Data</div>
          <h2>Risiko Corporate</h2>
          @if(isset($selectedPeriode))
          <div class="ff-preheading">Periode: {{ $selectedPeriode->tahun }}</div>
          @endif
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample4">
          <table class="table dataTable" id="example-corporate" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="unit">Unit</th>
                <th class="sort" data-sort="unit_type">Sasaran</th>
                <th class="sort mw-20r" data-sort="kategori_jenis_risiko">T2 & T3 KBUMN</th>
                <th class="sort mw-10r" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                <th class="sort mw-10r" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                <th class="sort mw-10r" data-sort="kontrol_eksisting">Jenis Kontrol Eksisting</th>
                <th class="sort mw-10r" data-sort="kategori dampak">Kategori Dampak</th>
                <th class="sort mw-10r" data-sort="nilai_risiko_inherent">Nilai Risiko</th>
                <th class="sort mw-10r" data-sort="eksposure_risiko_inherent">Eksposure Risiko</th>
                <th class="sort mw-10r" data-sort="total_biaya_rencana_perlakuan">Total Biaya Rencana Perlakuan</th>
                <th class="sort mw-15r" data-sort="waktu_terpapar">Waktu Terpapar</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list">
              @foreach ($risikoCorporate as $index => $item)
              <tr>
                <td class="index-number">
                  <span class="badge bg-success">Risiko Corporate</span> 
                  {{ $index + 1 }}
                </td>
                <td class="unit">
                  {{ $item->unit->name ?? '-' }}
                </td>
                <td class="unit_type">{{ $item->target_capaian_kinerja ?? '-' }}</td>
                <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title ?? '-' }} - {{ $item->jenisRisiko->title ?? '-' }}</td>
                <td class="peristiwa_risiko">{{ $item->peristiwa_risiko ?? '-' }}</td>
                <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
                <td class="kontrol_eksisting">{{ $item->jenisKontrolEksisting->jenis_kontrol ?? '-' }}</td>
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
                <td class="eksposure_risiko">{{ $item->riskAnalysis->eksposur_risiko ? 'Rp ' . number_format($item->riskAnalysis->eksposur_risiko, 0, ',', '.') : '-' }}</td>
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
                <td class="white-space-nowrap">
                  <a href="{{ route('risk-register-unit.view', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                  @if(isset($dataBatch) && $dataBatch->status != \App\Models\DataBatch::STATUS_FINISH)
                  <button type="button" class="btn-input-icon revert-button" data-risk-id="{{ $item->id }}" data-bs-toggle="tooltip"
                        title="Kembalikan ke Status Sebelumnya">
                        <span class="bx bx-undo"></span>
                    </button>
                @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        
        @if($showCorporateButton)
        <div class="mt-3 text-end">
          <button type="button" id="confirm-corporate-button" class="btn btn-primary">Konfirmasi Risiko Corporate</button>
        </div>
        @endif
        
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tableMain = $('#example').DataTable();
  const tableCorporate = $('#example-corporate').DataTable();

  // Fungsi untuk menangani perubahan nilai di select unit
  $('#filter-risk-event').on('change', function() {
    var riskEvent = $(this).val();
    tableMain.column(5).search(riskEvent).draw();
    tableCorporate.column(5).search(riskEvent).draw();
  });

  $('#filter-risk-t2t3').on('change', function() {
    var riskEvent = $(this).val();
    tableMain.column(4).search(riskEvent).draw();
    tableCorporate.column(4).search(riskEvent).draw();
  });

  // Function to handle changes in the risk level filter
  $('#filter-risk-level').on('change', function() {
    var riskLevel = $(this).val();
    tableMain.column(9).search(riskLevel).draw();
    tableCorporate.column(9).search(riskLevel).draw();
  });

  // Select/Deselect all checkboxes for main risks
  $('#select-all-main').on('click', function() {
    var rows = tableMain.rows({
      'search': 'applied'
    }).nodes();
    $('input.select-item-main', rows).prop('checked', this.checked);
  });

  // Handle individual row selection for main risks
  $('#example tbody').on('change', 'input.select-item-main', function() {
    if (!this.checked) {
      var el = $('#select-all-main').get(0);
      if (el && el.checked && ('indeterminate' in el)) {
        el.indeterminate = true;
      }
    }
  });

  // Handle ranking button click
  $(document).on('click', '#ranking-button', function() {
    const periodeId = document.getElementById('periode_id').value;
    
    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Sistem akan melakukan ranking risiko berdasarkan kriteria: Risiko kuantitatif dengan eksposur di atas rata-rata dan risiko kualitatif dengan nilai risiko >= 20 akan dijadikan risiko corporate",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, lakukan ranking!",
        cancelButtonText: "Tidak, batal",
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading indicator
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Kirim request AJAX
            $.ajax({
                url: '{{ route("corporate-risk.ranking-risiko") }}',
                type: 'POST',
                data: {
                    periode_id: periodeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Ranking risiko berhasil dilakukan. Risiko yang memenuhi kriteria telah dijadikan risiko corporate.',
                        icon: 'success'
                    }).then(() => {
                        // Reload halaman untuk menampilkan perubahan
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    console.error(xhr);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat melakukan ranking risiko.',
                        icon: 'error'
                    });
                }
            });
        }
    });
  });

  // Handle update to corporate button click
  $('#update-to-corporate-button').on('click', function() {
    const selectedRisks = [];
    document.querySelectorAll('.select-item-main:checked').forEach(function(checkbox) {
      selectedRisks.push(checkbox.value);
    });
    
    // Jika tidak ada risiko yang dipilih, tampilkan peringatan
    if(selectedRisks.length === 0) {
      Swal.fire({
        title: "Peringatan",
        text: "Silakan pilih minimal satu risiko untuk dijadikan risiko corporate",
        icon: "warning",
      });
      return;
    }

    Swal.fire({
      title: "Apakah Anda yakin?",
      text: "Risiko yang dipilih akan dijadikan sebagai Risiko Corporate",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, jadikan risiko corporate!",
      cancelButtonText: "Tidak, batal",
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('corporate-risk-form').submit(); // Kirim form jika dikonfirmasi
      }
    });
  });

  $(document).on('click', '#confirm-corporate-button', function() {
    const periodeId = document.getElementById('periode_id').value;
    
    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Sistem akan mengonfirmasi risiko corporate yang akan mengubah status data batch menjadi 8 dan is_proyek menjadi 1 untuk risiko corporate",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, konfirmasi!",
        cancelButtonText: "Tidak, batal",
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading indicator
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Kirim request AJAX
            $.ajax({
                url: '{{ route("corporate-risk.confirm-corporate") }}',
                type: 'POST',
                data: {
                    periode_id: periodeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Konfirmasi risiko corporate berhasil dilakukan.',
                        icon: 'success'
                    }).then(() => {
                        // Reload halaman untuk menampilkan perubahan
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    console.error(xhr);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengonfirmasi risiko corporate.',
                        icon: 'error'
                    });
                }
            });
        }
    });
  });

  $(document).on('click', '.revert-button', function() {
    const riskId = $(this).data('risk-id');
    const periodeId = document.getElementById('periode_id').value;
    
    Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Risiko ini akan dikembalikan ke status sebelumnya (Risiko Utama atau Rekomendasi Risiko Corporate)",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, kembalikan!",
        cancelButtonText: "Tidak, batal",
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading indicator
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Kirim request AJAX
            $.ajax({
                url: '{{ route("corporate-risk.revert-from-corporate") }}',
                type: 'POST',
                data: {
                    risk_id: riskId,
                    periode_id: periodeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Risiko berhasil dikembalikan ke status sebelumnya.',
                        icon: 'success'
                    }).then(() => {
                        // Reload halaman untuk menampilkan perubahan
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    console.error(xhr);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengembalikan risiko.',
                        icon: 'error'
                    });
                }
            });
        }
    });
  });
});
</script>
@endsection