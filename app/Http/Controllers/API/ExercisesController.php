<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Set;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ExercisesController extends Controller
{
    public function __invoke()
    {
        $exercises = Exercise::get();
        return $this->success($exercises, 'Exercises fetched successfully', 200);
    }

    public function getCategory()
    {
        $category = ExerciseCategory::latest()->get();
        return $this->success($category, 'Category fetched successfully', 200);
    }

    public function getCategoryExercises($id)
    {
        $category = ExerciseCategory::findOrFail($id);
        $exercises = Exercise::where('exercise_category_id', $id)->latest()->get();

        return $this->success([
            'category' => $category,
            'exercises' => $exercises
        ], 'Exercises fetched successfully', 200);
    }

    public function assesmentExercises()
    {
        $user = Auth::user();

        $age = Carbon::parse($user->dob)->age;

        if ($age < 14) {
            $allowedGenz = ['motherfits', 'both'];
        } else {
            $allowedGenz = ['fatherfits', 'both'];
        }

        $sets = Set::with('exercises')->whereIn('genz', $allowedGenz)
            ->get();

        return $this->success($sets, 'Sets fetched successfully', 200);
    }
    
    
}
