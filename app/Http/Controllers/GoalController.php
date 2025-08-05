<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,id',
            'exercise' => 'required|array',
            'exercise.*' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return $this->unprocessable($validator->errors()->toArray(), 'Validation Error');
        }

        try {
            $userId = $request->userId;
            $exerciseData = $request->input('exercise');

            $insertData = [];

            foreach ($exerciseData as $exerciseId => $value) {
                $insertData[] = [
                    'user_id' => $userId,
                    'exercise_id' => $exerciseId,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Goal::insert($insertData);

            return $this->success(null, 'User Exercise Goals added successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), [], 500);
        }
    }
}
