<?php

namespace App\Http\Controllers\RMI;

use App\Http\Controllers\Controller;
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
            'email' => 'required|email|max:255|unique:kuesioner_respondens,email',
            'group_id' => 'required|exists:groups,id',
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah pernah didaftarkan. Silahkan cek kotak masuk email anda.',
            'group_id.required' => 'Group harus dipilih.',
            'group_id.exists' => 'Group yang dipilih tidak valid.',
        ]);
        $verificationToken = \Illuminate\Support\Str::random(32);

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

        $email = $request->query('email');
        $verificationToken = $request->query('verification_token');

        $responden = \App\Models\KuesionerResponden::where('email', $email)
            ->where('rmi_period_id', $rmiPeriod->id)
            ->orderByRaw('CASE WHEN verification_token = ? THEN 0 ELSE 1 END', [$verificationToken])
            ->first();

        if (!$responden) {
            return view('kuesioner_publik.verification_status', [
                'status' => 'error',
                'message' => 'Data responden tidak ditemukan atau token verifikasi tidak valid.',
            ]);
        }

        if ($responden->isVerified()) {
            return view('kuesioner_publik.verification_status', [
                'status' => 'success',
                'message' => 'Email Anda sudah terverifikasi. Silahkan tunggu instruksi selanjutnya melalui email.',
            ]);
        }

        if ($responden->verification_token !== $verificationToken) {
            return view('kuesioner_publik.verification_status', [
                'status' => 'error',
                'message' => 'Token verifikasi tidak valid.',
            ]);
        }

        $responden->markAsVerified();

        return view('kuesioner_publik.verification_status', [
            'status' => 'success',
            'message' => 'Email Anda telah berhasil diverifikasi. Silahkan tunggu instruksi selanjutnya melalui email.',
        ]);
    }
}
