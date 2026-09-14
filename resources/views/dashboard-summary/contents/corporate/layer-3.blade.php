@once
  @push('styles')
    <style>
      .corporate-led-summary__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .corporate-led-stat {
        height: 100%;
        padding: 1rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.05);
      }

      .corporate-led-stat__label {
        color: var(--bs-secondary-color);
        font-size: 0.8rem;
      }

      .corporate-led-stat__value {
        margin-top: 0.35rem;
        color: var(--bs-heading-color);
        font-size: 1.15rem;
        font-weight: 700;
      }

      .corporate-led-stat__value--loss {
        color: var(--bs-danger);
      }

      .corporate-led-card {
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .corporate-led-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .corporate-led-card__title {
        margin: 0;
        color: var(--bs-heading-color);
        font-size: 1rem;
        font-weight: 700;
      }

      .corporate-led-table {
        min-width: 1120px;
        margin-bottom: 0;
        font-size: 0.78rem;
      }

      .corporate-led-table th {
        padding: 0.75rem;
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        font-size: 0.68rem;
        letter-spacing: 0.025rem;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
      }

      .corporate-led-table td {
        padding: 0.75rem;
        vertical-align: middle;
      }

      .corporate-led-table__entity {
        min-width: 170px;
        font-weight: 600;
      }

      .corporate-led-table__event {
        min-width: 210px;
      }

      .corporate-led-table__description {
        min-width: 280px;
        color: var(--bs-secondary-color);
      }

      .corporate-led-table__loss {
        color: var(--bs-danger);
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .corporate-led-category {
        display: inline-flex;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        color: var(--bs-primary);
        background-color: var(--bs-primary-bg-subtle);
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
      }
    </style>
  @endpush
@endonce

@php
  $isDivisionConsolidation = $isDivisionConsolidation ?? false;
  $isProjectConsolidation = $isProjectConsolidation ?? false;
  $isAllProjectsConsolidation = $isAllProjectsConsolidation ?? false;
  $corporateLossEvents = [
    [
      'date' => '12 Jul 2026',
      'event' => 'Gangguan sistem pembayaran korporat',
      'description' => 'Terjadi keterlambatan pemrosesan pembayaran kepada beberapa mitra strategis.',
      'category' => 'Operasional',
      'loss' => 3850000000,
    ],
    [
      'date' => '18 Jun 2026',
      'event' => 'Klaim kontraktual pelanggan',
      'description' => 'Klaim akibat keterlambatan penyelesaian kewajiban kontraktual.',
      'category' => 'Hukum',
      'loss' => 2125000000,
    ],
    [
      'date' => '03 Mei 2026',
      'event' => 'Penurunan nilai aset',
      'description' => 'Penyesuaian nilai atas aset operasional yang tidak lagi digunakan.',
      'category' => 'Finansial',
      'loss' => 1750000000,
    ],
  ];

  $divisionLossEvents = [
    [
      'entity' => 'Infrastructure 1 Division',
      'date' => '05 Jan 2026',
      'event' => 'Potensi keterlambatan piutang',
      'description' => 'Penerimaan piutang pelanggan melewati batas waktu pembayaran.',
      'category' => 'Finansial',
      'loss' => 45000000000,
    ],
    [
      'entity' => 'WTJJ',
      'date' => '01 Jan 2026',
      'event' => 'Keterbatasan kapasitas penyerapan sisi hilir',
      'description' => 'Kapasitas penyerapan sisi hilir lebih rendah dari proyeksi awal.',
      'category' => 'Operasional',
      'loss' => 4718834469,
    ],
    [
      'entity' => 'Infrastructure 1 Division',
      'date' => '26 Feb 2026',
      'event' => 'Keterlambatan pekerjaan',
      'description' => 'Pekerjaan pada proyek bendungan mengalami deviasi jadwal.',
      'category' => 'Proyek',
      'loss' => 1979300243,
    ],
    [
      'entity' => 'Supply Chain Management Division',
      'date' => '05 Mar 2026',
      'event' => 'Gangguan pasokan material strategis',
      'description' => 'Pasokan bahan baku dari vendor terlambat akibat gangguan distribusi.',
      'category' => 'Rantai Pasok',
      'loss' => 1000000000,
    ],
    [
      'entity' => 'Infrastructure 2 & Building Division',
      'date' => '01 Mar 2026',
      'event' => 'Kecelakaan kerja',
      'description' => 'Insiden terjatuh dari ketinggian pada area pekerjaan.',
      'category' => 'Kecelakaan',
      'loss' => 350000000,
    ],
  ];

  $projectLossEvents = [
    [
      'entity' => 'Proyek Bendungan Cipta',
      'date' => '22 Jun 2026',
      'event' => 'Kerusakan alat berat',
      'description' => 'Alat berat utama tidak dapat beroperasi dan membutuhkan penggantian komponen.',
      'category' => 'Peralatan',
      'loss' => 12650000000,
    ],
    [
      'entity' => 'Proyek Jalan Tol Utara',
      'date' => '14 Mei 2026',
      'event' => 'Pekerjaan ulang struktur',
      'description' => 'Pekerjaan struktur perlu diulang karena hasil pengujian tidak memenuhi spesifikasi.',
      'category' => 'Mutu',
      'loss' => 8750000000,
    ],
    [
      'entity' => 'Proyek Gedung Sentra',
      'date' => '07 Apr 2026',
      'event' => 'Keterlambatan pengiriman material',
      'description' => 'Material façade terlambat tiba dan berdampak pada jadwal penyelesaian.',
      'category' => 'Rantai Pasok',
      'loss' => 5325000000,
    ],
    [
      'entity' => 'Proyek Pelabuhan Timur',
      'date' => '18 Mar 2026',
      'event' => 'Gangguan cuaca ekstrem',
      'description' => 'Aktivitas lapangan dihentikan sementara akibat cuaca ekstrem.',
      'category' => 'Eksternal',
      'loss' => 2980000000,
    ],
    [
      'entity' => 'Proyek Transit Metropolitan',
      'date' => '09 Feb 2026',
      'event' => 'Utilitas eksisting tidak terpetakan',
      'description' => 'Ditemukan utilitas bawah tanah di luar informasi desain awal.',
      'category' => 'Perencanaan',
      'loss' => 1450000000,
    ],
  ];

  $corporateLossTotal = collect($corporateLossEvents)->sum('loss');
  $divisionLossTotal = collect($divisionLossEvents)->sum('loss');
  $projectLossTotal = collect($projectLossEvents)->sum('loss');
@endphp

<section class="dashboard-summary-layer corporate-led-summary" data-dashboard-category="{{ $isAllProjectsConsolidation ? 'consolidated-all-projects' : ($isProjectConsolidation ? 'consolidated-project' : ($isDivisionConsolidation ? 'consolidated-division' : 'corporate')) }}" data-layer="3">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="corporate-led-summary__title mb-1">Top Loss Events{{ $isProjectConsolidation ? ' Seluruh Proyek' : ($isDivisionConsolidation ? ' Seluruh Divisi' : '') }}</h4>
      <p class="text-muted mb-0">Ringkasan Loss Event Database {{ $isAllProjectsConsolidation ? 'teratas dari seluruh proyek semua divisi' : ($isProjectConsolidation ? 'teratas dari seluruh proyek dalam ' . ($selectedDivisionModel?->name ?? 'divisi terpilih') : ($isDivisionConsolidation ? 'teratas dari seluruh divisi' : 'korporat')) }} sampai periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
  </div>

  <div class="row g-3 mb-3">
    @foreach ($isProjectConsolidation ? [
      ['label' => 'Total LED Seluruh Proyek', 'count' => count($projectLossEvents), 'loss' => $projectLossTotal],
    ] : ($isDivisionConsolidation ? [
      ['label' => 'Total LED Seluruh Divisi', 'count' => count($divisionLossEvents), 'loss' => $divisionLossTotal],
    ] : [
      ['label' => 'Total LED Korporat', 'count' => count($corporateLossEvents), 'loss' => $corporateLossTotal],
      ['label' => 'Total LED Seluruh Divisi', 'count' => count($divisionLossEvents), 'loss' => $divisionLossTotal],
      ['label' => 'Total LED Seluruh Proyek', 'count' => count($projectLossEvents), 'loss' => $projectLossTotal],
    ]) as $summary)
      <div class="col-12 {{ $isDivisionConsolidation ? '' : 'col-md-4' }}">
        <div class="corporate-led-stat">
          <div class="corporate-led-stat__label">{{ $summary['label'] }} · {{ $summary['count'] }} kejadian</div>
          <div class="corporate-led-stat__value corporate-led-stat__value--loss">
            Rp {{ number_format($summary['loss'], 0, ',', '.') }}
          </div>
        </div>
      </div>
    @endforeach
  </div>

  @unless ($isDivisionConsolidation || $isProjectConsolidation)
  <div class="corporate-led-card mb-3">
    <div class="corporate-led-card__header">
      <h5 class="corporate-led-card__title">Top 10 Loss Event Data Korporat</h5>
      <span class="badge bg-secondary-subtle text-secondary">{{ count($corporateLossEvents) }} kejadian</span>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover corporate-led-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Tanggal Kejadian</th>
            <th>Nama Kejadian</th>
            <th>Deskripsi Kejadian</th>
            <th>Kategori Kejadian</th>
            <th class="text-end">Nilai Kerugian</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($corporateLossEvents as $event)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td class="text-nowrap">{{ $event['date'] }}</td>
              <td class="corporate-led-table__event">{{ $event['event'] }}</td>
              <td class="corporate-led-table__description">{{ $event['description'] }}</td>
              <td><span class="corporate-led-category">{{ $event['category'] }}</span></td>
              <td class="corporate-led-table__loss">Rp {{ number_format($event['loss'], 0, ',', '.') }}</td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-link p-1" title="Lihat detail dummy" aria-label="Lihat detail">
                  <span class="bx bx-show fs-5" aria-hidden="true"></span>
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endunless

  @unless ($isProjectConsolidation)
  <div class="corporate-led-card mb-3">
    <div class="corporate-led-card__header">
      <h5 class="corporate-led-card__title">Top 10 Loss Event {{ $isDivisionConsolidation ? 'Seluruh Divisi' : 'Data Divisi' }}</h5>
      <span class="badge bg-secondary-subtle text-secondary">Seluruh Divisi</span>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover corporate-led-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Divisi</th>
            <th>Tanggal Kejadian</th>
            <th>Nama Kejadian</th>
            <th>Deskripsi Kejadian</th>
            <th>Kategori Kejadian</th>
            <th class="text-end">Nilai Kerugian</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($divisionLossEvents as $event)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td class="corporate-led-table__entity">{{ $event['entity'] }}</td>
              <td class="text-nowrap">{{ $event['date'] }}</td>
              <td class="corporate-led-table__event">{{ $event['event'] }}</td>
              <td class="corporate-led-table__description">{{ $event['description'] }}</td>
              <td><span class="corporate-led-category">{{ $event['category'] }}</span></td>
              <td class="corporate-led-table__loss">Rp {{ number_format($event['loss'], 0, ',', '.') }}</td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-link p-1" title="Lihat detail dummy" aria-label="Lihat detail">
                  <span class="bx bx-show fs-5" aria-hidden="true"></span>
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endunless

  @unless ($isDivisionConsolidation)
  <div class="corporate-led-card">
    <div class="corporate-led-card__header">
      <h5 class="corporate-led-card__title">Top 10 Loss Event Data Proyek</h5>
      <span class="badge bg-secondary-subtle text-secondary">Seluruh Proyek</span>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover corporate-led-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama Proyek</th>
            <th>Tanggal Kejadian</th>
            <th>Nama Kejadian</th>
            <th>Deskripsi Kejadian</th>
            <th>Kategori Kejadian</th>
            <th class="text-end">Nilai Kerugian</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($projectLossEvents as $event)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td class="corporate-led-table__entity">{{ $event['entity'] }}</td>
              <td class="text-nowrap">{{ $event['date'] }}</td>
              <td class="corporate-led-table__event">{{ $event['event'] }}</td>
              <td class="corporate-led-table__description">{{ $event['description'] }}</td>
              <td><span class="corporate-led-category">{{ $event['category'] }}</span></td>
              <td class="corporate-led-table__loss">Rp {{ number_format($event['loss'], 0, ',', '.') }}</td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-link p-1" title="Lihat detail dummy" aria-label="Lihat detail">
                  <span class="bx bx-show fs-5" aria-hidden="true"></span>
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endunless
</section>
