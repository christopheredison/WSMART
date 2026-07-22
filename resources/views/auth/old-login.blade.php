@extends('layouts.auth')

@section('dashboard')
<main class="main" id="top">
    <div class="container-fluid">
      <script>
        var isFluid = JSON.parse(localStorage.getItem('isFluid'));
        if (isFluid) {
          var container = document.querySelector('[data-layout]');
          container.classList.remove('container');
          container.classList.add('container-fluid');
        }
      </script>
      <div class="row min-vh-100 bg-100">
        <div class="col-6 d-none d-lg-block position-relative">
          <div class="bg-holder" style="background-image:url(../../../assets/img/generic/14.jpg);background-position: 50% 20%;">
          </div>
          <!--/.bg-holder-->

        </div>
        <div class="col-sm-10 col-md-6 px-sm-0 align-self-center mx-auto py-5">
          <div class="row justify-content-center g-0">
            <div class="col-lg-9 col-xl-8 col-xxl-6">
              <div class="card">
                <div class="card-header bg-circle-shape bg-shape text-center p-2"><a class="font-sans-serif fw-bolder fs-4 z-index-1 position-relative link-light light" href="#">Screenolog</a></div>
                <div class="card-body p-4">
                  <div class="row flex-between-center">
                    <div class="col-auto">
                      <h3>Login</h3>
                    </div>
                    {{-- <div class="col-auto fs--1 text-600"><span class="mb-0 fw-semi-bold">New User?</span> <span><a href="../../../pages/authentication/split/register.html">Create account</a></span></div> --}}
                  </div>
                  <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                      <label class="form-label" for="split-login-email">Email address</label>
                      <input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus id="email" type="email" />
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                      <div class="d-flex justify-content-between">
                        <label class="form-label" for="split-login-password">Password</label>
                      </div>
                      <input class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" id="split-login-password" type="password" />
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="row flex-between-center">
                      <div class="col-auto">
                        <div class="form-check mb-0">
                          <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                          <label class="form-check-label mb-0" for="split-checkbox">Remember me</label>
                        </div>
                      </div>
                        {{-- <div class="col-auto">
                            @if (Route::has('password.request'))
                                <a class="btn fs--1" href="{{ route('password.request') }}">
                                    {{ __('Forgot Password?') }}
                                </a>
                            @endif
                        </div> --}}
                    </div>
                    <div class="mb-3">
                      <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">Log in</button>
                    </div>
                  </form>
                  {{-- <div class="position-relative mt-4">
                    <hr />
                    <div class="divider-content-center">or log in with</div>
                  </div>
                  <div class="row g-2 mt-2">
                    <div class="col-sm-6"><a class="btn btn-outline-google-plus btn-sm d-block w-100" href="#"><span class="fab fa-google-plus-g me-2" data-fa-transform="grow-8"></span> google</a></div>
                    <div class="col-sm-6"><a class="btn btn-outline-facebook btn-sm d-block w-100" href="#"><span class="fab fa-facebook-square me-2" data-fa-transform="grow-8"></span> facebook</a></div>
                  </div> --}}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
