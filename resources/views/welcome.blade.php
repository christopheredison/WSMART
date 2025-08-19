@extends('layouts.auth')
@section('dashboard')
<main class="main p-0 min-vh-100" id="top">
  <div class="position-absolute w-100 h-100 start-0">
    <img src="assets/img/auth-bg.avif" class="img-cover" alt="">
  </div>
  <div class="authentication-wrapper d-flex align-items-center px-3 px-lg-10">
    <div class="row h-100 w-100 justify-content-center flex-md-nowrap g-0 gx-md-10">
      <div class="col-12 col-md-9 col-xxl-8 text-center text-md-start ms-md-10 me-md-n10">
        <div class="card ratio ratio-1x1 ratio-lg-4x3 border-0">
          <img src="assets/img/auth-card-bg.avif" class="img-cover position-absolute z-index--1" alt="">
          <div class="card-body p-5 p-xl-7 p-xxl-9 z-10">
            <div class="app-brand col-2 col-lg-1 mx-auto mx-md-0 text-primary mb-7">
              @include('partials.logo',["width"=>150])
            </div>
            <div class="col-md-8">
              <h1 class="mb-4">WIKA Sistem MAnagement Risiko Terintegrasi<br />(W-SMART)</h1>
              <h5>PT Wijaya Karya (Persero) Tbk</h5>
            </div>
          </div>
        </div>
      </div>
      <div class="col-11 col-md-7 col-lg-6 col-xl-5 col-xxl-4 mt-n7 ms-md-n9 ms-lg-n10 my-md-auto">
        <div class="card h-auto">
          <div class="card-body p-4 p-md-5 p-xl-6 p-xxl-7">
            <h3 class="text-center mb-4">Login</h3>
            @if($errors->any())
            <div class="alert alert-danger">
              <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif
            <form class="needs-validation" novalidate="" method="POST" action="{{ route('login') }}">
              @csrf
              <div class="has-validation mb-3">
                <label class="form-label d-none" for="card-email">Email / NIP / NIK</label>
                <input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}"
                  required autocomplete="email" autofocus id="email" type="text" placeholder="Email / NIP / NIK" />
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
              <div class="row flex-center">
                <div class="col-12">
                  <a class="btn btn-primary d-block w-100 mt-3" href="{{ config('wzone.url') }}" id="login-sso-btn">Log in SSO</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
