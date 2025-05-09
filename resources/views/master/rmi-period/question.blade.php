@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')
<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('rmi-period.update-question', $period->id) }}">
            @csrf
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0">Set Pertanyaan Periode RMI</h2>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <select name="group_id" id="group_id" class="form-select">
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive table-question">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>Pertanyaan</th>
                                    <th>Jawaban 1</th>
                                    <th>Jawaban 2</th>
                                    <th>Jawaban 3</th>
                                    <th>Jawaban 4</th>
                                    <th>Jawaban 5</th>
                                    <th>Parameter</th>
                                    <th style="width: 80px;">
                                        <input type="checkbox" id="check-all">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <a href="{{ route('rmi-period.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="button" class="btn btn-primary ms-2" id="save-questions">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const groups = {!! json_encode($groups) !!};
    const periodQuestions = {!! json_encode($period->periodQuestions->pluck('question_id')) !!};
    $(document).ready(function() {
        $('#group_id').change(function() {
            const group = groups[$(this).val()];
            const tbody = $('tbody');
            tbody.empty();
            if (!group || group.questions.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada pertanyaan</td>
                    </tr>
                `);
                $('#check-all').prop('checked', false);
                return;
            }
            const questions = group.questions;
            questions.forEach(function(question, index) {
                tbody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${question.question}</td>
                        <td>${question.answer_choices.find(choice => choice.level === 1)?.answer || '-'}</td>
                        <td>${question.answer_choices.find(choice => choice.level === 2)?.answer || '-'}</td>
                        <td>${question.answer_choices.find(choice => choice.level === 3)?.answer || '-'}</td>
                        <td>${question.answer_choices.find(choice => choice.level === 4)?.answer || '-'}</td>
                        <td>${question.answer_choices.find(choice => choice.level === 5)?.answer || '-'}</td>
                        <td>${question.measurement_parameter.statement || '-'}</td>
                        <td>
                            <input type="checkbox" name="selected_questions[]" value="${question.id}" ${periodQuestions.includes(question.id) ? 'checked' : ''} class="checked-question">
                        </td>
                    </tr>
                `);
            });

            if ($('.checked-question:checked').length === $('.checked-question').length) {
                $('#check-all').prop('checked', true);
            } else {
                $('#check-all').prop('checked', false); 
            }
        }).change();

        $('.table-question tbody').on('change', '.checked-question', function() {
            const questionId = parseInt($(this).val());
            const isChecked = $(this).is(':checked');
            if (isChecked) {
                periodQuestions.push(questionId);
            } else {
                const index = periodQuestions.indexOf(questionId);
                if (index > -1) {
                    periodQuestions.splice(index, 1);
                }
            }

            if ($('.checked-question:checked').length === $('.checked-question').length) {
                $('#check-all').prop('checked', true);
            } else {
                $('#check-all').prop('checked', false); 
            }
        });

        $('#check-all').change(function() {
            const isChecked = $(this).is(':checked');
            if (isChecked) {
                $('.checked-question:not(:checked)').prop('checked', true).change();
            } else {
                $('.checked-question:checked').prop('checked', false).change();
            }
        });

        $('#save-questions').click(function() {
            const formData = {
                _token: '{{ csrf_token() }}',
                questions: periodQuestions
            };
            $.ajax({
                url: '{{ route('rmi-period.update-question', $period->id) }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Berhasil disimpan',
                        icon: 'success'
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Gagal',
                        text: xhr.responseJSON.message || 'Gagal menyimpan pertanyaan',
                        icon: 'error'
                    });
                }
            });
        });
    });
</script>
@endpush