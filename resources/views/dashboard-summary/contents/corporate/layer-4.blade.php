@once
  @push('styles')
    <style>
      .corporate-risk-parameter__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .risk-parameter-card {
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .risk-parameter-card__header {
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .kri-category-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.75rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
        background-color: var(--bs-tertiary-bg);
      }

      .kri-category-tabs .nav-link {
        padding: 0.55rem 0.9rem;
        border: 1px solid transparent;
        border-radius: 999px;
        color: var(--bs-secondary-color);
        background-color: transparent;
        font-size: 0.8rem;
        font-weight: 600;
      }

      .kri-category-tabs .nav-link:hover {
        color: var(--bs-primary);
        border-color: var(--bs-primary-border-subtle);
        background-color: var(--bs-primary-bg-subtle);
      }

      .kri-category-tabs .nav-link.active {
        color: #fff;
        border-color: var(--bs-primary);
        background-color: var(--bs-primary);
      }

      .corporate-kri-table {
        min-width: 1320px;
        margin-bottom: 0;
        font-size: 0.76rem;
      }

      .corporate-kri-table th {
        padding: 0.75rem;
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        font-size: 0.66rem;
        letter-spacing: 0.025rem;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
      }

      .corporate-kri-table td {
        padding: 0.75rem;
        vertical-align: middle;
      }

      .corporate-kri-table__risk,
      .corporate-kri-table__cause,
      .corporate-kri-table__indicator {
        min-width: 220px;
      }

      .corporate-kri-table__owner {
        min-width: 160px;
        font-weight: 600;
      }

      .kri-status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        min-width: 92px;
        padding: 0.35rem 0.6rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
      }

      .kri-status::before {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 50%;
        content: "";
      }

      .kri-status--safe {
        color: #087443;
        background-color: #dff7eb;
      }

      .kri-status--safe::before {
        background-color: #0bad68;
      }

      .kri-status--alert {
        color: #8a6500;
        background-color: #fff3cd;
      }

      .kri-status--alert::before {
        background-color: #f3b700;
      }

      .kri-status--danger {
        color: #a52836;
        background-color: #fde3e6;
      }

      .kri-status--danger::before {
        background-color: #e63757;
      }

      .effectiveness-summary {
        display: grid;
        grid-template-columns: 180px 1fr;
        align-items: center;
        gap: 1.5rem;
        min-height: 280px;
        padding: 1.25rem;
      }

      .effectiveness-donut {
        position: relative;
        display: grid;
        width: 160px;
        height: 160px;
        place-items: center;
        border-radius: 50%;
        background: conic-gradient(var(--bs-primary) 0 78%, var(--bs-danger) 78% 100%);
      }

      .effectiveness-donut::before {
        position: absolute;
        width: 105px;
        height: 105px;
        border-radius: 50%;
        background-color: var(--bs-body-bg);
        content: "";
      }

      .effectiveness-donut__value {
        position: relative;
        z-index: 1;
        color: var(--bs-heading-color);
        font-size: 1.35rem;
        font-weight: 700;
        text-align: center;
      }

      .effectiveness-donut__value small {
        display: block;
        color: var(--bs-secondary-color);
        font-size: 0.65rem;
        font-weight: 500;
      }

      .effectiveness-legend {
        display: grid;
        gap: 0.75rem;
      }

      .effectiveness-legend__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.75rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.6rem;
      }

      .effectiveness-legend__label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
      }

      .effectiveness-legend__swatch {
        width: 0.8rem;
        height: 0.8rem;
        border-radius: 0.2rem;
      }

      .effectiveness-detail-list {
        display: grid;
        gap: 0.6rem;
      }

      .effectiveness-detail-item {
        padding: 0.75rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.55rem;
        background-color: var(--bs-tertiary-bg);
      }

      .effectiveness-detail-item__title {
        color: var(--bs-heading-color);
        font-weight: 600;
      }

      .effectiveness-detail-item__meta {
        margin-top: 0.25rem;
        color: var(--bs-secondary-color);
        font-size: 0.75rem;
      }

      @media (max-width: 767.98px) {
        .effectiveness-summary {
          grid-template-columns: 1fr;
          justify-items: center;
        }

        .effectiveness-legend {
          width: 100%;
        }
      }
    </style>
  @endpush
@endonce

@php
  $isDivisionConsolidation = $isDivisionConsolidation ?? false;
  $kriGroups = [
    'corporate' => [
      'label' => 'Korporat',
      'rows' => [
        [
          'owner' => 'Unit Korporat',
          'risk' => 'Potensi kegagalan restrukturisasi keuangan',
          'cause' => 'Arus kas operasional berada di bawah kebutuhan pendanaan.',
          'indicator' => 'Rasio arus kas operasi terhadap kewajiban',
          'safe' => '> 1,20',
          'alert' => '1,00–1,20',
          'danger' => '< 1,00',
          'current' => '1,08',
          'status' => 'Siaga',
        ],
        [
          'owner' => 'Unit Korporat',
          'risk' => 'Potensi penurunan daya saing',
          'cause' => 'Pertumbuhan kontrak baru lebih rendah dari target.',
          'indicator' => 'Persentase pencapaian kontrak baru',
          'safe' => '≥ 100%',
          'alert' => '80–99%',
          'danger' => '< 80%',
          'current' => '76%',
          'status' => 'Bahaya',
        ],
      ],
    ],
    'division' => [
      'label' => 'Divisi',
      'rows' => [
        [
          'owner' => 'Infrastructure 1 Division',
          'risk' => 'Keterlambatan penerimaan piutang',
          'cause' => 'Dokumen penagihan belum lengkap dari proyek.',
          'indicator' => 'Collection period tagihan bruto',
          'safe' => '≤ 30 hari',
          'alert' => '31–60 hari',
          'danger' => '> 60 hari',
          'current' => '48 hari',
          'status' => 'Siaga',
        ],
        [
          'owner' => 'Building Division',
          'risk' => 'Penurunan margin usaha',
          'cause' => 'Peningkatan biaya material dan subkontraktor.',
          'indicator' => 'Deviasi margin terhadap RKAP',
          'safe' => '≤ 2%',
          'alert' => '2–5%',
          'danger' => '> 5%',
          'current' => '1,4%',
          'status' => 'Aman',
        ],
      ],
    ],
    'project' => [
      'label' => 'Proyek',
      'rows' => [
        [
          'owner' => 'Proyek Bendungan Cipta',
          'risk' => 'Perpanjangan waktu pelaksanaan',
          'cause' => 'Proyek mengalami slowdown pada pekerjaan kritis.',
          'indicator' => 'Deviasi progress fisik terhadap waktu',
          'safe' => '0–5%',
          'alert' => '5–10%',
          'danger' => '> 10%',
          'current' => '12,6%',
          'status' => 'Bahaya',
        ],
        [
          'owner' => 'Proyek Jalan Tol Utara',
          'risk' => 'Kenaikan harga kebutuhan proyek',
          'cause' => 'Harga material terdampak inflasi selama pelaksanaan.',
          'indicator' => 'Inflasi harga sumber daya utama',
          'safe' => '< 3%',
          'alert' => '3–5%',
          'danger' => '> 5%',
          'current' => '4,2%',
          'status' => 'Siaga',
        ],
      ],
    ],
    'ap' => [
      'label' => 'Anak Perusahaan',
      'rows' => [
        [
          'owner' => 'WIKA Beton',
          'risk' => 'Penurunan utilisasi kapasitas produksi',
          'cause' => 'Permintaan produk pracetak lebih rendah dari proyeksi.',
          'indicator' => 'Utilisasi kapasitas pabrik',
          'safe' => '≥ 80%',
          'alert' => '60–79%',
          'danger' => '< 60%',
          'current' => '72%',
          'status' => 'Siaga',
        ],
        [
          'owner' => 'WIKA Industri',
          'risk' => 'Gangguan rantai pasok bahan baku',
          'cause' => 'Ketergantungan terhadap vendor bahan baku tertentu.',
          'indicator' => 'Hari keterlambatan pasokan strategis',
          'safe' => '≤ 3 hari',
          'alert' => '4–7 hari',
          'danger' => '> 7 hari',
          'current' => '2 hari',
          'status' => 'Aman',
        ],
      ],
    ],
  ];

  if ($isDivisionConsolidation) {
    $kriGroups = [
      'all-divisions' => [
        'label' => 'Top KRI Seluruh Divisi',
        'rows' => [
          ...$kriGroups['division']['rows'],
          [
            'owner' => 'Infrastructure 2 & Building Division',
            'risk' => 'Deviasi waktu penyelesaian pekerjaan',
            'cause' => 'Produktivitas pekerjaan utama berada di bawah rencana.',
            'indicator' => 'Deviasi progres fisik terhadap jadwal',
            'safe' => '≤ 3%',
            'alert' => '4–7%',
            'danger' => '> 7%',
            'current' => '8,2%',
            'status' => 'Bahaya',
          ],
          [
            'owner' => 'Supply Chain Management Division',
            'risk' => 'Gangguan ketersediaan material strategis',
            'cause' => 'Lead time pemasok utama mengalami peningkatan.',
            'indicator' => 'Hari keterlambatan material strategis',
            'safe' => '≤ 3 hari',
            'alert' => '4–7 hari',
            'danger' => '> 7 hari',
            'current' => '5 hari',
            'status' => 'Siaga',
          ],
        ],
      ],
    ];
  }

  $effectiveTreatments = [
    ['risk' => 'Keterlambatan penerimaan piutang', 'treatment' => 'Percepatan verifikasi dokumen penagihan', 'owner' => 'Finance Division'],
    ['risk' => 'Gangguan pasokan material', 'treatment' => 'Penambahan vendor alternatif strategis', 'owner' => 'Supply Chain Division'],
    ['risk' => 'Penurunan margin proyek', 'treatment' => 'Value engineering untuk pekerjaan utama', 'owner' => 'Infrastructure 1 Division'],
  ];

  $ineffectiveTreatments = [
    ['risk' => 'Deviasi waktu pelaksanaan', 'treatment' => 'Penambahan jam kerja pada aktivitas kritis', 'owner' => 'Proyek Bendungan Cipta'],
    ['risk' => 'Kenaikan harga material', 'treatment' => 'Negosiasi ulang harga vendor utama', 'owner' => 'Proyek Jalan Tol Utara'],
  ];

  if ($isDivisionConsolidation) {
    $effectiveTreatments = [
      ['risk' => 'Keterlambatan penerimaan piutang', 'treatment' => 'Percepatan verifikasi dokumen penagihan', 'owner' => 'Finance Division'],
      ['risk' => 'Gangguan pasokan material', 'treatment' => 'Penambahan vendor alternatif strategis', 'owner' => 'Supply Chain Management Division'],
      ['risk' => 'Penurunan margin usaha', 'treatment' => 'Program efisiensi biaya operasional', 'owner' => 'Building Division'],
    ];
    $ineffectiveTreatments = [
      ['risk' => 'Deviasi waktu pelaksanaan', 'treatment' => 'Penambahan sumber daya pada aktivitas kritis', 'owner' => 'Infrastructure 1 Division'],
      ['risk' => 'Kenaikan biaya operasional', 'treatment' => 'Negosiasi ulang kontrak pemasok', 'owner' => 'Infrastructure 2 & Building Division'],
    ];
  }
@endphp

<section class="dashboard-summary-layer corporate-risk-parameter" data-dashboard-category="{{ $isDivisionConsolidation ? 'consolidated-division' : 'corporate' }}" data-layer="4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="corporate-risk-parameter__title mb-1">Parameter Risiko {{ $isDivisionConsolidation ? 'Konsolidasi Divisi' : 'Korporat' }}</h4>
      <p class="text-muted mb-0">{{ $isDivisionConsolidation ? 'Top Key Risk Indicator seluruh divisi' : 'Key Risk Indicator' }} dan efektivitas perlakuan risiko periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
  </div>

  <div class="risk-parameter-card mb-4">
    <div class="risk-parameter-card__header">
      <h5 class="mb-1">Key Risk Indicator (KRI)</h5>
      <p class="text-muted small mb-0">Daftar indikator dengan status Aman, Siaga, dan Bahaya.</p>
    </div>

    <ul class="nav kri-category-tabs" id="{{ $isDivisionConsolidation ? 'division-consolidation' : 'corporate' }}-kri-tabs" role="tablist">
      @foreach ($kriGroups as $groupKey => $group)
        <li class="nav-item" role="presentation">
          <button
            class="nav-link {{ $loop->first ? 'active' : '' }}"
            id="kri-{{ $groupKey }}-tab"
            data-bs-toggle="tab"
            data-bs-target="#kri-{{ $groupKey }}"
            type="button"
            role="tab"
            aria-controls="kri-{{ $groupKey }}"
            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
          >
            {{ $group['label'] }}
          </button>
        </li>
      @endforeach
    </ul>

    <div class="tab-content">
      @foreach ($kriGroups as $groupKey => $group)
        <div
          class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
          id="kri-{{ $groupKey }}"
          role="tabpanel"
          aria-labelledby="kri-{{ $groupKey }}-tab"
          tabindex="0"
        >
          <div class="table-responsive">
            <table class="table table-striped table-hover corporate-kri-table">
              <thead>
                <tr>
                  <th>Pemilik</th>
                  <th>Risiko</th>
                  <th>Penyebab</th>
                  <th>KRI</th>
                  <th>Batas Aman</th>
                  <th>Batas Siaga</th>
                  <th>Batas Bahaya</th>
                  <th>Kondisi Saat Ini</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($group['rows'] as $kri)
                  @php
                    $statusClass = match ($kri['status']) {
                      'Aman' => 'safe',
                      'Siaga' => 'alert',
                      default => 'danger',
                    };
                  @endphp
                  <tr>
                    <td class="corporate-kri-table__owner">{{ $kri['owner'] }}</td>
                    <td class="corporate-kri-table__risk">{{ $kri['risk'] }}</td>
                    <td class="corporate-kri-table__cause">{{ $kri['cause'] }}</td>
                    <td class="corporate-kri-table__indicator">{{ $kri['indicator'] }}</td>
                    <td class="text-center text-nowrap">{{ $kri['safe'] }}</td>
                    <td class="text-center text-nowrap">{{ $kri['alert'] }}</td>
                    <td class="text-center text-nowrap">{{ $kri['danger'] }}</td>
                    <td class="text-center fw-bold text-nowrap">{{ $kri['current'] }}</td>
                    <td class="text-center">
                      <span class="kri-status kri-status--{{ $statusClass }}">{{ $kri['status'] }}</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <h4 class="corporate-risk-parameter__title mb-3">Efektivitas Perlakuan Risiko</h4>

  <div class="row g-3">
    <div class="col-12 col-xl-5">
      <div class="risk-parameter-card h-100">
        <div class="risk-parameter-card__header">
          <h5 class="mb-1">Ringkasan Efektivitas</h5>
          <p class="text-muted small mb-0">Penilaian perlakuan pada risiko berstatus selesai.</p>
        </div>
        <div class="effectiveness-summary">
          <div class="effectiveness-donut" aria-label="78 persen perlakuan efektif">
            <div class="effectiveness-donut__value">
              78%
              <small>Efektif</small>
            </div>
          </div>
          <div class="effectiveness-legend">
            <div class="effectiveness-legend__item">
              <span class="effectiveness-legend__label">
                <span class="effectiveness-legend__swatch bg-primary"></span>
                Perlakuan Efektif
              </span>
              <strong>14</strong>
            </div>
            <div class="effectiveness-legend__item">
              <span class="effectiveness-legend__label">
                <span class="effectiveness-legend__swatch bg-danger"></span>
                Perlakuan Tidak Efektif
              </span>
              <strong>4</strong>
            </div>
            <div class="effectiveness-legend__item">
              <span class="text-muted">Total risiko selesai</span>
              <strong>18</strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="risk-parameter-card h-100">
        <div class="risk-parameter-card__header">
          <h5 class="mb-1">Detail Risiko Selesai (Closed)</h5>
          <p class="text-muted small mb-0">Contoh detail perlakuan berdasarkan hasil evaluasi.</p>
        </div>
        <div class="p-3">
          <div class="accordion" id="corporate-effectiveness-accordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="effective-treatment-heading">
                <button
                  class="accordion-button"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#effective-treatment-content"
                  aria-expanded="true"
                  aria-controls="effective-treatment-content"
                >
                  Perlakuan Efektif
                  <span class="badge bg-primary ms-2">{{ count($effectiveTreatments) }}</span>
                </button>
              </h2>
              <div
                id="effective-treatment-content"
                class="accordion-collapse collapse show"
                aria-labelledby="effective-treatment-heading"
                data-bs-parent="#corporate-effectiveness-accordion"
              >
                <div class="accordion-body">
                  <div class="effectiveness-detail-list">
                    @foreach ($effectiveTreatments as $treatment)
                      <div class="effectiveness-detail-item">
                        <div class="effectiveness-detail-item__title">{{ $treatment['risk'] }}</div>
                        <div class="effectiveness-detail-item__meta">
                          {{ $treatment['treatment'] }} · {{ $treatment['owner'] }}
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header" id="ineffective-treatment-heading">
                <button
                  class="accordion-button collapsed"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#ineffective-treatment-content"
                  aria-expanded="false"
                  aria-controls="ineffective-treatment-content"
                >
                  Perlakuan Tidak Efektif
                  <span class="badge bg-danger ms-2">{{ count($ineffectiveTreatments) }}</span>
                </button>
              </h2>
              <div
                id="ineffective-treatment-content"
                class="accordion-collapse collapse"
                aria-labelledby="ineffective-treatment-heading"
                data-bs-parent="#corporate-effectiveness-accordion"
              >
                <div class="accordion-body">
                  <div class="effectiveness-detail-list">
                    @foreach ($ineffectiveTreatments as $treatment)
                      <div class="effectiveness-detail-item">
                        <div class="effectiveness-detail-item__title">{{ $treatment['risk'] }}</div>
                        <div class="effectiveness-detail-item__meta">
                          {{ $treatment['treatment'] }} · {{ $treatment['owner'] }}
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
