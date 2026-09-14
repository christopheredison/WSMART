@extends('layouts.default')

@section('title', 'Dashboard Summary')

@push('styles')
<style>
  .dashboard-summary-tab-content {
    min-height: 360px;
  }

  .period-picker {
    position: relative;
  }

  .dashboard-summary-filter-card {
    z-index: 20;
    overflow: visible !important;
  }

  .dashboard-summary-content-card {
    z-index: 1;
  }

  .period-picker-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    min-height: 42px;
    text-align: left;
  }

  .period-picker-menu {
    position: absolute;
    z-index: 1080;
    top: calc(100% + 0.5rem);
    left: 0;
    width: min(100%, 360px);
    min-width: 300px;
    padding: 1rem;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.75rem;
    background-color: var(--bs-body-bg);
    box-shadow: var(--bs-box-shadow-lg);
  }

  .period-picker-header {
    display: grid;
    grid-template-columns: 2.5rem 1fr 2.5rem;
    align-items: center;
    margin-bottom: 0.75rem;
  }

  .period-picker-year {
    color: var(--bs-heading-color);
    font-weight: 600;
    text-align: center;
  }

  .period-picker-nav {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    padding: 0;
    border: 0;
    border-radius: 50%;
    color: var(--bs-body-color);
    background: transparent;
  }

  .period-picker-nav:hover:not(:disabled) {
    color: var(--bs-primary);
    background-color: var(--bs-primary-bg-subtle);
  }

  .period-picker-nav:disabled {
    opacity: 0.3;
  }

  .period-picker-months {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
  }

  .period-picker-month {
    padding: 0.75rem 0.5rem;
    border: 0;
    border-radius: 0.5rem;
    color: var(--bs-body-color);
    background: transparent;
  }

  .period-picker-month:hover:not(:disabled):not(.active) {
    color: var(--bs-primary);
    background-color: var(--bs-primary-bg-subtle);
  }

  .period-picker-month.active {
    color: var(--bs-white);
    background-color: var(--bs-primary);
  }

  .period-picker-month:disabled {
    color: var(--bs-secondary-color);
    opacity: 0.35;
  }

  .dashboard-summary-tabs {
    display: flex;
    width: 100%;
    margin: 0;
    border: 1px solid var(--bs-border-color);
    border-bottom: 3px solid var(--bs-primary);
    border-radius: 0.5rem 0.5rem 0 0;
    background-color: var(--bs-body-bg);
  }

  .dashboard-summary-tabs .nav-item {
    flex: 1 1 0;
  }

  .dashboard-summary-tabs .nav-item + .nav-item {
    border-left: 1px solid var(--bs-border-color);
  }

  .dashboard-summary-tabs .nav-link {
    position: relative;
    width: 100%;
    padding: 0.875rem 1rem;
    border: 0;
    border-radius: 0;
    color: var(--bs-secondary-color);
    background-color: transparent;
    font-weight: 600;
    transition: color 0.2s ease, background-color 0.2s ease;
  }

  .dashboard-summary-tabs .nav-link:hover:not(.active) {
    color: var(--bs-primary);
    background-color: var(--bs-primary-bg-subtle);
  }

  .dashboard-summary-tabs .nav-link.active {
    color: var(--bs-white);
    background-color: var(--bs-primary);
  }

  .dashboard-summary-tabs .nav-link.active::after {
    position: absolute;
    bottom: -0.6rem;
    left: 50%;
    width: 0;
    height: 0;
    border-top: 0.6rem solid var(--bs-primary);
    border-right: 0.6rem solid transparent;
    border-left: 0.6rem solid transparent;
    content: "";
    transform: translateX(-50%);
  }

  .dashboard-summary-tabs .nav-link:focus-visible {
    z-index: 1;
    outline: 3px solid var(--bs-primary-border-subtle);
    outline-offset: -3px;
  }

  @media (max-width: 575.98px) {
    .dashboard-summary-tabs .nav-link {
      padding-right: 0.5rem;
      padding-left: 0.5rem;
      font-size: 0.875rem;
    }
  }
</style>
@endpush

@section('dashboard')
<div class="row g-3">
  <div class="col-12">
    <div class="card border-0 shadow-sm dashboard-summary-filter-card">
      <div class="card-header bg-white border-bottom">
        <h5 class="mb-1">Filtering Data</h5>
        <p class="text-muted mb-0">Gunakan filter berikut untuk menyesuaikan data dashboard.</p>
      </div>

      <div class="card-body">
        <form id="dashboard-summary-filter" method="GET" action="{{ route('dashboard-summary') }}">
          <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label" for="period-picker-toggle">Periode (Bulan dan Tahun)</label>
              <div class="period-picker" id="period-picker">
                <input type="hidden" name="periode" id="periode" value="{{ $selectedPeriod }}">
                <button
                  type="button"
                  class="form-control period-picker-toggle"
                  id="period-picker-toggle"
                  aria-haspopup="dialog"
                  aria-expanded="false"
                  @disabled($periodes->isEmpty())
                >
                  <span id="period-picker-display">{{ $selectedPeriodDisplay }}</span>
                  <span class="bx bx-calendar fs-5" aria-hidden="true"></span>
                </button>

                <div
                  class="period-picker-menu d-none"
                  id="period-picker-menu"
                  role="dialog"
                  aria-label="Pilih bulan dan tahun"
                >
                  <div class="period-picker-header">
                    <button type="button" class="period-picker-nav" id="period-picker-prev" aria-label="Tahun sebelumnya">
                      <span class="bx bx-chevron-left fs-4" aria-hidden="true"></span>
                    </button>
                    <div class="period-picker-year" id="period-picker-year"></div>
                    <button type="button" class="period-picker-nav" id="period-picker-next" aria-label="Tahun berikutnya">
                      <span class="bx bx-chevron-right fs-4" aria-hidden="true"></span>
                    </button>
                  </div>
                  <div class="period-picker-months" id="period-picker-months"></div>
                </div>
              </div>
              @if ($periodes->isEmpty())
                <div class="form-text text-danger">Tidak ada periode aktif.</div>
              @endif
            </div>

            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label" for="korporasi">Korporasi</label>
              <select class="form-select" name="korporasi" id="korporasi">
                <option value="induk" @selected($selectedKorporasi === 'induk')>Induk</option>
                @if ($apUnits->isNotEmpty())
                  <optgroup label="Anak Perusahaan">
                    @foreach ($apUnits as $apUnit)
                      <option value="{{ $apUnit->id }}" @selected((string) $apUnit->id === $selectedKorporasi)>
                        {{ $apUnit->name }}
                      </option>
                    @endforeach
                  </optgroup>
                @endif
              </select>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label" for="unit_kerja">Unit Kerja (Divisi)</label>
              <select class="form-select" name="divisi" id="unit_kerja" @disabled($isApSelected)>
                <option value="none" @selected($selectedDivision === 'none')>None</option>
                <option value="all" @selected($selectedDivision === 'all')>Semua</option>
                @foreach ($divisions as $division)
                  <option value="{{ $division->id }}" @selected((string) $division->id === $selectedDivision)>
                    {{ $division->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label" for="proyek">Proyek</label>
              <select
                class="form-select"
                name="proyek"
                id="proyek"
                @disabled($isApSelected || $selectedDivision === 'none')
              >
                <option value="none" @selected($selectedProject === 'none')>None</option>
                <option value="all" @selected($selectedProject === 'all')>Semua</option>
                @foreach ($projects as $project)
                  <option value="{{ $project->id }}" @selected((string) $project->id === $selectedProject)>
                    {{ $project->project_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('dashboard-summary') }}" class="btn btn-outline-secondary">Reset</a>
            <button type="submit" class="btn btn-primary">Terapkan Filter</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card border-0 shadow-sm dashboard-summary-content-card">
      <div class="card-header bg-white border-bottom">
        <h5 class="mb-3">{{ $dashboardTitle }}</h5>

        @php
          $layerLabels = [
            1 => 'Manajemen Kinerja Berbasis Risiko',
            2 => 'Profil Risiko',
            3 => 'Loss Event Database',
            4 => 'Parameter Risiko',
          ];
        @endphp

        <ul class="nav dashboard-summary-tabs" id="dashboard-summary-tabs" role="tablist">
          @foreach (range(1, 4) as $layer)
            <li class="nav-item" role="presentation">
              <button
                class="nav-link {{ $layer === 1 ? 'active' : '' }}"
                id="layer-{{ $layer }}-tab"
                data-bs-toggle="tab"
                data-bs-target="#layer-{{ $layer }}"
                type="button"
                role="tab"
                aria-controls="layer-{{ $layer }}"
                aria-selected="{{ $layer === 1 ? 'true' : 'false' }}"
              >
                {{ $layerLabels[$layer] }}
              </button>
            </li>
          @endforeach
        </ul>
      </div>

      <div class="card-body">
        <div class="tab-content dashboard-summary-tab-content" id="dashboard-summary-tab-content">
          @foreach (range(1, 4) as $layer)
            <div
              class="tab-pane fade {{ $layer === 1 ? 'show active' : '' }}"
              id="layer-{{ $layer }}"
              role="tabpanel"
              aria-labelledby="layer-{{ $layer }}-tab"
              tabindex="0"
            >
              @include("dashboard-summary.contents.{$dashboardCategory}.layer-{$layer}")
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const periodPicker = document.getElementById('period-picker');
    const periodToggle = document.getElementById('period-picker-toggle');
    const periodMenu = document.getElementById('period-picker-menu');
    const periodInput = document.getElementById('periode');
    const periodDisplay = document.getElementById('period-picker-display');
    const periodYearLabel = document.getElementById('period-picker-year');
    const periodMonths = document.getElementById('period-picker-months');
    const periodPrev = document.getElementById('period-picker-prev');
    const periodNext = document.getElementById('period-picker-next');
    const korporasiSelect = document.getElementById('korporasi');
    const divisionSelect = document.getElementById('unit_kerja');
    const projectSelect = document.getElementById('proyek');
    const projectsUrl = @json(route('dashboard-summary.projects'));
    const activePeriodYears = @json($periodes->pluck('tahun')->map(fn ($year) => (int) $year)->sort()->values());
    const maximumPeriod = @json($maximumPeriod);
    const monthNames = [
      'Januari', 'Februari', 'Maret',
      'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September',
      'Oktober', 'November', 'Desember',
    ];
    let displayedPeriodYear = Number((periodInput.value || maximumPeriod).slice(0, 4));

    const renderPeriodPicker = function () {
      const selectedPeriod = periodInput.value;
      const yearIndex = activePeriodYears.indexOf(displayedPeriodYear);
      periodYearLabel.textContent = displayedPeriodYear;
      periodPrev.disabled = yearIndex <= 0;
      periodNext.disabled = yearIndex === -1 || yearIndex >= activePeriodYears.length - 1;
      periodMonths.innerHTML = '';

      monthNames.forEach(function (monthName, index) {
        const month = String(index + 1).padStart(2, '0');
        const periodValue = `${displayedPeriodYear}-${month}`;
        const button = document.createElement('button');

        button.type = 'button';
        button.className = 'period-picker-month';
        button.textContent = monthName.substring(0, 3);
        button.disabled = periodValue > maximumPeriod;
        button.classList.toggle('active', periodValue === selectedPeriod);
        button.setAttribute('aria-label', `${monthName} ${displayedPeriodYear}`);
        button.setAttribute('aria-pressed', periodValue === selectedPeriod ? 'true' : 'false');

        button.addEventListener('click', function () {
          periodInput.value = periodValue;
          periodDisplay.textContent = `${monthName} ${displayedPeriodYear}`;
          periodMenu.classList.add('d-none');
          periodToggle.setAttribute('aria-expanded', 'false');
          periodToggle.focus();
        });

        periodMonths.appendChild(button);
      });
    };

    if (activePeriodYears.length > 0) {
      periodToggle.addEventListener('click', function () {
        const willOpen = periodMenu.classList.contains('d-none');
        periodMenu.classList.toggle('d-none');
        periodToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

        if (willOpen) {
          renderPeriodPicker();
        }
      });

      periodPrev.addEventListener('click', function () {
        const yearIndex = activePeriodYears.indexOf(displayedPeriodYear);
        if (yearIndex > 0) {
          displayedPeriodYear = activePeriodYears[yearIndex - 1];
          renderPeriodPicker();
        }
      });

      periodNext.addEventListener('click', function () {
        const yearIndex = activePeriodYears.indexOf(displayedPeriodYear);
        if (yearIndex < activePeriodYears.length - 1) {
          displayedPeriodYear = activePeriodYears[yearIndex + 1];
          renderPeriodPicker();
        }
      });

      document.addEventListener('click', function (event) {
        if (!periodPicker.contains(event.target)) {
          periodMenu.classList.add('d-none');
          periodToggle.setAttribute('aria-expanded', 'false');
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !periodMenu.classList.contains('d-none')) {
          periodMenu.classList.add('d-none');
          periodToggle.setAttribute('aria-expanded', 'false');
          periodToggle.focus();
        }
      });
    }

    const resetProjectOptions = function () {
      projectSelect.innerHTML = '';
      projectSelect.add(new Option('None', 'none'));
      projectSelect.add(new Option('Semua', 'all'));
    };

    const syncFilterState = async function () {
      const isApSelected = korporasiSelect.value !== 'induk';

      divisionSelect.disabled = isApSelected;
      if (isApSelected) {
        divisionSelect.value = 'none';
      }

      const hasSpecificDivision = !isApSelected
        && divisionSelect.value !== 'none'
        && divisionSelect.value !== 'all';
      const hasAllDivisions = !isApSelected && divisionSelect.value === 'all';

      projectSelect.disabled = !(hasSpecificDivision || hasAllDivisions);
      resetProjectOptions();

      if (hasAllDivisions) {
        projectSelect.value = 'none';
        return;
      }

      if (!hasSpecificDivision) {
        projectSelect.value = 'none';
        return;
      }

      projectSelect.disabled = true;

      try {
        const response = await fetch(`${projectsUrl}?division_id=${encodeURIComponent(divisionSelect.value)}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        if (!response.ok) {
          throw new Error('Gagal memuat daftar proyek.');
        }

        const projects = await response.json();
        projects.forEach(function (project) {
          projectSelect.add(new Option(project.project_name, project.id));
        });
      } catch (error) {
        console.error(error);
      } finally {
        projectSelect.disabled = false;
      }
    };

    korporasiSelect.addEventListener('change', syncFilterState);
    divisionSelect.addEventListener('change', syncFilterState);
  });
</script>
@endpush
