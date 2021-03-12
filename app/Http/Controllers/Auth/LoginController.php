<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Requests\LoginRequest;

use Socialite;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();
        auth()->user()->setSessionTimeout();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => 'Login Complete',
                'redirect' => '/',
            ]);
        } else {
            return redirect()->intended('/');
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (request()->expectsJson()) {
            return response()->json([
                //'success' => 'Logout Complete',
                'redirect' => '/login?'.(request('timeout') ? 'timeout=true' : 'logout=true'),
            ]);
        } else {
            return redirect('/login')->with(['success' => 'Logout Complete']);
        }
    }

    /**
     * Redirect to Google for OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->with(['hd' => 'brentwood.ca'])->redirect();
    }

    /**
     * Process the successful response from a Google Login
     */
    public function handleGoogleCallback()
    {
        $user = User::createOrUpdateFromGoogle(Socialite::driver('google')->user());
        $user->setGroupsFromGoogle();
        auth()->login($user);
        auth()->user()->setSessionTimeout();
        return redirect()->intended('/');
    }

    /**
     * Set the intended page that was being requested before we are redirected
     * to the login page
     */
    public function intendedUrl()
    {
        if (request('url')) {
            session()->put('url.intended', request('url'));
        }

        return response()->json();
    }

    /**
     * A json request to see if the authenticated users session is still valid
     * otherwise we send a response code that will log them out
     */
    public function getTimeout()
    {
        if ($this->isTimedOut()) {
            return response()->json(['error' => 'Session Expired'], 419);
        } else {
            return response()->json(['success' => 'Session Valied']);
        }
    }

    protected function isTimedOut()
    {
        $timeout = session()->get('timeout');

        if (!$timeout) {
            true;
        }

        return $timeout->isPast();
    }

    public function setTimeout()
    {
        if (!$this->isTimedOut()) {
            auth()->user()->setSessionTimeout();
        }

        return response()->json([
            'success' => 'Session Updated',
        ]);
    }
}
