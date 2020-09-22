<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\RoleValidation;
use App\Models\Role;
use App\Models\User;

class RolesController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('viewAny', Role::class)) {
            return redirect('/')->with('error', 'You do not have access to view Roles');
        }

        $roles = Role::all()->load('users');
        return view('roles.index', compact('roles'));
    }

    public function store(RoleValidation $request, $id = null)
    {
        if ($id) {
            $role = Role::findOrFail($id);
            if (!auth()->user()->can('update', $role)) {
                return response()->json(['error' => 'You do not have permission to update that Role'], 403);
            }
        } else {
            if (!auth()->user()->can('create', Role::class)) {
                return response()->json(['error' => 'You do not have permission to create Roles'], 403);
            }
        }

        $role = (new Role)->saveRole($id, requestInput());

        return response()->json([
            'success' => $role->name.' Saved',
            'role' => $role->refresh()->load('users'),
        ]);
    }

    public function search()
    {
        if (!auth()->user()->can('viewAny', Role::class)) {
            return response()->json([ 'error' => 'You do not have permission to search for roles' ], 403);
        }

        return (new Role)->search();
    }

    public function removeUser($id)
    {
        $role = Role::findOrFail($id);
        if (auth()->user()->can('update', $role)) {
            $user = User::findOrFail(requestInput('user_id'));
            $user->removeRole($role);

            return response()->json([
                'success' => $user->name.' Removed From '.$role->name,
            ]);
        } else {
            return redirect()->route('home')->with(['error' => 'You do not have permission to update roles']);
        }
    }
}
