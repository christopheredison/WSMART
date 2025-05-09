@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')
<div class="row">
    <div class="col-12">
        <div class="card mb-3 btn-reveal-trigger">
            <div class="card-header position-relative">
                <div class="card-header-title">
                    <h5 class="mb-0">Detail Periode RMI</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Tahun</label>
                            <p class="mb-0">{{ $rmiPeriod->year }}</p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Tanggal Mulai</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($rmiPeriod->start_date)->format('d/m/Y') }}</p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Tanggal Berakhir</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($rmiPeriod->end_date)->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Daftar Pertanyaan Periode Ini</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 40px">No</th>
                                        <th>Pertanyaan</th>
                                        <th>Parameter</th>
                                        <th>Jawaban 1</th>
                                        <th>Jawaban 2</th>
                                        <th>Jawaban 3</th>
                                        <th>Jawaban 4</th>
                                        <th>Jawaban 5</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rmiPeriod->periodQuestions as $i => $pq)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $pq->question?->question ?? '-' }}</td>
                                            <td>{{ $pq->question?->measurementParameter->statement ?? '-' }}</td>
                                            <td>{{ optional($pq->question?->answerChoices->where('level', 1)->first())->answer }}</td>
                                            <td>{{ optional($pq->question?->answerChoices->where('level', 2)->first())->answer }}</td>
                                            <td>{{ optional($pq->question?->answerChoices->where('level', 3)->first())->answer }}</td>
                                            <td>{{ optional($pq->question?->answerChoices->where('level', 4)->first())->answer }}</td>
                                            <td>{{ optional($pq->question?->answerChoices->where('level', 5)->first())->answer }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada pertanyaan untuk periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('rmi-period.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection