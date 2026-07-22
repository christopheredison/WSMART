@extends('layouts.default')

@section('dashboard')

<div class="container-fluid px-md-4 mt-md-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h3 mb-1 text-gray-800 fw-bold">
                Catatan Pembaruan Sistem
            </h2>
            <p class="text-dark mb-0">
                Rincian peluncuran fitur terbaru, peningkatan performa, serta perbaikan sistem pada aplikasi W-SMART.
            </p>
        </div>
    </div>

    <!-- Daftar Release Notes (TIMELINE LAYOUT) -->
    <div class="row justify-content-center mt-2">
      <div class="col-12">

        @if(count($releases) > 0)
            <div class="timeline-container ps-3 ps-md-4">
                @foreach($releases as $release)
                  <div class="timeline-item position-relative pb-5">
                      <div class="timeline-line"></div>
                      <div class="timeline-dot bg-info border border-4 border-white shadow-sm"></div>

                      <div class="card shadow-sm border-0 release-card ms-4 ms-md-5 rounded-4">
                        <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 rounded-top-4">
                          <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info px-3 py-2 rounded-pill shadow-sm">
                              Versi {{ $release->version }}
                            </span>
                            @if($loop->first)
                              <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill animate-pulse">Terbaru</span>
                            @endif
                          </div>
                          <div class="fw-bold">
                            <i class="bx bx-calendar-event me-1"></i>
                            {{ \Carbon\Carbon::parse($release->date)->translatedFormat('d F Y') }}
                          </div>
                        </div>

                        <div class="card-body px-4 py-4 px-md-5">
                          <h4 class="fw-bolder text-dark mb-4 pb-3 border-bottom">
                            {{ $release->title }}
                          </h4>

                          <div class="markdown-content text-dark">
                            <x-markdown>
                                {!! $release->body !!}
                            </x-markdown>
                          </div>
                        </div>
                      </div>
                  </div>
                @endforeach
            </div>
        @else
          <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body text-center py-5">
              <i class="bx bx-file-blank text-muted mb-3" style="font-size: 5rem;"></i>
              <h4 class="text-muted fw-bold">Belum ada catatan pembaruan.</h4>
              <p class="text-secondary" style="font-size: 1.1rem;">Informasi mengenai fitur baru akan ditampilkan di sini.</p>
            </div>
          </div>
        @endif

      </div>
    </div>
</div>

@endsection

@push('styles')
<style>
  .timeline-container {
      position: relative;
  }

  .timeline-item {
      position: relative;
  }

  .timeline-line {
      position: absolute;
      top: 30px;
      bottom: -30px;
      left: 7px;
      width: 4px;
      background-color: #e9ecef;
      border-radius: 2px;
      z-index: 1;
  }

  .timeline-item:last-child .timeline-line {
      display: none;
  }

  .timeline-dot {
      position: absolute;
      top: 25px;
      left: 0;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      z-index: 2;
  }

  /* ====== KARTU & EFEK ====== */
  .release-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .release-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0,0,0,.08)!important;
  }

  @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4); }
      70% { box-shadow: 0 0 0 6px rgba(25, 135, 84, 0); }
      100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
  }
  .animate-pulse {
      animation: pulse 2s infinite;
  }

  .markdown-content p {
      font-size: 1rem;
      line-height: 1.7;
      margin-bottom: 1rem;
      color: #333;
  }

  .markdown-content h3,
  .markdown-content h4 {
      font-size: 1.15rem;
      font-weight: 700;
      color: #1a1d20;
      margin-top: 2rem;
      margin-bottom: 0.8rem;
  }

  .markdown-content ul {
      padding-left: 1.5rem;
      margin-bottom: 1.2rem;
  }

  .markdown-content li {
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 0.4rem;
      color: #333;
  }

  .markdown-content code {
      background-color: #f8f9fa;
      padding: 0.2rem 0.4rem;
      border-radius: 4px;
      color: #d63384;
      font-size: 0.9rem;
      border: 1px solid #e9ecef;
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  }

  .markdown-content blockquote {
      border-left: 4px solid #0d6efd;
      color: #555;
      background-color: #f8f9fa;
      padding: 1rem;
      margin-top: 1.2rem;
      margin-bottom: 1.2rem;
      border-radius: 0 0.5rem 0.5rem 0;
      font-style: italic;
      font-size: 0.95rem;
  }
</style>
@endpush
