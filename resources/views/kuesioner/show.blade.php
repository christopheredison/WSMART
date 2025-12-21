@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')
<div class="row">
    <div class="col-12">
        <div class="card mb-3 btn-reveal-trigger">
            <div class="card-header position-relative">
                <div class="card-header-title">
                    <h5 class="mb-2">Hasil Pengisian Kuesioner</h5>
                    <p class="mb-0">{{ request()->user()->group?->name ?? '-' }}</p>
                </div>
            </div>
            <div class="card-body">
                @if ($questions->count() > 0)
                    @foreach($questions as $idx => $question)
                    <div class="question-item-wrapper">
                        <div class="question-item">
                            <span class="item-number">{{ $idx + 1 }}</span>
                            <span class="item-content">{{ $question->question->question }}</span>
                        </div>
                        @for ($i = 1; $i <= 5; $i++)
                            @php
                                $selected = (isset($answers[$question->id]) && $answers[$question->id] == $i);
                                $answerObj = optional($question->question->answerChoices)->firstWhere('level', $i);
                            @endphp
                            <div class="answer-item{{ $selected ? ' selected' : '' }}" data-level="{{ $i }}">
                                <span class="item-number">{{ $i }}</span>
                                <span class="item-content">{{ $answerObj ? $answerObj->answer : '-' }}</span>
                                @if($selected)
                                    <span class="checkmark">&#10003;</span>
                                @endif
                            </div>
                        @endfor
                        <hr class="mt-4 mb-6">
                    </div>
                    @endforeach
                @else
                    <p class="text-center">Tidak ada pertanyaan survey</p>
                @endif
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ ($type ?? false) == 'responden' ? route('kuesioner-responden.index') : route('kuesioner.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .question-item {
        margin-bottom: 2rem;
        padding: 1rem;
        padding-top: 2rem;
        border: 2px solid var(--bs-gray-300);
        position: relative;
    }
    .question-item .item-number {
        background-color: #545454;
        color: #fff;
        display: block;
        padding: 10px;
        position: absolute;
        top: -20px;
        left: 1rem;
        min-width: 200px;
        text-align: center;
    }
    .question-item .item-number::before {
        content: 'Pertanyaan ';
    }
    .question-item .item-content {
        display: block;
        font-size: 1.2rem;
        font-weight: 500;
        margin-bottom: 1.5rem;
        color: #222;
    }
    .answer-item {
        margin-bottom: 2rem;
        padding: 1rem;
        padding-top: 2rem;
        border: 2px solid var(--bs-gray-300);
        position: relative;
        cursor: pointer;
        background: #ededed;
        border-radius: 4px;
        display: flex;
        align-items: flex-start;
        transition: border 0.2s, background 0.2s;
    }
    .answer-item.selected {
        border: 2px solid #ffd600;
        background: #fffde7;
    }
    .answer-item .item-number {
        background-color: #d6d6d6;
        display: block;
        padding: 10px;
        position: absolute;
        top: -20px;
        left: 1rem;
        min-width: 200px;
        text-align: center;
    }
    .answer-item.selected .item-number {
        background-color: #ffd600;
        color: #fff;
    }
    .answer-item .item-number::before {
        content: 'Jawaban ';
    }
    .answer-item .item-content {
        font-size: 1rem;
        color: #222;
        margin-left: 1rem;
        flex: 1;
    }
    .answer-item .checkmark {
        position: absolute;
        top: -2.25rem;
        right: -0.75rem;
        font-size: 3rem;
        font-weight: 900;
        display: block;
        color: #222;
    }
</style>
@endpush
