<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Http\Requests\UserValidation;

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
        $user = (new User)->saveUser($id, requestInput());

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
}
