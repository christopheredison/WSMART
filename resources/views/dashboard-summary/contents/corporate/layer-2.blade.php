@once
  @push('styles')
    <style>
      .corporate-risk-profile__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .corporate-risk-panel {
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .corporate-risk-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .risk-level-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
        padding: 0.75rem 1.125rem;
        border-radius: 0.5rem;
        background-color: var(--bs-tertiary-bg);
      }

      .risk-level-legend__item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--bs-secondary-color);
        font-size: 0.75rem;
      }

      .risk-level-legend__swatch {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 0.2rem;
      }

      .corporate-risk-map {
        position: relative;
        padding-left: 1.75rem;
      }

      .corporate-risk-map__grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(72px, 1fr));
        min-width: 480px;
        overflow: hidden;
        border: 2px solid var(--bs-body-bg);
        border-radius: 0.5rem;
      }

      .corporate-risk-map__scroll {
        overflow-x: auto;
      }

      .corporate-risk-map__cell {
        position: relative;
        min-height: 58px;
        padding: 0.4rem;
        border: 1px solid rgba(255, 255, 255, 0.85);
        color: #fff;
      }

      .corporate-risk-map__cell--low {
        background-color: #16a800;
      }

      .corporate-risk-map__cell--low-moderate {
        background-color: #70d500;
      }

      .corporate-risk-map__cell--moderate {
        background-color: #ffd91a;
      }

      .corporate-risk-map__cell--moderate-high {
        background-color: #ff9f0a;
      }

      .corporate-risk-map__cell--high {
        background-color: #f45d43;
      }

      .corporate-risk-map__score {
        position: absolute;
        top: 0.25rem;
        right: 0.35rem;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.7rem;
        font-weight: 700;
      }

      .corporate-risk-map__markers {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        padding-top: 1rem;
      }

      .risk-marker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.65rem;
        padding: 0.15rem 0.3rem;
        border: 2px solid #fff;
        border-radius: 0.3rem;
        color: #fff;
        background-color: #172033;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
        font-size: 0.65rem;
        font-weight: 700;
      }

      .risk-marker--current {
        background-color: var(--bs-primary);
      }

      .risk-marker--unverified::after {
        margin-left: 0.1rem;
        color: #fff;
        content: "*";
      }

      .corporate-risk-map__axis-y {
        position: absolute;
        top: 50%;
        left: -0.35rem;
        color: var(--bs-secondary-color);
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.25rem;
        transform: translateY(-50%) rotate(-90deg);
      }

      .corporate-risk-map__axis-x {
        margin-top: 0.5rem;
        color: var(--bs-secondary-color);
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.35rem;
        text-align: center;
      }

      .corporate-risk-status {
        padding: 1rem 1.125rem;
        border: 1px solid #f2d675;
        border-radius: 0.65rem;
        color: #6f5600;
        background-color: #fff6d8;
      }

      .corporate-risk-table {
        min-width: 1180px;
        margin-bottom: 0;
        font-size: 0.75rem;
      }

      .corporate-risk-table th {
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        font-size: 0.68rem;
        letter-spacing: 0.02rem;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
      }

      .corporate-risk-table td {
        vertical-align: middle;
      }

      .corporate-risk-table__event {
        min-width: 260px;
      }

      .risk-level-badge {
        display: inline-block;
        min-width: 94px;
        padding: 0.35rem 0.5rem;
        border-radius: 0.35rem;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        text-align: center;
      }

      .risk-level-badge--high {
        background-color: #f45d43;
      }

      .risk-level-badge--moderate-high {
        background-color: #ff9f0a;
      }

      .risk-level-badge--moderate {
        color: #665700;
        background-color: #ffd91a;
      }

      .risk-level-badge--low-moderate {
        background-color: #70b900;
      }

      .risk-level-badge--low {
        background-color: #168400;
      }

      .risk-distribution-card {
        height: 100%;
        padding: 1rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.05);
      }

      .risk-distribution-card__title {
        color: var(--bs-heading-color);
        font-size: 0.95rem;
        font-weight: 700;
        text-align: center;
      }

      .risk-distribution-chart {
        position: relative;
        display: grid;
        width: 180px;
        height: 180px;
        margin: 1.25rem auto;
        place-items: center;
        border-radius: 50%;
      }

      .risk-distribution-chart::before {
        position: absolute;
        width: 104px;
        height: 104px;
        border-radius: 50%;
        background-color: var(--bs-body-bg);
        box-shadow: inset 0 0 0 1px var(--bs-border-color);
        content: "";
      }

      .risk-distribution-chart__total {
        position: relative;
        z-index: 1;
        color: var(--bs-heading-color);
        font-size: 1.35rem;
        font-weight: 700;
        text-align: center;
      }

      .risk-distribution-chart__total small {
        display: block;
        color: var(--bs-secondary-color);
        font-size: 0.65rem;
        font-weight: 500;
      }

      .risk-distribution-legend {
        display: grid;
        gap: 0.45rem;
      }

      .risk-distribution-legend__item {
        display: grid;
        grid-template-columns: 0.75rem minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.5rem;
        border-radius: 0.4rem;
        background-color: var(--bs-tertiary-bg);
        font-size: 0.72rem;
      }

      .risk-distribution-legend__swatch {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 0.2rem;
      }

      .risk-distribution-legend__value {
        color: var(--bs-heading-color);
        font-weight: 700;
        white-space: nowrap;
      }

      .corporate-risk-panel.is-fullscreen {
        position: fixed;
        z-index: 1090;
        inset: 1rem;
        overflow: auto;
        background-color: var(--bs-body-bg);
      }

      body.has-risk-map-fullscreen {
        overflow: hidden;
      }
    </style>
  @endpush
@endonce

@php
  $inherentResidualMarkers = [
    '5-1' => ['R6', 'R8'],
    '5-2' => ['R4', 'R5'],
    '5-5' => ['R1'],
    '4-1' => ['R4'],
    '3-3' => ['R2'],
    '3-5' => ['R2'],
    '1-4' => ['R3'],
  ];

  $currentMarkers = [
    '5-1' => ['R6*'],
    '5-2' => ['R4', 'R5*'],
    '5-5' => ['R1*'],
    '3-5' => ['R2*'],
    '1-4' => ['R3'],
  ];

  $corporateRisks = [
    [
      'code' => 'R1',
      'event' => 'Potensi kegagalan restrukturisasi keuangan',
      'inherent' => ['impact' => 'Rp 2,13 T', 'probability' => '90%', 'score' => 25, 'level' => 'High'],
      'residual' => ['impact' => 'Rp 2,13 T', 'probability' => '90%', 'score' => 25, 'level' => 'High'],
      'current' => ['impact' => 'Rp 2,13 T', 'probability' => '90%', 'score' => 25, 'level' => 'High *'],
    ],
    [
      'code' => 'R2',
      'event' => 'Potensi kenaikan harga besi beton',
      'inherent' => ['impact' => 'Rp 534,63 M', 'probability' => '50%', 'score' => 23, 'level' => 'High'],
      'residual' => ['impact' => 'Rp 133,66 M', 'probability' => '50%', 'score' => 13, 'level' => 'Moderate'],
      'current' => ['impact' => 'Rp 534,63 M', 'probability' => '50%', 'score' => 23, 'level' => 'High *'],
    ],
    [
      'code' => 'R3',
      'event' => 'Potensi hilangnya kepercayaan stakeholder',
      'inherent' => ['impact' => 'Rp 0', 'probability' => '17%', 'score' => 15, 'level' => 'Moderate High'],
      'residual' => ['impact' => 'Rp 0', 'probability' => '17%', 'score' => 15, 'level' => 'Moderate High'],
      'current' => ['impact' => 'Rp 0', 'probability' => '17%', 'score' => 15, 'level' => 'Moderate High'],
    ],
    [
      'code' => 'R4',
      'event' => 'Diferensiasi melemahnya kemampuan dasar di sektor EPCC',
      'inherent' => ['impact' => 'Rp 58,52 M', 'probability' => '80%', 'score' => 12, 'level' => 'Moderate'],
      'residual' => ['impact' => 'Rp 48,67 M', 'probability' => '75%', 'score' => 4, 'level' => 'Low'],
      'current' => ['impact' => 'Rp 58,52 M', 'probability' => '80%', 'score' => 12, 'level' => 'Moderate *'],
    ],
    [
      'code' => 'R5',
      'event' => 'Potensi kegagalan pengembalian hutang usaha',
      'inherent' => ['impact' => 'Rp 61,87 M', 'probability' => '90%', 'score' => 12, 'level' => 'Moderate'],
      'residual' => ['impact' => 'Rp 55,68 M', 'probability' => '90%', 'score' => 12, 'level' => 'Moderate'],
      'current' => ['impact' => 'Rp 61,87 M', 'probability' => '90%', 'score' => 12, 'level' => 'Moderate *'],
    ],
    [
      'code' => 'R6',
      'event' => 'Potensi penurunan daya saing',
      'inherent' => ['impact' => 'Rp 49,31 M', 'probability' => '100%', 'score' => 7, 'level' => 'Low to Moderate'],
      'residual' => ['impact' => 'Rp 49,31 M', 'probability' => '5%', 'score' => 7, 'level' => 'Low to Moderate'],
      'current' => ['impact' => 'Rp 49,31 M', 'probability' => '100%', 'score' => 7, 'level' => 'Low to Moderate *'],
    ],
  ];

  $riskLevelClass = static function (string $level): string {
    $normalizedLevel = str_replace(' *', '', $level);

    return match ($normalizedLevel) {
      'High' => 'high',
      'Moderate High' => 'moderate-high',
      'Moderate' => 'moderate',
      'Low to Moderate' => 'low-moderate',
      default => 'low',
    };
  };

  $riskDistributionLevels = [
    'High' => '#f45d43',
    'Moderate High' => '#ff9f0a',
    'Moderate' => '#ffd91a',
    'Low to Moderate' => '#70b900',
    'Low' => '#168400',
  ];
  $riskDistributionGroups = [];

  foreach ([
    'inherent' => 'Level Risiko Inheren',
    'residual' => 'Level Risiko Residual',
    'current' => 'Level Risiko Realisasi Terkini',
  ] as $riskType => $title) {
    $counts = array_fill_keys(array_keys($riskDistributionLevels), 0);

    foreach ($corporateRisks as $risk) {
      $normalizedLevel = str_replace(' *', '', $risk[$riskType]['level']);

      if (array_key_exists($normalizedLevel, $counts)) {
        $counts[$normalizedLevel]++;
      }
    }

    $total = array_sum($counts);
    $start = 0;
    $segments = [];

    foreach ($riskDistributionLevels as $level => $color) {
      if ($counts[$level] === 0) {
        continue;
      }

      $end = $start + (($counts[$level] / $total) * 100);
      $segments[] = "{$color} {$start}% {$end}%";
      $start = $end;
    }

    $riskDistributionGroups[] = [
      'title' => $title,
      'counts' => $counts,
      'total' => $total,
      'gradient' => implode(', ', $segments),
    ];
  }
@endphp

<section class="dashboard-summary-layer corporate-risk-profile" data-dashboard-category="corporate" data-layer="2">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="corporate-risk-profile__title mb-1">Profil Risiko Korporat</h4>
      <p class="text-muted mb-0">Pemetaan dan daftar risiko korporat periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
  </div>

  <div class="corporate-risk-panel" id="corporate-risk-map-panel">
    <div class="corporate-risk-panel__header">
      <div>
        <h5 class="mb-1">Peta Risiko</h5>
        <p class="text-muted small mb-0">Perbandingan posisi risiko inheren, residual, dan realisasi terkini.</p>
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary" id="corporate-risk-fullscreen">
        <span class="bx bx-fullscreen me-1" aria-hidden="true"></span>
        <span>Perbesar Peta</span>
      </button>
    </div>

    <div class="p-3">
      <div class="risk-level-legend mb-3">
        @foreach ([
          ['label' => 'High', 'color' => '#f45d43'],
          ['label' => 'Moderate to High', 'color' => '#ff9f0a'],
          ['label' => 'Moderate', 'color' => '#ffd91a'],
          ['label' => 'Low to Moderate', 'color' => '#70d500'],
          ['label' => 'Low', 'color' => '#16a800'],
        ] as $legend)
          <span class="risk-level-legend__item">
            <span class="risk-level-legend__swatch" style="background-color: {{ $legend['color'] }}"></span>
            {{ $legend['label'] }}
          </span>
        @endforeach
      </div>

      <div class="row g-4">
        <div class="col-12 col-xxl-6">
          <h6 class="mb-3">Peta Risiko Inheren dan Residual</h6>
          <div class="corporate-risk-map">
            <span class="corporate-risk-map__axis-y">LIKELIHOOD</span>
            <div class="corporate-risk-map__scroll">
              <div class="corporate-risk-map__grid">
                @foreach (range(5, 1) as $likelihood)
                  @foreach (range(1, 5) as $impact)
                    @php
                      $score = $likelihood * $impact;
                      $cellClass = match (true) {
                        $score >= 20 => 'high',
                        $score >= 15 => 'moderate-high',
                        $score >= 10 => 'moderate',
                        $score >= 5 => 'low-moderate',
                        default => 'low',
                      };
                      $cellKey = "{$likelihood}-{$impact}";
                    @endphp
                    <div class="corporate-risk-map__cell corporate-risk-map__cell--{{ $cellClass }}">
                      <span class="corporate-risk-map__score">{{ $score }}</span>
                      @if (isset($inherentResidualMarkers[$cellKey]))
                        <div class="corporate-risk-map__markers">
                          @foreach ($inherentResidualMarkers[$cellKey] as $marker)
                            <span class="risk-marker">{{ $marker }}</span>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  @endforeach
                @endforeach
              </div>
            </div>
            <div class="corporate-risk-map__axis-x">IMPACT</div>
          </div>
        </div>

        <div class="col-12 col-xxl-6">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h6 class="mb-0">Peta Risiko Terkini</h6>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" aria-label="Kuartal risiko">
                <option>Q3 - {{ $selectedPeriodDisplay }}</option>
              </select>
              <select class="form-select form-select-sm" aria-label="Tahun risiko">
                <option>{{ substr($selectedPeriod, 0, 4) }}</option>
              </select>
            </div>
          </div>
          <div class="corporate-risk-map">
            <span class="corporate-risk-map__axis-y">LIKELIHOOD</span>
            <div class="corporate-risk-map__scroll">
              <div class="corporate-risk-map__grid">
                @foreach (range(5, 1) as $likelihood)
                  @foreach (range(1, 5) as $impact)
                    @php
                      $score = $likelihood * $impact;
                      $cellClass = match (true) {
                        $score >= 20 => 'high',
                        $score >= 15 => 'moderate-high',
                        $score >= 10 => 'moderate',
                        $score >= 5 => 'low-moderate',
                        default => 'low',
                      };
                      $cellKey = "{$likelihood}-{$impact}";
                    @endphp
                    <div class="corporate-risk-map__cell corporate-risk-map__cell--{{ $cellClass }}">
                      <span class="corporate-risk-map__score">{{ $score }}</span>
                      @if (isset($currentMarkers[$cellKey]))
                        <div class="corporate-risk-map__markers">
                          @foreach ($currentMarkers[$cellKey] as $marker)
                            <span class="risk-marker risk-marker--current {{ str_ends_with($marker, '*') ? 'risk-marker--unverified' : '' }}">
                              {{ rtrim($marker, '*') }}
                            </span>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  @endforeach
                @endforeach
              </div>
            </div>
            <div class="corporate-risk-map__axis-x">IMPACT</div>
          </div>
        </div>
      </div>

      <div class="corporate-risk-status mt-4">
        <div class="d-flex gap-2">
          <span class="bx bx-info-circle fs-5" aria-hidden="true"></span>
          <div>
            <h6 class="mb-1">Informasi Status Realisasi {{ $selectedPeriodDisplay }}</h6>
            <p class="small mb-1">
              Tanda bintang merah pada Peta Risiko Terkini dan tabel menandakan monitoring belum diverifikasi pada bulan cutoff.
            </p>
            <strong class="small">Risiko belum diperbarui: R1, R2, R4, R5, dan R6.</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="corporate-risk-panel mt-3">
    <div class="corporate-risk-panel__header">
      <div>
        <h5 class="mb-1">Daftar Risiko Korporat</h5>
        <p class="text-muted small mb-0">Ringkasan nilai risiko inheren, residual, dan realisasi terkini.</p>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover corporate-risk-table">
        <thead>
          <tr>
            <th rowspan="2">Kode</th>
            <th rowspan="2">Peristiwa Risiko</th>
            <th colspan="4">Inheren</th>
            <th colspan="4">Residual</th>
            <th colspan="4">Realisasi Terkini</th>
          </tr>
          <tr>
            @foreach (range(1, 3) as $group)
              <th>Nilai Dampak</th>
              <th>Probabilitas</th>
              <th>Nilai Risiko</th>
              <th>Level Risiko</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($corporateRisks as $risk)
            <tr>
              <td><a href="#" class="fw-bold">{{ $risk['code'] }}</a></td>
              <td class="corporate-risk-table__event">{{ $risk['event'] }}</td>
              @foreach (['inherent', 'residual', 'current'] as $riskType)
                <td class="text-end text-nowrap">{{ $risk[$riskType]['impact'] }}</td>
                <td class="text-center">{{ $risk[$riskType]['probability'] }}</td>
                <td class="text-center fw-semibold">{{ $risk[$riskType]['score'] }}</td>
                <td class="text-center">
                  <span class="risk-level-badge risk-level-badge--{{ $riskLevelClass($risk[$riskType]['level']) }}">
                    {{ $risk[$riskType]['level'] }}
                  </span>
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="corporate-risk-panel mt-3">
    <div class="corporate-risk-panel__header">
      <div>
        <h5 class="mb-1">Distribusi Level Risiko Korporat</h5>
        <p class="text-muted small mb-0">Jumlah dan persentase risiko berdasarkan lima tingkat level risiko.</p>
      </div>
    </div>
    <div class="p-3">
      <div class="row g-3">
        @foreach ($riskDistributionGroups as $distribution)
          <div class="col-12 col-lg-4">
            <div class="risk-distribution-card">
              <h6 class="risk-distribution-card__title">{{ $distribution['title'] }}</h6>
              <div
                class="risk-distribution-chart"
                style="background: conic-gradient({{ $distribution['gradient'] }})"
                role="img"
                aria-label="{{ $distribution['title'] }} dari {{ $distribution['total'] }} risiko"
              >
                <div class="risk-distribution-chart__total">
                  {{ $distribution['total'] }}
                  <small>Total Risiko</small>
                </div>
              </div>
              <div class="risk-distribution-legend">
                @foreach ($riskDistributionLevels as $level => $color)
                  @php
                    $count = $distribution['counts'][$level];
                    $percentage = $distribution['total'] > 0
                      ? round(($count / $distribution['total']) * 100, 1)
                      : 0;
                  @endphp
                  <div class="risk-distribution-legend__item">
                    <span class="risk-distribution-legend__swatch" style="background-color: {{ $color }}"></span>
                    <span>{{ $level }}</span>
                    <span class="risk-distribution-legend__value">{{ $count }} ({{ $percentage }}%)</span>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

@once
  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const mapPanel = document.getElementById('corporate-risk-map-panel');
        const fullscreenButton = document.getElementById('corporate-risk-fullscreen');

        if (!mapPanel || !fullscreenButton) {
          return;
        }

        fullscreenButton.addEventListener('click', function () {
          const isFullscreen = mapPanel.classList.toggle('is-fullscreen');
          document.body.classList.toggle('has-risk-map-fullscreen', isFullscreen);
          fullscreenButton.querySelector('.bx').className = isFullscreen
            ? 'bx bx-exit-fullscreen me-1'
            : 'bx bx-fullscreen me-1';
          fullscreenButton.querySelector('span:last-child').textContent = isFullscreen
            ? 'Tutup Layar Penuh'
            : 'Perbesar Peta';
        });
      });
    </script>
  @endpush
@endonce
