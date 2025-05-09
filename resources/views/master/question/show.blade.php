@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')
<div class="row">
    <div class="col-12">
        <div class="card mb-3 btn-reveal-trigger">
            <div class="card-header position-relative">
                <div class="card-header-title">
                    <h5 class="mb-0">Detail Pertanyaan</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Kelompok</label>
                            <p class="mb-0">{{ $question->group->name }}</p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Parameter</label>
                            <p class="mb-0">{{ $question->measurementParameter->statement }}</p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Pertanyaan</label>
                            <p class="mb-0">{{ $question->question }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Tanggal Dibuat</label>
                            <p class="mb-0">{{ $question->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Terakhir Diperbarui</label>
                            <p class="mb-0">{{ $question->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Pilihan Jawaban</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">Level</th>
                                        <th>Jawaban</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($question->answerChoices->sortBy('level') as $answer)
                                        <tr>
                                            <td class="text-center">{{ $answer->level }}</td>
                                            <td>{{ $answer->answer }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('question.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection