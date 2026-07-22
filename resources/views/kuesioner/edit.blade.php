@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')
<div class="row">
    <div class="col-12">
        <div class="card mb-3 btn-reveal-trigger">
            <div class="card-header position-relative">
                <div class="card-header-title">
                    <h5 class="mb-2">Pertanyaan Survey</h5>
                    <p class="mb-0">{{ request()->user()->group?->name ?? '-' }}</p>
                </div>
            </div>
            <div class="card-body">
                @if ($questions->count() > 0)
                <form action="{{ route('kuesioner.update', $periode->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div id="questions-container" data-idx="0" data-selected-answer="">
                        <div class="question-item">
                            <span class="item-number"></span>
                            <span class="item-content"></span>
                        </div>
                        @for ($i = 1; $i <= 5; $i++)
                        <div class="answer-item" data-level="{{ $i }}">
                            <span class="item-number">{{ $i }}</span>
                            <span class="item-content"></span>
                            <span class="checkmark">&#10003;</span>
                        </div>
                        @endfor
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('kuesioner.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="button" class="btn btn-success" id="prev-question">Sebelumnya</button>
                        <button type="button" class="btn btn-primary" id="next-question">Selanjutnya</button>
                        <button type="button" class="btn btn-primary" id="save-question">Simpan</button>
                    </div>
                </form>
                @else
                <p class="text-center">Tidak ada pertanyaan survey</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 

@push('scripts')
<script>
const questions = {!! json_encode($questions) !!};
const currentAnswers = {!! json_encode((object) $currentAnswers) !!};

function renderQuestion() {
    const idx = parseInt($('#questions-container').data('idx'));
    const question = questions[idx];
    if (!question && questions.length > 0) {
        $('#questions-container').data('idx', 0);
        question = questions[0];
    }

    if (!question) {
        return;
    }

    $('.question-item .item-content').text(question.question.question);
    $('.question-item .item-number').text(idx + 1);
    for (let i = 1; i <= 5; i++) {
        const answer = question.question.answer_choices.find(answer => answer.level === i);
        $('.answer-item[data-level="' + i + '"] .item-content').text(answer.answer);
        $('.answer-item[data-level="' + i + '"] input').val(answer.level);
    }

    if (idx > 0) {
        $('#prev-question').show();
    } else {
        $('#prev-question').hide();
    }

    if (idx < questions.length - 1) {
        $('#next-question').show();
    } else {
        $('#next-question').hide();
    }

    if (idx === questions.length - 1) {
        $('#save-question').show();
    } else {
        $('#save-question').hide();
    }
}

function gotopage(idx) {
    const nextAnswer = currentAnswers[questions[idx]?.id] || '';
    $('#questions-container').data('idx', idx).attr('data-idx', idx).attr('data-selected-answer', nextAnswer).data('selected-answer', nextAnswer);
    renderQuestion();
}

async function syncAnswer(complete = false) {
    return $.ajax({
        url: '{{ route('kuesioner.update', $periode->id) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            answers: currentAnswers,
            complete: complete ? 1 : 0
        }
    });
}

$(document).ready(function() {
    $('#questions-container').on('click', '.answer-item', function() {
        const level = $(this).data('level');
        const idx = parseInt($('#questions-container').data('idx'));
        $('#questions-container').attr('data-selected-answer', level).data('selected-answer', level);
        currentAnswers[questions[idx].id] = level;
        syncAnswer();
    });

    $('#next-question').on('click', function() {
        const idx = parseInt($('#questions-container').data('idx'));
        const nextIdx = idx + 1;
        gotopage(nextIdx);
    });

    $('#prev-question').on('click', function() {
        const idx = parseInt($('#questions-container').data('idx'));
        const level = $('#questions-container').data('selected-answer');
        currentAnswers[questions[idx].id] = level;
        const nextIdx = idx - 1;
        gotopage(nextIdx);
    });

    $('#save-question').on('click', function() {
        Swal.fire({
            title: 'Apakah anda yakin ingin menyelesaikan survey?',
            icon: 'warning',
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                syncAnswer(true).then((response) => {
                    Swal.fire({
                        title: response.message || 'Survey berhasil diselesaikan',
                        icon: 'success',
                    }).then(() => {
                        window.location.href = '{{ route('kuesioner.index') }}';
                    });
                });
            }
        });
    });
    gotopage(0);
});
</script>
@endpush

@push('styles')
<style>
    .question-item {
        margin-bottom: 4rem;
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

    .answer-item {
        margin-bottom: 2rem;
        padding: 1rem;
        padding-top: 2rem;
        border: 2px solid var(--bs-gray-300);
        position: relative;
        cursor: pointer;
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
    .answer-item .item-number::before {
        content: 'Jawaban ';
    }

    .answer-item .checkmark {
        position: absolute;
        top: -2.25rem;
        right: -0.75rem;
        font-size: 3rem;
        font-weight: 900;
        display: none;
    }

    @for ($i = 1; $i <= 5; $i++)
    #questions-container[data-selected-answer="{{ $i }}"] .answer-item[data-level="{{ $i }}"] .checkmark {
        display: block;
    }
    @endfor
</style>
@endpush