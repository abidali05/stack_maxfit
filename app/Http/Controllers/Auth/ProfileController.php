<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;    
use App\Repositories\Contracts\UserRepositoryInterface;

class ProfileController extends Controller
{
    protected $userRepo;
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    public function profile()
    {
        $user = $this->userRepo->profile();
        return view('auth.profile', compact('user'));
    }
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:15|unique:users,number,' . Auth::user()->id,
            'password' => 'nullable|string|min:6|confirmed|regex:/[^A-Za-z0-9]/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::user()->id,
        ]);

        $user = $this->userRepo->profile_update($data);
        if ($user) {
            Toastr::info('Profile updated successfully!', 'Success');
            return redirect()->route('profile');
        } else {
            Toastr::error('Something went wrong', 'Error');
            return redirect()->route('profile')->with('error', 'Profile not updated.');
        }
    }
}
