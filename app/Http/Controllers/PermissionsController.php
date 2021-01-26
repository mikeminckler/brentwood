<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Models\Permission;
use App\Http\Requests\PermissionValidation;

use App\Models\User;
use App\Models\Role;
use App\Models\Page;

class PermissionsController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('viewAny', Permission::class)) {
            return redirect('/')->with(['error' => 'You do not have permission to view that page']);
        }

        return view('permissions.index');
    }

    public function load()
    {
        if (!auth()->user()->can('viewAny', Permission::class)) {
            return response()->json(['error' => 'You do not have permission to view page accesses'], 403);
        }

        $objectable = Permission::findObjectable(requestInput());
        $objectable->load('permissions', 'permissions.accessable');

        return response()->json([
            'objectable' => $objectable,
        ]);
    }

    public function store(PermissionValidation $request)
    {
        $objectable = Permission::findObjectable(requestInput());

        if (requestInput('users')) {
            foreach (requestInput('users') as $user) {
                $user = User::findOrFail(Arr::get($user, 'id'));
                $objectable->createPermission($user);
            }
        }

        if (requestInput('roles')) {
            foreach (requestInput('roles') as $role) {
                $role = Role::findOrFail(Arr::get($role, 'id'));
                $objectable->createPermission($role);
            }
        }

        return response()->json([
            'success' => 'Permission Created',
            'objectable' => $objectable->refresh()->load('permissions', 'permissions.accessable'),
        ]);
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        if (!auth()->user()->can('delete', $permission)) {
            return response()->json(['error' => 'You do not have permission to remove page accesses'], 403);
        }

        $permission->delete();

        return response()->json(['success' => 'Permission Removed']);
    }
}
