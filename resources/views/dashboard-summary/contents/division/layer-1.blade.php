@once
  @push('styles')
    <style>
      .division-performance {
        --division-performance-ink: #1e2129;
        --division-performance-soft-blue: #e5f7fc;
      }

      .division-performance__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .division-performance__identity {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 0.85rem;
        border: 1px solid var(--bs-primary-border-subtle);
        border-radius: 999px;
        color: var(--bs-primary);
        background-color: var(--bs-primary-bg-subtle);
        font-size: 0.8rem;
        font-weight: 700;
      }

      .division-performance-card {
        height: 100%;
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .division-performance-card__header {
        padding: 1rem 1.125rem;
        border-bottom: 1px dashed var(--bs-border-color);
      }

      .division-performance-card__header--actual {
        background-color: var(--division-performance-soft-blue);
      }

      .division-performance-card__header--projection {
        color: #fff;
        background-color: var(--division-performance-ink);
      }

      .division-performance-card__title {
        margin: 0;
        color: inherit;
        font-size: 1rem;
        font-weight: 700;
      }

      .division-performance-card__body {
        padding: 0.75rem 1.125rem;
      }

      .division-performance-metric {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.65rem 0;
      }

      .division-performance-metric + .division-performance-metric {
        border-top: 1px solid var(--bs-border-color);
      }

      .division-performance-metric__label {
        color: var(--bs-secondary-color);
        font-size: 0.85rem;
      }

      .division-performance-metric__value {
        color: var(--bs-heading-color);
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .division-performance-metric__value--loss {
        color: var(--bs-danger);
        font-size: 1.05rem;
      }

      .division-performance-metric__value--exposure {
        color: var(--bs-warning);
      }

      .division-performance-result {
        display: flex;
        min-height: 88px;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.125rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-tertiary-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.05);
      }

      .division-performance-result__title {
        color: var(--bs-heading-color);
        font-weight: 700;
      }

      .division-performance-result__formula {
        margin-top: 0.25rem;
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
      }

      .division-performance-result__value {
        color: var(--bs-primary);
        font-size: 1.15rem;
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .division-performance-result__value--actual {
        color: #94cf16;
      }

      @media (max-width: 575.98px) {
        .division-performance-metric,
        .division-performance-result {
          align-items: flex-start;
          flex-direction: column;
        }

        .division-performance-metric__value,
        .division-performance-result__value {
          text-align: left;
          white-space: normal;
        }
      }
    </style>
  @endpush
@endonce

@php
  $divisionName = $selectedDivisionModel?->name ?? 'Divisi Terpilih';
  $selectedPeriodYear = substr($selectedPeriod, 0, 4);

  $actualMetrics = [
    ['label' => 'Omzet Penjualan', 'value' => 'Rp 2,84 T'],
    ['label' => 'LSP Rencana', 'value' => 'Rp 312,50 M'],
    ['label' => 'LSP Realisasi', 'value' => 'Rp 286,75 M'],
  ];

  $projectionMetrics = [
    ['label' => 'Proyeksi Omzet Penjualan', 'value' => 'Rp 4,15 T'],
    ['label' => 'LSP Rencana Tahunan', 'value' => 'Rp 485,20 M'],
    ['label' => 'Proyeksi LSP', 'value' => 'Rp 452,80 M'],
  ];
@endphp

<section class="dashboard-summary-layer division-performance" data-dashboard-category="division" data-layer="1">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="division-performance__title mb-1">Manajemen Kinerja Berbasis Risiko Divisi</h4>
      <p class="text-muted mb-0">Ringkasan kinerja dan eksposur risiko divisi terpilih periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="division-performance__identity">
        <i class="bx bx-buildings" aria-hidden="true"></i>
        {{ $divisionName }}
      </span>
      <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-6">
      <div class="division-performance-card">
        <div class="division-performance-card__header division-performance-card__header--actual">
          <h5 class="division-performance-card__title">Hasil Usaha s.d. {{ $selectedPeriodDisplay }}</h5>
        </div>
        <div class="division-performance-card__body">
          @foreach ($actualMetrics as $metric)
            <div class="division-performance-metric">
              <span class="division-performance-metric__label">{{ $metric['label'] }}</span>
              <span class="division-performance-metric__value">{{ $metric['value'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="division-performance-card">
        <div class="division-performance-card__header division-performance-card__header--projection">
          <h5 class="division-performance-card__title">Proyeksi Hasil Usaha s.d. Desember {{ $selectedPeriodYear }}</h5>
        </div>
        <div class="division-performance-card__body">
          @foreach ($projectionMetrics as $metric)
            <div class="division-performance-metric">
              <span class="division-performance-metric__label">{{ $metric['label'] }}</span>
              <span class="division-performance-metric__value">{{ $metric['value'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="row g-3">
        <div class="col-12">
          <div class="division-performance-card">
            <div class="division-performance-card__header">
              <h5 class="division-performance-card__title">Loss Event Database (LED) Proyek di {{ $divisionName }}</h5>
            </div>
            <div class="division-performance-card__body">
              <div class="division-performance-metric">
                <span class="division-performance-metric__label">Total Kerugian Finansial</span>
                <span class="division-performance-metric__value division-performance-metric__value--loss">Rp 8,75 M</span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="division-performance-card">
            <div class="division-performance-card__header">
              <h5 class="division-performance-card__title">Loss Event Database (LED) Divisi</h5>
            </div>
            <div class="division-performance-card__body">
              <div class="division-performance-metric">
                <span class="division-performance-metric__label">Total Kerugian Finansial {{ $divisionName }}</span>
                <span class="division-performance-metric__value division-performance-metric__value--loss">Rp 282,66 Jt</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="division-performance-card">
        <div class="division-performance-card__header">
          <h5 class="division-performance-card__title">Eksposur Risiko Residual {{ $divisionName }}</h5>
        </div>
        <div class="division-performance-card__body">
          <div class="division-performance-metric">
            <span class="division-performance-metric__label">Residual Total s.d. {{ $selectedPeriodDisplay }}</span>
            <span class="division-performance-metric__value division-performance-metric__value--exposure">Rp 3,06 T</span>
          </div>
          <div class="division-performance-metric">
            <span class="division-performance-metric__label">Proyeksi Annual {{ $selectedPeriodYear }}</span>
            <span class="division-performance-metric__value division-performance-metric__value--exposure">Rp 3,06 T</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="division-performance-result">
        <div>
          <div class="division-performance-result__title">Hasil Usaha Aktual {{ $divisionName }} s.d. {{ $selectedPeriodDisplay }}</div>
          <div class="division-performance-result__formula">LSP Realisasi − LED Proyek − LED Divisi</div>
        </div>
        <div class="division-performance-result__value division-performance-result__value--actual">Rp 277,72 M</div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="division-performance-result">
        <div>
          <div class="division-performance-result__title">Proyeksi Hasil Usaha {{ $divisionName }} s.d. Desember {{ $selectedPeriodYear }}</div>
          <div class="division-performance-result__formula">Proyeksi LSP − Eksposur Annual</div>
        </div>
        <div class="division-performance-result__value">Rp -2,60 T</div>
      </div>
    </div>
  </div>
</section>
