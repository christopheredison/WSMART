@once
  @push('styles')
    <style>
      .anper-risk-profile__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .anper-risk-panel {
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .anper-risk-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .anper-risk-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        background-color: var(--bs-tertiary-bg);
      }

      .anper-risk-legend__item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
      }

      .anper-risk-legend__swatch {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 0.2rem;
      }

      .anper-risk-map {
        position: relative;
        padding-left: 1.5rem;
      }

      .anper-risk-map__scroll {
        overflow-x: auto;
      }

      .anper-risk-map__grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(72px, 1fr));
        min-width: 480px;
        overflow: hidden;
        border: 2px solid var(--bs-body-bg);
        border-radius: 0.5rem;
      }

      .anper-risk-map__cell {
        position: relative;
        min-height: 58px;
        padding: 0.4rem;
        border: 1px solid rgba(255, 255, 255, 0.9);
        color: #fff;
      }

      .anper-risk-map__cell--low { background-color: #16a800; }
      .anper-risk-map__cell--low-moderate { background-color: #70d500; }
      .anper-risk-map__cell--moderate { background-color: #ffd91a; }
      .anper-risk-map__cell--moderate-high { background-color: #ff9f0a; }
      .anper-risk-map__cell--high { background-color: #f45d43; }

      .anper-risk-map__score {
        position: absolute;
        top: 0.25rem;
        right: 0.35rem;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.68rem;
        font-weight: 700;
      }

      .anper-risk-map__markers {
        display: flex;
        flex-wrap: wrap;
        gap: 0.2rem;
        padding-top: 1rem;
      }

      .anper-risk-marker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.55rem;
        padding: 0.12rem 0.25rem;
        border: 2px solid #fff;
        border-radius: 0.25rem;
        color: #fff;
        background-color: #172033;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
        font-size: 0.62rem;
        font-weight: 700;
      }

      .anper-risk-marker--current {
        background-color: var(--bs-primary);
      }

      .anper-risk-marker--unverified::after {
        margin-left: 0.1rem;
        content: "*";
      }

      .anper-risk-map__axis-y {
        position: absolute;
        top: 50%;
        left: -0.5rem;
        color: var(--bs-secondary-color);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.2rem;
        transform: translateY(-50%) rotate(-90deg);
      }

      .anper-risk-map__axis-x {
        margin-top: 0.5rem;
        color: var(--bs-secondary-color);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.3rem;
        text-align: center;
      }

      .anper-risk-status {
        padding: 1rem;
        border: 1px solid #f2d675;
        border-radius: 0.65rem;
        color: #6f5600;
        background-color: #fff6d8;
      }

      .anper-risk-table {
        min-width: 1160px;
        margin-bottom: 0;
        font-size: 0.74rem;
      }

      .anper-risk-table th {
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        font-size: 0.66rem;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
      }

      .anper-risk-table td {
        vertical-align: middle;
      }

      .anper-risk-table__event {
        min-width: 250px;
      }

      .anper-risk-level {
        display: inline-block;
        min-width: 92px;
        padding: 0.35rem 0.5rem;
        border-radius: 0.3rem;
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
        text-align: center;
      }

      .anper-risk-level--high { background-color: #f45d43; }
      .anper-risk-level--moderate-high { background-color: #ff9f0a; }
      .anper-risk-level--moderate { color: #665700; background-color: #ffd91a; }
      .anper-risk-level--low-moderate { background-color: #70b900; }
      .anper-risk-level--low { background-color: #168400; }

      .anper-risk-panel.is-fullscreen {
        position: fixed;
        z-index: 1090;
        inset: 1rem;
        overflow: auto;
        background-color: var(--bs-body-bg);
      }
    </style>
  @endpush
@endonce

@php
  $apName = $selectedApUnit?->name ?? 'Anak Perusahaan';
  $inherentMarkers = [
    '5-2' => ['R6'],
    '5-4' => ['R5'],
    '5-5' => ['R1', 'R3', 'R4'],
    '4-1' => ['R8', 'R2'],
    '4-2' => ['R5'],
    '3-1' => ['R7'],
    '3-3' => ['R4'],
    '2-4' => ['R1'],
    '1-1' => ['R9', 'R3'],
  ];
  $currentMarkers = [
    '5-5' => ['R3*'],
    '4-4' => ['R1*'],
    '4-5' => ['R4*'],
    '3-1' => ['R5*', 'R7*', 'R8*', 'R2*'],
    '2-1' => ['R9*'],
    '1-4' => ['R6*'],
  ];
  $apRisks = [
    ['code' => 'R1', 'event' => 'Potensi kehilangan kepercayaan stakeholder', 'inherent' => ['impact' => 'Rp 44,78 M', 'score' => 25, 'level' => 'High'], 'residual' => ['impact' => 'Rp 31,87 M', 'score' => 18, 'level' => 'Moderate High'], 'current' => ['impact' => 'Rp 31,04 M', 'score' => 19, 'level' => 'Moderate High *']],
    ['code' => 'R2', 'event' => 'Somasi oleh vendor', 'inherent' => ['impact' => 'Rp 36,00 M', 'score' => 25, 'level' => 'High'], 'residual' => ['impact' => 'Rp 26,67 M', 'score' => 4, 'level' => 'Low'], 'current' => ['impact' => 'Rp 60,00 M', 'score' => 3, 'level' => 'Low *']],
    ['code' => 'R3', 'event' => 'Potensi penurunan daya saing', 'inherent' => ['impact' => 'Rp 311,57 M', 'score' => 25, 'level' => 'High'], 'residual' => ['impact' => 'Rp 8,53 M', 'score' => 14, 'level' => 'Moderate'], 'current' => ['impact' => 'Rp 25,85 M', 'score' => 25, 'level' => 'High']],
    ['code' => 'R4', 'event' => 'Potensi pelaksanaan pekerjaan proyek mundur', 'inherent' => ['impact' => 'Rp 29,04 M', 'score' => 24, 'level' => 'High'], 'residual' => ['impact' => 'Rp 381,36 M', 'score' => 8, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 13,33 M', 'score' => 24, 'level' => 'High']],
    ['code' => 'R5', 'event' => 'Potensi tingginya beban bunga bank', 'inherent' => ['impact' => 'Rp 252,83 M', 'score' => 22, 'level' => 'High'], 'residual' => ['impact' => 'Rp 127,06 M', 'score' => 9, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 30,07 M', 'score' => 3, 'level' => 'Low *']],
    ['code' => 'R6', 'event' => 'Potensi tidak tercapainya target asset recycling', 'inherent' => ['impact' => 'Rp 158,62 M', 'score' => 15, 'level' => 'Moderate'], 'residual' => ['impact' => 'Rp 31,72 M', 'score' => 1, 'level' => 'Low'], 'current' => ['impact' => 'Rp 158,62 M', 'score' => 15, 'level' => 'Moderate *']],
  ];
  $levelClass = static fn (string $level): string => match (str_replace(' *', '', $level)) {
    'High' => 'high',
    'Moderate High' => 'moderate-high',
    'Moderate' => 'moderate',
    'Low to Moderate' => 'low-moderate',
    default => 'low',
  };
@endphp

<section class="dashboard-summary-layer anper-risk-profile" data-dashboard-category="ap" data-layer="2">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="anper-risk-profile__title mb-1">Profil Risiko Anak Perusahaan</h4>
      <p class="text-muted mb-0">Pemetaan risiko {{ $apName }} periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
  </div>

  <div class="anper-risk-panel" id="anper-risk-map-panel">
    <div class="anper-risk-panel__header">
      <div>
        <h5 class="mb-1">Peta Risiko</h5>
        <p class="text-muted small mb-0">Perbandingan posisi risiko inheren, residual, dan terkini.</p>
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary" id="anper-risk-fullscreen">
        <span class="bx bx-fullscreen me-1" aria-hidden="true"></span>
        <span>Perbesar Peta</span>
      </button>
    </div>

    <div class="p-3">
      <div class="anper-risk-legend mb-3">
        @foreach ([['High', '#f45d43'], ['Moderate to High', '#ff9f0a'], ['Moderate', '#ffd91a'], ['Low to Moderate', '#70d500'], ['Low', '#16a800']] as $legend)
          <span class="anper-risk-legend__item">
            <span class="anper-risk-legend__swatch" style="background-color: {{ $legend[1] }}"></span>
            {{ $legend[0] }}
          </span>
        @endforeach
      </div>

      <div class="row g-4">
        @foreach ([
          ['title' => 'Peta Risiko Inheren dan Residual', 'markers' => $inherentMarkers, 'current' => false],
          ['title' => 'Peta Risiko Terkini', 'markers' => $currentMarkers, 'current' => true],
        ] as $map)
          <div class="col-12 col-xxl-6">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <h6 class="mb-0">{{ $map['title'] }}</h6>
              @if ($map['current'])
                <div class="d-flex gap-2">
                  <select class="form-select form-select-sm" aria-label="Kuartal risiko AP">
                    <option>Q3 - {{ $selectedPeriodDisplay }}</option>
                  </select>
                  <select class="form-select form-select-sm" aria-label="Tahun risiko AP">
                    <option>{{ substr($selectedPeriod, 0, 4) }}</option>
                  </select>
                </div>
              @endif
            </div>
            <div class="anper-risk-map">
              <span class="anper-risk-map__axis-y">LIKELIHOOD</span>
              <div class="anper-risk-map__scroll">
                <div class="anper-risk-map__grid">
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
                      <div class="anper-risk-map__cell anper-risk-map__cell--{{ $cellClass }}">
                        <span class="anper-risk-map__score">{{ $score }}</span>
                        @if (isset($map['markers'][$cellKey]))
                          <div class="anper-risk-map__markers">
                            @foreach ($map['markers'][$cellKey] as $marker)
                              <span class="anper-risk-marker {{ $map['current'] ? 'anper-risk-marker--current' : '' }} {{ str_ends_with($marker, '*') ? 'anper-risk-marker--unverified' : '' }}">
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
              <div class="anper-risk-map__axis-x">IMPACT</div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="anper-risk-status mt-4">
        <div class="d-flex gap-2">
          <span class="bx bx-info-circle fs-5" aria-hidden="true"></span>
          <div>
            <h6 class="mb-1">Informasi Status Realisasi {{ $selectedPeriodDisplay }}</h6>
            <p class="small mb-1">Tanda bintang menunjukkan risiko yang monitoring-nya belum diverifikasi pada bulan cutoff.</p>
            <strong class="small">Risiko belum diperbarui: R1, R2, R4, R5, R6, R7, R8, dan R9.</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="anper-risk-panel mt-3">
    <div class="anper-risk-panel__header">
      <div>
        <h5 class="mb-1">Daftar Risiko Anak Perusahaan</h5>
        <p class="text-muted small mb-0">Ringkasan perbandingan risiko {{ $apName }}.</p>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover anper-risk-table">
        <thead>
          <tr>
            <th rowspan="2">Kode</th>
            <th rowspan="2">Peristiwa Risiko</th>
            <th colspan="3">Inheren</th>
            <th colspan="3">Residual</th>
            <th colspan="3">Realisasi Terkini</th>
          </tr>
          <tr>
            @foreach (range(1, 3) as $group)
              <th>Nilai Dampak</th>
              <th>Nilai Risiko</th>
              <th>Level Risiko</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($apRisks as $risk)
            <tr>
              <td><a href="#" class="fw-bold">{{ $risk['code'] }}</a></td>
              <td class="anper-risk-table__event">{{ $risk['event'] }}</td>
              @foreach (['inherent', 'residual', 'current'] as $riskType)
                <td class="text-end text-nowrap">{{ $risk[$riskType]['impact'] }}</td>
                <td class="text-center fw-semibold">{{ $risk[$riskType]['score'] }}</td>
                <td class="text-center">
                  <span class="anper-risk-level anper-risk-level--{{ $levelClass($risk[$riskType]['level']) }}">
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

  @include('dashboard-summary.contents._risk-distribution-charts', [
    'riskDistributionSource' => $apRisks,
    'riskDistributionTitle' => "Anak Perusahaan {$apName}",
  ])
</section>

@once
  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const panel = document.getElementById('anper-risk-map-panel');
        const button = document.getElementById('anper-risk-fullscreen');

        if (!panel || !button) {
          return;
        }

        button.addEventListener('click', function () {
          const isFullscreen = panel.classList.toggle('is-fullscreen');
          document.body.style.overflow = isFullscreen ? 'hidden' : '';
          button.querySelector('.bx').className = isFullscreen
            ? 'bx bx-exit-fullscreen me-1'
            : 'bx bx-fullscreen me-1';
          button.querySelector('span:last-child').textContent = isFullscreen
            ? 'Tutup Layar Penuh'
            : 'Perbesar Peta';
        });
      });
    </script>
  @endpush
@endonce
