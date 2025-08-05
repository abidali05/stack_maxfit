<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Contracts\UserRepositoryInterface;

class ProfileController extends Controller
{
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function updateProfile(Request $request)
    {
        try {
            // Get the current user by their ID
            $user = User::findOrFail($request->user_id);

            // Validate the request data
            $data = $request->validate([
                'name' => 'nullable|string|max:255',
                'number' => 'nullable|string|max:15|unique:users,number,' . $user->id,
                'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'user_id' => 'nullable|integer|exists:users,id',
            ]);

            // Call the userRepo method to update the user profile
            $user = $this->userRepo->user_profile_update($data);

            // Return success response if user is updated
            return $this->success($user, 'User updated successfully', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle user not found exception
            return $this->error('User not found.', ['user_id' => ['The user with the provided ID was not found.']], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return $this->error('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Something went wrong: ' . $e->getMessage(), [], 500);
        }
    }


    public function checkUserName(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        $usernameExists = User::where('username', $request->username)->exists();

        if ($usernameExists) {
            return response()->json([
                'message' => 'Username is already taken.',
                'status' => 'error',
            ], 400);
        }

        return response()->json([
            'message' => 'Username is available.',
            'status' => 'success',
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
        'old_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/[^A-Za-z0-9]/'
            ],
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

                // Check if old password matches
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Old password is incorrect.',
            ], 403);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }
}
