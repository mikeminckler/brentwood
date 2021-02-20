<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionsController extends Controller
{
    public function editingToggle($type)
    {
        if (!auth()->user()->hasRole(Str::plural($type).'-editor')) {
            return response()->json(['error' => 'You are not en editor'], 403);
        }

        if (session()->has('editing')) {
            session()->forget('editing');
            return response()->json([
                'success' => 'Editing Disabled',
                'editing' => false,
            ]);
        } else {
            session()->put('editing', true);
            return response()->json([
                'success' => 'Editing Enabled',
                'editing' => true,
            ]);
        }
    }
}
