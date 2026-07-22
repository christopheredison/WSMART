@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row mb-5">
  <div class="col-12">
    <div class="ff-preheading">Risk Governance</div>
    <h2>Struktur Tata Kelola Risiko</h2>
  </div>
</div>
<div class="risk-governance row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-none">
        <h4>Organ Pengelola Risiko dan Tata Kelola 3 Lini</h4>
      </div>
      <div class="card-body">
        <span class="three-line-chart">@include('partials.struktur-tata-kelola-risiko')</span>
        <div class="row g-3 pt-6">
          <div class="col-12 col-md-4">
            <div class="card card-sm">
              <div class="card-header">
                <span class="ff-heading-bold">Lini Pertama</span>
              </div>
              <div class="card-body d-flex flex-column align-items-end">
                <p>Unit kerja yang langsung terlibat dalam pelaksanaan manajemen risiko di tingkat operasional dan
                  memiliki tanggung jawab langsung terhadap pencapaian tujuan dan sasaran Universitas.</p>
                <div class="badge bg-dark rounded-pill px-2 mt-auto">
                  Pasal 14
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="card card-sm">
              <div class="card-header">
                <span class="ff-heading-bold">Lini Kedua</span>
              </div>
              <div class="card-body d-flex flex-column align-items-end">
                <p>Fungsi-fungsi yang mendukung dan memantau manajemen risiko di seluruh Universitas mencakup fungsi
                  manajemen risiko, kepatuhan, pengendalian internal, dan fungsi-fungsi serupa yang memberikan
                  pemantauan dan pengawasan indpenden terhadap risiko.</p>
                <div class="badge bg-dark rounded-pill px-2 mt-auto">
                  Pasal 15
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="card card-sm">
              <div class="card-header">
                <span class="ff-heading-bold">Lini Ketiga</span>
              </div>
              <div class="card-body d-flex flex-column align-items-end">
                <p>Fungsi yang memberikan jaminan independen terhadap efektivitas dan efisiensi manajemen risiko,
                  pengendalian internal, dan proses-proses Universitas</p>
                <div class="badge bg-dark rounded-pill px-2 mt-auto">
                  Pasal 16
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row pt-4 pt-lg-7">
          <div class="col-12">
            <h6>Keterangan</h6>
            <hr>
            <ol class="list-col-md-2">
              <li><span class="ff-heading-sm">Pendelegasian Wewenang Manajemen Risiko</span>
                <br>Rektor dapat, apabila dipandang perlu, mendelegasikan wewenang kepada Wakil Rektor yang
                membidangi perencanaan sebagai Pejabat yang membidangi Manajemen Risiko
              </li>
              <li>
                <span class="ff-heading-sm">Rapat Eksekutif MRT</span>
                <br>Rapat Eksekutif Manajemen Risiko Terintegrasi yang dipimpin oleh Rektor dan Wakil Rektor untuk
                membahas Profil Risiko Universitas secara terintegrasi.
              </li>
              <li>
                <span class="ff-heading-sm">Unit Manajemen Risiko</span>
                <br>Menjalankan tugas dan fungsi lini 2 di tingkat Universitas.
              </li>
              <li>
                <span class="ff-heading-sm">Unit Administrasi Risiko</span>
                <br>Unit yang memfasilitasi proses manajemen risiko di tingkat Fakultas dan menyampaikan laporannya
                kepada Unit Manajemen Risiko.
              </li>
              <li>
                <span class="ff-heading-sm">Risk Officer pada Unit Kerja</span>
                <br>Setiap Unit Kerja perlu menetapkan pihak yang akan ditugaskan sebagai Risk Officer untuk
                membantu Risk Owner/Risk Champion dalam proses risk assessment dan pengelolaan risk register dan
                risk dashboard Unit Kerja.
              </li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection