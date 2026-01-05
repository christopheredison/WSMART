<nav class="navbar navbar-light navbar-glass navbar-top navbar-expand">
  <div class="container-fluid">
    <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button"
      data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse"
      aria-expanded="false" aria-label="Toggle Navigation">
      <span class="navbar-toggle-icon">
        <i class="bx bx-menu bx-sm pt-1"></i>
      </span>
    </button>
    <div class="row justify-content-between g-0 w-100">
      <div class="col-6 col-md-auto d-flex align-items-center">
        <h5 class="mb-0">WIKA Sistem MAnagement Risiko Terintegrasi (W-SMART)</h5>
      </div>
      <div class="col-auto">
        <ul class="navbar-nav navbar-nav-icons ms-auto flex-row align-items-center">
          <li class="nav-item me-3">
            <a class="nav-link px-0 position-relative" href="{{ route('tasks.index') }}" title="Tugas Saya">
              <span class="bx bx-task fs-4"></span>

              <span id="navbar-task-count" class="position-absolute start-100 translate-middle badge rounded-pill bg-danger border border-light d-flex justify-content-center align-items-center" style="display: none; font-size: 0.6rem; height: 1rem; width: 1rem; top: 4px;">
                0
                <span class="visually-hidden">pending tasks</span>
              </span>
            </a>
          </li>

          <!-- Notifikasi -->
          <li class="nav-item dropdown">
            <a class="nav-link notification-indicator notification-indicator-primary px-0 icon-indicator" id="navbarDropdownNotification" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <span class="bx bx-bell fs-4" data-fa-transform="shrink-6"></span>
              <span class="notification-indicator-number">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-card dropdown-menu-notification" aria-labelledby="navbarDropdownNotification">
              <div class="card card-notification shadow-none">
                <div class="card-header">
                  <div class="row justify-content-between align-items-center">
                    <div class="col-auto">
                      <h6 class="card-header-title mb-0">Notifikasi</h6>
                    </div>
                    <div class="col-auto ps-0 ps-sm-3"><a class="card-link fw-normal" href="#" id="markAllAsRead">Tandai semua sebagai dibaca</a></div>
                  </div>
                </div>
                <div class="scrollbar-overlay" style="max-height: 19rem">
                  <div class="list-group list-group-flush fw-normal fs--1" id="notification-list">
                    <!-- Notifikasi akan diisi secara dinamis dengan JavaScript -->
                  </div>
                </div>
                <div class="card-footer text-center border-top"><a class="card-link d-block" href="{{ route('notifications.index') }}">Lihat semua notifikasi</a></div>
              </div>
            </div>

            <!-- Modal Detail Notifikasi -->
            <div class="modal fade" id="notificationDetailModal" tabindex="-1" role="dialog" aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="notificationDetailModalLabel">Detail Notifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="notification-detail">
                      <h6 id="notification-title"></h6>
                      <p id="notification-message"></p>
                      <div class="notification-meta">
                        <small id="notification-time"></small>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="toggleReadStatus" class="btn btn-outline-primary">Tandai Belum Dibaca</button>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <!-- End Notifikasi -->

          <a class="tx-g700 font-base" href="{{ route('home') }}">
            <div class="d-flex align-items-center py-3">
              <div>{{ Auth::user()->name }}</div>
            </div>
          </a>
          <li class="nav-item dropdown"><a class="nav-link pe-0 ps-2" id="navbarDropdownUser" role="button"
              data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <div class="avatar avatar-xl">
                <img class="rounded-circle" src="{{ asset('assets/img/avatar.png') }}" alt="" />

              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end py-0" aria-labelledby="navbarDropdownUser">
              <div class="bg-white dark__bg-1000 rounded-2 py-2">
                {{--
          <a class="dropdown-item fw-bold text-warning" href="#"><span class="fas fa-crown me-1"></span><span>Go Pro</span></a>
        --}}
                <div class="dropdown-divider"></div>
                {{-- <a class="dropdown-item" href="#!">Set status</a> --}}
                <a class="dropdown-item" href="{{ route('profile') }}">Profile &amp; account</a>
                {{-- <a class="dropdown-item" href="#!">Feedback</a> --}}

                <div class="dropdown-divider"></div>
                {{-- <a class="dropdown-item" href="pages/user/settings.html">Settings</a> --}}
                {{-- <a class="dropdown-item" href="pages/authentication/card/logout.html">Logout</a> --}}
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                  {{ __('Logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                  @csrf
                </form>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
