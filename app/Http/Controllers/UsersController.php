<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Chat;
use App\Models\Page;
use App\Http\Requests\UserValidation;
use App\Http\Requests\RequestPasswordResetValidation;
use App\Http\Requests\ResetPasswordValidation;

use App\Events\UserBanned;

use App\Utilities\PageResponse;

use App\Mail\EmailVerification;
use App\Mail\ResetPassword;

class UsersController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('viewAny', User::class)) {
            return redirect('/')->with(['error' => 'You do not have permission to view that page']);
        }

        return view('users.index');
    }

    public function load()
    {
        if (!auth()->user()->can('viewAny', User::class)) {
            return response()->json([
                'error' => 'You do not have permission to load users'
            ], 403);
        }

        $users = User::with('roles')->get();

        return response()->json([
            'users' => $users,
        ]);
    }

    public function store(UserValidation $request, $id = null)
    {
        if ($id) {
            if (!auth()->user()->can('update', User::find($id))) {
                return redirect()->route('home')->with(['error' => 'You do not have permission to udpate that user']);
            }
        }
        $user = (new User)->saveUser(requestInput(), $id);

        return response()->json([
            'success' => $user->name.' Saved',
            'user' => $user,
        ]);
    }

    public function search()
    {
        if (!auth()->user()->can('viewAny', User::class)) {
            return response()->json([ 'error' => 'You do not have permission to search for users' ], 403);
        }

        return (new User)->search();
    }

    public function ban($id)
    {
        $user = User::findOrFail($id);

        if (requestInput('room')) {
            if (!Chat::canModerateRoom(requestInput('room'))) {
                return response()->json([ 'error' => 'You do not have permission to ban for users' ], 403);
            }
        } else {
            if (!auth()->user()->can('ban', $user)) {
                return response()->json([ 'error' => 'You do not have permission to ban for users' ], 403);
            }
        }

        $user->banned_at = now();
        $user->save();

        Chat::where('user_id', $user->id)->delete();

        broadcast(new UserBanned($user));

        return response()->json([
            'success' => $user->name.' Banned',
        ]);
    }

    public function sendEmailVerification($id)
    {
        $user = User::findOrFail($id);

        if (!auth()->user()->can('update', $user)) {
            return response()->json(['error' => 'You do not have access to that user']);
        }

        if ($user->email_verified_at) {
            return response()->json(['error' => 'Your email has already been confirmed'], 422);
        }

        Mail::to($user->email)
                ->queue(new EmailVerification($user));

        return response()->json([
            'success' => 'Email Verification Sent',
        ]);
    }

    public function verifyEmail($id)
    {
        if (! request()->hasValidSignature()) {
            abort(401);
        }

        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            return (new PageResponse)->view(Page::getHomePage(), 'pages.view')->with(['error' => 'Your email has already been confirmed']);
        }

        $user->email_verified_at = now();
        $user->save();

        return (new PageResponse)->view(Page::getHomePage(), 'pages.view')->with(['success' => 'Email Verification Complete']);
    }

    public function requestPasswordReset(RequestPasswordResetValidation $request)
    {
        $user = User::where('email', requestInput('email'))->first();

        if (!$user) {
            return response()->json(['error' => 'There is no user with that email address'], 401);
        }

        if ($user->oauth_id) {
            return response()->json(['error' => 'Please reset your password via Google'], 422);
        }

        Mail::to($user->email)
                ->queue(new ResetPassword($user));

        return response()->json([
            'success' => 'Password Reset Email Sent',
        ]);
    }

    public function viewResetPassword($id)
    {
        if (! request()->hasValidSignature()) {
            abort(401);
        }
        
        $user = User::findOrFail($id);

        return view('auth.reset-password', compact('user'));
    }

    public function resetPassword(ResetPasswordValidation $request, $id)
    {
        $user = User::findOrFail($id);

        $user->password = Hash::make(requestInput('password'));
        $user->save();
        auth()->login($user);
        auth()->user()->setSessionTimeout();

        return response()->json([
            'success' => 'Password Reset',
        ]);
    }
}
