@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row mb-5">
  <div class="col-12">
    <div class="ff-preheading">Risk Governance</div>
    <h2>Taksonomi Risiko</h2>
  </div>
</div>
<div class="taxonomy row g-5 mb-5">
  <div class="col-12">
    <div class="card p-lg-5">
      <div class="card-body pt-3">
        <span class="three-line-chart">@include('partials.taksonomi-risiko')</span>
      </div>
    </div>
  </div>
</div>
<!--===================== Modal Jenis Risiko 1 =====================-->
<div class="modal fade" id="tr1Modal" tabindex="-1" aria-labelledby="tr1ModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content modal-taxonomy">
      <div class="modal-header pb-0">
        <h3 class="modal-title list-1 h4">
          <div class="d-flex gap-2">
            <span>1.</span>
            <span>Risiko Tridharma Perguruan Tinggi</span>
          </div>
        </h3>
      </div>
      <div class="modal-body">
        @include('partials.tr-jenis-risiko-1')
      </div>
    </div>
  </div>
</div>
<!--===================== Modal Jenis Risiko 2 =====================-->
<div class="modal fade" id="tr2Modal" tabindex="-1" aria-labelledby="tr2ModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content modal-taxonomy">
      <div class="modal-header pb-0">
        <h3 class="modal-title list-2 h4">
          <div class="d-flex gap-2">
            <span>2.</span>
            <span>Risiko Kebijakan, Strategi, dan Reputasi</span>
          </div>
        </h3>
      </div>
      <div class="modal-body">
        @include('partials.tr-jenis-risiko-2')
      </div>
    </div>
  </div>
</div>
<!--===================== Modal Jenis Risiko 3 =====================-->
<div class="modal fade" id="tr3Modal" tabindex="-1" aria-labelledby="tr3ModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content modal-taxonomy">
      <div class="modal-header pb-0">
        <h3 class="modal-title list-3 h4">
          <div class="d-flex gap-2">
            <span>3.</span>
            <span>Risiko Operasional & Sumber Daya</span>
          </div>
        </h3>
      </div>
      <div class="modal-body">
        @include('partials.tr-jenis-risiko-3')
      </div>
    </div>
  </div>
</div>
@endsection