<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Single hand-rolled login screen for the cashier/admin account
     * seeded by AdminUserSeeder — no register/reset routes exist or are
     * ever linked to, on purpose.
     */
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
