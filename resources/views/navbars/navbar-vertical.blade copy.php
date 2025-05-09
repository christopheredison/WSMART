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
        <div class="app-brand primary">
          @include('partials.logo')
        </div>
        <div class="app-brand secondary">
          @include('partials.logo-secondary')
        </div>
      </a>
      <div class="toggle-icon-wrapper">
        <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip"
          data-bs-placement="left" title="Toggle Navigation">
          <span class="navbar-toggle-icon">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
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
            <div class="col ps-0">
              {{--
                    <a class="nav-link dropdown-indicator {{ request()->is('home*') ? 'active' : '' }}"
              href="#dashboard" role="button" data-bs-toggle="collapse"
              aria-expanded="{{ request()->is('dashboard*') ? 'true' : 'false' }}" aria-controls="dashboard">
              <div class="d-flex align-items-center">
                <i class="menu-icon tf-icons bx bx-tachometer"></i>
                <span class="nav-link-text">Dashboard</span>
              </div>
              </a>
              <ul class="nav collapse {{ request()->is('home*') ? 'show' : '' }}" id="dashboard">
                <li class="nav-item">
                  <a class="nav-link {{ request()->is('home') ? 'active' : '' }}" href="/home">
                    <span class="nav-link-text">Corporate</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ request()->is('fakultas') ? 'active' : '' }}" href="#">
                    <span class="nav-link-text">Unit</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ request()->is('direktorat-biro') ? 'active' : '' }}" href="#">
                    <span class="nav-link-text">Proyek</span>
                  </a>
                </li>
                --}}
            </div>
          </ul>
        </li>
        <!-- Dashboard Menu End -->
        
        
        <!-- Risk Governance Menu Start -->
        <li class="nav-item">
          <a class="nav-link dropdown-indicator {{ request()->is('peraturan-mwa') ||  request()->is('proses-manajemen-risiko') ||  request()->is('taksonomi-risiko') || request()->is('struktur-tata-kelola-risiko') ? 'active' : '' }}"
            href="#risk-governance" role="button" data-bs-toggle="collapse"
            aria-expanded="{{ request()->is('peraturan-mwa') ||  request()->is('proses-manajemen-risiko') ||  request()->is('taksonomi-risiko') || request()->is('struktur-tata-kelola-risiko') ? 'true' : 'false' }}"
            aria-controls="risk-governance">
            <div class="d-flex align-items-center">
              <i class="menu-icon tf-icons bx bx-pyramid"></i>
              <span class="nav-link-text">Risk Governance</span>
            </div>
          </a>
          <ul
            class="nav collapse {{ request()->is('peraturan-mwa') ||  request()->is('proses-manajemen-risiko') ||  request()->is('taksonomi-risiko') || request()->is('struktur-tata-kelola-risiko') ? 'show' : '' }}"
            id="risk-governance">
            <li class="nav-item">
              <a class="nav-link {{ request()->is('struktur-tata-kelola-risiko') ? 'active' : '' }}"
                href="/struktur-tata-kelola-risiko">
                <span class="nav-link-text">Struktur Tata Kelola Risiko</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('taksonomi-risiko') ? 'active' : '' }}" href="/taksonomi-risiko">
                <span class="nav-link-text">Taksonomi Risiko</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('proses-manajemen-risiko') ? 'active' : '' }}"
                href="/proses-manajemen-risiko">
                <span class="nav-link-text">Proses Manajemen Risiko</span>
              </a>
            </li>
            <!-- <li class="nav-item">
              <a class="nav-link {{ request()->is('peraturan-mwa') ? 'active' : '' }}" href="/peraturan-mwa">
                <span class="nav-link-text">Peraturan</span>
              </a>
            </li> -->
          </ul>
        </li>
        <!-- Risk Governance Menu End -->
        

        @can('risk_profile')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->route()->named('profil-risiko') ? 'active' : '' }}"
            href="{{ route('profil-risiko') }}">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-doughnut-chart"></i>
              <span class="nav-link-text">Profil Risiko</span>
            </span>
          </a>
        </li>
        @endcan

        @can('input_menu')
        <!-- Input Data Menu Start -->
        <li class="nav-item">
          @php $shouldInputDataOpen = in_array(url()->current(), [route('capaian-tck.index'),
          route('capaian-tkmru.index'), url('risk-register'), url('risk-monitoring'), url('loss-event-database'),
          url('strategi-risiko')])
          @endphp
          <a class="nav-link dropdown-indicator {{ $shouldInputDataOpen ? '' : 'collapsed' }}" href="#input-data"
            role="button" data-bs-toggle="collapse" aria-expanded="{{ $shouldInputDataOpen ? 'true' : 'false' }}"
            aria-controls="input-data">
            <div class="d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-layer-plus"></i>
              <span class="nav-link-text">Input Data</span>
              {{-- <span id="" class="badge bg-danger rounded-pill fw-medium ms-auto me-3">ROF</span> --}}
            </div>
          </a>
          <ul class="nav collapse {{ $shouldInputDataOpen ? 'show' : '' }}" id="input-data">

            @can('strategi_risiko')
            <li class="nav-item">
              <a class="nav-link {{ request()->is('strategi-risiko') ? 'active' : '' }}" href="/strategi-risiko">
                <span class="nav-link-text">Strategi Risiko</span>
              </a>
            </li>
            @endcan
            @can('risk_register_list')
            <li class="nav-item"><a class="nav-link {{ request()->is('risk-register') ? 'active' : '' }}"
                href="/risk-register">
                <span class="nav-link-text">Risk Register</span>
              </a>
            </li>
            @endcan
            @can('risk_monitoring_list')
            <li class="nav-item"><a class="nav-link {{ request()->is('risk-monitoring') ? 'active' : '' }}"
                href="/risk-monitoring">
                <span class="nav-link-text">Risk Monitoring</span>
              </a>
            </li>
            @endcan
            @can('lost_event_list')
            <li class="nav-item"><a class="nav-link {{ request()->is('loss-event-database') ? 'active' : '' }}"
                href="/loss-event-database">
                <span class="nav-link-text">Loss Event Database</span>
              </a>
            </li>
            @endcan
            @can('capaian_tck')
            <li class="nav-item">
              <a class="nav-link {{ url()->current() == route('capaian-tck.index') ? 'active' : '' }}" href="#">
                <span class="nav-link-text">Capaian TCK</span>
              </a>
            </li>
            @endcan
            @can('capaian_tkmru')
            <li class="nav-item"><a
                class="nav-link {{ url()->current() == route('capaian-tkmru.index') ? 'active' : '' }}" href="#">
                <span class="nav-link-text">Capaian TKMRU</span>
              </a>
            </li>
            @endcan
          </ul>
        </li>
        <!-- Input Data Menu End -->
        @endcan
        

        <!-- Ranking Risiko Menu Start -->
        @can('project_periode_list')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->routeIs('project-periode-list.index') ? 'active' : '' }}" href="{{route('project-periode-list.index')}}" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-dock-bottom"></i>
              <span class="nav-link-text">Project List</span>
            </span>
          </a>
        </li>
        @endcan
        <!-- Ranking Risiko Menu Start -->
        @can('ranking_risiko_view')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->is('risk-champion') ? 'active' : '' }}" href="/risk-champion" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-chart"></i>
              <span class="nav-link-text">Ranking Risiko</span>
              <span id="" class="badge bg-danger rounded-pill fw-medium ms-auto me-5">RC</span>
            </span>
          </a>
        </li>
        @endcan
        <!-- Ranking Risiko Menu End -->

        <!-- Prioritas Risiko Menu Start -->
        @can('prioritas_risiko_view')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->is('risk-owner') ? 'active' : '' }}" href="/risk-owner" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon d-flex align-items-center w-100">
              <i class="menu-icon tf-icons bx bx-bookmarks"></i>
              <span class="nav-link-text">Prioritas Risiko</span>
              <span id="" class="badge bg-danger rounded-pill fw-medium ms-auto me-5">RO</span>
            </span>
          </a>
        </li>
        @endcan
        <!-- Prioritas Risiko Menu End -->

        <!-- Ranking Risiko Menu Start -->
        @can('manajemen_master')
        <li class="nav-item single-indicator">
          <a class="nav-link {{ request()->is('universitas') ? 'active' : '' }}" href="/universitas" role="button"
            data-bs-toggle="" aria-expanded="false">
            <span class="nav-link-icon">
              <i class="menu-icon tf-icons bx bx-bar-chart"></i>
              <span class="nav-link-text">Ranking Risiko</span>
            </span>
          </a>
        </li>
        <!-- Ranking Risiko Menu End -->

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
          <a class="nav-link dropdown-indicator {{ request()->is('peristiwa-risiko') ||  request()->is('rencana-kegiatan') ||  request()->is('master-risiko') ||  request()->is('tck') ||  request()->is('area-dampak') ||  request()->is('skala-dampak') ||  request()->is('skala-probabilitas') ||  request()->is('skala-probabilitas') ||  request()->is('sikap-risiko') ||  request()->is('periode') ||  request()->is('kategori-risiko') ||  request()->is('kategori-risiko') ||  request()->is('jenis-risiko') ||  request()->is('peristiwa-risiko') ||  request()->is('unit-type') ||  request()->is('unit') ||  request()->is('roles') || request()->is('users') ? 'active' : '' }}"
            href="#master-data" role="button" data-bs-toggle="collapse" aria-expanded="false"
            aria-controls="master-data">
            <div class="d-flex align-items-center">
              <i class="menu-icon tf-icons bx bx-traffic-cone"></i>
              <span class="nav-link-text">Master Data</span>
            </div>
          </a>
            <ul
            class="nav collapse {{ request()->is(['peristiwa-risiko', 'rencana-kegiatan', 'master-risiko', 'tck', 'area-dampak', 'skala-dampak', 'skala-probabilitas', 'sikap-risiko', 'periode', 'kategori-risiko', 'jenis-risiko', 'unit-type', 'unit', 'roles', 'users']) || request()->routeIs(['project-divisi.index', 'project-sektor.index', 'projects.index', 'master-kri.index', 'penilaian-efektivitas-kontrol.index', 'kontrol-eksisting.index', 'jenis-kontrol-eksisting.index', 'jenis-rencana-perlakuan-risiko.index', 'opsi-perlakuan-risiko.index']) ? 'show' : '' }}"
            id="master-data">
            @can('manajemen_unit')
            <li class="nav-item">
              <a class="nav-link {{ request()->is('unit-type') ? 'active' : '' }}" href="/unit-type">
                <span class="nav-link-text">Unit Type</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('unit') ? 'active' : '' }}" href="/unit">
                <span class="nav-link-text">Unit</span>
              </a>
            </li>
            @endcan
            @can('project_divisi_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('project-divisi.index') ? 'active' : '' }}" href="{{ route('project-divisi.index') }}">
                <span class="nav-link-text">Project Divisi</span>
              </a>
            </li>
            @endcan
            @can('project_sektor_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('project-sektor.index') ? 'active' : '' }}" href="{{ route('project-sektor.index') }}">
                <span class="nav-link-text">Project Sektor</span>
              </a>
            </li>
            @endcan
            @can('project_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                <span class="nav-link-text">Project</span>
              </a>
            </li>
            @endcan
            @can('master_kri_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('master-kri.index') ? 'active' : '' }}" href="{{ route('master-kri.index') }}">
                <span class="nav-link-text">Master KRI</span>
              </a>
            </li>
            @endcan
            @can('jenis_kontrol_eksisting_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('jenis-kontrol-eksisting.index') ? 'active' : '' }}" href="{{ route('jenis-kontrol-eksisting.index') }}">
                <span class="nav-link-text">Jenis Eksisting Kontrol</span>
              </a>
            </li>
            @endcan
            @can('kontrol_eksisting_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('kontrol-eksisting.index') ? 'active' : '' }}" href="{{ route('kontrol-eksisting.index') }}">
                <span class="nav-link-text">Eksisting Kontrol</span>
              </a>
            </li>
            @endcan
            @can('penilaian_efektivitas_kontrol_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('penilaian-efektivitas-kontrol.index') ? 'active' : '' }}" href="{{ route('penilaian-efektivitas-kontrol.index') }}">
                <span class="nav-link-text">Penilaian Efektivitas Kontrol</span>
              </a>
            </li>
            @endcan
            @can('jenis_rencana_perlakuan_risiko_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('jenis-rencana-perlakuan-risiko.index') ? 'active' : '' }}" href="{{ route('jenis-rencana-perlakuan-risiko.index') }}">
                <span class="nav-link-text">Jenis Rencana Perlakuan Risiko</span>
              </a>
            </li>
            @endcan
            @can('opsi_perlakuan_risiko_list')
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('opsi-perlakuan-risiko.index') ? 'active' : '' }}" href="{{ route('opsi-perlakuan-risiko.index') }}">
                <span class="nav-link-text">Opsi Perlakuan Risiko</span>
              </a>
            </li>
            @endcan
            @can('manajemen_user')
            <li class="nav-item"><a class="nav-link {{ request()->is('roles') ? 'active' : '' }}" href="/roles">
                <span class="nav-link-text">Role</span>
              </a>
            </li>
            <li class="nav-item"><a class="nav-link {{ request()->is('users') ? 'active' : '' }}" href="/users">
                <span class="nav-link-text">User</span>
              </a>
            </li>
            @endcan
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
              <a class="nav-link {{ request()->is('periode') ? 'active' : '' }}" href="/periode">
                <span class="nav-link-text">Periode</span>
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
        @can('risk_control_setting')
        <li class="nav-item">
          <a class="nav-link dropdown-indicator {{ request()->is('risk-control-setting') ? 'active' : '' }}"
            href="#risk-setting" role="button" data-bs-toggle="collapse" aria-expanded="false"
            aria-controls="risk-setting">
            <i class="menu-icon tf-icons bx bx-pie-chart"></i>
            <span class="nav-link-text">Risk Setting</span>
          </a>
          <ul class="nav collapse {{ request()->is('risk-control-setting') ? 'show' : '' }}" id="risk-setting">
            <li class="nav-item">
              <a class="nav-link {{ request()->is('risk-control-setting') ? 'active' : '' }}"
                href="/risk-control-setting">
                <span class="nav-link-text">Risk Control Setting</span>
              </a>
            </li>
          </ul>
        </li>
        @endcan

        <div class="row navbar-vertical-label-wrapper">
          <div class="col-auto navbar-vertical-label">
            Documents
          </div>
          <div class="col ps-0">
            <hr class="mb-0 navbar-vertical-divider" />
          </div>
        </div>

        <li class="nav-item single-indicator">
          <a class="nav-link" href="../documents/user-manual-aplikasi-simr.pdf" target="blank">
            <span class="nav-link-icon">
              <i class="menu-icon tf-icons bx bx-archive"></i>
              <span class="nav-link-text">Panduan Penggunaan</span>
            </span>
          </a>
        </li>
      </ul>

    </div>
  </div>
</nav>


<!-- ----- navbar-vertical end -------------- -->