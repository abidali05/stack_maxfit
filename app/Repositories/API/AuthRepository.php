<?php

namespace App\Repositories\API;

use App\Models\User;
use App\Models\PhysicalAssessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Contracts\API\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    public function register(array $data)
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        if ($data['password'] != null) {
            $user->password = Hash::make($data['password']);
        }
        $user->number = $data['number'];
        $user->terms_conditions = $data['terms_conditions'];
        if (isset($data['image']) && $data['image']->isValid()) {
            $imageName = time() . '.' . $data['image']->getClientOriginalExtension();
            $path = $data['image']->storeAs('uploads/profile_images', $imageName, 'public');
            $user->image = $path;
        }
        $user->save();
        return $user;
    }



    public function login(array $data)
    {
        if (!Auth::attempt($data)) {
            return null;
        }
        $user = Auth::user();
        $user->token = $user->createToken('auth:sanctum')->plainTextToken;

        return $user;
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
    }

    public function profile()
    {
        return auth('sanctum')->user();
    }

    public function upateProfile($data)
    {
        $user = auth('sanctum')->user();

        // if (isset($data['image']) && $data['image']->isValid()) {
        //     if ($user->image && Storage::disk('public')->exists($user->image)) {
        //         Storage::disk('public')->delete($user->image);
        //     }
        //     $imageName = time() . '.' . $data['image']->getClientOriginalExtension();
        //     $path = $data['image']->storeAs('uploads/profile_images', $imageName, 'public');
        //     $user->image = $path;
        // }

        // $user->number = $data['number'];
        // $user->email = $data['email'];
        $user->name = $data['name'];
        $user->cnic = $data['cnic'];
        $user->dob = $data['dob'];
        $user->organisation_type = $data['organisation_type'];
        $user->organisation_id = $data['organisation_id'];
        $user->class = $data['class'];
        $user->hobbies = $data['hobbies'];
        $user->sports_played = $data['sports_played'];
        $user->guardian_name = $data['guardian_name'];
        $user->guardian_email = $data['guardian_email'];
        $user->guardian_number = $data['guardian_number'];
        $user->guardian_cnic = $data['guardian_cnic'];
        $user->country = $data['country'];
        $user->state_province = $data['state_province'];
        $user->city = $data['city'];

        $user->save();

        return $user;
    }

    public function physicalAssessment($data) {
        $user = auth('sanctum')->user();
        if ($user->id !== (int) $data['user_id']) {
            return null;
        }

        return PhysicalAssessment::create([
            'user_id' => $data['user_id'],
            'height_cm' => $data['height_cm'],
            'weight_kg' => $data['weight_kg'],
            'bmi' => $data['bmi'],
            'gender' => $data['gender'],
            'body_shape' => $data['body_shape'],
            'required_body_shape' => $data['required_body_shape'],
        ]);

        return $user;
    }
}
