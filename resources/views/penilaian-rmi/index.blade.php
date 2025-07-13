@extends('layouts.default')
@section('dashboard')

@include('partials.success-message')

<div class="container my-4">
  <h2 class="mb-4">Daftar Periode RMI</h2>

  <form method="GET" action="{{ route('penilaian-rmi.index') }}" class="mb-3 d-flex">
    <input type="text" name="q" value="{{ request('q') }}" class="form-control me-2" placeholder="Cari periode RMI...">
    <button type="submit" class="btn btn-primary">Filter</button>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Tahun Periode RMI</th>
          <th>Status</th>
          <th>Score RMI</th>
          <th>Score Aspek Dimensi</th>
          <th>Deskripsi</th>
          <th>Kinerja</th>
          <th>KPMR</th>
          <th>Peringkat Komposit Risiko</th>
          <th>Nilai Konversi</th>
          <th>Score Aspek Kinerja</th>
          <th>Score Penyesuaian</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($periods as $index => $period)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $period->year }}</td>
            <td>
              @if($period->status == 1)
                <span class="badge bg-warning text-dark">Dalam Proses</span>
              @else
                <span class="badge bg-success">Selesai</span>
              @endif
            </td>
            <td>{{ $period->final_score_rmi }}</td>
            <td>{{ $period->score_rmi }}</td>
            <td>{{ $period->score_rmi_desc }}</td>
            <td>
              {{-- Tampilkan deskripsi Kinerja dari relasi skala_kinerjas --}}
              {{ $period->kinerja ?? '-' }}
            </td>
            <td>
              {{-- Deskripsi KPMR dari relasi skala_kpmrs --}}
              {{ $period->kpmr ?? '-' }}
            </td>
            <td>{{ $period->peringkat_komposit_risiko ?? '-' }}</td>
            <td>{{ $period->nilai_konversi ?? '-' }}</td>
            <td>{{ $period->score_aspek_kinerja ?? '-' }}</td>
            <td>{{ $period->adjusment_score ?? '-' }}</td>
            <td>
              <a href="{{ route('penilaian-rmi.show', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                title="Lihat Detail">
                <span class="bx bx-show"></span>
              </a>
              <a href="{{ route('penilaian-rmi.aspek-dinamis', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                title="Penilaian Aspek Dimensi">
                <span class="bx bx-bar-chart-alt-2"></span>
              </a>
              <a href="{{ route('penilaian-rmi.aspek-kinerja', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                title="Penilaian Aspek Kinerja">
                <span class="bx bx-line-chart"></span>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{ $periods->links() }}
</div>

@endsection
