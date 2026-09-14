@once
  @push('styles')
    <style>
      .division-led__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .division-led__identity {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        color: var(--bs-info);
        background-color: var(--bs-info-bg-subtle);
        font-size: 0.78rem;
        font-weight: 700;
      }

      .division-led-card {
        height: 100%;
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .division-led-card__header {
        display: flex;
        min-height: 82px;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.125rem;
        border-bottom: 1px dashed var(--bs-border-color);
      }

      .division-led-card__title {
        margin: 0;
        color: var(--bs-heading-color);
        font-size: 1rem;
        font-weight: 700;
      }

      .division-led-card__total {
        color: var(--bs-danger);
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
      }

      .division-led-table {
        min-width: 680px;
        margin-bottom: 0;
        font-size: 0.8rem;
      }

      .division-led-table th {
        padding: 0.75rem 1rem;
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        font-size: 0.67rem;
        letter-spacing: 0.03rem;
        text-transform: uppercase;
        white-space: nowrap;
      }

      .division-led-table td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
      }

      .division-led-table__rank {
        width: 52px;
        color: var(--bs-secondary-color);
        text-align: center;
      }

      .division-led-table__project {
        min-width: 170px;
        font-weight: 600;
      }

      .division-led-table__event {
        min-width: 230px;
      }

      .division-led-table__loss {
        color: var(--bs-danger);
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .division-led-empty {
        display: grid;
        min-height: 230px;
        padding: 2rem;
        place-items: center;
        color: var(--bs-secondary-color);
        text-align: center;
      }

      .division-led-empty__icon {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 2rem;
        opacity: 0.55;
      }
    </style>
  @endpush
@endonce

@php
  $divisionName = $selectedDivisionModel?->name ?? 'Divisi Terpilih';
  $selectedYear = substr($selectedPeriod, 0, 4);

  $projectLossEvents = [
    ['project' => 'Proyek Jalan Tol Utara', 'name' => 'Pekerjaan ulang struktur akibat hasil uji mutu', 'loss' => 8750000000],
    ['project' => 'Proyek Bendungan Cipta', 'name' => 'Kerusakan alat berat utama', 'loss' => 6425000000],
    ['project' => 'Proyek Gedung Sentra', 'name' => 'Keterlambatan pengiriman material façade', 'loss' => 3150000000],
    ['project' => 'Proyek Pelabuhan Timur', 'name' => 'Penghentian pekerjaan akibat cuaca ekstrem', 'loss' => 2280000000],
    ['project' => 'Proyek Transit Metropolitan', 'name' => 'Utilitas eksisting tidak terpetakan', 'loss' => 1450000000],
  ];

  $divisionLossEvents = [
    ['name' => 'Tidak tercapainya target penjualan periode berjalan', 'loss' => 282663285],
    ['name' => 'Keterlambatan penerimaan pembayaran pelanggan', 'loss' => 215400000],
    ['name' => 'Gangguan operasional sistem pelaporan', 'loss' => 178250000],
    ['name' => 'Kehilangan material pada area penyimpanan', 'loss' => 96500000],
    ['name' => 'Klaim atas ketidaksesuaian administrasi kontrak', 'loss' => 78750000],
  ];

  $projectLossTotal = collect($projectLossEvents)->sum('loss');
  $divisionLossTotal = collect($divisionLossEvents)->sum('loss');
@endphp

<section class="dashboard-summary-layer division-led" data-dashboard-category="division" data-layer="3">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="division-led__title mb-1">Loss Event Database Divisi</h4>
      <p class="text-muted mb-0">Loss event proyek dan operasional {{ $divisionName }} periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <span class="division-led__identity">
        <i class="bx bx-buildings" aria-hidden="true"></i>{{ $divisionName }}
      </span>
      <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-xxl-6">
      <div class="division-led-card">
        <div class="division-led-card__header">
          <div>
            <h5 class="division-led-card__title">Top 5 Loss Event Proyek ({{ $selectedYear }})</h5>
            <p class="text-muted small mb-0">Proyek yang berada di {{ $divisionName }}.</p>
          </div>
          <span class="division-led-card__total">Total Rp {{ number_format($projectLossTotal, 0, ',', '.') }}</span>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover division-led-table">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Nama Proyek</th>
                <th>Nama Kejadian</th>
                <th class="text-end">Nilai Kerugian</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($projectLossEvents as $event)
                <tr>
                  <td class="division-led-table__rank">{{ $loop->iteration }}</td>
                  <td class="division-led-table__project">{{ $event['project'] }}</td>
                  <td class="division-led-table__event">{{ $event['name'] }}</td>
                  <td class="division-led-table__loss">Rp {{ number_format($event['loss'], 0, ',', '.') }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-5">Tidak ada data.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xxl-6">
      <div class="division-led-card">
        <div class="division-led-card__header">
          <div>
            <h5 class="division-led-card__title">Top 10 Loss Event Operasional Divisi ({{ $selectedYear }})</h5>
            <p class="text-muted small mb-0">{{ $divisionName }} · diurutkan berdasarkan nilai kerugian terbesar.</p>
          </div>
          <span class="division-led-card__total">Total Rp {{ number_format($divisionLossTotal, 0, ',', '.') }}</span>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-hover division-led-table">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Nama Kejadian</th>
                <th class="text-end">Nilai Kerugian</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($divisionLossEvents as $event)
                <tr>
                  <td class="division-led-table__rank">{{ $loop->iteration }}</td>
                  <td class="division-led-table__event">{{ $event['name'] }}</td>
                  <td class="division-led-table__loss">Rp {{ number_format($event['loss'], 0, ',', '.') }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-5">Tidak ada data.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
