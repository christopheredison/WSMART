@once
  @push('styles')
    <style>
      .anper-mkbr__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .anper-mkbr-card {
        height: 100%;
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .anper-mkbr-card__header {
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .anper-mkbr-card__header--actual {
        background-color: #e8f6fc;
      }

      .anper-mkbr-card__header--projection {
        color: #fff;
        background-color: #172033;
      }

      .anper-mkbr-card__title {
        margin: 0;
        color: inherit;
        font-size: 1rem;
        font-weight: 700;
      }

      .anper-mkbr-card__body {
        padding: 0.75rem 1.125rem;
      }

      .anper-mkbr-metric {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.65rem 0;
      }

      .anper-mkbr-metric + .anper-mkbr-metric {
        border-top: 1px dashed var(--bs-border-color);
      }

      .anper-mkbr-metric__label {
        color: var(--bs-secondary-color);
        font-size: 0.875rem;
      }

      .anper-mkbr-metric__value {
        color: var(--bs-heading-color);
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .anper-mkbr-metric__value--danger {
        color: var(--bs-danger);
      }

      .anper-mkbr-metric__value--warning {
        color: var(--bs-warning);
      }

      .anper-mkbr-highlight {
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

      .anper-mkbr-highlight__label {
        color: var(--bs-heading-color);
        font-weight: 700;
      }

      .anper-mkbr-highlight__note {
        margin-top: 0.25rem;
        color: var(--bs-secondary-color);
        font-size: 0.75rem;
      }

      .anper-mkbr-highlight__value {
        color: var(--bs-primary);
        font-size: 1.2rem;
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .anper-mkbr-highlight__value--success {
        color: var(--bs-success);
      }

      @media (max-width: 575.98px) {
        .anper-mkbr-metric,
        .anper-mkbr-highlight {
          align-items: flex-start;
          flex-direction: column;
        }

        .anper-mkbr-metric__value,
        .anper-mkbr-highlight__value {
          text-align: left;
          white-space: normal;
        }
      }
    </style>
  @endpush
@endonce

@php
  $selectedPeriodYear = substr($selectedPeriod, 0, 4);
  $apName = $selectedApUnit?->name ?? 'Anak Perusahaan';
@endphp

<section class="dashboard-summary-layer anper-mkbr" data-dashboard-category="ap" data-layer="1">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="anper-mkbr__title mb-1">Manajemen Kinerja Berbasis Risiko</h4>
      <p class="text-muted mb-0">Ringkasan kinerja {{ $apName }} periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-6">
      <div class="anper-mkbr-card">
        <div class="anper-mkbr-card__header anper-mkbr-card__header--actual">
          <h5 class="anper-mkbr-card__title">Hasil Usaha s.d. {{ $selectedPeriodDisplay }}</h5>
        </div>
        <div class="anper-mkbr-card__body">
          <div class="anper-mkbr-metric">
            <span class="anper-mkbr-metric__label">Biaya Usaha</span>
            <span class="anper-mkbr-metric__value">Rp 2,84 T</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="anper-mkbr-card">
        <div class="anper-mkbr-card__header anper-mkbr-card__header--projection">
          <h5 class="anper-mkbr-card__title">Proyeksi Hasil Usaha s.d. Desember {{ $selectedPeriodYear }}</h5>
        </div>
        <div class="anper-mkbr-card__body">
          <div class="anper-mkbr-metric">
            <span class="anper-mkbr-metric__label">Proyeksi Biaya Usaha</span>
            <span class="anper-mkbr-metric__value">Rp 4,12 T</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="anper-mkbr-card">
        <div class="anper-mkbr-card__header">
          <h5 class="anper-mkbr-card__title">Loss Event Database (LED) Anak Perusahaan</h5>
        </div>
        <div class="anper-mkbr-card__body">
          <div class="anper-mkbr-metric">
            <span class="anper-mkbr-metric__label">Total Kerugian Finansial</span>
            <span class="anper-mkbr-metric__value anper-mkbr-metric__value--danger">Rp 6,75 M</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="anper-mkbr-card">
        <div class="anper-mkbr-card__header">
          <h5 class="anper-mkbr-card__title">Eksposur Risiko Residual</h5>
        </div>
        <div class="anper-mkbr-card__body">
          <div class="anper-mkbr-metric">
            <span class="anper-mkbr-metric__label">Residual Total s.d. {{ $selectedPeriodDisplay }}</span>
            <span class="anper-mkbr-metric__value anper-mkbr-metric__value--warning">Rp 185,40 M</span>
          </div>
          <div class="anper-mkbr-metric">
            <span class="anper-mkbr-metric__label">Proyeksi Annual {{ $selectedPeriodYear }}</span>
            <span class="anper-mkbr-metric__value anper-mkbr-metric__value--warning">Rp 245,90 M</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="anper-mkbr-highlight">
        <div>
          <div class="anper-mkbr-highlight__label">Hasil Usaha Aktual s.d. {{ $selectedPeriodDisplay }}</div>
          <div class="anper-mkbr-highlight__note">Biaya Usaha − LED Anak Perusahaan</div>
        </div>
        <div class="anper-mkbr-highlight__value anper-mkbr-highlight__value--success">Rp 2,83 T</div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="anper-mkbr-highlight">
        <div>
          <div class="anper-mkbr-highlight__label">Proyeksi Hasil Usaha s.d. Desember {{ $selectedPeriodYear }}</div>
          <div class="anper-mkbr-highlight__note">Proyeksi Biaya Usaha − Eksposur Risiko Annual</div>
        </div>
        <div class="anper-mkbr-highlight__value">Rp 3,87 T</div>
      </div>
    </div>
  </div>
</section>
