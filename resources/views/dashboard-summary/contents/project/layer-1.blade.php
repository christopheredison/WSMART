@once
  @push('styles')
    <style>
      .project-performance__title { color: var(--bs-primary); font-weight: 700; }
      .project-performance__identity {
        display: inline-flex; align-items: center; gap: .45rem; padding: .5rem .8rem;
        border-radius: 999px; color: var(--bs-info); background: var(--bs-info-bg-subtle);
        font-size: .78rem; font-weight: 700;
      }
      .project-kpi {
        height: 100%; padding: 1rem 1.125rem; border: 2px solid var(--bs-border-color);
        border-radius: .7rem; background: var(--bs-body-bg);
      }
      .project-kpi--contract { border-color: #91d414; }
      .project-kpi--sales { border-color: #5656f5; }
      .project-kpi--progress { border-color: #e9b417; }
      .project-kpi__label { color: var(--bs-secondary-color); font-size: .72rem; text-transform: uppercase; }
      .project-kpi__value { margin-top: .25rem; color: var(--bs-heading-color); font-size: 1.15rem; font-weight: 700; }
      .project-performance-card {
        height: 100%; overflow: hidden; border: 1px solid var(--bs-border-color);
        border-radius: .75rem; background: var(--bs-body-bg);
        box-shadow: 0 .25rem 1rem rgba(23, 32, 51, .06);
      }
      .project-performance-card__header {
        padding: 1rem 1.125rem; border-bottom: 1px dashed var(--bs-border-color);
      }
      .project-performance-card__header--actual { background: #e5f7fc; }
      .project-performance-card__header--projection { color: #fff; background: #1e2129; }
      .project-performance-card__title { margin: 0; color: inherit; font-size: 1rem; font-weight: 700; }
      .project-performance-card__body { padding: .7rem 1.125rem; }
      .project-performance-metric {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .65rem 0;
      }
      .project-performance-metric + .project-performance-metric { border-top: 1px solid var(--bs-border-color); }
      .project-performance-metric__label { color: var(--bs-secondary-color); font-size: .84rem; }
      .project-performance-metric__value { color: var(--bs-heading-color); font-weight: 700; text-align: right; white-space: nowrap; }
      .project-performance-metric__value--danger { color: var(--bs-danger); font-size: 1rem; }
      .project-performance-metric__value--warning { color: var(--bs-warning); }
      .project-performance-result {
        display: flex; min-height: 86px; align-items: center; justify-content: space-between; gap: 1rem;
        padding: 1rem 1.125rem; border: 1px solid var(--bs-border-color); border-radius: .75rem;
        background: var(--bs-tertiary-bg); box-shadow: 0 .25rem 1rem rgba(23, 32, 51, .05);
      }
      .project-performance-result__title { color: var(--bs-heading-color); font-weight: 700; }
      .project-performance-result__formula { margin-top: .2rem; color: var(--bs-secondary-color); font-size: .72rem; }
      .project-performance-result__value { color: var(--bs-primary); font-size: 1.15rem; font-weight: 700; text-align: right; white-space: nowrap; }
      .project-performance-result__value--actual { color: #94cf16; }
      @media (max-width: 575.98px) {
        .project-performance-metric, .project-performance-result { align-items: flex-start; flex-direction: column; }
        .project-performance-metric__value, .project-performance-result__value { text-align: left; white-space: normal; }
      }
    </style>
  @endpush
@endonce

@php
  $projectName = $selectedProjectModel?->project_name ?? 'Proyek Terpilih';
  $divisionName = $selectedDivisionModel?->name ?? 'Divisi Terpilih';
  $selectedYear = substr($selectedPeriod, 0, 4);
  $actualMetrics = [
    ['label' => 'Omzet Penjualan', 'value' => 'Rp 428,50 M'],
    ['label' => 'LSP Rencana', 'value' => 'Rp 52,80 M'],
    ['label' => 'LSP Realisasi', 'value' => 'Rp 47,35 M'],
  ];
  $projectionMetrics = [
    ['label' => 'Proyeksi Omzet Penjualan', 'value' => 'Rp 685,00 M'],
    ['label' => 'LSP Rencana Proyek', 'value' => 'Rp 82,40 M'],
    ['label' => 'Proyeksi LSP', 'value' => 'Rp 76,25 M'],
  ];
@endphp

<section class="dashboard-summary-layer project-performance" data-dashboard-category="project" data-layer="1">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="project-performance__title mb-1">Manajemen Kinerja Berbasis Risiko Proyek</h4>
      <p class="text-muted mb-0">{{ $projectName }} · {{ $divisionName }} · periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <span class="project-performance__identity"><i class="bx bx-hard-hat" aria-hidden="true"></i>{{ $projectName }}</span>
      <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4"><div class="project-kpi project-kpi--contract"><div class="project-kpi__label">Omzet Kontrak Total</div><div class="project-kpi__value">Rp 1,24 T</div></div></div>
    <div class="col-12 col-md-4"><div class="project-kpi project-kpi--sales"><div class="project-kpi__label">Omzet Penjualan s.d. {{ $selectedPeriodDisplay }}</div><div class="project-kpi__value">Rp 428,50 M</div></div></div>
    <div class="col-12 col-md-4"><div class="project-kpi project-kpi--progress"><div class="project-kpi__label">Progress s.d. {{ $selectedPeriodDisplay }}</div><div class="project-kpi__value">47,85%</div></div></div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xl-6">
      <div class="project-performance-card">
        <div class="project-performance-card__header project-performance-card__header--actual"><h5 class="project-performance-card__title">LSP s.d. {{ $selectedPeriodDisplay }}</h5></div>
        <div class="project-performance-card__body">
          @foreach ($actualMetrics as $metric)
            <div class="project-performance-metric"><span class="project-performance-metric__label">{{ $metric['label'] }}</span><span class="project-performance-metric__value">{{ $metric['value'] }}</span></div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-12 col-xl-6">
      <div class="project-performance-card">
        <div class="project-performance-card__header project-performance-card__header--projection"><h5 class="project-performance-card__title">Proyeksi LSP s.d. Proyek Selesai</h5></div>
        <div class="project-performance-card__body">
          @foreach ($projectionMetrics as $metric)
            <div class="project-performance-metric"><span class="project-performance-metric__label">{{ $metric['label'] }}</span><span class="project-performance-metric__value">{{ $metric['value'] }}</span></div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-12 col-xl-6">
      <div class="project-performance-card">
        <div class="project-performance-card__header"><h5 class="project-performance-card__title">Loss Event Database (LED)</h5></div>
        <div class="project-performance-card__body"><div class="project-performance-metric"><span class="project-performance-metric__label">Total Kerugian Finansial</span><span class="project-performance-metric__value project-performance-metric__value--danger">Rp 8,75 M</span></div></div>
      </div>
    </div>
    <div class="col-12 col-xl-6">
      <div class="project-performance-card">
        <div class="project-performance-card__header"><h5 class="project-performance-card__title">Eksposur Risiko Residual</h5></div>
        <div class="project-performance-card__body"><div class="project-performance-metric"><span class="project-performance-metric__label">Residual Realisasi Total</span><span class="project-performance-metric__value project-performance-metric__value--warning">Rp 136,00 M</span></div></div>
      </div>
    </div>
    <div class="col-12 col-xl-6">
      <div class="project-performance-result">
        <div><div class="project-performance-result__title">Potensi Hasil Usaha s.d. {{ $selectedPeriodDisplay }}</div><div class="project-performance-result__formula">LSP Realisasi − LED</div></div>
        <div class="project-performance-result__value project-performance-result__value--actual">Rp 38,60 M</div>
      </div>
    </div>
    <div class="col-12 col-xl-6">
      <div class="project-performance-result">
        <div><div class="project-performance-result__title">Proyeksi Hasil Usaha s.d. Desember {{ $selectedYear }}</div><div class="project-performance-result__formula">Proyeksi LSP − Eksposur Residual</div></div>
        <div class="project-performance-result__value">Rp -59,75 M</div>
      </div>
    </div>
  </div>
</section>
