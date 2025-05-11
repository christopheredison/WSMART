@extends('layouts.default')

@section('dashboard')
  {{-- @include('partials.dashboard') --}}

<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 ratio ratio-1x1 ratio-md-4x3 ratio-lg-21x9 ratio-xl-16x9">
      <img src="../assets/img/hero-bg.webp" class="img-cover" alt="dashboard">
      <div class="card-header p-lg-8 p-xxl-10 border-0 d-flex flex-column text-white">
        <div class="col-2 col-md-1">
          @include('partials.logo')
        </div>
        <h1 class="my-auto col-md-8 fs-md-5 fs-xl-6 fs-xxl-8">Sistem Informasi Manajemen Risiko (SIMR)</h1>
      </div>
    </div>
  </div>
</div>
@endsection
