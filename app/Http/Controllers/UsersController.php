<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\Chat;
use App\Models\Page;
use App\Http\Requests\UserValidation;

use App\Events\UserBanned;

use App\Utilities\PageResponse;

use App\Mail\EmailVerification;

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
}
