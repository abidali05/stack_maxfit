<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\UserRepositoryInterface;
use Brian2694\Toastr\Facades\Toastr;

class UsersController extends Controller
{
    protected $userRepo;
    public function __construct(UserRepositoryInterface $userRepo){
        $this->userRepo = $userRepo;
    }

    public function index()
    {
        $users = $this->userRepo->getusers();
        return view('users.index', compact('users'));
    }

    public function create(){
        $data = $this->userRepo->get_create_data();
        return view('users.create', compact('data'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'number' => 'required|string|max:15|unique:users,number',
            'role' => 'required|in:admin,user',
            'password' => 'nullable|string|min:6|confirmed|regex:/[^A-Za-z0-9]/',
            'province' => 'required|exists:provinces,id',
            'city' => 'required|exists:cities,id',
            'organisation_type' => 'required|exists:organisation_types,id',
            'organisation' => 'required|exists:organisations,id',
            'cnic' => 'required|digits_between:13,15',
            'dob' => 'required|date',
            'class' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female',
            'hobbies' => 'required|string',
            'sports_played' => 'required|string',
            'guardian_name' => 'required|string|max:255',
            'guardian_email' => 'required|email',
            'guardian_phone' => 'required|string|max:15',
            'guardian_dob' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
        ]);


        $this->userRepo->store_user($validated);
        Toastr::success('User created successfully', 'Success');
        return redirect()->route('users.index');
    }

    public function edit($id)
    {
        $user = $this->userRepo->get_user($id);
        if(!$user){
        Toastr::error('User not found', 'Error');
        return redirect()->route('users.index');
        }
        $data = $this->userRepo->get_create_data();
        return view('users.edit', compact('user', 'data'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'number' => 'required|string|max:15|unique:users,number,' . $id,
            'role' => 'required|in:admin,user',
            'password' => 'nullable|string|min:6|confirmed|regex:/[^A-Za-z0-9]/',
            'province' => 'required|exists:provinces,id',
            'city' => 'required|exists:cities,id',
            'organisation_type' => 'required|exists:organisation_types,id',
            'organisation' => 'required|exists:organisations,id',
            'cnic' => 'required|digits_between:13,15',
            'dob' => 'required|date',
            'class' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female',
            'hobbies' => 'required|string',
            'sports_played' => 'required|string',
            'guardian_name' => 'required|string|max:255',
            'guardian_email' => 'required|email',
            'guardian_phone' => 'required|string|max:15',
            'guardian_dob' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
        ]);
        $this->userRepo->update_user($validated, $id);
        Toastr::success('User updated successfully', 'Success');
        return redirect()->route('users.index');
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->userRepo->delete_user($request->id);
        if(!$user){
            Toastr::error('User not found', 'Error');
            return redirect()->route('users.index');
        }
        Toastr::success('User deleted successfully', 'Success');
        return redirect()->route('users.index');
    }
    
}
