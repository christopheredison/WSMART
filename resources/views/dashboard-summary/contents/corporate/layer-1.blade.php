@once
  @push('styles')
    <style>
      .mkbr-corporate {
        --mkbr-soft-blue: #e8f6fc;
        --mkbr-soft-green: #e8f8f1;
        --mkbr-ink: #172033;
      }

      .mkbr-corporate__heading {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .mkbr-card {
        height: 100%;
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .mkbr-card__header {
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .mkbr-card__header--actual {
        background-color: var(--mkbr-soft-blue);
      }

      .mkbr-card__header--projection {
        color: #fff;
        background-color: var(--mkbr-ink);
      }

      .mkbr-card__title {
        margin: 0;
        color: inherit;
        font-size: 1rem;
        font-weight: 700;
      }

      .mkbr-card__body {
        padding: 0.75rem 1.125rem;
      }

      .mkbr-metric {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.625rem 0;
      }

      .mkbr-metric + .mkbr-metric {
        border-top: 1px dashed var(--bs-border-color);
      }

      .mkbr-metric__label {
        color: var(--bs-secondary-color);
        font-size: 0.875rem;
      }

      .mkbr-metric__value {
        color: var(--bs-heading-color);
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .mkbr-value--danger {
        color: var(--bs-danger);
      }

      .mkbr-value--warning {
        color: var(--bs-warning);
      }

      .mkbr-highlight {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 88px;
        padding: 1rem 1.125rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-tertiary-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.05);
      }

      .mkbr-highlight__label {
        color: var(--bs-heading-color);
        font-weight: 700;
      }

      .mkbr-highlight__note {
        margin-top: 0.25rem;
        color: var(--bs-secondary-color);
        font-size: 0.75rem;
      }

      .mkbr-highlight__value {
        color: var(--bs-primary);
        font-size: 1.25rem;
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .mkbr-highlight__value--success {
        color: var(--bs-success);
      }

      @media (max-width: 575.98px) {
        .mkbr-metric,
        .mkbr-highlight {
          align-items: flex-start;
          flex-direction: column;
        }

        .mkbr-metric__value,
        .mkbr-highlight__value {
          text-align: left;
          white-space: normal;
        }
      }
    </style>
  @endpush
@endonce

@php
  $isDivisionConsolidation = $isDivisionConsolidation ?? false;
  $isProjectConsolidation = $isProjectConsolidation ?? false;
  $isAllProjectsConsolidation = $isAllProjectsConsolidation ?? false;
  $selectedPeriodYear = substr($selectedPeriod, 0, 4);

  $actualMetrics = $isDivisionConsolidation ? [
    ['label' => 'Total Omzet Seluruh Divisi', 'value' => 'Rp 10,92 T'],
    ['label' => 'LSP Rencana Seluruh Divisi', 'value' => 'Rp 940,25 M'],
    ['label' => 'LSP Realisasi Seluruh Divisi', 'value' => 'Rp 821,40 M'],
  ] : [
    ['label' => 'Omzet Penjualan', 'value' => 'Rp 12,84 T'],
    ['label' => 'LSP Rencana', 'value' => 'Rp 1,02 T'],
    ['label' => 'LSP Realisasi', 'value' => 'Rp 890,35 M'],
  ];

  $projectionMetrics = $isDivisionConsolidation ? [
    ['label' => 'Proyeksi Omzet Seluruh Divisi', 'value' => 'Rp 16,28 T'],
    ['label' => 'LSP Rencana Tahunan Seluruh Divisi', 'value' => 'Rp 1,31 T'],
    ['label' => 'Proyeksi LSP Seluruh Divisi', 'value' => 'Rp 1,19 T'],
  ] : [
    ['label' => 'Proyeksi Omzet Penjualan', 'value' => 'Rp 18,75 T'],
    ['label' => 'LSP Rencana Tahunan', 'value' => 'Rp 1,48 T'],
    ['label' => 'Proyeksi LSP', 'value' => 'Rp 1,36 T'],
  ];

  if ($isProjectConsolidation) {
    $actualMetrics = [
      ['label' => 'Total Omzet Seluruh Proyek', 'value' => 'Rp 2,42 T'],
      ['label' => 'LSP Rencana Seluruh Proyek', 'value' => 'Rp 286,80 M'],
      ['label' => 'LSP Realisasi Seluruh Proyek', 'value' => 'Rp 251,35 M'],
    ];
    $projectionMetrics = [
      ['label' => 'Proyeksi Omzet Seluruh Proyek', 'value' => 'Rp 3,76 T'],
      ['label' => 'LSP Rencana Tahunan Seluruh Proyek', 'value' => 'Rp 438,20 M'],
      ['label' => 'Proyeksi LSP Seluruh Proyek', 'value' => 'Rp 405,70 M'],
    ];
  }
@endphp

<section class="dashboard-summary-layer mkbr-corporate" data-dashboard-category="{{ $isAllProjectsConsolidation ? 'consolidated-all-projects' : ($isProjectConsolidation ? 'consolidated-project' : ($isDivisionConsolidation ? 'consolidated-division' : 'corporate')) }}" data-layer="1">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="mkbr-corporate__heading mb-1">Manajemen Kinerja Berbasis Risiko {{ $isAllProjectsConsolidation ? 'Konsolidasi Seluruh Proyek' : ($isProjectConsolidation ? 'Konsolidasi Proyek' : ($isDivisionConsolidation ? 'Konsolidasi Divisi' : 'Korporat')) }}</h4>
      <p class="text-muted mb-0">Ringkasan kinerja dan eksposur risiko {{ $isAllProjectsConsolidation ? 'seluruh proyek dari semua divisi' : ($isProjectConsolidation ? 'seluruh proyek dalam ' . ($selectedDivisionModel?->name ?? 'divisi terpilih') : ($isDivisionConsolidation ? 'seluruh divisi' : 'perusahaan')) }} periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-6">
      <div class="mkbr-card">
        <div class="mkbr-card__header mkbr-card__header--actual">
          <h5 class="mkbr-card__title">Hasil Usaha s.d. {{ $selectedPeriodDisplay }}</h5>
        </div>
        <div class="mkbr-card__body">
          @foreach ($actualMetrics as $metric)
            <div class="mkbr-metric">
              <span class="mkbr-metric__label">{{ $metric['label'] }}</span>
              <span class="mkbr-metric__value">{{ $metric['value'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="mkbr-card">
        <div class="mkbr-card__header mkbr-card__header--projection">
          <h5 class="mkbr-card__title">Proyeksi Hasil Usaha s.d. Desember {{ $selectedPeriodYear }}</h5>
        </div>
        <div class="mkbr-card__body">
          @foreach ($projectionMetrics as $metric)
            <div class="mkbr-metric">
              <span class="mkbr-metric__label">{{ $metric['label'] }}</span>
              <span class="mkbr-metric__value">{{ $metric['value'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="row g-3">
        @unless ($isProjectConsolidation)
        <div class="col-12">
          <div class="mkbr-card">
            <div class="mkbr-card__header">
              <h5 class="mkbr-card__title">{{ $isDivisionConsolidation ? 'Loss Event Database Seluruh Divisi' : 'Loss Event Database Divisi / AP' }}</h5>
            </div>
            <div class="mkbr-card__body">
              <div class="mkbr-metric">
                <span class="mkbr-metric__label">Total Kerugian Finansial</span>
                <span class="mkbr-metric__value mkbr-value--danger">{{ $isDivisionConsolidation ? 'Rp 53,05 M' : 'Rp 14,16 M' }}</span>
              </div>
            </div>
          </div>
        </div>
        @endunless

        @unless ($isDivisionConsolidation)
        <div class="col-12">
          <div class="mkbr-card">
            <div class="mkbr-card__header">
              <h5 class="mkbr-card__title">Loss Event Database Proyek</h5>
            </div>
            <div class="mkbr-card__body">
              <div class="mkbr-metric">
                <span class="mkbr-metric__label">Total Kerugian Finansial</span>
                <span class="mkbr-metric__value mkbr-value--danger">Rp 99,19 M</span>
              </div>
            </div>
          </div>
        </div>
        @endunless
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="mkbr-card">
        <div class="mkbr-card__header">
          <h5 class="mkbr-card__title">Eksposur Risiko Residual {{ $isProjectConsolidation ? 'Seluruh Proyek' : ($isDivisionConsolidation ? 'Seluruh Divisi' : 'Unit Korporat') }}</h5>
        </div>
        <div class="mkbr-card__body">
          <div class="mkbr-metric">
            <span class="mkbr-metric__label">Residual Realisasi Tahunan {{ $selectedPeriodYear }}</span>
            <span class="mkbr-metric__value mkbr-value--warning">{{ $isProjectConsolidation ? 'Rp 635,40 M' : ($isDivisionConsolidation ? 'Rp 986,40 M' : 'Rp 1,48 T') }}</span>
          </div>
          <div class="mkbr-metric">
            <span class="mkbr-metric__label">Residual Total s.d. {{ $selectedPeriodDisplay }}</span>
            <span class="mkbr-metric__value mkbr-value--warning">{{ $isProjectConsolidation ? 'Rp 472,85 M' : ($isDivisionConsolidation ? 'Rp 742,85 M' : 'Rp 1,12 T') }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="mkbr-highlight">
        <div>
          <div class="mkbr-highlight__label">Hasil Usaha Aktual {{ $isProjectConsolidation ? 'Seluruh Proyek ' : ($isDivisionConsolidation ? 'Seluruh Divisi ' : '') }}s.d. {{ $selectedPeriodDisplay }}</div>
          <div class="mkbr-highlight__note">Omzet Penjualan − Total Kerugian LED</div>
        </div>
        <div class="mkbr-highlight__value mkbr-highlight__value--success">{{ $isProjectConsolidation ? 'Rp 152,16 M' : ($isDivisionConsolidation ? 'Rp 10,87 T' : 'Rp 12,73 T') }}</div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="mkbr-highlight">
        <div>
          <div class="mkbr-highlight__label">Proyeksi Hasil Usaha {{ $isProjectConsolidation ? 'Seluruh Proyek ' : ($isDivisionConsolidation ? 'Seluruh Divisi ' : '') }}s.d. Desember {{ $selectedPeriodYear }}</div>
          <div class="mkbr-highlight__note">Proyeksi setelah memperhitungkan eksposur risiko residual</div>
        </div>
        <div class="mkbr-highlight__value">Rp 17,39 T</div>
      </div>
    </div>
  </div>
</section>
