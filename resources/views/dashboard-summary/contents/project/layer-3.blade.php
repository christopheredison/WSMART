@once
  @push('styles')
    <style>
      .project-led__title { color: var(--bs-primary); font-weight: 700; }
      .project-led__identity {
        display: inline-flex; align-items: center; gap: .45rem; padding: .5rem .8rem;
        border-radius: 999px; color: var(--bs-info); background: var(--bs-info-bg-subtle);
        font-size: .78rem; font-weight: 700;
      }
      .project-led-stat {
        height: 100%; padding: 1rem; border: 1px solid var(--bs-border-color); border-radius: .7rem;
        background: var(--bs-body-bg); box-shadow: 0 .25rem 1rem rgba(23, 32, 51, .05);
      }
      .project-led-stat__label { color: var(--bs-secondary-color); font-size: .76rem; }
      .project-led-stat__value { margin-top: .3rem; color: var(--bs-heading-color); font-size: 1.1rem; font-weight: 700; }
      .project-led-stat__value--loss { color: var(--bs-danger); }
      .project-led-card {
        overflow: hidden; border: 1px solid var(--bs-border-color); border-radius: .75rem;
        background: var(--bs-body-bg); box-shadow: 0 .25rem 1rem rgba(23, 32, 51, .06);
      }
      .project-led-card__header {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        padding: 1rem 1.125rem; border-bottom: 1px solid var(--bs-border-color);
      }
      .project-led-table { min-width: 1050px; margin-bottom: 0; font-size: .78rem; }
      .project-led-table th {
        padding: .75rem; color: var(--bs-secondary-color); background: var(--bs-tertiary-bg);
        font-size: .67rem; letter-spacing: .025rem; text-transform: uppercase; white-space: nowrap;
      }
      .project-led-table td { padding: .75rem; vertical-align: middle; }
      .project-led-table__event { min-width: 210px; font-weight: 600; }
      .project-led-table__description { min-width: 290px; color: var(--bs-secondary-color); }
      .project-led-table__loss { color: var(--bs-danger); font-weight: 700; text-align: right; white-space: nowrap; }
      .project-led-category {
        display: inline-flex; padding: .25rem .55rem; border-radius: 999px;
        color: var(--bs-primary); background: var(--bs-primary-bg-subtle); font-size: .7rem; font-weight: 600;
      }
    </style>
  @endpush
@endonce

@php
  $projectName = $selectedProjectModel?->project_name ?? 'Proyek Terpilih';
  $divisionName = $selectedDivisionModel?->name ?? 'Divisi Terpilih';
  $selectedYear = substr($selectedPeriod, 0, 4);
  $projectLossEvents = [
    ['date' => '12 Jul 2026', 'name' => 'Pekerjaan ulang struktur akibat hasil uji mutu', 'description' => 'Bagian struktur perlu diperbaiki karena hasil pengujian belum memenuhi spesifikasi.', 'category' => 'Mutu', 'loss' => 8750000000],
    ['date' => '28 Jun 2026', 'name' => 'Kerusakan alat berat utama', 'description' => 'Alat berat berhenti beroperasi dan membutuhkan penggantian komponen utama.', 'category' => 'Peralatan', 'loss' => 6425000000],
    ['date' => '17 Mei 2026', 'name' => 'Keterlambatan pengiriman material', 'description' => 'Material strategis datang melewati jadwal dan menghambat pekerjaan kritis.', 'category' => 'Rantai Pasok', 'loss' => 3150000000],
    ['date' => '09 Apr 2026', 'name' => 'Penghentian pekerjaan akibat cuaca ekstrem', 'description' => 'Aktivitas lapangan dihentikan sementara untuk menjaga keselamatan pekerja.', 'category' => 'Eksternal', 'loss' => 2280000000],
    ['date' => '21 Mar 2026', 'name' => 'Utilitas eksisting tidak terpetakan', 'description' => 'Ditemukan utilitas bawah tanah yang tidak tercantum pada desain awal.', 'category' => 'Perencanaan', 'loss' => 1450000000],
    ['date' => '08 Feb 2026', 'name' => 'Kecelakaan kerja ringan', 'description' => 'Insiden kerja menimbulkan biaya penanganan dan penghentian aktivitas sementara.', 'category' => 'K3', 'loss' => 375000000],
  ];
  $projectLossTotal = collect($projectLossEvents)->sum('loss');
@endphp

<section class="dashboard-summary-layer project-led" data-dashboard-category="project" data-layer="3">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="project-led__title mb-1">Loss Event Database Proyek</h4>
      <p class="text-muted mb-0">{{ $projectName }} · {{ $divisionName }} · periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <span class="project-led__identity"><i class="bx bx-hard-hat" aria-hidden="true"></i>{{ $projectName }}</span>
      <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4"><div class="project-led-stat"><div class="project-led-stat__label">Jumlah Loss Event</div><div class="project-led-stat__value">{{ count($projectLossEvents) }} kejadian</div></div></div>
    <div class="col-12 col-md-4"><div class="project-led-stat"><div class="project-led-stat__label">Total Kerugian Finansial</div><div class="project-led-stat__value project-led-stat__value--loss">Rp {{ number_format($projectLossTotal, 0, ',', '.') }}</div></div></div>
    <div class="col-12 col-md-4"><div class="project-led-stat"><div class="project-led-stat__label">Tahun Pelaporan</div><div class="project-led-stat__value">{{ $selectedYear }}</div></div></div>
  </div>

  <div class="project-led-card">
    <div class="project-led-card__header">
      <div><h5 class="mb-1">Top 10 Loss Event Proyek ({{ $selectedYear }})</h5><p class="text-muted small mb-0">Diurutkan berdasarkan nilai kerugian terbesar.</p></div>
      <span class="badge bg-danger-subtle text-danger">{{ count($projectLossEvents) }} kejadian</span>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover project-led-table">
        <thead><tr><th>#</th><th>Tanggal Kejadian</th><th>Nama Kejadian</th><th>Deskripsi Kejadian</th><th>Kategori</th><th class="text-end">Nilai Kerugian</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
          @foreach ($projectLossEvents as $event)
            <tr>
              <td>{{ $loop->iteration }}</td><td class="text-nowrap">{{ $event['date'] }}</td>
              <td class="project-led-table__event">{{ $event['name'] }}</td>
              <td class="project-led-table__description">{{ $event['description'] }}</td>
              <td><span class="project-led-category">{{ $event['category'] }}</span></td>
              <td class="project-led-table__loss">Rp {{ number_format($event['loss'], 0, ',', '.') }}</td>
              <td class="text-center"><button type="button" class="btn btn-sm btn-link p-1" title="Lihat detail dummy"><i class="bx bx-show fs-5" aria-hidden="true"></i></button></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>
