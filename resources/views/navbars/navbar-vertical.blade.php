<!-- ---- navbar-vertical starts------------ -->
<nav class="navbar navbar-light navbar-glass navbar-vertical navbar-expand-xl">
  <script>
  var navbarStyle = localStorage.getItem("navbarStyle");
  if (navbarStyle && navbarStyle !== 'transparent') {
    document.querySelector('.navbar-vertical').classList.add(`navbar-${navbarStyle}`);
  }
  </script>
  <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
    <div class="brand-wrapper">
      <a class="navbar-brand" href="{{ route('home') }}">
        <!-- <div class="app-brand primary">
          @include('partials.logo')
        </div>
        <div class="app-brand secondary">
          @include('partials.logo-secondary')
        </div> -->
      </a>
      <div class="toggle-icon-wrapper">
        <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip"
          data-bs-placement="left" title="Toggle Navigation">
          <span class="navbar-toggle-icon">
            <i class="bx bx-chevron-left align-middle"></i>
          </span>
        </button>
      </div>
    </div>
    <div class="navbar-vertical-content scrollbar">
      <ul class="navbar-nav flex-column" id="navbarVerticalNav">
        <!-- Dashboard Menu Start -->
        <li class="nav-item">
          @php $shouldDashboardOpen = in_array(url()->current(), [route('home'), route('home', 'universitas'),
          route('home', ['data' => 'fakultas']), route('home', ['data' => 'biro'])]) @endphp
          <a class="nav-link dropdown-indicator {{ $shouldDashboardOpen ? '' : 'collapsed' }} {{ request()->is('dashboard-unit') || request()->is('dashboard-proyek') || request()->is('dashboard-anper') || request()->is('dashboard-kri') ? 'active' : '' }}"
            href="#dashboard" role="button" data-bs-toggle="collapse"
            aria-expanded="{{ $shouldDashboardOpen ? 'true' : 'false' }}" aria-controls="dashboard">
            <div class="d-flex align-items-center">
              <i class="menu-icon tf-icons bx bx-tachometer"></i>
              <span class="nav-link-text">Dashboard</span>
            </div>
          </a>
          <ul
            class="nav collapse {{ $shouldDashboardOpen ? 'show' : '' }} {{ request()->is('dashboard-unit') || request()->is('dashboard-proyek') || request()->is('dashboard-anper') || request()->is('dashboard-kri') ? 'show' : '' }}"
            id="dashboard">
            @can('dashboard_universitas')
            <li class="nav-item">
              <a class="nav-link {{ (url()->current() == route('home', 'universitas')) ? 'active' : '' }}"
                href="{{ route('home', 'universitas') }}">
                <span class="nav-link-text">Corporate</span>
              </a>
            </li>
            @endcan
            <li class="nav-item">
              <a class="nav-link {{ request()->is('dashboard-unit') ? 'active' : '' }}" href="/dashboard-unit">
                <span class="nav-link-text">Unit</span>
              </a>
            </li>
            <li class="nav-item"><a class="nav-link {{ request()->is('dashboard-proyek') ? 'active' : '' }}"
                href="/dashboard-proyek">
                <span class="nav-link-text">Proyek</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('dashboard-anper') ? 'active' : '' }} " href="/dashboard-anper">
                <span class="nav-link-text">Anak Perusahaan</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('dashboard-kri') ? 'active' : '' }} " href="/dashboard-kri">
                <span class="nav-link-text">KRI</span>
              </a>
            </li>
          </ul>
        </li>
        <!-- Dashboard Menu End -->

        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->is('risk-register-unit') ? 'active' : '' }}" href="/risk-register-unit/periods"
            role="button" data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon">
              <i class="menu-icon tf-icons bx bx-grid-alt"></i>
              <span class="nav-link-text">Risk Register Divisi</span>
            </span>
          </a>
        </li>

        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('unit-led.index') ? 'active' : '' }}"
            href="{{route('unit-led.index')}}" role="button" data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Loss Event Divisi</span>
            </span>
          </a>
        </li>

        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('laporan.unit') ? 'active' : '' }}"
            href="{{route('laporan.unit')}}" role="button" data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Laporan Divisi</span>
            </span>
          </a>
        </li>

        <!-- Ranking Risiko Menu Start -->
        @can('project_periode_list')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('project-periode-list.index') ? 'active' : '' }}"
            href="{{route('project-periode-list.index')}}" role="button" data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Project List</span>
            </span>
          </a>
        </li>
        @endcan

        {{-- @can('project_led_list')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('project-led.index') ? 'active' : '' }}"
            href="{{route('project-led.index')}}" role="button" data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Loss Event Project</span>
            </span>
          </a>
        </li>
        @endcan --}}

        @can('project_periode_list')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('laporan.project') ? 'active' : '' }}"
            href="{{route('laporan.project')}}" role="button" data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Laporan Project</span>
            </span>
          </a>
        </li>
        @endcan

        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('metrik-strategi-risiko.index') ? 'active' : '' }}" href="{{route('metrik-strategi-risiko.index')}}" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Metrik Strategi Risiko</span>
            </span>
          </a>
        </li>
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('sasaran-strategi.index') ? 'active' : '' }}" href="{{route('sasaran-strategi.index')}}" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Seleksi Sasaran & Strategi Bisnis</span>
            </span>
          </a>
        </li>
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('penilaian-rmi.index') ? 'active' : '' }}" href="{{route('penilaian-rmi.index')}}" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Penilaian RMI</span>
            </span>
          </a>
        </li>
        @can('kuesioner')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('kuesioner.index') ? 'active' : '' }}" href="{{route('kuesioner.index')}}" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Kuesioner</span>
            </span>
          </a>
        </li>
        @endcan
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('ict.index') ? 'active' : '' }}" href="{{route('ict.index')}}" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Internal Control Testing</span>
            </span>
          </a>
        </li>

        @can('manajemen_master')
        <div class="row navbar-vertical-label-wrapper">
          <div class="col-auto navbar-vertical-label">
            Settings
          </div>
          <div class="col ps-0">
            <hr class="mb-0 navbar-vertical-divider" />
          </div>
        </div>

        <!-- Master Data Start -->
        <li class="nav-item">
          <a class="nav-link dropdown-indicator {{ request()->is('peristiwa-risiko') ||  request()->is('rencana-kegiatan') ||  request()->is('master-risiko') ||  request()->is('tck') ||  request()->is('area-dampak') ||  request()->is('skala-dampak') ||  request()->is('skala-probabilitas') ||  request()->is('skala-probabilitas') ||  request()->is('sikap-risiko') ||  request()->is('periode') ||  request()->is('kategori-risiko') ||  request()->is('kategori-risiko') ||  request()->is('jenis-risiko') ||  request()->is('peristiwa-risiko') ||  request()->is('unit-type') ||  request()->is('unit') ||  request()->is('roles') || request()->is('users') || request()->routeIs(['project-divisi.index', 'project-sektor.index', 'projects.index', 'master-kri.index', 'penilaian-efektivitas-kontrol.index', 'kontrol-eksisting.index', 'jenis-kontrol-eksisting.index', 'jenis-rencana-perlakuan-risiko.index', 'opsi-perlakuan-risiko.index', 'project-type.index', 'project-location.index', 'rmi-period.index', 'question.index', 'measurement-parameter.index', 'jabatan.index']) ? 'active' : '' }}"
            href="#master-data" role="button" data-bs-toggle="collapse" aria-expanded="false"
            aria-controls="master-data">
            <div class="d-flex align-items-center">
              <i class="menu-icon tf-icons bx bx-traffic-cone"></i>
              <span class="nav-link-text">Master Data</span>
            </div>
          </a>
            <ul
            class="nav collapse {{ request()->is(['peristiwa-risiko', 'rencana-kegiatan', 'master-risiko', 'tck', 'area-dampak', 'skala-dampak', 'skala-probabilitas', 'sikap-risiko', 'periode', 'kategori-risiko', 'jenis-risiko', 'unit-type', 'unit', 'roles', 'users']) || request()->routeIs(['project-divisi.index', 'project-sektor.index', 'projects.index', 'master-kri.index', 'penilaian-efektivitas-kontrol.index', 'kontrol-eksisting.index', 'jenis-kontrol-eksisting.index', 'jenis-rencana-perlakuan-risiko.index', 'opsi-perlakuan-risiko.index', 'project-type.index', 'project-location.index', 'rmi-period.index', 'question.index', 'measurement-parameter.index', 'jabatan.index']) ? 'show' : '' }}"
            id="master-data">

            @can('project_divisi_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('project-divisi.index') ? 'active' : '' }}"
                href="{{ route('project-divisi.index') }}">
                <span class="nav-link-text">Project Divisi</span>
              </a>
            </li>
            @endcan

            @can('project_sektor_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('project-sektor.index') ? 'active' : '' }}"
                href="{{ route('project-sektor.index') }}">
                <span class="nav-link-text">Project Sektor</span>
              </a>
            </li>
            @endcan

            @can('project_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}"
                href="{{ route('projects.index') }}">
                <span class="nav-link-text">Project</span>
              </a>
            </li>
            @endcan

            @can('master_kri_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('master-kri.index') ? 'active' : '' }}"
                href="{{ route('master-kri.index') }}">
                <span class="nav-link-text">Master KRI</span>
              </a>
            </li>
            @endcan

            @can('jenis_kontrol_eksisting_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('jenis-kontrol-eksisting.index') ? 'active' : '' }}"
                href="{{ route('jenis-kontrol-eksisting.index') }}">
                <span class="nav-link-text">Jenis Eksisting Kontrol</span>
              </a>
            </li>
            @endcan

            @can('kontrol_eksisting_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('kontrol-eksisting.index') ? 'active' : '' }}"
                href="{{ route('kontrol-eksisting.index') }}">
                <span class="nav-link-text">Eksisting Kontrol</span>
              </a>
            </li>
            @endcan

            @can('penilaian_efektivitas_kontrol_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('penilaian-efektivitas-kontrol.index') ? 'active' : '' }}"
                href="{{ route('penilaian-efektivitas-kontrol.index') }}">
                <span class="nav-link-text">Penilaian Efektivitas Kontrol</span>
              </a>
            </li>
            @endcan

            @can('jenis_rencana_perlakuan_risiko_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('jenis-rencana-perlakuan-risiko.index') ? 'active' : '' }}"
                href="{{ route('jenis-rencana-perlakuan-risiko.index') }}">
                <span class="nav-link-text">Jenis Rencana Perlakuan Risiko</span>
              </a>
            </li>
            @endcan

            @can('opsi_perlakuan_risiko_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('opsi-perlakuan-risiko.index') ? 'active' : '' }}"
                href="{{ route('opsi-perlakuan-risiko.index') }}">
                <span class="nav-link-text">Opsi Perlakuan Risiko</span>
              </a>
            </li>
            @endcan

            @can('project_type_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('project-type.index') ? 'active' : '' }}"
                href="{{ route('project-type.index') }}">
                <span class="nav-link-text">Tipe Proyek</span>
              </a>
            </li>
            @endcan

            @can('project_location_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('project-location.index') ? 'active' : '' }}"
                href="{{ route('project-location.index') }}">
                <span class="nav-link-text">Lokasi Proyek</span>
              </a>
            </li>
            @endcan

            <li class="nav-item">
              <a class="nav-link {{ request()->is('periode') ? 'active' : '' }}" href="/periode">
                <span class="nav-link-text">Periode Risiko</span>
              </a>
            </li>
            @can('rmi_period_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('rmi-period.index') ? 'active' : '' }}" href="{{ route('rmi-period.index') }}">
                <span class="nav-link-text">Periode RMI</span>
              </a>
            </li>
            @endcan

            @can('question_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('question.index') ? 'active' : '' }}" href="{{ route('question.index') }}">
                <span class="nav-link-text">Pertanyaan Survey</span>
              </a>
            </li>
            @endcan
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('measurement-parameter.index') ? 'active' : '' }}"
                href="{{ route('measurement-parameter.index') }}">
                <span class="nav-link-text">Parameter Pengukuran</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}"
                href="{{ route('users.index') }}">
                <span class="nav-link-text">Manage User</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}"
                href="{{ route('roles.index') }}">
                <span class="nav-link-text">Manage Role</span>
              </a>
            </li>
            @can('jabatan_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('jabatan.index') ? 'active' : '' }}"
                href="{{ route('jabatan.index') }}">
                <span class="nav-link-text">Jabatan</span>
              </a>
            </li>
            @endcan
            {{--
            <li class="nav-item">
              <a class="nav-link {{ request()->is('tck') ? 'active' : '' }}" href="/tck">
            <span class="nav-link-text">KPI/Sasaran</span>
            </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('area-dampak') ? 'active' : '' }}" href="/area-dampak">
            <span class="nav-link-text">Area Dampak</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('skala-dampak') ? 'active' : '' }}" href="/skala-dampak">
            <span class="nav-link-text">Skala Dampak</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('skala-probabilitas') ? 'active' : '' }}" href="/skala-probabilitas">
            <span class="nav-link-text">Skala Probabilitas</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('sikap-risiko') ? 'active' : '' }}" href="/sikap-risiko">
            <span class="nav-link-text">Sikap Risiko</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('kategori-risiko') ? 'active' : '' }}" href="/kategori-risiko">
            <span class="nav-link-text">Kategori Risiko</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('jenis-risiko') ? 'active' : '' }}" href="/jenis-risiko">
            <span class="nav-link-text">Jenis Risiko</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('peristiwa-risiko') ? 'active' : '' }}" href="/peristiwa-risiko">
            <span class="nav-link-text">Peristiwa Risiko</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('rencana-kegiatan') ? 'active' : '' }}" href="/rencana-kegiatan">
            <span class="nav-link-text">Rencana Kegiatan</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->is('master-risiko') ? 'active' : '' }}" href="/master-risiko">
            <span class="nav-link-text">Master Risiko</span>
          </a>
        </li>
        --}}
      </ul>
      </li>
      <!-- Master Data End -->

      @endcan
      {{-- @can('risk_map_setting') --}}
      <li class="nav-item single-indicator">
        <a class="nav-link {{ request()->is('risk-map-setting') ? 'active' : '' }}" href="/risk-map-setting"
          role="button" data-bs-toggle="" aria-expanded="false">
          <span class="nav-link-icon">
            <i class="menu-icon tf-icons bx bx-grid-alt"></i>
            <span class="nav-link-text">Risk Map Setting</span>
          </span>
        </a>
      </li>
      {{-- @endcan --}}


      <div class="row navbar-vertical-label-wrapper">
        <div class="col-auto navbar-vertical-label">
          Documents
        </div>
        <div class="col ps-0">
          <hr class="mb-0 navbar-vertical-divider" />
        </div>
      </div>

      <!-- <li class="nav-item single-indicator">
        <a class="nav-link" href="#" target="blank">
          <span class="nav-link-icon">
            <i class="menu-icon tf-icons bx bx-archive"></i>
            <span class="nav-link-text">Panduan Penggunaan</span>
          </span>
        </a>
      </li> -->
        @can('backups.index')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('backups.index') ? 'active' : '' }}" href="{{ route('backups.index') }}">
            <span class="nav-link-icon">
              <i class="menu-icon tf-icons bx bx-cloud-download"></i>
              <span class="nav-link-text">Backup & Restore</span>
            </span>
          </a>
        </li>
        @endcan
      </ul>

    </div>
  </div>
</nav>


<!-- ----- navbar-vertical end -------------- -->
