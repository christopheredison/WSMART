@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row mb-5">
  <div class="col-12">
    <div class="ff-preheading">Risk Governance</div>
    <h2>Proses Manajemen Risiko</h2>
  </div>
</div>
<div class="rm-process row g-5 mb-5">
  <div class="col-12">
    <div class="card p-lg-5">
      <div class="card-body pt-3">
        <span class="three-line-chart">@include('partials.proses-manajemen-risiko')</span>
      </div>
    </div>
  </div>
</div>
@endsection