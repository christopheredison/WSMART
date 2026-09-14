@once
  @push('styles')
    <style>
      .division-consolidation-risk__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .division-consolidation-panel {
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .division-consolidation-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .division-risk-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.2rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        background-color: var(--bs-tertiary-bg);
      }

      .division-risk-legend__item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
      }

      .division-risk-legend__swatch {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 0.2rem;
      }

      .division-consolidation-map {
        position: relative;
        padding-left: 1.5rem;
      }

      .division-consolidation-map__scroll {
        overflow-x: auto;
      }

      .division-consolidation-map__grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(84px, 1fr));
        min-width: 540px;
        overflow: hidden;
        border: 2px solid var(--bs-body-bg);
        border-radius: 0.5rem;
      }

      .division-consolidation-map__cell {
        position: relative;
        min-height: 64px;
        padding: 0.4rem;
        border: 1px solid rgba(255, 255, 255, 0.9);
        color: #fff;
      }

      .division-consolidation-map__cell--low { background-color: #16a800; }
      .division-consolidation-map__cell--low-moderate { background-color: #70d500; }
      .division-consolidation-map__cell--moderate { background-color: #ffd91a; }
      .division-consolidation-map__cell--moderate-high { background-color: #ff9f0a; }
      .division-consolidation-map__cell--high { background-color: #f45d43; }

      .division-consolidation-map__score {
        position: absolute;
        top: 0.25rem;
        right: 0.35rem;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.68rem;
        font-weight: 700;
      }

      .division-consolidation-map__markers {
        display: flex;
        flex-wrap: wrap;
        gap: 0.2rem;
        padding-top: 1rem;
      }

      .division-risk-marker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.13rem 0.3rem;
        border: 2px solid #fff;
        border-radius: 0.25rem;
        color: #fff;
        background-color: #172033;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
        font-size: 0.58rem;
        font-weight: 700;
        white-space: nowrap;
      }

      .division-risk-marker--current {
        background-color: var(--bs-primary);
      }

      .division-risk-marker--unverified::after {
        margin-left: 0.1rem;
        content: "*";
      }

      .division-consolidation-map__axis-y {
        position: absolute;
        top: 50%;
        left: -0.5rem;
        color: var(--bs-secondary-color);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.2rem;
        transform: translateY(-50%) rotate(-90deg);
      }

      .division-consolidation-map__axis-x {
        margin-top: 0.5rem;
        color: var(--bs-secondary-color);
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.3rem;
        text-align: center;
      }

      .division-consolidation-status {
        padding: 1rem;
        border: 1px solid #f2d675;
        border-radius: 0.65rem;
        color: #6f5600;
        background-color: #fff6d8;
      }

      .division-consolidation-table {
        min-width: 1320px;
        margin-bottom: 0;
        font-size: 0.73rem;
      }

      .division-consolidation-table th {
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        font-size: 0.64rem;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
      }

      .division-consolidation-table td {
        vertical-align: middle;
      }

      .division-consolidation-table__division {
        min-width: 170px;
        font-weight: 600;
      }

      .division-consolidation-table__event {
        min-width: 250px;
      }

      .division-risk-level {
        display: inline-block;
        min-width: 92px;
        padding: 0.35rem 0.5rem;
        border-radius: 0.3rem;
        color: #fff;
        font-size: 0.66rem;
        font-weight: 700;
        text-align: center;
      }

      .division-risk-level--high { background-color: #f45d43; }
      .division-risk-level--moderate-high { background-color: #ff9f0a; }
      .division-risk-level--moderate { color: #665700; background-color: #ffd91a; }
      .division-risk-level--low-moderate { background-color: #70b900; }
      .division-risk-level--low { background-color: #168400; }

      .division-consolidation-panel.is-fullscreen {
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
  $isSingleDivisionRiskProfile = $isSingleDivisionRiskProfile ?? false;
  $isProjectConsolidationRiskProfile = $isProjectConsolidationRiskProfile ?? false;
  $isAllProjectsConsolidationRiskProfile = $isAllProjectsConsolidationRiskProfile ?? false;
  $isSingleProjectRiskProfile = $isSingleProjectRiskProfile ?? false;
  $riskProfileDivisionName = $isSingleDivisionRiskProfile
    ? ($selectedDivisionModel?->name ?? 'Divisi Terpilih')
    : null;

  $divisionMarkers = [
    '5-2' => ['INF-R2'],
    '5-4' => ['BDG-R1'],
    '5-5' => ['INF-R1', 'SCM-R1'],
    '4-1' => ['WTJ-R3'],
    '4-3' => ['SCM-R2'],
    '3-2' => ['BDG-R4'],
    '3-4' => ['INF-R5'],
    '2-1' => ['WTJ-R2'],
    '1-3' => ['BDG-R6'],
  ];
  $divisionCurrentMarkers = [
    '5-5' => ['INF-R1*'],
    '4-4' => ['BDG-R1*'],
    '4-5' => ['SCM-R1'],
    '3-2' => ['INF-R2*', 'WTJ-R3'],
    '2-1' => ['WTJ-R2*'],
    '2-3' => ['SCM-R2'],
    '1-2' => ['BDG-R4*'],
  ];
  $divisionRisks = [
    ['division' => 'Infrastructure 1 Division', 'code' => 'INF-R1', 'event' => 'Potensi keterlambatan penerimaan piutang', 'inherent' => ['impact' => 'Rp 450,00 M', 'score' => 25, 'level' => 'High'], 'residual' => ['impact' => 'Rp 225,00 M', 'score' => 15, 'level' => 'Moderate High'], 'current' => ['impact' => 'Rp 315,00 M', 'score' => 25, 'level' => 'High *']],
    ['division' => 'Infrastructure 1 Division', 'code' => 'INF-R2', 'event' => 'Potensi deviasi waktu penyelesaian pekerjaan', 'inherent' => ['impact' => 'Rp 178,00 M', 'score' => 20, 'level' => 'High'], 'residual' => ['impact' => 'Rp 95,00 M', 'score' => 10, 'level' => 'Moderate'], 'current' => ['impact' => 'Rp 112,00 M', 'score' => 12, 'level' => 'Moderate *']],
    ['division' => 'Building Division', 'code' => 'BDG-R1', 'event' => 'Potensi kenaikan biaya material utama', 'inherent' => ['impact' => 'Rp 286,00 M', 'score' => 20, 'level' => 'High'], 'residual' => ['impact' => 'Rp 142,00 M', 'score' => 12, 'level' => 'Moderate'], 'current' => ['impact' => 'Rp 207,00 M', 'score' => 16, 'level' => 'Moderate High *']],
    ['division' => 'Building Division', 'code' => 'BDG-R4', 'event' => 'Potensi pekerjaan ulang akibat ketidaksesuaian mutu', 'inherent' => ['impact' => 'Rp 92,00 M', 'score' => 12, 'level' => 'Moderate'], 'residual' => ['impact' => 'Rp 44,00 M', 'score' => 6, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 51,00 M', 'score' => 5, 'level' => 'Low to Moderate *']],
    ['division' => 'Supply Chain Management Division', 'code' => 'SCM-R1', 'event' => 'Potensi gangguan pasokan material strategis', 'inherent' => ['impact' => 'Rp 165,00 M', 'score' => 25, 'level' => 'High'], 'residual' => ['impact' => 'Rp 82,00 M', 'score' => 15, 'level' => 'Moderate High'], 'current' => ['impact' => 'Rp 88,00 M', 'score' => 20, 'level' => 'High']],
    ['division' => 'WTJJ', 'code' => 'WTJ-R2', 'event' => 'Potensi penurunan kapasitas penyerapan sisi hilir', 'inherent' => ['impact' => 'Rp 74,00 M', 'score' => 8, 'level' => 'Low to Moderate'], 'residual' => ['impact' => 'Rp 35,00 M', 'score' => 4, 'level' => 'Low'], 'current' => ['impact' => 'Rp 49,00 M', 'score' => 2, 'level' => 'Low *']],
  ];

  if ($isSingleDivisionRiskProfile) {
    $divisionMarkers = [
      '5-1' => ['R2', 'R3'],
      '5-2' => ['R1'],
      '3-1' => ['R2'],
      '2-3' => ['R1'],
    ];
    $divisionCurrentMarkers = [
      '4-1' => ['R3*'],
      '3-1' => ['R2*'],
      '2-1' => ['R1*'],
    ];
    $divisionRisks = [
      ['division' => $riskProfileDivisionName, 'code' => 'R1', 'event' => 'Potensi keterlambatan pembayaran piutang bermasalah', 'inherent' => ['impact' => 'Rp 383,70 M', 'score' => 23, 'level' => 'High'], 'residual' => ['impact' => 'Rp 151,73 M', 'score' => 11, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 47,36 M', 'score' => 2, 'level' => 'Low *']],
      ['division' => $riskProfileDivisionName, 'code' => 'R2', 'event' => 'Potensi okupansi aset berkurang', 'inherent' => ['impact' => 'Rp 6,35 M', 'score' => 7, 'level' => 'Low to Moderate'], 'residual' => ['impact' => 'Rp 3,47 M', 'score' => 3, 'level' => 'Low'], 'current' => ['impact' => 'Rp 2,35 M', 'score' => 3, 'level' => 'Low *']],
      ['division' => $riskProfileDivisionName, 'code' => 'R3', 'event' => 'Potensi menurunnya daya beli dan minat masyarakat', 'inherent' => ['impact' => 'Rp 36,03 M', 'score' => 7, 'level' => 'Low to Moderate'], 'residual' => ['impact' => 'Rp 25,80 M', 'score' => 7, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 11,21 M', 'score' => 4, 'level' => 'Low *']],
    ];
  }

  if ($isProjectConsolidationRiskProfile) {
    $riskProfileDivisionName = $isAllProjectsConsolidationRiskProfile
      ? 'Semua Divisi'
      : ($selectedDivisionModel?->name ?? 'Divisi Terpilih');
    $divisionMarkers = [
      '5-5' => ['JTU-R1', 'BDC-R1'],
      '5-3' => ['GDS-R2'],
      '4-4' => ['PLT-R1'],
      '4-2' => ['JTU-R3'],
      '3-3' => ['TRM-R2'],
      '2-2' => ['BDC-R4'],
    ];
    $divisionCurrentMarkers = [
      '5-4' => ['JTU-R1*'],
      '4-3' => ['BDC-R1'],
      '4-2' => ['GDS-R2*'],
      '3-3' => ['PLT-R1'],
      '2-2' => ['TRM-R2*'],
    ];
    $divisionRisks = [
      ['division' => 'Proyek Jalan Tol Utara', 'code' => 'JTU-R1', 'event' => 'Potensi deviasi waktu penyelesaian pekerjaan', 'inherent' => ['impact' => 'Rp 286,00 M', 'score' => 25, 'level' => 'High'], 'residual' => ['impact' => 'Rp 142,00 M', 'score' => 16, 'level' => 'Moderate High'], 'current' => ['impact' => 'Rp 198,00 M', 'score' => 20, 'level' => 'High *']],
      ['division' => 'Proyek Bendungan Cipta', 'code' => 'BDC-R1', 'event' => 'Potensi kerusakan alat berat utama', 'inherent' => ['impact' => 'Rp 215,00 M', 'score' => 25, 'level' => 'High'], 'residual' => ['impact' => 'Rp 118,00 M', 'score' => 12, 'level' => 'Moderate'], 'current' => ['impact' => 'Rp 136,00 M', 'score' => 16, 'level' => 'Moderate High']],
      ['division' => 'Proyek Gedung Sentra', 'code' => 'GDS-R2', 'event' => 'Potensi kenaikan harga material utama', 'inherent' => ['impact' => 'Rp 178,00 M', 'score' => 15, 'level' => 'Moderate High'], 'residual' => ['impact' => 'Rp 82,00 M', 'score' => 10, 'level' => 'Moderate'], 'current' => ['impact' => 'Rp 97,00 M', 'score' => 8, 'level' => 'Low to Moderate *']],
      ['division' => 'Proyek Pelabuhan Timur', 'code' => 'PLT-R1', 'event' => 'Potensi gangguan pekerjaan akibat cuaca ekstrem', 'inherent' => ['impact' => 'Rp 155,00 M', 'score' => 16, 'level' => 'Moderate High'], 'residual' => ['impact' => 'Rp 76,00 M', 'score' => 9, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 88,00 M', 'score' => 9, 'level' => 'Low to Moderate']],
      ['division' => 'Proyek Transit Metropolitan', 'code' => 'TRM-R2', 'event' => 'Potensi utilitas eksisting tidak terpetakan', 'inherent' => ['impact' => 'Rp 98,00 M', 'score' => 9, 'level' => 'Low to Moderate'], 'residual' => ['impact' => 'Rp 48,00 M', 'score' => 6, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 62,00 M', 'score' => 4, 'level' => 'Low *']],
    ];
  }

  if ($isSingleProjectRiskProfile) {
    $riskProfileDivisionName = $selectedProjectModel?->project_name ?? 'Proyek Terpilih';
    $divisionMarkers = [
      '5-5' => ['R1'],
      '4-3' => ['R2'],
      '3-2' => ['R3'],
      '2-2' => ['R4'],
    ];
    $divisionCurrentMarkers = [
      '4-4' => ['R1*'],
      '3-3' => ['R2'],
      '2-2' => ['R3*'],
      '1-2' => ['R4'],
    ];
    $divisionRisks = [
      ['division' => $riskProfileDivisionName, 'code' => 'R1', 'event' => 'Potensi deviasi waktu penyelesaian pekerjaan', 'inherent' => ['impact' => 'Rp 286,00 M', 'score' => 25, 'level' => 'High'], 'residual' => ['impact' => 'Rp 142,00 M', 'score' => 16, 'level' => 'Moderate High'], 'current' => ['impact' => 'Rp 198,00 M', 'score' => 16, 'level' => 'Moderate High *']],
      ['division' => $riskProfileDivisionName, 'code' => 'R2', 'event' => 'Potensi kenaikan harga material utama', 'inherent' => ['impact' => 'Rp 178,00 M', 'score' => 12, 'level' => 'Moderate'], 'residual' => ['impact' => 'Rp 82,00 M', 'score' => 9, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 97,00 M', 'score' => 9, 'level' => 'Low to Moderate']],
      ['division' => $riskProfileDivisionName, 'code' => 'R3', 'event' => 'Potensi keterlambatan pembayaran termin', 'inherent' => ['impact' => 'Rp 125,00 M', 'score' => 8, 'level' => 'Low to Moderate'], 'residual' => ['impact' => 'Rp 64,00 M', 'score' => 6, 'level' => 'Low to Moderate'], 'current' => ['impact' => 'Rp 76,00 M', 'score' => 4, 'level' => 'Low *']],
      ['division' => $riskProfileDivisionName, 'code' => 'R4', 'event' => 'Potensi ketidaksesuaian mutu pekerjaan', 'inherent' => ['impact' => 'Rp 88,00 M', 'score' => 4, 'level' => 'Low'], 'residual' => ['impact' => 'Rp 42,00 M', 'score' => 2, 'level' => 'Low'], 'current' => ['impact' => 'Rp 51,00 M', 'score' => 2, 'level' => 'Low']],
    ];
  }
  $divisionLevelClass = static fn (string $level): string => match (str_replace(' *', '', $level)) {
    'High' => 'high',
    'Moderate High' => 'moderate-high',
    'Moderate' => 'moderate',
    'Low to Moderate' => 'low-moderate',
    default => 'low',
  };
@endphp

<section class="dashboard-summary-layer division-consolidation-risk" data-dashboard-category="{{ $isSingleProjectRiskProfile ? 'project' : ($isAllProjectsConsolidationRiskProfile ? 'consolidated-all-projects' : ($isProjectConsolidationRiskProfile ? 'consolidated-project' : ($isSingleDivisionRiskProfile ? 'division' : 'consolidated-division'))) }}" data-layer="2">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="division-consolidation-risk__title mb-1">Profil Risiko {{ $isSingleProjectRiskProfile ? 'Proyek' : ($isAllProjectsConsolidationRiskProfile ? 'Konsolidasi Seluruh Proyek' : ($isProjectConsolidationRiskProfile ? 'Konsolidasi Proyek' : ($isSingleDivisionRiskProfile ? 'Divisi' : 'Konsolidasi Divisi'))) }}</h4>
      <p class="text-muted mb-0">{{ $isSingleProjectRiskProfile ? "Profil risiko {$riskProfileDivisionName}" : ($isAllProjectsConsolidationRiskProfile ? 'Rangkuman risiko seluruh proyek dari semua divisi' : ($isProjectConsolidationRiskProfile ? "Rangkuman risiko seluruh proyek dalam {$riskProfileDivisionName}" : ($isSingleDivisionRiskProfile ? "Profil risiko {$riskProfileDivisionName}" : 'Rangkuman risiko seluruh divisi tanpa data proyek'))) }} periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      @if ($isSingleDivisionRiskProfile || $isProjectConsolidationRiskProfile || $isSingleProjectRiskProfile)
        <span class="badge bg-info-subtle text-info px-3 py-2">
          <i class="bx bx-buildings me-1" aria-hidden="true"></i>{{ $riskProfileDivisionName }}
        </span>
      @endif
      <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
    </div>
  </div>

  <div class="division-consolidation-panel" id="division-consolidation-map-panel">
    <div class="division-consolidation-panel__header">
      <div>
        <h5 class="mb-1">Peta Risiko {{ $isSingleProjectRiskProfile ? $riskProfileDivisionName : ($isProjectConsolidationRiskProfile ? 'Seluruh Proyek' : ($isSingleDivisionRiskProfile ? $riskProfileDivisionName : 'Konsolidasi Divisi')) }}</h5>
        <p class="text-muted small mb-0">{{ $isSingleProjectRiskProfile ? 'Marker menunjukkan kode risiko pada proyek terpilih.' : ($isProjectConsolidationRiskProfile ? 'Marker menunjukkan singkatan proyek dan kode risikonya.' : ($isSingleDivisionRiskProfile ? 'Marker menunjukkan kode risiko pada divisi terpilih.' : 'Kode marker menunjukkan singkatan divisi dan kode risikonya.')) }}</p>
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary" id="division-consolidation-fullscreen">
        <span class="bx bx-fullscreen me-1" aria-hidden="true"></span>
        <span>Perbesar Peta</span>
      </button>
    </div>

    <div class="p-3">
      <div class="division-risk-legend mb-3">
        @foreach ([['High', '#f45d43'], ['Moderate to High', '#ff9f0a'], ['Moderate', '#ffd91a'], ['Low to Moderate', '#70d500'], ['Low', '#16a800']] as $legend)
          <span class="division-risk-legend__item">
            <span class="division-risk-legend__swatch" style="background-color: {{ $legend[1] }}"></span>
            {{ $legend[0] }}
          </span>
        @endforeach
      </div>

      <div class="row g-4">
        @foreach ([
          ['title' => (($isSingleDivisionRiskProfile || $isSingleProjectRiskProfile) ? 'Peta Risiko Inheren dan Residual' : 'Peta Risiko Inheren dan Residual Seluruh ' . ($isProjectConsolidationRiskProfile ? 'Proyek' : 'Divisi')), 'markers' => $divisionMarkers, 'current' => false],
          ['title' => (($isSingleDivisionRiskProfile || $isSingleProjectRiskProfile) ? 'Peta Risiko Terkini' : 'Peta Risiko Terkini Seluruh ' . ($isProjectConsolidationRiskProfile ? 'Proyek' : 'Divisi')), 'markers' => $divisionCurrentMarkers, 'current' => true],
        ] as $map)
          <div class="col-12 col-xxl-6">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <h6 class="mb-0">{{ $map['title'] }}</h6>
              @if ($map['current'])
                <div class="d-flex gap-2">
                  <select class="form-select form-select-sm" aria-label="Kuartal konsolidasi divisi">
                    <option>Q3 - {{ $selectedPeriodDisplay }}</option>
                  </select>
                  <select class="form-select form-select-sm" aria-label="Tahun konsolidasi divisi">
                    <option>{{ substr($selectedPeriod, 0, 4) }}</option>
                  </select>
                </div>
              @endif
            </div>
            <div class="division-consolidation-map">
              <span class="division-consolidation-map__axis-y">LIKELIHOOD</span>
              <div class="division-consolidation-map__scroll">
                <div class="division-consolidation-map__grid">
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
                      <div class="division-consolidation-map__cell division-consolidation-map__cell--{{ $cellClass }}">
                        <span class="division-consolidation-map__score">{{ $score }}</span>
                        @if (isset($map['markers'][$cellKey]))
                          <div class="division-consolidation-map__markers">
                            @foreach ($map['markers'][$cellKey] as $marker)
                              <span class="division-risk-marker {{ $map['current'] ? 'division-risk-marker--current' : '' }} {{ str_ends_with($marker, '*') ? 'division-risk-marker--unverified' : '' }}">
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
              <div class="division-consolidation-map__axis-x">IMPACT</div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="division-consolidation-status mt-4">
        <div class="d-flex gap-2">
          <span class="bx bx-info-circle fs-5" aria-hidden="true"></span>
          <div>
            <h6 class="mb-1">Informasi Status Monitoring {{ $isSingleProjectRiskProfile ? $riskProfileDivisionName : ($isProjectConsolidationRiskProfile ? 'Seluruh Proyek' : ($isSingleDivisionRiskProfile ? $riskProfileDivisionName : 'Seluruh Divisi')) }}</h6>
            <p class="small mb-1">Tanda bintang menunjukkan risiko yang monitoring-nya belum diverifikasi pada bulan cutoff.</p>
            <strong class="small">Belum diperbarui: {{ $isSingleProjectRiskProfile ? 'R1 dan R3.' : ($isProjectConsolidationRiskProfile ? 'JTU-R1, GDS-R2, dan TRM-R2.' : ($isSingleDivisionRiskProfile ? 'R1, R2, dan R3.' : 'INF-R1, INF-R2, BDG-R1, BDG-R4, dan WTJ-R2.')) }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="division-consolidation-panel mt-3">
    <div class="division-consolidation-panel__header">
      <div>
        <h5 class="mb-1">Daftar Risiko {{ $isSingleProjectRiskProfile ? $riskProfileDivisionName : ($isProjectConsolidationRiskProfile ? 'Konsolidasi Proyek' : ($isSingleDivisionRiskProfile ? $riskProfileDivisionName : 'Konsolidasi Divisi')) }}</h5>
        <p class="text-muted small mb-0">{{ $isSingleProjectRiskProfile ? 'Risiko pada proyek terpilih.' : ($isAllProjectsConsolidationRiskProfile ? 'Seluruh proyek dari semua divisi.' : ($isProjectConsolidationRiskProfile ? "Seluruh proyek dalam {$riskProfileDivisionName}." : ($isSingleDivisionRiskProfile ? "Risiko pada {$riskProfileDivisionName}." : 'Seluruh divisi · data proyek tidak disertakan.'))) }}</p>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover division-consolidation-table">
        <thead>
          <tr>
            @unless ($isSingleDivisionRiskProfile || $isSingleProjectRiskProfile)
              <th rowspan="2">{{ $isProjectConsolidationRiskProfile ? 'Proyek' : 'Divisi' }}</th>
            @endunless
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
          @foreach ($divisionRisks as $risk)
            <tr>
              @unless ($isSingleDivisionRiskProfile || $isSingleProjectRiskProfile)
                <td class="division-consolidation-table__division">{{ $risk['division'] }}</td>
              @endunless
              <td><a href="#" class="fw-bold">{{ $risk['code'] }}</a></td>
              <td class="division-consolidation-table__event">{{ $risk['event'] }}</td>
              @foreach (['inherent', 'residual', 'current'] as $riskType)
                <td class="text-end text-nowrap">{{ $risk[$riskType]['impact'] }}</td>
                <td class="text-center fw-semibold">{{ $risk[$riskType]['score'] }}</td>
                <td class="text-center">
                  <span class="division-risk-level division-risk-level--{{ $divisionLevelClass($risk[$riskType]['level']) }}">
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
    'riskDistributionSource' => $divisionRisks,
    'riskDistributionTitle' => $isSingleProjectRiskProfile
      ? "Proyek {$riskProfileDivisionName}"
      : ($isProjectConsolidationRiskProfile
        ? ($isAllProjectsConsolidationRiskProfile ? 'Konsolidasi Seluruh Proyek' : "Konsolidasi Proyek dalam {$riskProfileDivisionName}")
        : ($isSingleDivisionRiskProfile
          ? "Divisi {$riskProfileDivisionName}"
          : 'Konsolidasi Divisi')),
  ])
</section>

@once
  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const panel = document.getElementById('division-consolidation-map-panel');
        const button = document.getElementById('division-consolidation-fullscreen');

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
