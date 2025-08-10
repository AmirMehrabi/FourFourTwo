<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        \Log::info('Login tokens debug', [
            'session_token' => $request->session()->token(),
            'header_X_CSRF_TOKEN' => $request->header('X-CSRF-TOKEN'),
            'header_X_XSRF_TOKEN' => $request->header('X-XSRF-TOKEN'),
            'input__token' => $request->input('_token'),
            'cookies_present' => array_keys($request->cookies->all()),
        ]);
        $request->authenticate();

        $request->session()->regenerate();
        // Debug & defensive handling for unexpected intended URLs like '/form'
        $intended = $request->session()->get('url.intended');

        if ($intended === '/form') {
            \Log::warning('Login redirect override: unexpected intended URL /form encountered, overriding to dashboard');
            $request->session()->forget('url.intended');
        } else {
            \Log::info('Login redirect debug', [
                'intended' => $intended,
                'previous' => url()->previous(),
                'current' => url()->current(),
                'request_path' => $request->path(),
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
