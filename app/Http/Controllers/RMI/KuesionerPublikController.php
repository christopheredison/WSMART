<?php

namespace App\Http\Controllers\RMI;

use App\Http\Controllers\Controller;
use App\Models\KuesionerResponden;
use App\Models\RMIPeriod;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class KuesionerPublikController extends Controller
{
    public function register($token)
    {
        $rmiPeriod = \App\Models\RMIPeriod::getByToken($token);
        if (!$rmiPeriod) {
            return abort(404, 'Periode RMI tidak ditemukan.');
        }

        $groups = \App\Models\Group::all();

        return view('kuesioner_publik.register', [
            'rmiPeriod' => $rmiPeriod,
            'token' => $token,
            'groups' => $groups,
        ]);
    }

    public function doRegister(Request $request, $token)
    {
        $rmiPeriod = \App\Models\RMIPeriod::getByToken($token);
        if (!$rmiPeriod) {
            return abort(404, 'Periode RMI tidak ditemukan.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'group_id' => 'required|exists:groups,id',
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah pernah didaftarkan. Silahkan cek kotak masuk email anda.',
            'group_id.required' => 'Group harus dipilih.',
            'group_id.exists' => 'Group yang dipilih tidak valid.',
        ]);

        $checkExisting = \App\Models\KuesionerResponden::where('email', $request->input('email'))
            ->where('rmi_period_id', $rmiPeriod->id)
            ->first();
        if ($checkExisting) {
            if ($checkExisting->isVerified()) {
                if ($checkExisting->approval_status === 'approved') {
                    return redirect()->route('kuesioner-publik.register', ['token' => $token])
                        ->withErrors(['email' => 'Email sudah pernah didaftarkan dan diverifikasi. Silahkan cek kotak masuk email anda.']);
                } elseif ($checkExisting->approval_status !== 'rejected') {
                    return redirect()->route('kuesioner-publik.register', ['token' => $token])
                        ->withErrors(['email' => 'Email sudah pernah didaftarkan tetapi belum diverifikasi. Silahkan cek kotak masuk email anda untuk verifikasi.']);
                }
            }
        }

        $verificationToken = \Illuminate\Support\Str::random(32);

        if ($checkExisting) {
            \App\Models\KuesionerResponden::where('email', $request->input('email'))
                ->where('rmi_period_id', $rmiPeriod->id)
                ->delete();
        }

        $responden = \App\Models\KuesionerResponden::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'group_id' => $request->input('group_id'),
            'rmi_period_id' => $rmiPeriod->id,
            'verification_token' => $verificationToken,
        ]);

        $responden->sendVerificationEmail();

        return redirect()->route('kuesioner-publik.register', ['token' => $token])
            ->withSuccess('Pendaftaran berhasil! Silakan periksa email Anda untuk verifikasi.');
    }

    public function verify(Request $request, $token)
    {
        $rmiPeriod = \App\Models\RMIPeriod::getByToken($token);
        if (!$rmiPeriod) {
            return abort(404, 'Periode RMI tidak ditemukan.');
        }

        $email = $request->input('email');
        $verificationToken = $request->input('verification_token');

        $responden = \App\Models\KuesionerResponden::where('email', $email)
            ->where('rmi_period_id', $rmiPeriod->id)
            ->orderByRaw('CASE WHEN verification_token = ? THEN 0 ELSE 1 END', [$verificationToken])
            ->first();

        if (!$responden) {
            return view('kuesioner_publik.verification_status', [
                'title' => 'Verifikasi Gagal',
                'status' => 'error',
                'message' => 'Data responden tidak ditemukan atau token verifikasi tidak valid.',
            ]);
        }

        if ($responden->isVerified()) {
            return view('kuesioner_publik.verification_status', [
                'title' => 'Verifikasi Berhasil',
                'status' => 'success',
                'message' => 'Email Anda sudah terverifikasi. Silahkan tunggu instruksi selanjutnya melalui email.',
            ]);
        }

        if ($responden->verification_token !== $verificationToken) {
            return view('kuesioner_publik.verification_status', [
                'title' => 'Verifikasi Gagal',
                'status' => 'error',
                'message' => 'Token verifikasi tidak valid.',
            ]);
        }

        $responden->markAsVerified();

        return view('kuesioner_publik.verification_status', [
            'title' => 'Verifikasi Berhasil',
            'status' => 'success',
            'message' => 'Email Anda telah berhasil diverifikasi. Silahkan tunggu instruksi selanjutnya melalui email.',
        ]);
    }

    public function fill(Request $request, $token) {
        $periode = RMIPeriod::getByToken($token);
        if (!$periode) {
            return view('kuesioner_publik.verification_status', [
                'title' => 'Tidak Ditemukan',
                'status' => 'error',
                'message' => 'Periode RMI tidak ditemukan.',
            ]);
        }
        $user = KuesionerResponden::where('email', $request->input('email'))
            ->where('rmi_period_id', $periode->id)
            ->where('verification_token', $request->input('verification_token'))
            ->first();

        if (!$user || !$user->isVerified() || !$user->isApproved()) {
            return view('kuesioner_publik.verification_status', [
                'title' => 'Akses Ditolak',
                'status' => 'error',
                'message' => 'Akses ditolak. Pastikan Anda telah terverifikasi dan disetujui untuk mengisi kuesioner.',
            ]);
        }

        $user->load('group');

        $periode->load(['periodQuestions' => function($query) use ($user) {
            $query->with(['question' => function($query) use ($user) {
                $query->where('group_id', $user->group_id);
                $query->with('answerChoices');
            }])->whereHas('question', function($query) use ($user) {
                $query->where('group_id', $user->group_id);
            });
        },'userSurvey' => function($query) use ($user) {
            $query->where('kuesioner_responden_id', $user->id);
        }]);

        if ($periode->userSurvey?->status == 1) {
            return view('kuesioner_publik.verification_status', [
                'title' => 'Kuesioner Sudah Selesai',
                'status' => 'success',
                'message' => 'Anda telah menyelesaikan kuesioner ini. Hubungi administrator jika ingin mengulang.',
            ]);
        }

        $questions = $periode->periodQuestions;
        $currentAnswers = $periode->userSurvey?->userAnswers->pluck('level', 'period_question_id');
        return view('kuesioner_publik.edit', compact('periode', 'questions', 'currentAnswers', 'user', 'token'));
    }

    public function update(Request $request, $token)
    {
        $periode = RMIPeriod::getByToken($token);
        if (!$periode) {
            return response()->json([
                'message' => 'Periode RMI tidak ditemukan.',
            ], 404);
        }
        $user = KuesionerResponden::where('email', $request->input('email'))
            ->where('rmi_period_id', $periode->id)
            ->where('verification_token', $request->input('verification_token'))
            ->first();

        if (!$user || !$user->isVerified() || !$user->isApproved()) {
            return response()->json([
                'message' => 'Akses ditolak. Pastikan Anda telah terverifikasi dan disetujui untuk mengisi kuesioner.',
            ], 403);
        }

        $periode->load([
            'userSurvey' => function($query) use ($user) {
                $query->where('kuesioner_responden_id', $user->id);
            }, 
            'periodQuestions' => function($query) use ($user) {
                $query->whereHas('question', function($query) use ($user) {
                    $query->where('group_id', $user->group_id);
                })->with('question.answerChoices');
            }
        ]);
        $answers = $request->answers;
        $userSurvey = $periode->userSurvey;

        if (!$userSurvey) {
            $userSurvey = $periode->userSurvey()->create([
                'kuesioner_responden_id' => $user->id,
                'status' => '0',
            ]);
        }

        if ($userSurvey->status == 1) {
            return response()->json([
                'message' => 'Kuesioner sudah selesai diisi',
            ], 400);
        }

        foreach ($answers ?? [] as $questionId => $answer) {
            $periodeQuestion = $periode->periodQuestions->where('id', $questionId)->first();

            if (!$periodeQuestion) {
                continue;
            }

            UserAnswer::updateOrCreate([
                'period_question_id' => $periodeQuestion->id,
                'user_survey_id' => $userSurvey->id,
            ], [
                'answer_choice_id' => $periodeQuestion->question->answerChoices->where('level', $answer)->first()->id,
                'level' => $answer,
                'kuesioner_responden_id' => $user->id,
                'question_notes' => null,
                'answer_notes' => null,
            ]);
        }

        if ($request->complete) {
            $userSurvey->update([
                'status' => '1',
            ]);
        }

        return response()->json([
            'message' => 'Kuesioner berhasil disimpan',
        ]);
    }
}
