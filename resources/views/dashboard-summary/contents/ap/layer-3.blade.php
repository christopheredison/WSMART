@once
  @push('styles')
    <style>
      .anper-led__title {
        color: var(--bs-primary);
        font-weight: 700;
      }

      .anper-led-card {
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background-color: var(--bs-body-bg);
        box-shadow: 0 0.25rem 1rem rgba(23, 32, 51, 0.06);
      }

      .anper-led-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.125rem;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .anper-led-table {
        min-width: 680px;
        margin-bottom: 0;
        font-size: 0.8rem;
      }

      .anper-led-table th {
        padding: 0.8rem 1rem;
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        font-size: 0.68rem;
        letter-spacing: 0.03rem;
        text-transform: uppercase;
      }

      .anper-led-table td {
        padding: 0.8rem 1rem;
        vertical-align: middle;
      }

      .anper-led-table__rank {
        width: 56px;
        color: var(--bs-secondary-color);
        text-align: center;
      }

      .anper-led-table__event {
        color: var(--bs-heading-color);
        font-weight: 600;
      }

      .anper-led-table__loss {
        color: var(--bs-danger);
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
      }

      .anper-led-total {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 999px;
        color: var(--bs-danger);
        background-color: var(--bs-danger-bg-subtle);
        font-size: 0.75rem;
        font-weight: 700;
      }
    </style>
  @endpush
@endonce

@php
  $apName = $selectedApUnit?->name ?? 'Anak Perusahaan';
  $selectedYear = substr($selectedPeriod, 0, 4);
  $apLossEvents = [
    ['name' => 'Keterlambatan pembayaran dari pelanggan utama', 'loss' => 6850000000],
    ['name' => 'Kerusakan mesin produksi utama', 'loss' => 4275000000],
    ['name' => 'Pekerjaan ulang akibat ketidaksesuaian mutu', 'loss' => 3125000000],
    ['name' => 'Klaim pelanggan atas keterlambatan pengiriman', 'loss' => 2780000000],
    ['name' => 'Gangguan pasokan bahan baku strategis', 'loss' => 1965000000],
    ['name' => 'Kecelakaan kerja pada area produksi', 'loss' => 875000000],
    ['name' => 'Kerusakan material selama penyimpanan', 'loss' => 640000000],
    ['name' => 'Gangguan sistem informasi operasional', 'loss' => 425000000],
  ];
  $apLossTotal = collect($apLossEvents)->sum('loss');
@endphp

<section class="dashboard-summary-layer anper-led" data-dashboard-category="ap" data-layer="3">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h4 class="anper-led__title mb-1">Loss Event Database Anak Perusahaan</h4>
      <p class="text-muted mb-0">Daftar kerugian utama {{ $apName }} periode {{ $selectedPeriodDisplay }}.</p>
    </div>
    <span class="badge bg-primary-subtle text-primary px-3 py-2">Data Dummy</span>
  </div>

  <div class="anper-led-card">
    <div class="anper-led-card__header">
      <div>
        <h5 class="mb-1">Top 10 Loss Event Anak Perusahaan ({{ $selectedYear }})</h5>
        <p class="text-muted small mb-0">{{ $apName }} · diurutkan berdasarkan nilai kerugian terbesar.</p>
      </div>
      <span class="anper-led-total">
        Total Rp {{ number_format($apLossTotal, 0, ',', '.') }}
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-hover anper-led-table">
        <thead>
          <tr>
            <th class="text-center">#</th>
            <th>Nama Kejadian</th>
            <th class="text-end">Nilai Kerugian</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($apLossEvents as $event)
            <tr>
              <td class="anper-led-table__rank">{{ $loop->iteration }}</td>
              <td class="anper-led-table__event">{{ $event['name'] }}</td>
              <td class="anper-led-table__loss">Rp {{ number_format($event['loss'], 0, ',', '.') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>
