<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Models\PageAccess;
use App\Http\Requests\PageAccessValidation;

use App\Models\User;
use App\Models\Role;
use App\Models\Page;

class PageAccessesController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('viewAny', PageAccess::class)) {
            return redirect('/')->with(['error' => 'You do not have permission to view that page']);
        }

        return view('page-accesses.index');
    }

    public function page($id)
    {
        if (!auth()->user()->can('viewAny', PageAccess::class)) {
            return response()->json(['error' => 'You do not have permission to view page accesses'], 403);
        }

        $page = Page::findOrFail($id);
        $page->load('pageAccesses', 'pageAccesses.accessable');

        return response()->json([
            'page' => $page,
        ]);
    }

    public function store(PageAccessValidation $request)
    {
        $page = Page::find(request('page_id'));

        if (requestInput('users')) {
            foreach (requestInput('users') as $user) {
                $user = User::findOrFail(Arr::get($user, 'id'));
                $page->createPageAccess($user);
            }
        }

        if (requestInput('roles')) {
            foreach (requestInput('roles') as $role) {
                $role = Role::findOrFail(Arr::get($role, 'id'));
                $page->createPageAccess($role);
            }
        }

        return response()->json([
            'success' => 'Page Access Created',
            'page' => $page->refresh()->load('pageAccesses', 'pageAccesses.accessable'),
        ]);
    }

    public function destroy($id)
    {
        $page_access = PageAccess::findOrFail($id);

        if (!auth()->user()->can('delete', $page_access)) {
            return response()->json(['error' => 'You do not have permission to remove page accesses'], 403);
        }

        $page_access->delete();

        return response()->json(['success' => 'Page Access Removed']);
    }
}
