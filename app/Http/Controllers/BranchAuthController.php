<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.branches-login'); // Create view
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Custom guard for branches
        if (Auth::guard('branch')->attempt($credentials, $request->filled('remember'))) {
            return redirect()->route('branch.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
}
