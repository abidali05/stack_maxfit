<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserAssessmentExerciseController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,id',
            'exerice' => 'required|array',
            'exerice.*' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return $this->unprocessable($validator->errors()->toArray(), 'Validation Error');
        }

        try {
            $userId = $request->userId;
            $exerciseData = $request->input('exerice');

            $insertData = [];

            foreach ($exerciseData as $exerciseId => $value) {
                // Optionally validate existence of exercise_id here again if needed
                $insertData[] = [
                    'user_id' => $userId,
                    'exercise_id' => $exerciseId,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('user_exercise_assessment')->insert($insertData);

            return $this->success(null, 'User Exercise Assessments added successfully', 200);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), [], 500);
        }

        return $this->success($assessment, 'User Assessment added successfully', 200);
    }
}
