@extends('layouts.auth')
@section('dashboard')
<main class="main p-0 min-vh-100" id="top">
  <div class="authentication-wrapper">
    <div class="row justify-content-center align-items-center vh-100 g-0">
      <div class="col-11 col-md-8 col-lg-5 col-xxl-4 mx-auto z-index-2">
        <div class="card">
          <div class="card-body p-5">
            <div class="app-brand col-4 mx-auto mb-5">
              @include('partials.logo',["width"=>150])
            </div>
            <h1 class="text-center mb-3">Sistem Informasi<br>Manajemen Risiko (SIMR)</h1>
            <h5 class="text-center mb-7">PT Wijaya Karya (Persero) Tbk</h5>
            <form class="needs-validation" novalidate="" method="POST" action="{{ route('login') }}">
              @csrf
              <div class="has-validation mb-3">
                <label class="form-label d-none" for="card-email">Email address</label>
                <input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}"
                  required autocomplete="email" autofocus id="email" type="email" placeholder="Email address" />
                @error('email')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
              </div>
              <div class="password-input-col has-validation mb-3">
                <label class="form-label d-none" for="card-password">Password</label>
                <input class="form-control form_password @error('password') is-invalid @enderror" name="password"
                  required autocomplete="current-password" id="split-login-password" type="password"
                  placeholder="Password" />
                <i id="pass" class='bx bx-show-alt'></i>
                @error('password')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
              </div>
              <div class="row flex-between-center mb-3">
                <div class="col-auto">
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="card-checkbox" checked="checked" />
                    <label class="form-check-label mb-0" for="card-checkbox">Remember me</label>
                  </div>
                </div>
              </div>
              <div class="row flex-center">
                <div class="col-12">
                  <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">Log in</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6 vh-100 position-absolute position-lg-relative">
        <img src="assets/img/pp-auth.webp" class="img-cover" alt="">
      </div>
    </div>
  </div>
</main>
@endsection