@extends('layouts.auth')
@section('dashboard')
<main class="main p-0 min-vh-100" id="top">
  <div class="position-absolute w-100 h-100 start-0">
    <img src="{{asset('assets/img/auth-bg.avif')}}" class="img-cover" alt="">
  </div>
  <div class="authentication-wrapper d-flex align-items-center px-3 px-lg-10">
    <div class="row h-100 w-100 justify-content-center flex-md-nowrap g-0 gx-md-10">
      <div class="col-12 col-md-9 col-xxl-8 text-center text-md-start ms-md-10 me-md-n10">
        <div class="card ratio ratio-1x1 ratio-lg-4x3 border-0">
          <img src="{{asset('assets/img/auth-card-bg.avif')}}" class="img-cover position-absolute z-index--1" alt="">
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
            <h3 class="text-center mb-4">Pendaftaran Pengisian Kuesioner</h3>
            <h4 class="text-center mb-4">Periode {{ $rmiPeriod->year }}</h4>
            @if($errors->any())
            <div class="alert alert-danger">
              @if(count($errors->all()) == 1)
                {{ $errors->first() }}
              @else
              <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
              @endif
            </div>
            @endif
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            <form class="needs-validation" novalidate="" method="POST" action="{{ route('kuesioner-publik.do-register', ['token' => $token]) }}">
              @csrf
              <div class="has-validation mb-3">
                <label class="form-label d-none" for="card-email">Email</label>
                <input class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}"
                  required autocomplete="email" autofocus id="email" type="text" placeholder="Email" />
                @error('email')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
              </div>
                <div class="has-validation mb-3">
                <label class="form-label d-none" for="name">Nama</label>
                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}"
                  required autocomplete="name" id="name" type="text" placeholder="Nama" />
                @error('name')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
                </div>
                <div class="has-validation mb-3">
                <label class="form-label d-none" for="group_id">Kelompok</label>
                <select class="form-control @error('group_id') is-invalid @enderror" name="group_id" required id="group_id">
                  <option value="">Pilih Kelompok</option>
                  @foreach($groups as $group)
                  <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                  @endforeach
                </select>
                @error('group_id')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
              </div>
              <div class="row flex-center">
                <div class="col-12">
                  <button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">Kirim</button>
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