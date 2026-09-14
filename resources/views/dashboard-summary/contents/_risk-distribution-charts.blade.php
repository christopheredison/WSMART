@once
  @push('styles')
    <style>
      .shared-risk-distribution {
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .shared-risk-distribution__header {
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .shared-risk-distribution-card {
        height: 100%;
        padding: 1rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.05);
      }

      .shared-risk-distribution-card__title {
        color: var(--bs-heading-color);
        font-size: 0.95rem;
        font-weight: 700;
        text-align: center;
      }

      .shared-risk-distribution-chart {
        position: relative;
        display: grid;
        width: 180px;
        height: 180px;
        margin: 1.25rem auto;
        place-items: center;
        border-radius: 50%;
      }

      .shared-risk-distribution-chart::before {
        position: absolute;
        width: 104px;
        height: 104px;
        border-radius: 50%;
        background-color: var(--bs-body-bg);
        box-shadow: inset 0 0 0 1px var(--bs-border-color);
        content: "";
      }

      .shared-risk-distribution-chart__total {
        position: relative;
        z-index: 1;
        color: var(--bs-heading-color);
        font-size: 1.35rem;
        font-weight: 700;
        text-align: center;
      }

      .shared-risk-distribution-chart__total small {
        display: block;
        color: var(--bs-secondary-color);
        font-size: 0.65rem;
        font-weight: 500;
      }

      .shared-risk-distribution-legend {
        display: grid;
        gap: 0.45rem;
      }

      .shared-risk-distribution-legend__item {
        display: grid;
        grid-template-columns: 0.75rem minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.5rem;
        border-radius: 0.4rem;
        background-color: var(--bs-tertiary-bg);
        font-size: 0.72rem;
      }

      .shared-risk-distribution-legend__swatch {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 0.2rem;
      }

      .shared-risk-distribution-legend__value {
        color: var(--bs-heading-color);
        font-weight: 700;
        white-space: nowrap;
      }
    </style>
  @endpush
@endonce

@php
  $sharedRiskLevels = [
    'High' => '#f45d43',
    'Moderate High' => '#ff9f0a',
    'Moderate' => '#ffd91a',
    'Low to Moderate' => '#70b900',
    'Low' => '#168400',
  ];
  $sharedRiskGroups = [];

  foreach ([
    'inherent' => 'Level Risiko Inheren',
    'residual' => 'Level Risiko Residual',
    'current' => 'Level Risiko Realisasi Terkini',
  ] as $sharedRiskType => $sharedRiskGroupTitle) {
    $sharedCounts = array_fill_keys(array_keys($sharedRiskLevels), 0);

    foreach ($riskDistributionSource as $sharedRisk) {
      $sharedNormalizedLevel = str_replace(' *', '', $sharedRisk[$sharedRiskType]['level']);

      if (array_key_exists($sharedNormalizedLevel, $sharedCounts)) {
        $sharedCounts[$sharedNormalizedLevel]++;
      }
    }

    $sharedTotal = array_sum($sharedCounts);
    $sharedStart = 0;
    $sharedSegments = [];

    foreach ($sharedRiskLevels as $sharedLevel => $sharedColor) {
      if ($sharedCounts[$sharedLevel] === 0 || $sharedTotal === 0) {
        continue;
      }

      $sharedEnd = $sharedStart + (($sharedCounts[$sharedLevel] / $sharedTotal) * 100);
      $sharedSegments[] = "{$sharedColor} {$sharedStart}% {$sharedEnd}%";
      $sharedStart = $sharedEnd;
    }

    $sharedRiskGroups[] = [
      'title' => $sharedRiskGroupTitle,
      'counts' => $sharedCounts,
      'total' => $sharedTotal,
      'gradient' => $sharedSegments
        ? implode(', ', $sharedSegments)
        : '#e9ecef 0% 100%',
    ];
  }
@endphp

<div class="shared-risk-distribution mt-3">
  <div class="shared-risk-distribution__header">
    <h5 class="mb-1">Distribusi Level Risiko {{ $riskDistributionTitle }}</h5>
    <p class="text-muted small mb-0">Jumlah dan persentase risiko berdasarkan lima tingkat level risiko.</p>
  </div>
  <div class="p-3">
    <div class="row g-3">
      @foreach ($sharedRiskGroups as $sharedDistribution)
        <div class="col-12 col-lg-4">
          <div class="shared-risk-distribution-card">
            <h6 class="shared-risk-distribution-card__title">{{ $sharedDistribution['title'] }}</h6>
            <div
              class="shared-risk-distribution-chart"
              style="background: conic-gradient({{ $sharedDistribution['gradient'] }})"
              role="img"
              aria-label="{{ $sharedDistribution['title'] }} dari {{ $sharedDistribution['total'] }} risiko"
            >
              <div class="shared-risk-distribution-chart__total">
                {{ $sharedDistribution['total'] }}
                <small>Total Risiko</small>
              </div>
            </div>
            <div class="shared-risk-distribution-legend">
              @foreach ($sharedRiskLevels as $sharedLevel => $sharedColor)
                @php
                  $sharedCount = $sharedDistribution['counts'][$sharedLevel];
                  $sharedPercentage = $sharedDistribution['total'] > 0
                    ? round(($sharedCount / $sharedDistribution['total']) * 100, 1)
                    : 0;
                @endphp
                <div class="shared-risk-distribution-legend__item">
                  <span class="shared-risk-distribution-legend__swatch" style="background-color: {{ $sharedColor }}"></span>
                  <span>{{ $sharedLevel }}</span>
                  <span class="shared-risk-distribution-legend__value">{{ $sharedCount }} ({{ $sharedPercentage }}%)</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
