<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GhostLoginController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        $originalUser = $this->getOriginalUser($request);

        if (!$originalUser || !$originalUser->can('ghost_login')) {
            abort(403, 'Anda tidak memiliki permission ghost login.');
        }

        if ($user->id === $originalUser->id) {
            return redirect()
                ->back()
                ->with('error', 'Gunakan akun asli secara langsung. Tidak perlu ghost login ke akun sendiri.');
        }

        if ($user->id === $request->user()->id) {
            return redirect()
                ->back()
                ->with('error', 'Anda sudah berada di akun tersebut.');
        }

        if ($this->isAdminTarget($user)) {
            return redirect()
                ->back()
                ->with('error', 'User dengan role admin tidak dapat dijadikan target ghost login.');
        }

        Auth::login($user);

        $request->session()->put('ghost_login', [
            'original_user_id' => $originalUser->id,
            'original_user_name' => $originalUser->name,
            'original_user_email' => $originalUser->email,
            'impersonated_user_id' => $user->id,
            'impersonated_user_name' => $user->name,
            'impersonated_user_email' => $user->email,
            'started_at' => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('home')
            ->with('success', "Sekarang Anda sedang ghost login sebagai {$user->name}.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $ghostSession = $request->session()->get('ghost_login');

        if (!$ghostSession || empty($ghostSession['original_user_id'])) {
            return redirect()
                ->back()
                ->with('error', 'Sesi ghost login tidak ditemukan.');
        }

        $originalUser = User::withTrashed()->find($ghostSession['original_user_id']);

        if (!$originalUser || $originalUser->trashed()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Akun asli tidak lagi tersedia. Silakan login ulang.');
        }

        Auth::login($originalUser);
        $request->session()->forget('ghost_login');

        return redirect()
            ->route('users.index')
            ->with('success', "Anda sudah kembali ke akun asli {$originalUser->name}.");
    }

    protected function getOriginalUser(Request $request): ?User
    {
        $originalUserId = $request->session()->get('ghost_login.original_user_id');

        if ($originalUserId) {
            return User::withTrashed()->find($originalUserId);
        }

        return $request->user();
    }

    protected function isAdminTarget(User $user): bool
    {
        return $user->hasExactAdminRole();
    }
}
