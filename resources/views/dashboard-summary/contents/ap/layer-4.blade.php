@once
  @push('styles')
    <style>
      .anper-parameter__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .anper-parameter-card {
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .anper-parameter-card__header {
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .anper-kri-table {
        min-width: 1260px;
        margin-bottom: 0;
        font-size: 0.76rem;
      }

      .anper-kri-table th {
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        font-size: 0.66rem;
        letter-spacing: 0.025rem;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
      }

      .anper-kri-table td {
        vertical-align: middle;
      }

      .anper-kri-table__risk,
      .anper-kri-table__cause,
      .anper-kri-table__indicator {
        min-width: 220px;
      }

      .anper-kri-status {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
      }

      .anper-kri-status__dot {
        width: 0.8rem;
        height: 0.8rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 50%;
        background-color: var(--bs-tertiary-bg);
      }

      .anper-kri-status__dot--safe.is-active {
        border-color: var(--bs-success);
        background-color: var(--bs-success);
      }

      .anper-kri-status__dot--alert.is-active {
        border-color: var(--bs-warning);
        background-color: var(--bs-warning);
      }

      .anper-kri-status__dot--danger.is-active {
        border-color: var(--bs-danger);
        background-color: var(--bs-danger);
      }

      .anper-effectiveness {
        display: grid;
        grid-template-columns: 160px 1fr;
        align-items: center;
        gap: 1.25rem;
        min-height: 270px;
        padding: 1.25rem;
      }

      .anper-effectiveness__donut {
        position: relative;
        display: grid;
        width: 145px;
        height: 145px;
        place-items: center;
        border-radius: 50%;
        background: conic-gradient(var(--bs-primary) 0 75%, var(--bs-danger) 75% 100%);
      }

      .anper-effectiveness__donut::before {
        position: absolute;
        width: 94px;
        height: 94px;
        border-radius: 50%;
        background-color: var(--bs-body-bg);
        content: "";
      }

      .anper-effectiveness__value {
        position: relative;
        z-index: 1;
        color: var(--bs-heading-color);
        font-size: 1.3rem;
        font-weight: 700;
        text-align: center;
      }

      .anper-effectiveness__value small {
        display: block;
        color: var(--bs-secondary-color);
        font-size: 0.65rem;
        font-weight: 500;
      }

      .anper-effectiveness__legend {
        display: grid;
        gap: 0.65rem;
      }

      .anper-effectiveness__legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.7rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.55rem;
      }

      .anper-effectiveness-detail {
        padding: 0.7rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.5rem;
        background-color: var(--bs-tertiary-bg);
      }

      .anper-effectiveness-detail + .anper-effectiveness-detail {
        margin-top: 0.5rem;
      }

      .anper-effectiveness-detail__title {
        color: var(--bs-heading-color);
        font-weight: 600;
      }

      .anper-effectiveness-detail__meta {
        margin-top: 0.2rem;
        color: var(--bs-secondary-color);
        font-size: 0.74rem;
      }

      .anper-exposure-list {
        margin: 0;
        padding: 0;
        list-style: none;
      }

      .anper-exposure-list__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.75rem 0;
      }

      .anper-exposure-list__item + .anper-exposure-list__item {
        border-top: 1px dashed var(--bs-border-color);
      }

      .anper-exposure-list__risk {
        color: var(--bs-primary);
        font-size: 0.8rem;
      }

      .anper-exposure-list__value {
        padding: 0.3rem 0.55rem;
        border-radius: 999px;
        color: #5f4700;
        background-color: #f4c531;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
      }

      .project-exposure-kpi {
        height: 100%;
        padding: 1rem;
        border: 2px solid var(--project-exposure-accent, var(--bs-primary));
        border-radius: 0.7rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.2rem 0.8rem rgba(23, 32, 51, 0.04);
      }

      .project-exposure-kpi__label {
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
        text-transform: uppercase;
      }

      .project-exposure-kpi__value {
        margin-top: 0.3rem;
        color: var(--bs-heading-color);
        font-size: 1.05rem;
        font-weight: 700;
      }

      .project-exposure-kpi__note {
        margin-top: 0.35rem;
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
      }

      .project-exposure-kpi--realization .project-exposure-kpi__value {
        color: #f45d43;
      }

      .project-exposure-visual {
        height: 100%;
        padding: 1rem 1.125rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .project-exposure-donut {
        position: relative;
        display: grid;
        width: 230px;
        height: 230px;
        margin: 1.5rem auto;
        place-items: center;
        border-radius: 50%;
      }

      .project-exposure-donut::before {
        position: absolute;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background-color: var(--bs-body-bg);
        content: "";
      }

      .project-exposure-donut__value {
        position: relative;
        z-index: 1;
        color: var(--bs-heading-color);
        font-size: 1.2rem;
        font-weight: 700;
        text-align: center;
      }

      .project-exposure-donut__value small {
        display: block;
        color: var(--bs-secondary-color);
        font-size: 0.65rem;
        font-weight: 500;
      }

      .project-exposure-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem 1rem;
      }

      .project-exposure-legend__item {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
      }

      .project-exposure-legend__swatch {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 0.2rem;
      }

      .project-exposure-bars {
        display: grid;
        gap: 0.6rem;
        margin-top: 1.25rem;
      }

      .project-exposure-bar {
        display: grid;
        grid-template-columns: minmax(120px, 0.9fr) minmax(180px, 2fr);
        align-items: center;
        gap: 0.75rem;
      }

      .project-exposure-bar__label {
        overflow: hidden;
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .project-exposure-bar__track {
        position: relative;
        height: 1.55rem;
        overflow: hidden;
        border-radius: 0.3rem;
        background-color: var(--bs-tertiary-bg);
      }

      .project-exposure-bar__fill {
        height: 100%;
        min-width: 0.25rem;
        border-radius: 0.3rem;
        background-color: #ef6565;
      }

      .project-exposure-bar__value {
        position: absolute;
        z-index: 1;
        top: 50%;
        right: 0.45rem;
        color: var(--bs-heading-color);
        font-size: 0.68rem;
        font-weight: 700;
        transform: translateY(-50%);
      }

      @media (max-width: 767.98px) {
        .anper-effectiveness {
          grid-template-columns: 1fr;
          justify-items: center;
        }

        .anper-effectiveness__legend {
          width: 100%;
        }

        .anper-exposure-list__item {
          align-items: flex-start;
          flex-direction: column;
        }

        .project-exposure-bar {
          grid-template-columns: 1fr;
          gap: 0.25rem;
        }
      }
    </style>
  @endpush
@endonce

@php
  $isDivisionRiskParameter = $isDivisionRiskParameter ?? false;
  $isProjectConsolidationParameter = $isProjectConsolidationParameter ?? false;
  $isAllProjectsConsolidationParameter = $isAllProjectsConsolidationParameter ?? false;
  $isSingleProjectRiskParameter = $isSingleProjectRiskParameter ?? false;
  $apName = $isSingleProjectRiskParameter
    ? ($selectedProjectModel?->project_name ?? 'Proyek Terpilih')
    : ($isAllProjectsConsolidationParameter
      ? 'Semua Divisi'
      : (($isDivisionRiskParameter || $isProjectConsolidationParameter)
    ? ($selectedDivisionModel?->name ?? 'Divisi Terpilih')
    : ($selectedApUnit?->name ?? 'Anak Perusahaan')));
  $selectedYear = substr($selectedPeriod, 0, 4);
  $apKris = [
    ['risk' => 'Potensi pelaksanaan pekerjaan proyek mundur', 'cause' => 'Deviasi jadwal pada pekerjaan kritis.', 'indicator' => 'Project On Time On Budget (POTOB)', 'safe' => '100%', 'alert' => '85%', 'danger' => '70%', 'current' => '60,29%', 'status' => 'danger'],
    ['risk' => 'Potensi tingginya beban bunga bank', 'cause' => 'Peningkatan kewajiban pendanaan jangka pendek.', 'indicator' => 'Gearing Ratio', 'safe' => '< -2,27', 'alert' => '-2,64', 'danger' => '> -3,00', 'current' => '2,14', 'status' => 'danger'],
    ['risk' => 'Potensi kegagalan percepatan piutang usaha', 'cause' => 'Dokumen penagihan belum lengkap.', 'indicator' => 'Overdue Receivable Ratio', 'safe' => '< 49,74%', 'alert' => '49,74–53,77%', 'danger' => '> 56,35%', 'current' => '53,65%', 'status' => 'alert'],
    ['risk' => 'Potensi cost overrun harga komoditas', 'cause' => 'Inflasi harga material strategis.', 'indicator' => 'Inefisiensi Pengadaan', 'safe' => '< 0,75%', 'alert' => '0,75–1,50%', 'danger' => '> 1,50%', 'current' => '0,93%', 'status' => 'alert'],
    ['risk' => 'Somasi oleh vendor', 'cause' => 'Perselisihan kewajiban kontraktual.', 'indicator' => 'Penanganan Permasalahan Hukum', 'safe' => '100%', 'alert' => '75%', 'danger' => '< 50%', 'current' => '50%', 'status' => 'alert'],
    ['risk' => 'Potensi penurunan daya saing', 'cause' => 'Perolehan kontrak baru di bawah target.', 'indicator' => 'Perolehan Kontrak Baru', 'safe' => '100%', 'alert' => '81,83%', 'danger' => '< 70,20%', 'current' => '71%', 'status' => 'alert'],
  ];
  $effectiveTreatments = [
    ['risk' => 'Keterlambatan penerimaan piutang', 'action' => 'Percepatan kelengkapan dokumen tagihan'],
    ['risk' => 'Gangguan pasokan bahan baku', 'action' => 'Penambahan vendor material alternatif'],
    ['risk' => 'Penurunan utilisasi produksi', 'action' => 'Optimalisasi jadwal dan kapasitas pabrik'],
  ];
  $ineffectiveTreatments = [
    ['risk' => 'Deviasi waktu proyek', 'action' => 'Penambahan jam kerja pada aktivitas kritis'],
  ];
  $annualExposures = [
    ['risk' => 'Potensi tingginya beban bunga bank', 'value' => 76256002604],
    ['risk' => 'Potensi kehilangan kepercayaan stakeholder', 'value' => 40722482610],
    ['risk' => 'Potensi penurunan daya saing', 'value' => 31047684366],
    ['risk' => 'Potensi tidak tercapainya target asset recycling', 'value' => 26964899729],
    ['risk' => 'Potensi pelaksanaan pekerjaan proyek mundur', 'value' => 9997500000],
  ];
  $currentExposures = [
    ['risk' => 'Potensi kehilangan kepercayaan stakeholder', 'value' => 369735825522],
    ['risk' => 'Potensi tidak tercapainya target asset recycling', 'value' => 317820997568],
    ['risk' => 'Potensi tingginya beban bunga bank', 'value' => 314271204099],
    ['risk' => 'Potensi penurunan daya saing', 'value' => 308013615906],
    ['risk' => 'Potensi pelaksanaan pekerjaan proyek mundur', 'value' => 103959322769],
  ];

  if ($isDivisionRiskParameter) {
    $apKris = [
      ['risk' => 'Potensi menurunnya daya beli dan minat masyarakat', 'cause' => 'Perubahan kondisi pasar pada wilayah operasi divisi.', 'indicator' => 'Akumulasi omzet penjualan dibandingkan RKAP', 'safe' => '0–30%', 'alert' => '> 30–70%', 'danger' => '> 70%', 'current' => '35,00%', 'status' => 'alert'],
      ['risk' => 'Potensi keterlambatan pembayaran piutang', 'cause' => 'Dokumen penagihan proyek belum lengkap.', 'indicator' => 'Collection Period Tagihan Bruto', 'safe' => '≤ 30 hari', 'alert' => '31–60 hari', 'danger' => '> 60 hari', 'current' => '48 hari', 'status' => 'alert'],
      ['risk' => 'Potensi deviasi waktu pelaksanaan', 'cause' => 'Produktivitas pekerjaan utama berada di bawah rencana.', 'indicator' => 'Deviasi progres fisik terhadap jadwal', 'safe' => '≤ 3%', 'alert' => '4–7%', 'danger' => '> 7%', 'current' => '8,20%', 'status' => 'danger'],
      ['risk' => 'Potensi kenaikan biaya material', 'cause' => 'Harga material strategis mengalami peningkatan.', 'indicator' => 'Deviasi biaya material terhadap anggaran', 'safe' => '≤ 2%', 'alert' => '2–5%', 'danger' => '> 5%', 'current' => '4,15%', 'status' => 'alert'],
    ];
    $effectiveTreatments = [
      ['risk' => 'Keterlambatan penerimaan piutang', 'action' => 'Percepatan verifikasi dan kelengkapan dokumen tagihan proyek'],
      ['risk' => 'Gangguan pasokan material', 'action' => 'Penambahan vendor alternatif pada wilayah operasi divisi'],
      ['risk' => 'Penurunan margin usaha', 'action' => 'Penerapan program efisiensi biaya operasional'],
    ];
    $ineffectiveTreatments = [
      ['risk' => 'Deviasi waktu pekerjaan', 'action' => 'Penambahan jam kerja pada aktivitas kritis belum mencapai target'],
    ];
    $annualExposures = [
      ['risk' => 'Potensi keterlambatan penerimaan piutang proyek', 'value' => 425600000000],
      ['risk' => 'Potensi deviasi waktu penyelesaian pekerjaan', 'value' => 286750000000],
      ['risk' => 'Potensi kenaikan harga material strategis', 'value' => 195400000000],
      ['risk' => 'Potensi penurunan margin usaha divisi', 'value' => 142850000000],
      ['risk' => 'Potensi gangguan pasokan material proyek', 'value' => 98750000000],
    ];
    $currentExposures = [
      ['risk' => 'Potensi keterlambatan penerimaan piutang proyek', 'value' => 315200000000],
      ['risk' => 'Potensi deviasi waktu penyelesaian pekerjaan', 'value' => 212450000000],
      ['risk' => 'Potensi kenaikan harga material strategis', 'value' => 156800000000],
      ['risk' => 'Potensi penurunan margin usaha divisi', 'value' => 118350000000],
      ['risk' => 'Potensi gangguan pasokan material proyek', 'value' => 72450000000],
    ];
  }

  if ($isProjectConsolidationParameter) {
    $apKris = [
      ['risk' => 'Potensi deviasi waktu penyelesaian proyek', 'cause' => 'Produktivitas aktivitas kritis berada di bawah rencana.', 'indicator' => 'Project On Time On Budget (POTOB)', 'safe' => '≥ 95%', 'alert' => '85–94%', 'danger' => '< 85%', 'current' => '82,50%', 'status' => 'danger'],
      ['risk' => 'Potensi kenaikan biaya material proyek', 'cause' => 'Inflasi harga material strategis.', 'indicator' => 'Deviasi biaya material terhadap anggaran', 'safe' => '≤ 2%', 'alert' => '2–5%', 'danger' => '> 5%', 'current' => '4,70%', 'status' => 'alert'],
      ['risk' => 'Potensi keterlambatan pembayaran termin', 'cause' => 'Dokumen pendukung penagihan belum lengkap.', 'indicator' => 'Collection Period Tagihan Bruto', 'safe' => '≤ 30 hari', 'alert' => '31–60 hari', 'danger' => '> 60 hari', 'current' => '52 hari', 'status' => 'alert'],
      ['risk' => 'Potensi kecelakaan kerja proyek', 'cause' => 'Kepatuhan terhadap prosedur keselamatan menurun.', 'indicator' => 'Lost Time Injury Frequency Rate', 'safe' => '0', 'alert' => '0–1', 'danger' => '> 1', 'current' => '1,25', 'status' => 'danger'],
      ['risk' => 'Potensi gangguan pasokan material', 'cause' => 'Lead time pemasok utama meningkat.', 'indicator' => 'Hari keterlambatan material strategis', 'safe' => '≤ 3 hari', 'alert' => '4–7 hari', 'danger' => '> 7 hari', 'current' => '6 hari', 'status' => 'alert'],
    ];
    $effectiveTreatments = [
      ['risk' => 'Gangguan pasokan material proyek', 'action' => 'Pengadaan vendor alternatif untuk beberapa proyek prioritas'],
      ['risk' => 'Keterlambatan pembayaran termin', 'action' => 'Percepatan verifikasi dokumen penagihan seluruh proyek'],
      ['risk' => 'Ketidaksesuaian mutu pekerjaan', 'action' => 'Peningkatan inspeksi mutu pada pekerjaan kritis'],
    ];
    $ineffectiveTreatments = [
      ['risk' => 'Deviasi waktu penyelesaian proyek', 'action' => 'Penambahan jam kerja belum mengembalikan progres sesuai baseline'],
      ['risk' => 'Kenaikan biaya material', 'action' => 'Negosiasi vendor belum menutup seluruh deviasi harga'],
    ];
    $annualExposures = [
      ['risk' => 'Proyek Jalan Tol Utara — Deviasi waktu', 'value' => 286000000000],
      ['risk' => 'Proyek Bendungan Cipta — Kerusakan alat berat', 'value' => 215000000000],
      ['risk' => 'Proyek Gedung Sentra — Kenaikan harga material', 'value' => 178000000000],
      ['risk' => 'Proyek Pelabuhan Timur — Cuaca ekstrem', 'value' => 155000000000],
      ['risk' => 'Proyek Transit Metropolitan — Utilitas eksisting', 'value' => 98000000000],
    ];
    $currentExposures = [
      ['risk' => 'Proyek Jalan Tol Utara — Deviasi waktu', 'value' => 198000000000],
      ['risk' => 'Proyek Bendungan Cipta — Kerusakan alat berat', 'value' => 136000000000],
      ['risk' => 'Proyek Gedung Sentra — Kenaikan harga material', 'value' => 97000000000],
      ['risk' => 'Proyek Pelabuhan Timur — Cuaca ekstrem', 'value' => 88000000000],
      ['risk' => 'Proyek Transit Metropolitan — Utilitas eksisting', 'value' => 62000000000],
    ];
  }

  if ($isSingleProjectRiskParameter) {
    $apKris = [
      ['risk' => 'Potensi deviasi waktu penyelesaian proyek', 'cause' => 'Produktivitas aktivitas kritis berada di bawah rencana.', 'indicator' => 'Project On Time On Budget (POTOB)', 'safe' => '≥ 95%', 'alert' => '85–94%', 'danger' => '< 85%', 'current' => '82,50%', 'status' => 'danger'],
      ['risk' => 'Potensi kenaikan biaya material utama', 'cause' => 'Inflasi harga material dan perubahan pasokan.', 'indicator' => 'Deviasi biaya material terhadap anggaran', 'safe' => '≤ 2%', 'alert' => '2–5%', 'danger' => '> 5%', 'current' => '4,70%', 'status' => 'alert'],
      ['risk' => 'Potensi keterlambatan pembayaran termin', 'cause' => 'Dokumen pendukung penagihan belum lengkap.', 'indicator' => 'Collection Period Tagihan Bruto', 'safe' => '≤ 30 hari', 'alert' => '31–60 hari', 'danger' => '> 60 hari', 'current' => '52 hari', 'status' => 'alert'],
      ['risk' => 'Potensi kecelakaan kerja proyek', 'cause' => 'Kepatuhan prosedur keselamatan pada aktivitas kritis menurun.', 'indicator' => 'Lost Time Injury Frequency Rate', 'safe' => '0', 'alert' => '0–1', 'danger' => '> 1', 'current' => '1,25', 'status' => 'danger'],
    ];
    $effectiveTreatments = [
      ['risk' => 'Gangguan pasokan material', 'action' => 'Menambah vendor alternatif untuk material strategis'],
      ['risk' => 'Keterlambatan pembayaran termin', 'action' => 'Mempercepat verifikasi dan kelengkapan dokumen penagihan'],
      ['risk' => 'Ketidaksesuaian mutu pekerjaan', 'action' => 'Meningkatkan inspeksi mutu pada pekerjaan kritis'],
    ];
    $ineffectiveTreatments = [
      ['risk' => 'Deviasi waktu pelaksanaan', 'action' => 'Penambahan jam kerja belum mengembalikan progres sesuai baseline'],
    ];
  }

  if ($isProjectConsolidationParameter) {
    $consolidationExposureKpis = $isAllProjectsConsolidationParameter
      ? [
          ['label' => 'Total Risiko Open', 'value' => '432 Risiko', 'note' => '67 proyek aktif', 'color' => '#9bd419'],
          ['label' => 'Total Eksposur Inheren', 'value' => 'Rp 3,05 T', 'note' => 'Dampak inheren: Rp 4,31 T', 'color' => '#5656f5'],
          ['label' => 'Total Eksposur Residual', 'value' => 'Rp 938,33 M', 'note' => 'Dampak residual: Rp 1,84 T', 'color' => '#e9b417'],
          ['label' => 'Total Eksposur Realisasi', 'value' => 'Rp 1,12 T', 'note' => 'Dampak realisasi: Rp 2,72 T', 'color' => '#f45d43'],
        ]
      : [
          ['label' => 'Total Risiko Open', 'value' => '86 Risiko', 'note' => '12 proyek aktif', 'color' => '#9bd419'],
          ['label' => 'Total Eksposur Inheren', 'value' => 'Rp 1,12 T', 'note' => 'Dampak inheren: Rp 1,86 T', 'color' => '#5656f5'],
          ['label' => 'Total Eksposur Residual', 'value' => 'Rp 472,85 M', 'note' => 'Dampak residual: Rp 810,40 M', 'color' => '#e9b417'],
          ['label' => 'Total Eksposur Realisasi', 'value' => 'Rp 581,00 M', 'note' => 'Dampak realisasi: Rp 972,25 M', 'color' => '#f45d43'],
        ];

    $consolidationExposureDistribution = $isAllProjectsConsolidationParameter
      ? [
          ['label' => 'Infrastructure Division', 'value' => 622700000000, 'color' => '#5573ca'],
          ['label' => 'Building Division', 'value' => 400500000000, 'color' => '#8dca70'],
          ['label' => 'EPCC Division', 'value' => 98400000000, 'color' => '#fac557'],
        ]
      : [
          ['label' => 'Proyek Jalan Tol Utara', 'value' => 198000000000, 'color' => '#5573ca'],
          ['label' => 'Proyek Bendungan Cipta', 'value' => 136000000000, 'color' => '#8dca70'],
          ['label' => 'Proyek Gedung Sentra', 'value' => 97000000000, 'color' => '#fac557'],
          ['label' => 'Proyek Pelabuhan Timur', 'value' => 88000000000, 'color' => '#ef7967'],
          ['label' => 'Proyek Transit Metropolitan', 'value' => 62000000000, 'color' => '#8b70ca'],
        ];

    $consolidationTopProjects = [
      ['name' => 'Pembangunan SR Provinsi Kawasan Timur', 'value' => 135250000000],
      ['name' => 'JSDP Zona 6 WWTP Package', 'value' => 116170000000],
      ['name' => 'Pembangunan Gedung dan Kawasan Terpadu', 'value' => 111680000000],
      ['name' => 'Pembangunan Jalan Kawasan Industri', 'value' => 89290000000],
      ['name' => 'Pembangunan Gedung dan Kawasan Barat', 'value' => 58220000000],
      ['name' => 'Pembangunan RS Central Medical', 'value' => 57080000000],
      ['name' => 'EPC Coal Handling System', 'value' => 53050000000],
      ['name' => 'Bendungan Cijurey Paket 3', 'value' => 48270000000],
      ['name' => 'Pembangunan Bendungan Manikin', 'value' => 47660000000],
      ['name' => 'Patimban Access Toll Road', 'value' => 41900000000],
    ];

    $consolidationDistributionTotal = collect($consolidationExposureDistribution)->sum('value');
    $consolidationDistributionStart = 0;
    $consolidationDistributionSegments = [];

    foreach ($consolidationExposureDistribution as $distribution) {
      $distributionEnd = $consolidationDistributionStart
        + (($distribution['value'] / $consolidationDistributionTotal) * 100);
      $consolidationDistributionSegments[] = "{$distribution['color']} {$consolidationDistributionStart}% {$distributionEnd}%";
      $consolidationDistributionStart = $distributionEnd;
    }

    $consolidationDistributionGradient = implode(', ', $consolidationDistributionSegments);
    $consolidationTopProjectMax = collect($consolidationTopProjects)->max('value');
  }
@endphp

<section class="dashboard-summary-layer anper-parameter" data-dashboard-category="{{ $isSingleProjectRiskParameter ? 'project' : ($isAllProjectsConsolidationParameter ? 'consolidated-all-projects' : ($isProjectConsolidationParameter ? 'consolidated-project' : ($isDivisionRiskParameter ? 'division' : 'ap'))) }}" data-layer="4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="anper-parameter__title mb-1">Parameter Risiko {{ $isSingleProjectRiskParameter ? 'Proyek' : ($isAllProjectsConsolidationParameter ? 'Konsolidasi Seluruh Proyek' : ($isProjectConsolidationParameter ? 'Konsolidasi Proyek' : ($isDivisionRiskParameter ? 'Divisi' : 'Anak Perusahaan'))) }}</h4>
      <p class="text-muted mb-0">KRI dan efektivitas perlakuan risiko {{ $apName }}{{ $isSingleProjectRiskParameter ? '.' : ', termasuk eksposur risiko.' }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      @if ($isDivisionRiskParameter || $isProjectConsolidationParameter || $isSingleProjectRiskParameter)
        <span class="badge bg-info-subtle text-info px-3 py-2">
          <i class="bx bx-buildings me-1" aria-hidden="true"></i>{{ $apName }}
        </span>
      @endif
      <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
    </div>
  </div>

  <div class="anper-parameter-card mb-4">
    <div class="anper-parameter-card__header">
      <h5 class="mb-1">Key Risk Indicator (KRI)</h5>
      <p class="text-muted small mb-0">Daftar KRI dengan status Siaga dan Bahaya periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover anper-kri-table">
        <thead>
          <tr>
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
          @foreach ($apKris as $kri)
            <tr>
              <td class="anper-kri-table__risk">{{ $kri['risk'] }}</td>
              <td class="anper-kri-table__cause">{{ $kri['cause'] }}</td>
              <td class="anper-kri-table__indicator">{{ $kri['indicator'] }}</td>
              <td class="text-center text-nowrap">{{ $kri['safe'] }}</td>
              <td class="text-center text-nowrap">{{ $kri['alert'] }}</td>
              <td class="text-center text-nowrap">{{ $kri['danger'] }}</td>
              <td class="text-center fw-bold text-nowrap">{{ $kri['current'] }}</td>
              <td class="text-center">
                <span class="anper-kri-status" aria-label="Status {{ $kri['status'] === 'danger' ? 'Bahaya' : 'Siaga' }}">
                  <span class="anper-kri-status__dot anper-kri-status__dot--safe"></span>
                  <span class="anper-kri-status__dot anper-kri-status__dot--alert {{ $kri['status'] === 'alert' ? 'is-active' : '' }}"></span>
                  <span class="anper-kri-status__dot anper-kri-status__dot--danger {{ $kri['status'] === 'danger' ? 'is-active' : '' }}"></span>
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="d-flex flex-wrap justify-content-end gap-3 px-3 py-2 border-top small text-muted">
      <span><span class="anper-kri-status__dot anper-kri-status__dot--safe is-active d-inline-block me-1"></span>Aman</span>
      <span><span class="anper-kri-status__dot anper-kri-status__dot--alert is-active d-inline-block me-1"></span>Siaga</span>
      <span><span class="anper-kri-status__dot anper-kri-status__dot--danger is-active d-inline-block me-1"></span>Bahaya</span>
    </div>
  </div>

  <h4 class="anper-parameter__title mb-3">Efektivitas Perlakuan Risiko</h4>
  <div class="row g-3 mb-4">
    <div class="col-12 col-xl-5">
      <div class="anper-parameter-card h-100">
        <div class="anper-parameter-card__header">
          <h5 class="mb-1">Ringkasan Efektivitas</h5>
          <p class="text-muted small mb-0">Penilaian risiko yang sudah ditutup.</p>
        </div>
        <div class="anper-effectiveness">
          <div class="anper-effectiveness__donut">
            <div class="anper-effectiveness__value">75%<small>Efektif</small></div>
          </div>
          <div class="anper-effectiveness__legend">
            <div class="anper-effectiveness__legend-item">
              <span><span class="badge bg-primary me-2">&nbsp;</span>Efektif</span>
              <strong>{{ count($effectiveTreatments) }}</strong>
            </div>
            <div class="anper-effectiveness__legend-item">
              <span><span class="badge bg-danger me-2">&nbsp;</span>Tidak Efektif</span>
              <strong>{{ count($ineffectiveTreatments) }}</strong>
            </div>
            <div class="anper-effectiveness__legend-item">
              <span class="text-muted">Total risiko selesai</span>
              <strong>{{ count($effectiveTreatments) + count($ineffectiveTreatments) }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      <div class="anper-parameter-card h-100">
        <div class="anper-parameter-card__header">
          <h5 class="mb-1">Detail Risiko Selesai (Closed)</h5>
          <p class="text-muted small mb-0">Detail perlakuan efektif dan tidak efektif.</p>
        </div>
        <div class="accordion p-3" id="anper-effectiveness-accordion">
          @foreach ([
            ['id' => 'effective', 'label' => 'Perlakuan Efektif', 'items' => $effectiveTreatments, 'badge' => 'primary'],
            ['id' => 'ineffective', 'label' => 'Perlakuan Tidak Efektif', 'items' => $ineffectiveTreatments, 'badge' => 'danger'],
          ] as $section)
            <div class="accordion-item">
              <h2 class="accordion-header" id="anper-{{ $section['id'] }}-heading">
                <button
                  class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#anper-{{ $section['id'] }}-content"
                  aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                  aria-controls="anper-{{ $section['id'] }}-content"
                >
                  {{ $section['label'] }}
                  <span class="badge bg-{{ $section['badge'] }} ms-2">{{ count($section['items']) }}</span>
                </button>
              </h2>
              <div
                id="anper-{{ $section['id'] }}-content"
                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                aria-labelledby="anper-{{ $section['id'] }}-heading"
                data-bs-parent="#anper-effectiveness-accordion"
              >
                <div class="accordion-body">
                  @foreach ($section['items'] as $item)
                    <div class="anper-effectiveness-detail">
                      <div class="anper-effectiveness-detail__title">{{ $item['risk'] }}</div>
                      <div class="anper-effectiveness-detail__meta">{{ $item['action'] }}</div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  @if ($isProjectConsolidationParameter)
    <div class="mt-4 mb-4">
      <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
        <div>
          <h4 class="anper-parameter__title mb-1">Ringkasan Eksposur Risiko Proyek</h4>
          <p class="text-muted small mb-0">
            {{ $isAllProjectsConsolidationParameter
              ? 'Konsolidasi seluruh proyek dari semua divisi'
              : "Konsolidasi seluruh proyek di {$apName}" }}
            · periode {{ $selectedPeriodDisplay }}.
          </p>
        </div>
      </div>

      <div class="row g-3 mb-3">
        @foreach ($consolidationExposureKpis as $kpi)
          <div class="col-12 col-md-6 col-xxl-3">
            <div
              class="project-exposure-kpi {{ $loop->last ? 'project-exposure-kpi--realization' : '' }}"
              style="--project-exposure-accent: {{ $kpi['color'] }}"
            >
              <div class="project-exposure-kpi__label">{{ $kpi['label'] }}</div>
              <div class="project-exposure-kpi__value">{{ $kpi['value'] }}</div>
              <div class="project-exposure-kpi__note">{{ $kpi['note'] }}</div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="row g-3">
        <div class="col-12 col-xl-6">
          <div class="project-exposure-visual">
            <h5 class="mb-1">Distribusi Total Eksposur Realisasi</h5>
            <p class="text-muted small mb-0">
              {{ $isAllProjectsConsolidationParameter ? 'Per Divisi' : 'Per Proyek' }}
              · berdasarkan risiko kuantitatif.
            </p>
            <div
              class="project-exposure-donut"
              style="background: conic-gradient({{ $consolidationDistributionGradient }})"
              role="img"
              aria-label="Distribusi total eksposur realisasi"
            >
              <div class="project-exposure-donut__value">
                Rp {{ number_format($consolidationDistributionTotal / 1000000000, 1, ',', '.') }} M
                <small>Total Realisasi</small>
              </div>
            </div>
            <div class="project-exposure-legend">
              @foreach ($consolidationExposureDistribution as $distribution)
                @php
                  $distributionPercentage = round(
                    ($distribution['value'] / $consolidationDistributionTotal) * 100,
                    1
                  );
                @endphp
                <span class="project-exposure-legend__item">
                  <span class="project-exposure-legend__swatch" style="background-color: {{ $distribution['color'] }}"></span>
                  {{ $distribution['label'] }} ·
                  Rp {{ number_format($distribution['value'] / 1000000000, 1, ',', '.') }} M
                  ({{ $distributionPercentage }}%)
                </span>
              @endforeach
            </div>
          </div>
        </div>

        <div class="col-12 col-xl-6">
          <div class="project-exposure-visual">
            <h5 class="mb-1">10 Proyek Eksposur Realisasi Tertinggi</h5>
            <p class="text-muted small mb-0">Berdasarkan proyek dan risiko aktif.</p>
            <div class="project-exposure-bars">
              @foreach ($consolidationTopProjects as $projectExposure)
                @php
                  $barWidth = ($projectExposure['value'] / $consolidationTopProjectMax) * 100;
                @endphp
                <div class="project-exposure-bar">
                  <div class="project-exposure-bar__label" title="{{ $projectExposure['name'] }}">
                    {{ $projectExposure['name'] }}
                  </div>
                  <div class="project-exposure-bar__track">
                    <div class="project-exposure-bar__fill" style="width: {{ $barWidth }}%"></div>
                    <span class="project-exposure-bar__value">
                      Rp {{ number_format($projectExposure['value'] / 1000000000, 2, ',', '.') }} M
                    </span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  @unless ($isSingleProjectRiskParameter)
  <h4 class="anper-parameter__title mb-3">Eksposur Risiko {{ $isAllProjectsConsolidationParameter ? 'Seluruh Proyek dari Semua Divisi' : ($isProjectConsolidationParameter ? "Seluruh Proyek di {$apName}" : ($isDivisionRiskParameter ? "Proyek di {$apName}" : 'Anak Perusahaan')) }}</h4>
  <div class="row g-3">
    @foreach ([
      ['title' => "Top 5 Eksposur Risiko" . (($isDivisionRiskParameter || $isProjectConsolidationParameter) ? ' Proyek' : '') . " (Annual {$selectedYear})", 'items' => $annualExposures],
      ['title' => "Top 5 Eksposur Risiko" . (($isDivisionRiskParameter || $isProjectConsolidationParameter) ? ' Proyek' : '') . " (Total s.d. {$selectedPeriodDisplay})", 'items' => $currentExposures],
    ] as $exposureGroup)
      <div class="col-12 col-xl-6">
        <div class="anper-parameter-card h-100">
          <div class="anper-parameter-card__header">
            <h5 class="mb-0">{{ $exposureGroup['title'] }}</h5>
          </div>
          <div class="px-3 py-2">
            <ul class="anper-exposure-list">
              @foreach ($exposureGroup['items'] as $exposure)
                <li class="anper-exposure-list__item">
                  <span class="anper-exposure-list__risk">{{ $exposure['risk'] }}</span>
                  <span class="anper-exposure-list__value">Rp {{ number_format($exposure['value'], 0, ',', '.') }}</span>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    @endforeach
  </div>
  @endunless
</section>
