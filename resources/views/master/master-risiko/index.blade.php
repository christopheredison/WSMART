@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Master Risiko</h2>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-hover dataTable" data-paging="true" data-filter="true" data-scroll-y="false">
          <thead>
            <tr>
              <th class="white-space-nowrap" data-sort="no">#</th>
              <th>Kategori dan Jenis Risiko</th>
              <th>Peristiwa Risiko</th>
              <th>Deskripsi Peristiwa Risiko</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($masterRisiko as $index => $item)
            <tr>
              <td class="index-number">{{ $index + 1 }}</td>
              <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title }} -
                {{ $item->jenisRisiko->title }}</td>
              <td class="peristiwa_risiko">{{ $item->peristiwaRisiko->title }}</td>
              <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection