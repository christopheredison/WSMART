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