<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Chat;
use App\Http\Requests\UserValidation;

use App\Events\UserBanned;

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
}
