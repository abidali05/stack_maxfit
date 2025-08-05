<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Contracts\API\AuthRepositoryInterface;

class AuthController extends Controller
{
    protected $authRepo;

    public function __construct(AuthRepositoryInterface $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed|regex:/[^A-Za-z0-9]/',
            'number' => 'required|string|unique:users,number',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'terms_conditions' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return $this->unprocessable($validator->errors()->toArray(), 'Validation Error');
        }

        $data = $validator->validated();

        $user = $this->authRepo->register($data);

        $token = $user->createToken('sanctum')->plainTextToken;

        return $this->success(
            [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'Signup Successfull',
            200
        );
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string|exists:users,number',
            'password' => 'required|string|min:6|regex:/[^A-Za-z0-9]/'
        ]);

        if ($validator->fails()) {
            return $this->unprocessable($validator->errors()->toArray(), 'Validation Error');
        }
        $data = $validator->validated();

        $user = $this->authRepo->login($data);

        if (!$user) {
            return $this->error('Invalid credentials', [], 401);
        } else {
            return $this->success($user, 'Login Successfull', 200);
        }
    }

    public function logout(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return $this->unauthorized('Unauthorized', [], 401);
        }
        $this->authRepo->logout($user);
        return $this->success([], 'Logout Successfull', 200);
    }

    public function profile(Request $request)
    {
        $user = $this->authRepo->profile();
        $userDob = $user->dob;

        if ($userDob) {
            $age = Carbon::parse($userDob)->age;

            $genz = $age < 13 ? 'motherfits' : 'fatherfits';

            $user->genz = $genz;
        }
        if ($user) {
            return $this->success($user, 'Profile fetched successfully', 200);
        } else {
            return $this->unauthorized('Unauthorized', [], 401);
        }
    }
}
